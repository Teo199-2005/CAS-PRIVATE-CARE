/**
 * API Client
 * ==========
 * 
 * Production-grade HTTP client with:
 * - Type-safe responses
 * - Automatic retry with exponential backoff
 * - Request/response interceptors
 * - CSRF protection
 * - Rate limit handling
 * - Comprehensive error handling
 * 
 * @version 1.0.0
 * @date January 28, 2026
 */

import type { ApiResponse, AppError, RateLimitInfo } from '../types';

// =============================================================================
// Types
// =============================================================================

export interface RequestConfig {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  headers?: Record<string, string>;
  body?: unknown;
  params?: Record<string, string | number | boolean | undefined>;
  timeout?: number;
  retries?: number;
  retryDelay?: number;
  signal?: AbortSignal;
  credentials?: RequestCredentials;
  cache?: RequestCache;
}

export interface ApiClientConfig {
  baseUrl?: string;
  timeout?: number;
  retries?: number;
  retryDelay?: number;
  onRequest?: (config: RequestConfig) => RequestConfig | Promise<RequestConfig>;
  onResponse?: <T>(response: ApiResponse<T>) => ApiResponse<T> | Promise<ApiResponse<T>>;
  onError?: (error: ApiError) => void;
  onRateLimit?: (info: RateLimitInfo) => void;
}

export class ApiError extends Error {
  constructor(
    public status: number,
    public code: string,
    message: string,
    public details?: Record<string, unknown>,
    public retryable: boolean = false
  ) {
    super(message);
    this.name = 'ApiError';
  }

  static fromResponse(status: number, data: unknown): ApiError {
    const body = data as Record<string, unknown> | null;
    
    return new ApiError(
      status,
      body?.code as string || `HTTP_${status}`,
      body?.message as string || getDefaultMessage(status),
      body?.details as Record<string, unknown>,
      isRetryable(status)
    );
  }
}

// =============================================================================
// Helper Functions
// =============================================================================

function getDefaultMessage(status: number): string {
  const messages: Record<number, string> = {
    400: 'Bad request. Please check your input.',
    401: 'Please log in to continue.',
    403: 'You don\'t have permission to perform this action.',
    404: 'The requested resource was not found.',
    422: 'Validation failed. Please check your input.',
    429: 'Too many requests. Please try again later.',
    500: 'Server error. Please try again later.',
    502: 'Service temporarily unavailable.',
    503: 'Service temporarily unavailable.',
    504: 'Request timed out. Please try again.',
  };
  
  return messages[status] || 'An unexpected error occurred.';
}

function isRetryable(status: number): boolean {
  return [408, 429, 500, 502, 503, 504].includes(status);
}

function buildUrl(base: string, path: string, params?: Record<string, string | number | boolean | undefined>): string {
  // Ensure base doesn't end with slash and path starts with slash
  const normalizedBase = base.replace(/\/+$/, '');
  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  
  const url = new URL(normalizedPath, normalizedBase);
  
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        url.searchParams.append(key, String(value));
      }
    });
  }
  
  return url.toString();
}

function getCsrfToken(): string | null {
  // Try meta tag first
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) {
    return meta.getAttribute('content');
  }
  
  // Try cookie (Laravel Sanctum pattern)
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (match) {
    return decodeURIComponent(match[1]);
  }
  
  return null;
}

async function delay(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// =============================================================================
// API Client Class
// =============================================================================

export class ApiClient {
  private config: Required<ApiClientConfig>;
  private abortControllers: Map<string, AbortController> = new Map();

  constructor(config: ApiClientConfig = {}) {
    this.config = {
      baseUrl: config.baseUrl || window.location.origin,
      timeout: config.timeout || 30000,
      retries: config.retries || 3,
      retryDelay: config.retryDelay || 1000,
      onRequest: config.onRequest || ((c) => c),
      onResponse: config.onResponse || ((r) => r),
      onError: config.onError || (() => {}),
      onRateLimit: config.onRateLimit || (() => {}),
    };
  }

  /**
   * Make an HTTP request with full type safety
   */
  async request<T>(path: string, config: RequestConfig = {}): Promise<ApiResponse<T>> {
    const requestId = `${config.method || 'GET'}-${path}-${Date.now()}`;
    
    try {
      // Apply request interceptor
      const processedConfig = await this.config.onRequest(config);
      
      // Build headers
      const headers: Record<string, string> = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...processedConfig.headers,
      };

      // Add CSRF token for state-changing requests
      if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(processedConfig.method || 'GET')) {
        const csrfToken = getCsrfToken();
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken;
          headers['X-XSRF-TOKEN'] = csrfToken;
        }
      }

      // Add Content-Type for requests with body
      if (processedConfig.body && !(processedConfig.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
      }

      // Build URL with query params
      const url = buildUrl(this.config.baseUrl, path, processedConfig.params);

      // Create abort controller for timeout
      const controller = new AbortController();
      this.abortControllers.set(requestId, controller);

      // Set up timeout
      const timeoutId = setTimeout(() => {
        controller.abort();
      }, processedConfig.timeout || this.config.timeout);

      try {
        const response = await this.executeWithRetry<T>(
          url,
          {
            method: processedConfig.method || 'GET',
            headers,
            body: processedConfig.body instanceof FormData
              ? processedConfig.body
              : processedConfig.body
                ? JSON.stringify(processedConfig.body)
                : undefined,
            signal: processedConfig.signal || controller.signal,
            credentials: processedConfig.credentials || 'same-origin',
            cache: processedConfig.cache,
          },
          processedConfig.retries ?? this.config.retries,
          processedConfig.retryDelay ?? this.config.retryDelay
        );

        clearTimeout(timeoutId);
        
        // Apply response interceptor
        return await this.config.onResponse(response);
      } finally {
        clearTimeout(timeoutId);
        this.abortControllers.delete(requestId);
      }
    } catch (error) {
      if (error instanceof ApiError) {
        this.config.onError(error);
        throw error;
      }

      // Handle abort/timeout
      if (error instanceof DOMException && error.name === 'AbortError') {
        const apiError = new ApiError(408, 'TIMEOUT', 'Request timed out', undefined, true);
        this.config.onError(apiError);
        throw apiError;
      }

      // Handle network errors
      if (error instanceof TypeError && error.message.includes('fetch')) {
        const apiError = new ApiError(0, 'NETWORK_ERROR', 'Network error. Please check your connection.', undefined, true);
        this.config.onError(apiError);
        throw apiError;
      }

      // Re-throw unknown errors
      throw error;
    }
  }

  private async executeWithRetry<T>(
    url: string,
    init: RequestInit,
    retriesLeft: number,
    retryDelay: number
  ): Promise<ApiResponse<T>> {
    try {
      const response = await fetch(url, init);

      // Check for rate limiting
      if (response.status === 429) {
        const rateLimitInfo: RateLimitInfo = {
          remaining: parseInt(response.headers.get('X-RateLimit-Remaining') || '0'),
          limit: parseInt(response.headers.get('X-RateLimit-Limit') || '60'),
          reset_at: parseInt(response.headers.get('X-RateLimit-Reset') || '0'),
        };
        this.config.onRateLimit(rateLimitInfo);

        // Retry after the rate limit reset if we have retries left
        if (retriesLeft > 0) {
          const retryAfter = parseInt(response.headers.get('Retry-After') || '60') * 1000;
          await delay(Math.min(retryAfter, 60000)); // Max 60 second wait
          return this.executeWithRetry(url, init, retriesLeft - 1, retryDelay);
        }
      }

      // Handle non-OK responses
      if (!response.ok) {
        let errorData: unknown;
        try {
          errorData = await response.json();
        } catch {
          errorData = null;
        }

        const error = ApiError.fromResponse(response.status, errorData);

        // Retry if applicable
        if (error.retryable && retriesLeft > 0) {
          await delay(retryDelay);
          return this.executeWithRetry(url, init, retriesLeft - 1, retryDelay * 2);
        }

        throw error;
      }

      // Parse successful response
      const data = await response.json();
      
      return {
        success: true,
        data: data.data ?? data,
        message: data.message,
      } as ApiResponse<T>;
    } catch (error) {
      // Retry on network errors
      if (error instanceof TypeError && retriesLeft > 0) {
        await delay(retryDelay);
        return this.executeWithRetry(url, init, retriesLeft - 1, retryDelay * 2);
      }

      throw error;
    }
  }

  /**
   * Cancel a pending request
   */
  cancel(requestId: string): void {
    const controller = this.abortControllers.get(requestId);
    if (controller) {
      controller.abort();
      this.abortControllers.delete(requestId);
    }
  }

  /**
   * Cancel all pending requests
   */
  cancelAll(): void {
    this.abortControllers.forEach(controller => controller.abort());
    this.abortControllers.clear();
  }

  // Convenience methods
  async get<T>(path: string, params?: Record<string, string | number | boolean | undefined>): Promise<ApiResponse<T>> {
    return this.request<T>(path, { method: 'GET', params });
  }

  async post<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
    return this.request<T>(path, { method: 'POST', body });
  }

  async put<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
    return this.request<T>(path, { method: 'PUT', body });
  }

  async patch<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
    return this.request<T>(path, { method: 'PATCH', body });
  }

  async delete<T>(path: string): Promise<ApiResponse<T>> {
    return this.request<T>(path, { method: 'DELETE' });
  }
}

// =============================================================================
// Default API Client Instance
// =============================================================================

export const api = new ApiClient({
  baseUrl: window.location.origin,
  timeout: 30000,
  retries: 3,
  onError: (error) => {
    // Log errors in development
    if (import.meta.env.DEV) {
      console.error('[API Error]', error);
    }
    
    // Report to error tracking in production
    if (import.meta.env.PROD && error.status >= 500) {
      // Could integrate with error tracking service here
      console.error('[API Server Error]', {
        code: error.code,
        message: error.message,
        status: error.status,
      });
    }
  },
  onRateLimit: (info) => {
    console.warn('[Rate Limited]', info);
  },
});

// =============================================================================
// API Endpoints (typed helpers)
// =============================================================================

export const endpoints = {
  // Auth
  auth: {
    login: (credentials: { email: string; password: string }) => 
      api.post('/login', credentials),
    logout: () => 
      api.post('/logout'),
    register: (data: Record<string, unknown>) => 
      api.post('/register', data),
    forgotPassword: (email: string) => 
      api.post('/password/email', { email }),
    resetPassword: (data: { token: string; email: string; password: string; password_confirmation: string }) =>
      api.post('/reset-password', data),
    verifyEmail: (token: string) =>
      api.get(`/verify-email/${token}`),
    sendOtp: () =>
      api.post('/api/auth/send-otp'),
    verifyOtp: (code: string) =>
      api.post('/api/auth/verify-otp', { code }),
  },

  // User
  user: {
    profile: () => 
      api.get('/api/profile'),
    updateProfile: (data: Record<string, unknown>) => 
      api.post('/api/profile/update', data),
    changePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
      api.post('/api/profile/change-password', data),
  },

  // Bookings
  bookings: {
    list: () => 
      api.get('/api/bookings'),
    get: (id: number) => 
      api.get(`/api/bookings/${id}`),
    create: (data: Record<string, unknown>) => 
      api.post('/api/bookings', data),
    update: (id: number, data: Record<string, unknown>) => 
      api.put(`/api/bookings/${id}`, data),
    cancel: (id: number) =>
      api.post(`/api/bookings/${id}/cancel`),
  },

  // Payments
  payments: {
    createSetupIntent: () =>
      api.post('/api/stripe/create-setup-intent'),
    attachPaymentMethod: (paymentMethodId: string) =>
      api.post('/api/client/payments/attach', { payment_method_id: paymentMethodId }),
    listPaymentMethods: () =>
      api.get('/api/client/payments/methods'),
    detachPaymentMethod: (paymentMethodId: string) =>
      api.post(`/api/client/payments/detach/${paymentMethodId}`),
    processPayment: (data: { booking_id: number; payment_method_id: string }) =>
      api.post('/api/stripe/setup-intent', data),
  },

  // Admin
  admin: {
    stats: () =>
      api.get('/api/admin/stats'),
    users: (params?: Record<string, string | number | boolean>) =>
      api.get('/api/admin/users', params),
    bookings: (params?: Record<string, string | number | boolean>) =>
      api.get('/api/admin/bookings', params),
    approveUser: (id: number) =>
      api.post(`/api/admin/users/${id}/approve`),
    rejectUser: (id: number, reason?: string) =>
      api.post(`/api/admin/users/${id}/reject`, { reason }),
  },

  // Health
  health: {
    ping: () =>
      api.get('/api/health/ping'),
    check: () =>
      api.get('/api/health/check'),
  },
} as const;

export default api;
