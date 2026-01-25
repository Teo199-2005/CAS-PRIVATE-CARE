/**
 * Toast Notification Composable
 * Provides global notification system
 */

import { ref, reactive, readonly } from 'vue';

// Global state
const toasts = reactive([]);
const defaultDuration = 5000;
const maxToasts = 5;

let toastId = 0;

/**
 * Toast types and their default settings
 */
const toastDefaults = {
  success: {
    icon: '✓',
    color: '#10b981',
    bgColor: '#d1fae5',
    duration: 4000
  },
  error: {
    icon: '✕',
    color: '#ef4444',
    bgColor: '#fee2e2',
    duration: 6000
  },
  warning: {
    icon: '⚠',
    color: '#f59e0b',
    bgColor: '#fef3c7',
    duration: 5000
  },
  info: {
    icon: 'ℹ',
    color: '#3b82f6',
    bgColor: '#dbeafe',
    duration: 4000
  },
  loading: {
    icon: '⟳',
    color: '#6b7280',
    bgColor: '#f3f4f6',
    duration: 0 // No auto-dismiss
  }
};

/**
 * Add a new toast
 */
const addToast = (options) => {
  const id = ++toastId;
  const type = options.type || 'info';
  const defaults = toastDefaults[type] || toastDefaults.info;
  
  const toast = {
    id,
    type,
    message: options.message || '',
    title: options.title || '',
    icon: options.icon ?? defaults.icon,
    color: options.color || defaults.color,
    bgColor: options.bgColor || defaults.bgColor,
    duration: options.duration ?? defaults.duration,
    dismissible: options.dismissible !== false,
    action: options.action || null,
    position: options.position || 'top-right',
    createdAt: Date.now()
  };
  
  // Limit number of toasts
  while (toasts.length >= maxToasts) {
    toasts.shift();
  }
  
  toasts.push(toast);
  
  // Auto dismiss
  if (toast.duration > 0) {
    setTimeout(() => {
      removeToast(id);
    }, toast.duration);
  }
  
  return id;
};

/**
 * Remove a toast by id
 */
const removeToast = (id) => {
  const index = toasts.findIndex(t => t.id === id);
  if (index > -1) {
    toasts.splice(index, 1);
  }
};

/**
 * Clear all toasts
 */
const clearAll = () => {
  toasts.splice(0, toasts.length);
};

/**
 * Update an existing toast
 */
const updateToast = (id, options) => {
  const toast = toasts.find(t => t.id === id);
  if (toast) {
    Object.assign(toast, options);
    
    // If duration is set, start new timer
    if (options.duration && options.duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, options.duration);
    }
  }
};

/**
 * Toast notification composable
 */
export function useToast() {
  // Convenience methods
  const success = (message, options = {}) => {
    return addToast({ ...options, type: 'success', message });
  };
  
  const error = (message, options = {}) => {
    return addToast({ ...options, type: 'error', message });
  };
  
  const warning = (message, options = {}) => {
    return addToast({ ...options, type: 'warning', message });
  };
  
  const info = (message, options = {}) => {
    return addToast({ ...options, type: 'info', message });
  };
  
  const loading = (message, options = {}) => {
    return addToast({ ...options, type: 'loading', message, dismissible: false });
  };
  
  /**
   * Promise-based toast
   * Shows loading while promise is pending, then success/error
   */
  const promise = async (promiseOrFn, options = {}) => {
    const {
      loading: loadingMsg = 'Loading...',
      success: successMsg = 'Success!',
      error: errorMsg = 'An error occurred'
    } = options;
    
    const id = loading(loadingMsg);
    
    try {
      const result = typeof promiseOrFn === 'function' 
        ? await promiseOrFn() 
        : await promiseOrFn;
      
      const message = typeof successMsg === 'function' 
        ? successMsg(result) 
        : successMsg;
      
      updateToast(id, {
        type: 'success',
        message,
        icon: toastDefaults.success.icon,
        color: toastDefaults.success.color,
        bgColor: toastDefaults.success.bgColor,
        dismissible: true,
        duration: toastDefaults.success.duration
      });
      
      return result;
    } catch (err) {
      const message = typeof errorMsg === 'function' 
        ? errorMsg(err) 
        : (err.message || errorMsg);
      
      updateToast(id, {
        type: 'error',
        message,
        icon: toastDefaults.error.icon,
        color: toastDefaults.error.color,
        bgColor: toastDefaults.error.bgColor,
        dismissible: true,
        duration: toastDefaults.error.duration
      });
      
      throw err;
    }
  };
  
  /**
   * Confirmation toast with actions
   */
  const confirm = (message, options = {}) => {
    return new Promise((resolve) => {
      const id = addToast({
        type: 'warning',
        message,
        title: options.title || 'Confirm',
        duration: 0,
        dismissible: false,
        action: {
          confirm: {
            label: options.confirmLabel || 'Confirm',
            handler: () => {
              removeToast(id);
              resolve(true);
            }
          },
          cancel: {
            label: options.cancelLabel || 'Cancel',
            handler: () => {
              removeToast(id);
              resolve(false);
            }
          }
        }
      });
    });
  };
  
  return {
    toasts: readonly(toasts),
    add: addToast,
    remove: removeToast,
    update: updateToast,
    clear: clearAll,
    success,
    error,
    warning,
    info,
    loading,
    promise,
    confirm
  };
}

export default useToast;
