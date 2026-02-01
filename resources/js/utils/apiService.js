/**
 * API Service Layer
 * Centralized API handling with caching, retry, and error handling
 */

import axios from 'axios';
import { retryWithBackoff } from './performance';
import { logger } from './logger';

/**
 * Cache configuration
 */
const cache = new Map();
const DEFAULT_CACHE_TTL = 5 * 60 * 1000; // 5 minutes

/**
 * Create an API service instance
 * @param {Object} config - Configuration options
 * @returns {Object} API service instance
 */
export function createApiService(config = {}) {
    const {
        baseURL = '/api',
        timeout = 30000,
        withCredentials = true,
        cacheTTL = DEFAULT_CACHE_TTL,
        retryConfig = { maxRetries: 2, initialDelay: 1000 },
        onError = null,
        onUnauthorized = null
    } = config;
    
    // Create axios instance
    const client = axios.create({
        baseURL,
        timeout,
        withCredentials,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    
    // Request interceptor for CSRF token
    client.interceptors.request.use((config) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        return config;
    });
    
    // Response interceptor for error handling
    client.interceptors.response.use(
        (response) => response,
        async (error) => {
            const status = error.response?.status;
            
            // Handle 401 Unauthorized
            if (status === 401 && onUnauthorized) {
                onUnauthorized(error);
            }
            
            // Log errors in development
            logger.error('API Error', {
                url: error.config?.url,
                status,
                message: error.message
            });
            
            // Call custom error handler
            if (onError) {
                onError(error);
            }
            
            return Promise.reject(error);
        }
    );
    
    /**
     * Generate cache key from request config
     */
    const getCacheKey = (method, url, params) => {
        return `${method}:${url}:${JSON.stringify(params || {})}`;
    };
    
    /**
     * Check if cached response is still valid
     */
    const getCached = (key) => {
        const cached = cache.get(key);
        if (cached && Date.now() < cached.expires) {
            return cached.data;
        }
        cache.delete(key);
        return null;
    };
    
    /**
     * Store response in cache
     */
    const setCache = (key, data, ttl = cacheTTL) => {
        cache.set(key, {
            data,
            expires: Date.now() + ttl
        });
    };
    
    return {
        /**
         * GET request with optional caching
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {Promise} Response data
         */
        async get(url, options = {}) {
            const { params, cache: useCache = false, ttl = cacheTTL, retry = false } = options;
            const cacheKey = getCacheKey('GET', url, params);
            
            // Return cached response if available
            if (useCache) {
                const cached = getCached(cacheKey);
                if (cached) {
                    logger.debug('Cache hit', { url });
                    return cached;
                }
            }
            
            const makeRequest = async () => {
                const response = await client.get(url, { params });
                return response.data;
            };
            
            const data = retry
                ? await retryWithBackoff(makeRequest, retryConfig)
                : await makeRequest();
            
            // Cache the response
            if (useCache) {
                setCache(cacheKey, data, ttl);
            }
            
            return data;
        },
        
        /**
         * POST request
         * @param {string} url - Request URL
         * @param {Object} data - Request body
         * @param {Object} options - Request options
         * @returns {Promise} Response data
         */
        async post(url, data = {}, options = {}) {
            const { retry = false } = options;
            
            const makeRequest = async () => {
                const response = await client.post(url, data);
                return response.data;
            };
            
            return retry
                ? await retryWithBackoff(makeRequest, retryConfig)
                : await makeRequest();
        },
        
        /**
         * PUT request
         * @param {string} url - Request URL
         * @param {Object} data - Request body
         * @param {Object} options - Request options
         * @returns {Promise} Response data
         */
        async put(url, data = {}, options = {}) {
            const { retry = false } = options;
            
            const makeRequest = async () => {
                const response = await client.put(url, data);
                return response.data;
            };
            
            return retry
                ? await retryWithBackoff(makeRequest, retryConfig)
                : await makeRequest();
        },
        
        /**
         * PATCH request
         * @param {string} url - Request URL
         * @param {Object} data - Request body
         * @param {Object} options - Request options
         * @returns {Promise} Response data
         */
        async patch(url, data = {}, options = {}) {
            const { retry = false } = options;
            
            const makeRequest = async () => {
                const response = await client.patch(url, data);
                return response.data;
            };
            
            return retry
                ? await retryWithBackoff(makeRequest, retryConfig)
                : await makeRequest();
        },
        
        /**
         * DELETE request
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {Promise} Response data
         */
        async delete(url, options = {}) {
            const { data, retry = false } = options;
            
            const makeRequest = async () => {
                const response = await client.delete(url, { data });
                return response.data;
            };
            
            return retry
                ? await retryWithBackoff(makeRequest, retryConfig)
                : await makeRequest();
        },
        
        /**
         * Upload file with progress tracking
         * @param {string} url - Upload URL
         * @param {FormData} formData - Form data with file(s)
         * @param {Function} onProgress - Progress callback
         * @returns {Promise} Response data
         */
        async upload(url, formData, onProgress = null) {
            const response = await client.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: onProgress ? (event) => {
                    const percent = Math.round((event.loaded * 100) / event.total);
                    onProgress(percent, event);
                } : undefined
            });
            
            return response.data;
        },
        
        /**
         * Download file
         * @param {string} url - Download URL
         * @param {string} filename - Suggested filename
         * @returns {Promise} Download complete
         */
        async download(url, filename) {
            const response = await client.get(url, {
                responseType: 'blob'
            });
            
            // Create download link
            const blob = new Blob([response.data]);
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = filename || 'download';
            link.click();
            window.URL.revokeObjectURL(link.href);
        },
        
        /**
         * Clear all cached responses
         */
        clearCache() {
            cache.clear();
        },
        
        /**
         * Clear cached responses matching a pattern
         * @param {string|RegExp} pattern - URL pattern to clear
         */
        clearCachePattern(pattern) {
            const regex = typeof pattern === 'string' ? new RegExp(pattern) : pattern;
            for (const key of cache.keys()) {
                if (regex.test(key)) {
                    cache.delete(key);
                }
            }
        },
        
        /**
         * Get the underlying axios instance
         * @returns {AxiosInstance} Axios instance
         */
        getClient() {
            return client;
        }
    };
}

// Default API service instance
export const api = createApiService();

/**
 * Common API endpoints
 */
export const endpoints = {
    // Auth
    login: '/login',
    logout: '/logout',
    register: '/register',
    forgotPassword: '/forgot-password',
    resetPassword: '/reset-password',
    
    // User
    user: '/user',
    profile: '/profile',
    settings: '/settings',
    notifications: '/notifications',
    
    // Dashboard data
    dashboardStats: '/dashboard/stats',
    dashboardActivity: '/dashboard/activity',
    
    // Bookings
    bookings: '/bookings',
    bookingDetails: (id) => `/bookings/${id}`,
    bookingCancel: (id) => `/bookings/${id}/cancel`,
    bookingReschedule: (id) => `/bookings/${id}/reschedule`,
    
    // Caregivers
    caregivers: '/caregivers',
    caregiverDetails: (id) => `/caregivers/${id}`,
    caregiverAvailability: (id) => `/caregivers/${id}/availability`,
    caregiverReviews: (id) => `/caregivers/${id}/reviews`,
    
    // Payments
    payments: '/payments',
    paymentMethods: '/payment-methods',
    paymentIntent: '/payment-intent',
    
    // Reviews
    reviews: '/reviews',
    reviewCreate: '/reviews',
    
    // Messages
    messages: '/messages',
    messageThread: (id) => `/messages/${id}`,
    
    // Health
    health: '/api/health/check',
    ping: '/api/health/ping'
};

export default {
    createApiService,
    api,
    endpoints
};
