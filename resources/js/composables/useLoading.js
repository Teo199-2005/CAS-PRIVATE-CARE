import { ref, computed } from 'vue';

/**
 * Composable for managing loading states with context-aware messages
 * 
 * Usage:
 * const { isLoading, startLoading, stopLoading, loadingContext } = useLoading();
 * 
 * // Start loading with context
 * startLoading('dashboard');
 * 
 * // Fetch data
 * await fetchData();
 * 
 * // Stop loading
 * stopLoading();
 */

// Global loading state (shared across components)
const globalLoading = ref(false);
const globalContext = ref('default');
const globalMessage = ref('');
const loadingQueue = ref(new Set());

export function useLoading() {
    // Local loading state (per component)
    const localLoading = ref(false);
    const localContext = ref('default');
    const localMessage = ref('');

    /**
     * Start loading with optional context and custom message
     * @param {string} context - Loading context: 'dashboard', 'bookings', 'users', etc.
     * @param {string} message - Optional custom message override
     * @param {boolean} global - Whether to use global loading state
     */
    const startLoading = (context = 'default', message = '', global = true) => {
        if (global) {
            loadingQueue.value.add(context);
            globalLoading.value = true;
            globalContext.value = context;
            globalMessage.value = message;
        } else {
            localLoading.value = true;
            localContext.value = context;
            localMessage.value = message;
        }
    };

    /**
     * Stop loading
     * @param {string} context - Context to stop (for queue management)
     * @param {boolean} global - Whether to stop global loading
     */
    const stopLoading = (context = null, global = true) => {
        if (global) {
            if (context) {
                loadingQueue.value.delete(context);
            } else {
                loadingQueue.value.clear();
            }
            
            if (loadingQueue.value.size === 0) {
                globalLoading.value = false;
                globalContext.value = 'default';
                globalMessage.value = '';
            }
        } else {
            localLoading.value = false;
            localContext.value = 'default';
            localMessage.value = '';
        }
    };

    /**
     * Execute an async function with loading state
     * @param {Function} asyncFn - Async function to execute
     * @param {string} context - Loading context
     * @param {string} message - Optional custom message
     * @param {boolean} global - Whether to use global loading
     */
    const withLoading = async (asyncFn, context = 'default', message = '', global = true) => {
        try {
            startLoading(context, message, global);
            return await asyncFn();
        } finally {
            stopLoading(context, global);
        }
    };

    /**
     * Set custom loading message
     * @param {string} message - Message to display
     * @param {boolean} global - Whether to update global message
     */
    const setLoadingMessage = (message, global = true) => {
        if (global) {
            globalMessage.value = message;
        } else {
            localMessage.value = message;
        }
    };

    return {
        // Global state
        isLoading: computed(() => globalLoading.value),
        loadingContext: computed(() => globalContext.value),
        loadingMessage: computed(() => globalMessage.value),
        
        // Local state
        isLocalLoading: computed(() => localLoading.value),
        localLoadingContext: computed(() => localContext.value),
        localLoadingMessage: computed(() => localMessage.value),
        
        // Methods
        startLoading,
        stopLoading,
        withLoading,
        setLoadingMessage
    };
}

// Export global state for components that need to check loading status
export const globalLoadingState = {
    isLoading: computed(() => globalLoading.value),
    context: computed(() => globalContext.value),
    message: computed(() => globalMessage.value)
};
