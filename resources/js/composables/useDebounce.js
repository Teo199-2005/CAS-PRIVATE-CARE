/**
 * Debounce Composable
 * Provides debounce functionality for search inputs and API calls
 */

import { ref } from 'vue';

export function useDebounce() {
  const timeoutId = ref(null);

  /**
   * Debounce a function call
   * @param {Function} fn - Function to debounce
   * @param {number} delay - Delay in milliseconds
   * @returns {Function} Debounced function
   */
  const debounce = (fn, delay = 300) => {
    return (...args) => {
      if (timeoutId.value) {
        clearTimeout(timeoutId.value);
      }
      
      timeoutId.value = setTimeout(() => {
        fn(...args);
        timeoutId.value = null;
      }, delay);
    };
  };

  /**
   * Cancel pending debounce
   */
  const cancel = () => {
    if (timeoutId.value) {
      clearTimeout(timeoutId.value);
      timeoutId.value = null;
    }
  };

  return {
    debounce,
    cancel,
    isPending: () => timeoutId.value !== null
  };
}

// Standalone debounce function for direct import
// This creates a simple debounce without Vue reactivity
export function debounce(fn, delay = 300) {
  let timeoutId = null;
  
  const debouncedFn = (...args) => {
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
    
    timeoutId = setTimeout(() => {
      fn(...args);
      timeoutId = null;
    }, delay);
  };
  
  debouncedFn.cancel = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  };
  
  return debouncedFn;
}
