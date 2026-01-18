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
    console.error('CSRF token not found');
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
