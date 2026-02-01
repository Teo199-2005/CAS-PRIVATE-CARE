/**
 * Utils Index
 * 
 * Central export point for all utility functions.
 * 
 * @example
 * import { logger, animations, debounce, createStore, rules, api } from '@/utils';
 */

// Logging utilities
export { logger, LOG_LEVELS } from './logger';

// Web Vitals tracking
export { default as webVitals } from './webVitals';

// Animation utilities
export * from './animations';

// Performance utilities
export {
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
} from './performance';
export { default as performance } from './performance';

// State management utilities
export {
    createStore,
    createLocalStore,
    createFeatureFlags,
    createLoadingManager,
    createNotificationStore
} from './store';
export { default as storeUtils } from './store';

// Validation utilities
export {
    rules,
    combineRules,
    validateObject,
    createValidator
} from './validation';
export { default as validation } from './validation';

// API service utilities
export {
    createApiService,
    api,
    endpoints
} from './apiService';
export { default as apiService } from './apiService';
