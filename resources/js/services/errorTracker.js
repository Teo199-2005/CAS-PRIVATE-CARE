/**
 * Error Tracking Service
 * ======================
 * 
 * Production error tracking and monitoring for the frontend.
 * Captures errors, logs them, and can integrate with external
 * services like Sentry, LogRocket, etc.
 * 
 * @version 1.0.0
 * @since 2026-01-28
 */

class ErrorTracker {
  constructor() {
    this.isProduction = import.meta.env.PROD;
    this.errorBuffer = [];
    this.maxBufferSize = 50;
    this.flushInterval = 30000; // 30 seconds
    this.userContext = null;
    this.initialized = false;
  }

  /**
   * Initialize error tracking
   */
  init(options = {}) {
    if (this.initialized) return;

    this.options = {
      endpoint: '/api/errors/log',
      captureUnhandled: true,
      captureConsoleErrors: true,
      sampleRate: 1.0, // 1.0 = 100% of errors
      ...options,
    };

    if (this.options.captureUnhandled) {
      this.setupGlobalHandlers();
    }

    // Start flush interval
    if (this.isProduction) {
      setInterval(() => this.flush(), this.flushInterval);
    }

    this.initialized = true;
  }

  /**
   * Set user context for error reports
   */
  setUser(user) {
    this.userContext = user ? {
      id: user.id,
      email: user.email,
      role: user.role || user.type,
    } : null;
  }

  /**
   * Capture an error
   */
  captureError(error, context = {}) {
    // Sample rate check
    if (Math.random() > (this.options?.sampleRate || 1.0)) {
      return;
    }

    const errorReport = this.buildErrorReport(error, context);
    
    if (this.isProduction) {
      this.errorBuffer.push(errorReport);
      
      // Flush if buffer is full
      if (this.errorBuffer.length >= this.maxBufferSize) {
        this.flush();
      }
    } else {
      // In development, just log to console
      console.group('%c🚨 Error Captured', 'color: red; font-weight: bold;');
      console.error('Error:', error);
      console.log('Context:', context);
      console.log('Report:', errorReport);
      console.groupEnd();
    }

    return errorReport;
  }

  /**
   * Capture a message (non-error)
   */
  captureMessage(message, level = 'info', context = {}) {
    const report = {
      type: 'message',
      level,
      message,
      context,
      timestamp: new Date().toISOString(),
      url: window.location.href,
      user: this.userContext,
    };

    if (this.isProduction && level === 'error') {
      this.errorBuffer.push(report);
    } else {
      console.log(`[${level.toUpperCase()}]`, message, context);
    }

    return report;
  }

  /**
   * Capture a Vue error
   */
  captureVueError(err, instance, info) {
    const context = {
      componentName: instance?.$options?.name || 'Unknown',
      lifecycleHook: info,
      propsData: instance?.$props,
    };

    return this.captureError(err, context);
  }

  /**
   * Build structured error report
   */
  buildErrorReport(error, context = {}) {
    return {
      type: 'error',
      level: 'error',
      timestamp: new Date().toISOString(),
      
      // Error details
      error: {
        name: error.name || 'Error',
        message: error.message || String(error),
        stack: error.stack,
      },
      
      // Environment
      environment: {
        url: window.location.href,
        userAgent: navigator.userAgent,
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight,
        },
        language: navigator.language,
        platform: navigator.platform,
        online: navigator.onLine,
      },
      
      // User context
      user: this.userContext,
      
      // Additional context
      context,
      
      // Session info
      session: {
        id: this.getSessionId(),
        startTime: performance.timing?.navigationStart,
        duration: Date.now() - (performance.timing?.navigationStart || Date.now()),
      },
      
      // Performance data
      performance: this.getPerformanceData(),
    };
  }

  /**
   * Get or create session ID
   */
  getSessionId() {
    let sessionId = sessionStorage.getItem('errorTrackingSessionId');
    if (!sessionId) {
      sessionId = 'sess_' + Math.random().toString(36).substring(2, 15);
      sessionStorage.setItem('errorTrackingSessionId', sessionId);
    }
    return sessionId;
  }

  /**
   * Get performance metrics
   */
  getPerformanceData() {
    if (!window.performance) return null;

    const timing = performance.timing;
    const navigation = performance.getEntriesByType?.('navigation')?.[0];

    return {
      domComplete: timing?.domComplete - timing?.navigationStart,
      loadTime: timing?.loadEventEnd - timing?.navigationStart,
      firstPaint: navigation?.startTime,
      memory: performance.memory ? {
        usedJSHeapSize: performance.memory.usedJSHeapSize,
        totalJSHeapSize: performance.memory.totalJSHeapSize,
      } : null,
    };
  }

  /**
   * Setup global error handlers
   */
  setupGlobalHandlers() {
    // Unhandled errors
    window.addEventListener('error', (event) => {
      this.captureError(event.error || new Error(event.message), {
        type: 'unhandled',
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
      });
    });

    // Unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
      const error = event.reason instanceof Error 
        ? event.reason 
        : new Error(String(event.reason));
      
      this.captureError(error, {
        type: 'unhandledRejection',
      });
    });

    // Console.error interception
    if (this.options?.captureConsoleErrors) {
      const originalConsoleError = console.error;
      console.error = (...args) => {
        // Don't capture our own logs
        if (args[0]?.includes?.('Error Captured')) {
          originalConsoleError.apply(console, args);
          return;
        }

        const error = args[0] instanceof Error 
          ? args[0] 
          : new Error(args.map(String).join(' '));
        
        this.captureError(error, {
          type: 'consoleError',
          args: args.map(arg => {
            try {
              return typeof arg === 'object' ? JSON.stringify(arg) : String(arg);
            } catch {
              return '[Circular or non-serializable]';
            }
          }),
        });

        originalConsoleError.apply(console, args);
      };
    }
  }

  /**
   * Flush error buffer to server
   */
  async flush() {
    if (this.errorBuffer.length === 0) return;

    const errors = [...this.errorBuffer];
    this.errorBuffer = [];

    try {
      const response = await fetch(this.options?.endpoint || '/api/errors/log', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ errors }),
        keepalive: true, // Allows request to complete even if page is unloading
      });

      if (!response.ok) {
        // Put errors back in buffer for retry
        this.errorBuffer = [...errors, ...this.errorBuffer].slice(0, this.maxBufferSize);
      }
    } catch (e) {
      // Put errors back in buffer for retry
      this.errorBuffer = [...errors, ...this.errorBuffer].slice(0, this.maxBufferSize);
    }
  }

  /**
   * Create a Vue plugin
   */
  createVuePlugin() {
    const tracker = this;

    return {
      install(app) {
        // Global error handler
        app.config.errorHandler = (err, instance, info) => {
          tracker.captureVueError(err, instance, info);
          
          // Still log to console in development
          if (!tracker.isProduction) {
            console.error(err);
          }
        };

        // Warning handler (development only)
        if (!tracker.isProduction) {
          app.config.warnHandler = (msg, instance, trace) => {
            console.warn('[Vue Warn]', msg, trace);
          };
        }

        // Provide error tracker globally
        app.provide('errorTracker', tracker);
        app.config.globalProperties.$errorTracker = tracker;
      },
    };
  }
}

// Export singleton instance
export const errorTracker = new ErrorTracker();

// Vue plugin factory
export function createErrorTracker(options = {}) {
  errorTracker.init(options);
  return errorTracker.createVuePlugin();
}

export default errorTracker;
