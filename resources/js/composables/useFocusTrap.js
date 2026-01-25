/**
 * Focus Trap Composable
 * 
 * Provides focus trapping functionality for modals and dialogs
 * to ensure keyboard users can navigate properly without losing focus.
 * 
 * @module composables/useFocusTrap
 */

import { ref, onMounted, onUnmounted, nextTick } from 'vue';

/**
 * Selectors for focusable elements
 */
const FOCUSABLE_SELECTORS = [
  'a[href]',
  'button:not([disabled])',
  'textarea:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
  '[contenteditable="true"]',
].join(', ');

/**
 * useFocusTrap composable
 * 
 * @param {Object} options Configuration options
 * @param {boolean} options.escapeDeactivates Whether pressing Escape should deactivate the trap
 * @param {boolean} options.clickOutsideDeactivates Whether clicking outside should deactivate
 * @param {boolean} options.returnFocusOnDeactivate Whether to return focus when deactivated
 * @returns {Object} Focus trap methods and refs
 */
export function useFocusTrap(options = {}) {
  const {
    escapeDeactivates = true,
    clickOutsideDeactivates = false,
    returnFocusOnDeactivate = true,
  } = options;

  const containerRef = ref(null);
  const isActive = ref(false);
  const previousActiveElement = ref(null);

  /**
   * Get all focusable elements within the container
   */
  const getFocusableElements = () => {
    if (!containerRef.value) return [];
    return Array.from(containerRef.value.querySelectorAll(FOCUSABLE_SELECTORS))
      .filter(el => {
        // Check if element is visible
        const style = window.getComputedStyle(el);
        return style.display !== 'none' && style.visibility !== 'hidden';
      });
  };

  /**
   * Get the first focusable element
   */
  const getFirstFocusableElement = () => {
    const elements = getFocusableElements();
    return elements[0] || null;
  };

  /**
   * Get the last focusable element
   */
  const getLastFocusableElement = () => {
    const elements = getFocusableElements();
    return elements[elements.length - 1] || null;
  };

  /**
   * Handle keydown events for trapping focus
   */
  const handleKeyDown = (event) => {
    if (!isActive.value || !containerRef.value) return;

    if (event.key === 'Escape' && escapeDeactivates) {
      event.preventDefault();
      deactivate();
      return;
    }

    if (event.key !== 'Tab') return;

    const focusableElements = getFocusableElements();
    if (focusableElements.length === 0) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    // Shift + Tab (going backwards)
    if (event.shiftKey) {
      if (document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
      }
    } 
    // Tab (going forwards)
    else {
      if (document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
      }
    }
  };

  /**
   * Handle click outside events
   */
  const handleClickOutside = (event) => {
    if (!isActive.value || !containerRef.value || !clickOutsideDeactivates) return;
    
    if (!containerRef.value.contains(event.target)) {
      deactivate();
    }
  };

  /**
   * Activate the focus trap
   */
  const activate = async () => {
    if (isActive.value) return;

    // Store the currently focused element to restore later
    previousActiveElement.value = document.activeElement;

    isActive.value = true;

    // Wait for DOM to update
    await nextTick();

    // Add event listeners
    document.addEventListener('keydown', handleKeyDown);
    if (clickOutsideDeactivates) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    // Focus the first focusable element or the container itself
    const firstElement = getFirstFocusableElement();
    if (firstElement) {
      firstElement.focus();
    } else if (containerRef.value) {
      containerRef.value.setAttribute('tabindex', '-1');
      containerRef.value.focus();
    }
  };

  /**
   * Deactivate the focus trap
   */
  const deactivate = () => {
    if (!isActive.value) return;

    isActive.value = false;

    // Remove event listeners
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('mousedown', handleClickOutside);

    // Return focus to the previously focused element
    if (returnFocusOnDeactivate && previousActiveElement.value) {
      previousActiveElement.value.focus();
    }

    previousActiveElement.value = null;
  };

  /**
   * Pause the focus trap temporarily
   */
  const pause = () => {
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('mousedown', handleClickOutside);
  };

  /**
   * Resume the focus trap
   */
  const resume = () => {
    if (!isActive.value) return;
    document.addEventListener('keydown', handleKeyDown);
    if (clickOutsideDeactivates) {
      document.addEventListener('mousedown', handleClickOutside);
    }
  };

  // Cleanup on unmount
  onUnmounted(() => {
    deactivate();
  });

  return {
    containerRef,
    isActive,
    activate,
    deactivate,
    pause,
    resume,
    getFocusableElements,
    getFirstFocusableElement,
    getLastFocusableElement,
  };
}

/**
 * Directive for automatic focus trap on v-if elements
 * 
 * Usage: <div v-focus-trap="isOpen">...</div>
 */
export const vFocusTrap = {
  mounted(el, binding) {
    if (binding.value) {
      el._focusTrap = useFocusTrap();
      el._focusTrap.containerRef.value = el;
      el._focusTrap.activate();
    }
  },
  updated(el, binding) {
    if (binding.value && !binding.oldValue) {
      if (!el._focusTrap) {
        el._focusTrap = useFocusTrap();
        el._focusTrap.containerRef.value = el;
      }
      el._focusTrap.activate();
    } else if (!binding.value && binding.oldValue) {
      el._focusTrap?.deactivate();
    }
  },
  unmounted(el) {
    el._focusTrap?.deactivate();
  },
};

export default useFocusTrap;
