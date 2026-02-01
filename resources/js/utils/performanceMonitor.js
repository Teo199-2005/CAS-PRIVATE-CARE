/**
 * CAS Private Care - Performance Monitor
 * 
 * Client-side performance monitoring utility providing:
 * - Core Web Vitals tracking (LCP, FID, CLS, FCP, TTFB)
 * - Custom performance marks and measures
 * - Network request monitoring
 * - Memory usage tracking
 * - Performance budgets and alerts
 * - Automatic reporting to backend
 */

import { ref, readonly } from 'vue';

// Performance metrics state
const metrics = ref({
    lcp: null,  // Largest Contentful Paint
    fid: null,  // First Input Delay
    cls: null,  // Cumulative Layout Shift
    fcp: null,  // First Contentful Paint
    ttfb: null, // Time to First Byte
    tti: null,  // Time to Interactive (estimated)
});

const customMetrics = ref({});
const networkRequests = ref([]);
const memoryUsage = ref(null);
const isInitialized = ref(false);

// Performance budgets (thresholds for "good" performance)
const performanceBudgets = {
    lcp: 2500,   // ms - Good: <2.5s, Needs improvement: 2.5s-4s, Poor: >4s
    fid: 100,    // ms - Good: <100ms
    cls: 0.1,    // score - Good: <0.1
    fcp: 1800,   // ms - Good: <1.8s
    ttfb: 800,   // ms - Good: <800ms
};

// Configuration
let config = {
    reportEndpoint: '/api/v1/performance-metrics',
    enableReporting: true,
    sampleRate: 0.1, // Report 10% of sessions
    debug: false,
};

/**
 * Initialize performance monitoring
 */
export function initPerformanceMonitor(options = {}) {
    if (typeof window === 'undefined') return;
    if (isInitialized.value) return;

    config = { ...config, ...options };

    // Determine if this session should be sampled
    const shouldSample = Math.random() < config.sampleRate;

    if (config.debug) {
        console.log('[PerfMonitor] Initializing...', { shouldSample });
    }

    // Core Web Vitals observation
    observeCoreWebVitals();

    // Navigation timing
    measureNavigationTiming();

    // Network request monitoring
    observeNetworkRequests();

    // Memory monitoring (if available)
    if ('memory' in performance) {
        observeMemory();
    }

    // Long tasks monitoring
    observeLongTasks();

    // Report on page unload
    if (shouldSample && config.enableReporting) {
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                reportMetrics();
            }
        });
    }

    isInitialized.value = true;
}

/**
 * Observe Core Web Vitals using PerformanceObserver
 */
function observeCoreWebVitals() {
    // Largest Contentful Paint (LCP)
    try {
        const lcpObserver = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            const lastEntry = entries[entries.length - 1];
            metrics.value.lcp = Math.round(lastEntry.startTime);
            
            if (config.debug) {
                console.log('[PerfMonitor] LCP:', metrics.value.lcp, 'ms');
            }
        });
        lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
    } catch (e) {
        // LCP not supported
    }

    // First Input Delay (FID)
    try {
        const fidObserver = new PerformanceObserver((entryList) => {
            const firstEntry = entryList.getEntries()[0];
            metrics.value.fid = Math.round(firstEntry.processingStart - firstEntry.startTime);
            
            if (config.debug) {
                console.log('[PerfMonitor] FID:', metrics.value.fid, 'ms');
            }
        });
        fidObserver.observe({ type: 'first-input', buffered: true });
    } catch (e) {
        // FID not supported
    }

    // Cumulative Layout Shift (CLS)
    try {
        let clsValue = 0;
        let clsEntries = [];

        const clsObserver = new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsEntries.push(entry);
                    clsValue += entry.value;
                }
            }
            metrics.value.cls = Math.round(clsValue * 1000) / 1000;
            
            if (config.debug) {
                console.log('[PerfMonitor] CLS:', metrics.value.cls);
            }
        });
        clsObserver.observe({ type: 'layout-shift', buffered: true });
    } catch (e) {
        // CLS not supported
    }

    // First Contentful Paint (FCP)
    try {
        const fcpObserver = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntriesByName('first-contentful-paint');
            if (entries.length > 0) {
                metrics.value.fcp = Math.round(entries[0].startTime);
                
                if (config.debug) {
                    console.log('[PerfMonitor] FCP:', metrics.value.fcp, 'ms');
                }
            }
        });
        fcpObserver.observe({ type: 'paint', buffered: true });
    } catch (e) {
        // Paint timing not supported
    }
}

/**
 * Measure navigation timing
 */
function measureNavigationTiming() {
    try {
        const [nav] = performance.getEntriesByType('navigation');
        if (nav) {
            metrics.value.ttfb = Math.round(nav.responseStart - nav.requestStart);
            
            if (config.debug) {
                console.log('[PerfMonitor] TTFB:', metrics.value.ttfb, 'ms');
            }
        }
    } catch (e) {
        // Navigation timing not supported
    }
}

/**
 * Observe network requests
 */
function observeNetworkRequests() {
    try {
        const resourceObserver = new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (entry.initiatorType === 'fetch' || entry.initiatorType === 'xmlhttprequest') {
                    networkRequests.value.push({
                        name: entry.name,
                        duration: Math.round(entry.duration),
                        transferSize: entry.transferSize,
                        timestamp: Date.now(),
                    });

                    // Keep only last 50 requests
                    if (networkRequests.value.length > 50) {
                        networkRequests.value.shift();
                    }
                }
            }
        });
        resourceObserver.observe({ type: 'resource', buffered: true });
    } catch (e) {
        // Resource timing not supported
    }
}

/**
 * Observe memory usage
 */
function observeMemory() {
    const updateMemory = () => {
        if (performance.memory) {
            memoryUsage.value = {
                usedJSHeapSize: Math.round(performance.memory.usedJSHeapSize / 1048576), // MB
                totalJSHeapSize: Math.round(performance.memory.totalJSHeapSize / 1048576), // MB
                jsHeapSizeLimit: Math.round(performance.memory.jsHeapSizeLimit / 1048576), // MB
            };
        }
    };

    updateMemory();
    setInterval(updateMemory, 30000); // Update every 30 seconds
}

/**
 * Observe long tasks (>50ms)
 */
function observeLongTasks() {
    try {
        const longTaskObserver = new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (config.debug) {
                    console.log('[PerfMonitor] Long Task:', Math.round(entry.duration), 'ms');
                }
            }
        });
        longTaskObserver.observe({ type: 'longtask', buffered: true });
    } catch (e) {
        // Long task timing not supported
    }
}

/**
 * Create a custom performance mark
 */
export function mark(name) {
    try {
        performance.mark(`cas-${name}`);
    } catch (e) {
        // Mark not supported
    }
}

/**
 * Measure duration between two marks
 */
export function measure(name, startMark, endMark) {
    try {
        const startName = startMark ? `cas-${startMark}` : undefined;
        const endName = endMark ? `cas-${endMark}` : undefined;
        
        performance.measure(`cas-${name}`, startName, endName);
        
        const measures = performance.getEntriesByName(`cas-${name}`);
        if (measures.length > 0) {
            const duration = Math.round(measures[measures.length - 1].duration);
            customMetrics.value[name] = duration;
            
            if (config.debug) {
                console.log(`[PerfMonitor] Measure ${name}:`, duration, 'ms');
            }
            
            return duration;
        }
    } catch (e) {
        // Measure not supported
    }
    return null;
}

/**
 * Report metrics to backend
 */
async function reportMetrics() {
    if (!config.enableReporting) return;

    const payload = {
        url: window.location.href,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        connection: navigator.connection?.effectiveType || 'unknown',
        metrics: metrics.value,
        customMetrics: customMetrics.value,
        memory: memoryUsage.value,
        budgetStatus: getBudgetStatus(),
    };

    try {
        // Use sendBeacon for reliable delivery on page unload
        if (navigator.sendBeacon) {
            navigator.sendBeacon(
                config.reportEndpoint,
                JSON.stringify(payload)
            );
        } else {
            await fetch(config.reportEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                keepalive: true,
            });
        }
    } catch (e) {
        // Silently fail
    }
}

/**
 * Get budget status for each metric
 */
function getBudgetStatus() {
    const status = {};
    
    for (const [metric, budget] of Object.entries(performanceBudgets)) {
        const value = metrics.value[metric];
        if (value !== null) {
            status[metric] = {
                value,
                budget,
                status: value <= budget ? 'good' : value <= budget * 1.5 ? 'needs-improvement' : 'poor',
            };
        }
    }
    
    return status;
}

/**
 * Get overall performance score (0-100)
 */
export function getPerformanceScore() {
    let score = 100;
    let metricsCount = 0;

    for (const [metric, budget] of Object.entries(performanceBudgets)) {
        const value = metrics.value[metric];
        if (value !== null) {
            metricsCount++;
            const ratio = value / budget;
            if (ratio > 2) {
                score -= 20;
            } else if (ratio > 1.5) {
                score -= 10;
            } else if (ratio > 1) {
                score -= 5;
            }
        }
    }

    return Math.max(0, Math.min(100, score));
}

/**
 * Composable for using performance monitor in components
 */
export function usePerformanceMonitor() {
    return {
        metrics: readonly(metrics),
        customMetrics: readonly(customMetrics),
        networkRequests: readonly(networkRequests),
        memoryUsage: readonly(memoryUsage),
        isInitialized: readonly(isInitialized),
        mark,
        measure,
        getPerformanceScore,
        getBudgetStatus,
    };
}

export default {
    initPerformanceMonitor,
    mark,
    measure,
    usePerformanceMonitor,
    getPerformanceScore,
};
