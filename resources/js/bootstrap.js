import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enable credentials for session-based authentication
window.axios.defaults.withCredentials = true;

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    // Use warn instead of error to avoid alarming users in console
    console.warn('CSRF token not found - ensure meta tag is present for form submissions');
}

// Production-safe console wrapper - suppress console.log in production
if (import.meta.env.PROD) {
    const noop = () => {};
    window.console = {
        ...console,
        log: noop,
        debug: noop,
        info: noop,
        // Keep warn and error for production debugging
        warn: console.warn.bind(console),
        error: console.error.bind(console),
    };
}
