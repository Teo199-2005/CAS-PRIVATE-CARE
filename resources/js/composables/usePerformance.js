/**
 * Performance Composable
 * 
 * Vue composable for performance optimization utilities
 */

import { ref, onMounted, onUnmounted } from 'vue';
import { 
    debounce, 
    throttle, 
    createLazyObserver,
    measureWebVitals 
} from '@/utils/performance';

/**
 * Composable for debounced values
 * @param {*} initialValue - Initial value
 * @param {number} delay - Debounce delay in ms
 * @returns {Object} Reactive debounced value
 */
export function useDebouncedValue(initialValue = '', delay = 300) {
    const value = ref(initialValue);
    const debouncedValue = ref(initialValue);
    
    const updateDebounced = debounce((newValue) => {
        debouncedValue.value = newValue;
    }, delay);
    
    const setValue = (newValue) => {
        value.value = newValue;
        updateDebounced(newValue);
    };
    
    return {
        value,
        debouncedValue,
        setValue
    };
}

/**
 * Composable for throttled callbacks
 * @param {Function} callback - Function to throttle
 * @param {number} limit - Throttle limit in ms
 * @returns {Function} Throttled function
 */
export function useThrottle(callback, limit = 100) {
    return throttle(callback, limit);
}

/**
 * Composable for lazy loading elements
 * @param {Object} options - IntersectionObserver options
 * @returns {Object} Lazy loading helpers
 */
export function useLazyLoad(options = {}) {
    const observer = ref(null);
    const loadedItems = ref(new Set());
    
    onMounted(() => {
        observer.value = createLazyObserver((element) => {
            const id = element.dataset.lazyId;
            if (id) {
                loadedItems.value.add(id);
            }
            // Emit custom event for component to handle
            element.dispatchEvent(new CustomEvent('lazy-load', { bubbles: true }));
        }, options);
    });
    
    onUnmounted(() => {
        if (observer.value) {
            observer.value.disconnect();
        }
    });
    
    const observe = (element) => {
        if (observer.value && element) {
            observer.value.observe(element);
        }
    };
    
    const isLoaded = (id) => loadedItems.value.has(id);
    
    return {
        observe,
        isLoaded,
        loadedItems
    };
}

/**
 * Composable for Web Vitals monitoring
 * @returns {Object} Web Vitals metrics
 */
export function useWebVitals() {
    const metrics = ref({
        FCP: null,
        LCP: null,
        CLS: null,
        TTI: null
    });
    
    onMounted(() => {
        measureWebVitals((metric) => {
            metrics.value[metric.name] = {
                value: metric.value,
                rating: metric.rating
            };
        });
    });
    
    const getRating = (name) => {
        return metrics.value[name]?.rating || 'unknown';
    };
    
    const getValue = (name) => {
        return metrics.value[name]?.value || null;
    };
    
    return {
        metrics,
        getRating,
        getValue
    };
}

/**
 * Composable for measuring component render time
 * @param {string} componentName - Name of component for logging
 * @returns {Object} Render time helpers
 */
export function useRenderTime(componentName = 'Component') {
    const renderStart = ref(null);
    const renderTime = ref(null);
    
    onMounted(() => {
        renderStart.value = performance.now();
        
        // Measure after next frame (when render is complete)
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                renderTime.value = performance.now() - renderStart.value;
                
                if (import.meta.env.DEV && renderTime.value > 100) {
                    console.warn(`${componentName} took ${renderTime.value.toFixed(2)}ms to render`);
                }
            });
        });
    });
    
    return {
        renderTime
    };
}

/**
 * Composable for scroll position with throttling
 * @param {number} throttleMs - Throttle delay
 * @returns {Object} Scroll position and direction
 */
export function useScrollPosition(throttleMs = 100) {
    const scrollY = ref(0);
    const scrollX = ref(0);
    const scrollDirection = ref('none'); // 'up', 'down', 'none'
    const isScrolling = ref(false);
    
    let lastScrollY = 0;
    let scrollTimeout = null;
    
    const handleScroll = throttle(() => {
        const currentY = window.scrollY;
        const currentX = window.scrollX;
        
        scrollDirection.value = currentY > lastScrollY ? 'down' : currentY < lastScrollY ? 'up' : 'none';
        lastScrollY = currentY;
        
        scrollY.value = currentY;
        scrollX.value = currentX;
        isScrolling.value = true;
        
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isScrolling.value = false;
        }, 150);
    }, throttleMs);
    
    onMounted(() => {
        window.addEventListener('scroll', handleScroll, { passive: true });
    });
    
    onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll);
        clearTimeout(scrollTimeout);
    });
    
    return {
        scrollY,
        scrollX,
        scrollDirection,
        isScrolling
    };
}

/**
 * Composable for window resize with debouncing
 * @param {number} debounceMs - Debounce delay
 * @returns {Object} Window dimensions
 */
export function useWindowSize(debounceMs = 100) {
    const width = ref(typeof window !== 'undefined' ? window.innerWidth : 0);
    const height = ref(typeof window !== 'undefined' ? window.innerHeight : 0);
    
    const handleResize = debounce(() => {
        width.value = window.innerWidth;
        height.value = window.innerHeight;
    }, debounceMs);
    
    onMounted(() => {
        window.addEventListener('resize', handleResize, { passive: true });
    });
    
    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
    });
    
    return {
        width,
        height,
        isMobile: () => width.value < 768,
        isTablet: () => width.value >= 768 && width.value < 1024,
        isDesktop: () => width.value >= 1024
    };
}

export default {
    useDebouncedValue,
    useThrottle,
    useLazyLoad,
    useWebVitals,
    useRenderTime,
    useScrollPosition,
    useWindowSize
};
