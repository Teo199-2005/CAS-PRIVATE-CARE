/**
 * Performance utilities for Vue applications
 * Includes debounce, throttle, memoization, and performance monitoring
 */

/**
 * Debounce function execution
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(fn, delay = 300) {
    let timeoutId;
    
    const debounced = function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), delay);
    };
    
    debounced.cancel = () => clearTimeout(timeoutId);
    
    return debounced;
}

/**
 * Throttle function execution
 * @param {Function} fn - Function to throttle
 * @param {number} limit - Minimum time between calls in milliseconds
 * @returns {Function} Throttled function
 */
export function throttle(fn, limit = 100) {
    let inThrottle = false;
    let lastArgs = null;
    
    return function(...args) {
        if (!inThrottle) {
            fn.apply(this, args);
            inThrottle = true;
            
            setTimeout(() => {
                inThrottle = false;
                if (lastArgs) {
                    fn.apply(this, lastArgs);
                    lastArgs = null;
                }
            }, limit);
        } else {
            lastArgs = args;
        }
    };
}

/**
 * Memoize function results
 * @param {Function} fn - Function to memoize
 * @param {Function} keyGenerator - Optional key generator function
 * @returns {Function} Memoized function
 */
export function memoize(fn, keyGenerator = null) {
    const cache = new Map();
    
    const memoized = function(...args) {
        const key = keyGenerator ? keyGenerator(...args) : JSON.stringify(args);
        
        if (cache.has(key)) {
            return cache.get(key);
        }
        
        const result = fn.apply(this, args);
        cache.set(key, result);
        
        return result;
    };
    
    memoized.clear = () => cache.clear();
    memoized.delete = (key) => cache.delete(key);
    memoized.has = (key) => cache.has(key);
    memoized.size = () => cache.size;
    
    return memoized;
}

/**
 * Create a memoized function with LRU cache eviction
 * @param {Function} fn - Function to memoize
 * @param {number} maxSize - Maximum cache size
 * @returns {Function} Memoized function with LRU
 */
export function memoizeLRU(fn, maxSize = 100) {
    const cache = new Map();
    
    return function(...args) {
        const key = JSON.stringify(args);
        
        if (cache.has(key)) {
            // Move to end (most recently used)
            const value = cache.get(key);
            cache.delete(key);
            cache.set(key, value);
            return value;
        }
        
        const result = fn.apply(this, args);
        
        // Evict oldest if at capacity
        if (cache.size >= maxSize) {
            const firstKey = cache.keys().next().value;
            cache.delete(firstKey);
        }
        
        cache.set(key, result);
        return result;
    };
}

/**
 * Measure execution time of a function
 * @param {Function} fn - Function to measure
 * @param {string} label - Label for console output
 * @returns {Function} Wrapped function that logs execution time
 */
export function measureTime(fn, label = 'Function') {
    return async function(...args) {
        const start = performance.now();
        const result = await fn.apply(this, args);
        const end = performance.now();
        
        if (import.meta.env.DEV) {
            console.debug(`${label} executed in ${(end - start).toFixed(2)}ms`);
        }
        
        return result;
    };
}

/**
 * Create an async queue that processes items sequentially
 * @param {Function} processor - Async function to process each item
 * @param {number} concurrency - Number of concurrent operations
 * @returns {Object} Queue controller
 */
export function createAsyncQueue(processor, concurrency = 1) {
    const queue = [];
    let running = 0;
    
    const processNext = async () => {
        if (queue.length === 0 || running >= concurrency) {
            return;
        }
        
        running++;
        const { item, resolve, reject } = queue.shift();
        
        try {
            const result = await processor(item);
            resolve(result);
        } catch (error) {
            reject(error);
        } finally {
            running--;
            processNext();
        }
    };
    
    return {
        add(item) {
            return new Promise((resolve, reject) => {
                queue.push({ item, resolve, reject });
                processNext();
            });
        },
        get pending() {
            return queue.length;
        },
        get active() {
            return running;
        },
        clear() {
            queue.length = 0;
        }
    };
}

/**
 * Retry a function with exponential backoff
 * @param {Function} fn - Function to retry
 * @param {Object} options - Retry options
 * @returns {Promise} Result of successful execution
 */
export async function retryWithBackoff(fn, options = {}) {
    const {
        maxRetries = 3,
        initialDelay = 1000,
        maxDelay = 10000,
        factor = 2,
        onRetry = null
    } = options;
    
    let lastError;
    
    for (let attempt = 0; attempt <= maxRetries; attempt++) {
        try {
            return await fn();
        } catch (error) {
            lastError = error;
            
            if (attempt === maxRetries) {
                throw error;
            }
            
            const delay = Math.min(initialDelay * Math.pow(factor, attempt), maxDelay);
            
            if (onRetry) {
                onRetry(attempt + 1, delay, error);
            }
            
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    
    throw lastError;
}

/**
 * Create a cancelable promise
 * @param {Promise} promise - Promise to make cancelable
 * @returns {Object} Object with promise and cancel function
 */
export function makeCancelable(promise) {
    let isCanceled = false;
    
    const wrappedPromise = new Promise((resolve, reject) => {
        promise.then(
            value => isCanceled ? reject({ isCanceled: true }) : resolve(value),
            error => isCanceled ? reject({ isCanceled: true }) : reject(error)
        );
    });
    
    return {
        promise: wrappedPromise,
        cancel() {
            isCanceled = true;
        },
        get isCanceled() {
            return isCanceled;
        }
    };
}

/**
 * Batch multiple calls into a single execution
 * @param {Function} fn - Function to batch
 * @param {number} delay - Delay before executing batch
 * @returns {Function} Batched function
 */
export function batchCalls(fn, delay = 50) {
    let timeoutId;
    let batch = [];
    
    return function(item) {
        return new Promise((resolve, reject) => {
            batch.push({ item, resolve, reject });
            
            clearTimeout(timeoutId);
            timeoutId = setTimeout(async () => {
                const currentBatch = [...batch];
                batch = [];
                
                try {
                    const items = currentBatch.map(b => b.item);
                    const results = await fn(items);
                    
                    currentBatch.forEach((b, i) => {
                        b.resolve(results[i]);
                    });
                } catch (error) {
                    currentBatch.forEach(b => b.reject(error));
                }
            }, delay);
        });
    };
}

/**
 * Create an intersection observer for lazy loading
 * @param {Function} callback - Callback when element is visible
 * @param {Object} options - IntersectionObserver options
 * @returns {Object} Observer controller
 */
export function createLazyObserver(callback, options = {}) {
    const {
        root = null,
        rootMargin = '50px',
        threshold = 0.1
    } = options;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                callback(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { root, rootMargin, threshold });
    
    return {
        observe(element) {
            observer.observe(element);
        },
        unobserve(element) {
            observer.unobserve(element);
        },
        disconnect() {
            observer.disconnect();
        }
    };
}

/**
 * Measure Web Vitals metrics
 * @param {Function} onMetric - Callback for each metric
 */
export function measureWebVitals(onMetric) {
    // First Contentful Paint
    if ('PerformanceObserver' in window) {
        try {
            const paintObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.name === 'first-contentful-paint') {
                        onMetric({
                            name: 'FCP',
                            value: entry.startTime,
                            rating: entry.startTime < 1800 ? 'good' : entry.startTime < 3000 ? 'needs-improvement' : 'poor'
                        });
                    }
                }
            });
            paintObserver.observe({ entryTypes: ['paint'] });
        } catch (e) {
            // Observer not supported
        }
        
        // Largest Contentful Paint
        try {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                onMetric({
                    name: 'LCP',
                    value: lastEntry.startTime,
                    rating: lastEntry.startTime < 2500 ? 'good' : lastEntry.startTime < 4000 ? 'needs-improvement' : 'poor'
                });
            });
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        } catch (e) {
            // Observer not supported
        }
        
        // Cumulative Layout Shift
        try {
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                }
                onMetric({
                    name: 'CLS',
                    value: clsValue,
                    rating: clsValue < 0.1 ? 'good' : clsValue < 0.25 ? 'needs-improvement' : 'poor'
                });
            });
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        } catch (e) {
            // Observer not supported
        }
    }
    
    // Time to Interactive (approximation using load event)
    window.addEventListener('load', () => {
        setTimeout(() => {
            const timing = performance.timing;
            const tti = timing.domInteractive - timing.navigationStart;
            onMetric({
                name: 'TTI',
                value: tti,
                rating: tti < 3800 ? 'good' : tti < 7300 ? 'needs-improvement' : 'poor'
            });
        }, 0);
    });
}

export default {
    debounce,
    throttle,
    memoize,
    memoizeLRU,
    measureTime,
    createAsyncQueue,
    retryWithBackoff,
    makeCancelable,
    batchCalls,
    createLazyObserver,
    measureWebVitals
};
