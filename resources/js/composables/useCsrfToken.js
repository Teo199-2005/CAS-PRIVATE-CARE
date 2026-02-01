/**
 * CSRF Token Composable
 * Centralizes CSRF token handling to eliminate code duplication
 */

import { ref } from 'vue';

export function useCsrfToken() {
  const csrfToken = ref(null);
  const csrfError = ref(null);

  /**
   * Get CSRF token from meta tag
   * @returns {string} CSRF token
   * @throws {Error} If token not found
   */
  const getCsrfToken = () => {
    if (csrfToken.value) return csrfToken.value;
    
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
      csrfError.value = 'CSRF token not found';
      throw new Error('CSRF token not found. Please refresh the page.');
    }
    
    csrfToken.value = meta.getAttribute('content');
    return csrfToken.value;
  };

  /**
   * Get headers object with CSRF token included
   * @param {Object} additionalHeaders - Additional headers to include
   * @returns {Object} Headers object
   */
  const getAuthHeaders = (additionalHeaders = {}) => {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
      ...additionalHeaders
    };
  };

  /**
   * Make an authenticated fetch request
   * @param {string} url - Request URL
   * @param {Object} options - Fetch options
   * @returns {Promise<Response>}
   */
  const authFetch = async (url, options = {}) => {
    const headers = getAuthHeaders(options.headers);
    
    const response = await fetch(url, {
      ...options,
      headers,
      credentials: 'same-origin'
    });
    
    // Handle CSRF token expiration
    if (response.status === 419) {
      csrfToken.value = null;
      throw new Error('Session expired. Please refresh the page.');
    }
    
    return response;
  };

  /**
   * Refresh CSRF token (useful after long sessions)
   */
  const refreshToken = () => {
    csrfToken.value = null;
    getCsrfToken();
  };

  return {
    csrfToken,
    csrfError,
    getCsrfToken,
    getAuthHeaders,
    authFetch,
    refreshToken
  };
}

// Standalone exports for direct import
// These create a singleton instance for use outside of Vue components
let singletonInstance = null;

function getInstance() {
  if (!singletonInstance) {
    singletonInstance = useCsrfToken();
  }
  return singletonInstance;
}

export const getCsrfToken = () => getInstance().getCsrfToken();
export const getAuthHeaders = (additionalHeaders) => getInstance().getAuthHeaders(additionalHeaders);
export const authFetch = (url, options) => getInstance().authFetch(url, options);
export const refreshToken = () => getInstance().refreshToken();
