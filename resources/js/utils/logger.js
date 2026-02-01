/**
 * Logger Utility
 * 
 * A centralized logging utility that can be disabled in production.
 * Provides consistent logging format and can be easily toggled.
 * 
 * @example
 * import { logger } from '@/utils/logger';
 * logger.info('User logged in', { userId: 123 });
 * logger.error('Payment failed', error);
 */

const isDevelopment = import.meta.env.DEV;
const isDebugMode = import.meta.env.VITE_DEBUG === 'true';

// Log levels
const LOG_LEVELS = {
    DEBUG: 0,
    INFO: 1,
    WARN: 2,
    ERROR: 3,
    NONE: 4,
};

// Current log level (can be set via environment variable)
const currentLevel = isDevelopment ? LOG_LEVELS.DEBUG : LOG_LEVELS.ERROR;

/**
 * Format log message with timestamp and context
 */
const formatMessage = (level, message, context = null) => {
    const timestamp = new Date().toISOString();
    const prefix = `[${timestamp}] [${level}]`;
    
    if (context) {
        return [prefix, message, context];
    }
    return [prefix, message];
};

/**
 * Send log to remote service (for error tracking)
 */
const sendToRemote = async (level, message, context) => {
    // Only send errors in production
    if (!isDevelopment && level === 'ERROR') {
        try {
            await fetch('/api/client-errors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    level,
                    message,
                    context,
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    timestamp: new Date().toISOString(),
                }),
            });
        } catch {
            // Silently fail
        }
    }
};

export const logger = {
    /**
     * Debug level logging - only in development
     */
    debug(message, context = null) {
        if (currentLevel <= LOG_LEVELS.DEBUG) {
            console.debug(...formatMessage('DEBUG', message, context));
        }
    },

    /**
     * Info level logging
     */
    info(message, context = null) {
        if (currentLevel <= LOG_LEVELS.INFO) {
            console.info(...formatMessage('INFO', message, context));
        }
    },

    /**
     * Warning level logging
     */
    warn(message, context = null) {
        if (currentLevel <= LOG_LEVELS.WARN) {
            console.warn(...formatMessage('WARN', message, context));
        }
    },

    /**
     * Error level logging - always logged, sent to remote in production
     */
    error(message, context = null) {
        if (currentLevel <= LOG_LEVELS.ERROR) {
            console.error(...formatMessage('ERROR', message, context));
            sendToRemote('ERROR', message, context);
        }
    },

    /**
     * Group related logs together
     */
    group(label) {
        if (isDevelopment) {
            console.group(label);
        }
    },

    /**
     * End log group
     */
    groupEnd() {
        if (isDevelopment) {
            console.groupEnd();
        }
    },

    /**
     * Time a function execution
     */
    time(label) {
        if (isDevelopment) {
            console.time(label);
        }
    },

    /**
     * End timer and log duration
     */
    timeEnd(label) {
        if (isDevelopment) {
            console.timeEnd(label);
        }
    },

    /**
     * Log a table (useful for arrays/objects)
     */
    table(data) {
        if (isDevelopment) {
            console.table(data);
        }
    },

    /**
     * Assert a condition
     */
    assert(condition, message) {
        if (isDevelopment) {
            console.assert(condition, message);
        }
    },
};

// Export log levels for configuration
export { LOG_LEVELS };

// Default export
export default logger;
