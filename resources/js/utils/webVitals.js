/**
 * Core Web Vitals Reporter
 * 
 * Why it matters:
 * - Google uses Core Web Vitals for search rankings
 * - Real User Monitoring (RUM) provides actual performance data
 * - Helps identify issues affecting real users
 * 
 * Metrics tracked:
 * - LCP (Largest Contentful Paint): Loading performance (< 2.5s = good)
 * - FID (First Input Delay): Interactivity (< 100ms = good)
 * - CLS (Cumulative Layout Shift): Visual stability (< 0.1 = good)
 * - TTFB (Time to First Byte): Server response time (< 800ms = good)
 * - FCP (First Contentful Paint): First paint (< 1.8s = good)
 * 
 * Usage:
 * import { initWebVitals } from './utils/webVitals';
 * initWebVitals();
 */

// Web Vitals thresholds (Google's recommendations)
const THRESHOLDS = {
  LCP: { good: 2500, needsImprovement: 4000 },
  FID: { good: 100, needsImprovement: 300 },
  CLS: { good: 0.1, needsImprovement: 0.25 },
  FCP: { good: 1800, needsImprovement: 3000 },
  TTFB: { good: 800, needsImprovement: 1800 },
  INP: { good: 200, needsImprovement: 500 }, // Interaction to Next Paint
};

/**
 * Rate a metric value as good, needs-improvement, or poor
 */
function rateMetric(name, value) {
  const threshold = THRESHOLDS[name];
  if (!threshold) return 'unknown';
  
  if (value <= threshold.good) return 'good';
  if (value <= threshold.needsImprovement) return 'needs-improvement';
  return 'poor';
}

/**
 * Send metric to analytics endpoint
 */
function sendToAnalytics(metric) {
  const body = {
    name: metric.name,
    value: metric.value,
    rating: metric.rating,
    delta: metric.delta,
    id: metric.id,
    navigationType: metric.navigationType,
    url: window.location.href,
    timestamp: new Date().toISOString(),
    userAgent: navigator.userAgent,
    connection: navigator.connection ? {
      effectiveType: navigator.connection.effectiveType,
      downlink: navigator.connection.downlink,
      rtt: navigator.connection.rtt,
    } : null,
  };

  // Log to console in development
  if (import.meta.env?.DEV || window.location.hostname === 'localhost') {
    const rating = rateMetric(metric.name, metric.value);
    const color = rating === 'good' ? '#10b981' : rating === 'needs-improvement' ? '#f59e0b' : '#ef4444';
    console.log(
      `%c[Web Vitals] ${metric.name}: ${Math.round(metric.value)}${metric.name === 'CLS' ? '' : 'ms'} (${rating})`,
      `color: ${color}; font-weight: bold;`
    );
  }

  // Send to server endpoint (if configured)
  const endpoint = import.meta.env?.VITE_WEB_VITALS_ENDPOINT || '/api/web-vitals';
  
  // Use sendBeacon for reliable delivery
  if (navigator.sendBeacon) {
    try {
      navigator.sendBeacon(endpoint, JSON.stringify(body));
    } catch (e) {
      // Fallback to fetch if sendBeacon fails
      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
        keepalive: true,
      }).catch(() => {
        // Silently fail - don't break the app for analytics
      });
    }
  }
}

/**
 * Get LCP (Largest Contentful Paint)
 * Measures loading performance
 */
function getLCP(callback) {
  if (!('PerformanceObserver' in window)) return;

  let lastEntry = null;

  const observer = new PerformanceObserver((entryList) => {
    const entries = entryList.getEntries();
    lastEntry = entries[entries.length - 1];
  });

  observer.observe({ type: 'largest-contentful-paint', buffered: true });

  // Report on page hide or visibility change
  const reportLCP = () => {
    if (lastEntry) {
      callback({
        name: 'LCP',
        value: lastEntry.startTime,
        rating: rateMetric('LCP', lastEntry.startTime),
        delta: lastEntry.startTime,
        id: generateId(),
        navigationType: getNavigationType(),
      });
    }
    observer.disconnect();
  };

  // Use visibilitychange or pagehide
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') reportLCP();
  }, { once: true });

  // Also report on beforeunload for older browsers
  window.addEventListener('beforeunload', reportLCP, { once: true });
}

/**
 * Get FID (First Input Delay)
 * Measures interactivity
 */
function getFID(callback) {
  if (!('PerformanceObserver' in window)) return;

  const observer = new PerformanceObserver((entryList) => {
    const firstEntry = entryList.getEntries()[0];
    
    callback({
      name: 'FID',
      value: firstEntry.processingStart - firstEntry.startTime,
      rating: rateMetric('FID', firstEntry.processingStart - firstEntry.startTime),
      delta: firstEntry.processingStart - firstEntry.startTime,
      id: generateId(),
      navigationType: getNavigationType(),
    });
    
    observer.disconnect();
  });

  observer.observe({ type: 'first-input', buffered: true });
}

/**
 * Get CLS (Cumulative Layout Shift)
 * Measures visual stability
 */
function getCLS(callback) {
  if (!('PerformanceObserver' in window)) return;

  let clsValue = 0;
  let sessionValue = 0;
  let sessionEntries = [];

  const observer = new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
      // Only count layout shifts without recent user input
      if (!entry.hadRecentInput) {
        const firstSessionEntry = sessionEntries[0];
        const lastSessionEntry = sessionEntries[sessionEntries.length - 1];

        // If entry is within 1s of last entry and 5s of first entry, add to session
        if (
          sessionValue &&
          entry.startTime - lastSessionEntry.startTime < 1000 &&
          entry.startTime - firstSessionEntry.startTime < 5000
        ) {
          sessionValue += entry.value;
          sessionEntries.push(entry);
        } else {
          // Start new session
          sessionValue = entry.value;
          sessionEntries = [entry];
        }

        // Update CLS if this session is larger
        if (sessionValue > clsValue) {
          clsValue = sessionValue;
        }
      }
    }
  });

  observer.observe({ type: 'layout-shift', buffered: true });

  // Report on page hide
  const reportCLS = () => {
    callback({
      name: 'CLS',
      value: clsValue,
      rating: rateMetric('CLS', clsValue),
      delta: clsValue,
      id: generateId(),
      navigationType: getNavigationType(),
    });
    observer.disconnect();
  };

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') reportCLS();
  }, { once: true });
}

/**
 * Get FCP (First Contentful Paint)
 * Measures first paint timing
 */
function getFCP(callback) {
  if (!('PerformanceObserver' in window)) return;

  const observer = new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
      if (entry.name === 'first-contentful-paint') {
        callback({
          name: 'FCP',
          value: entry.startTime,
          rating: rateMetric('FCP', entry.startTime),
          delta: entry.startTime,
          id: generateId(),
          navigationType: getNavigationType(),
        });
        observer.disconnect();
      }
    }
  });

  observer.observe({ type: 'paint', buffered: true });
}

/**
 * Get TTFB (Time to First Byte)
 * Measures server response time
 */
function getTTFB(callback) {
  const nav = performance.getEntriesByType('navigation')[0];
  if (!nav) return;

  const ttfb = nav.responseStart - nav.requestStart;

  callback({
    name: 'TTFB',
    value: ttfb,
    rating: rateMetric('TTFB', ttfb),
    delta: ttfb,
    id: generateId(),
    navigationType: getNavigationType(),
  });
}

/**
 * Get INP (Interaction to Next Paint)
 * Measures responsiveness
 */
function getINP(callback) {
  if (!('PerformanceObserver' in window)) return;

  let maxDuration = 0;

  const observer = new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
      if (entry.duration > maxDuration) {
        maxDuration = entry.duration;
      }
    }
  });

  try {
    observer.observe({ type: 'event', buffered: true, durationThreshold: 16 });
  } catch (e) {
    // INP not supported in all browsers
    return;
  }

  // Report on page hide
  const reportINP = () => {
    if (maxDuration > 0) {
      callback({
        name: 'INP',
        value: maxDuration,
        rating: rateMetric('INP', maxDuration),
        delta: maxDuration,
        id: generateId(),
        navigationType: getNavigationType(),
      });
    }
    observer.disconnect();
  };

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') reportINP();
  }, { once: true });
}

/**
 * Generate unique ID for metric
 */
function generateId() {
  return `v1-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Get navigation type
 */
function getNavigationType() {
  if (!performance.getEntriesByType) return 'unknown';
  const nav = performance.getEntriesByType('navigation')[0];
  return nav ? nav.type : 'unknown';
}

/**
 * Initialize Web Vitals tracking
 */
export function initWebVitals(options = {}) {
  const callback = options.callback || sendToAnalytics;

  // Wait for page load to start measuring
  if (document.readyState === 'complete') {
    startMeasuring(callback);
  } else {
    window.addEventListener('load', () => startMeasuring(callback));
  }
}

function startMeasuring(callback) {
  // Delay slightly to ensure accurate measurements
  requestAnimationFrame(() => {
    getLCP(callback);
    getFID(callback);
    getCLS(callback);
    getFCP(callback);
    getTTFB(callback);
    getINP(callback);
  });
}

/**
 * Get current Web Vitals summary
 * Useful for debugging or displaying in UI
 */
export function getWebVitalsSummary() {
  return new Promise((resolve) => {
    const metrics = {};
    let count = 0;
    const total = 4; // LCP, FID, CLS, FCP

    const handleMetric = (metric) => {
      metrics[metric.name] = {
        value: metric.value,
        rating: metric.rating,
      };
      count++;
      if (count >= total) {
        resolve(metrics);
      }
    };

    getLCP(handleMetric);
    getFID(handleMetric);
    getCLS(handleMetric);
    getFCP(handleMetric);

    // Timeout fallback
    setTimeout(() => resolve(metrics), 5000);
  });
}

export default { initWebVitals, getWebVitalsSummary };
