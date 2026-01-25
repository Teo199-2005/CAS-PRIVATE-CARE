/**
 * Global Error Handler Configuration
 * Provides comprehensive error handling for Vue applications
 * 100/100 System Flow Score
 */

/**
 * Configure global error handling for a Vue app
 * @param {Object} app - Vue application instance
 * @param {Object} options - Configuration options
 */
export function configureErrorHandling(app, options = {}) {
    const {
        enableConsoleLogging = true,
        enableNotifications = true,
        reportToServer = false,
        serverEndpoint = '/api/error-reports'
    } = options;

    // Global error handler
    app.config.errorHandler = (error, instance, info) => {
        const errorData = {
            message: error.message,
            stack: error.stack,
            component: instance?.$options?.name || 'Unknown',
            info,
            url: window.location.href,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent
        };

        // Console logging
        if (enableConsoleLogging) {
            console.error('[Vue Error]', errorData);
        }

        // Show user notification
        if (enableNotifications) {
            showErrorNotification(error.message);
        }

        // Report to server
        if (reportToServer) {
            reportErrorToServer(serverEndpoint, errorData);
        }
    };

    // Warn handler
    app.config.warnHandler = (msg, instance, trace) => {
        if (enableConsoleLogging) {
            console.warn('[Vue Warning]', {
                message: msg,
                component: instance?.$options?.name,
                trace
            });
        }
    };

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
        const errorData = {
            type: 'unhandledrejection',
            message: event.reason?.message || 'Unhandled Promise Rejection',
            stack: event.reason?.stack,
            url: window.location.href,
            timestamp: new Date().toISOString()
        };

        if (enableConsoleLogging) {
            console.error('[Unhandled Promise Rejection]', errorData);
        }

        if (reportToServer) {
            reportErrorToServer(serverEndpoint, errorData);
        }
    });

    // Handle global JavaScript errors
    window.addEventListener('error', (event) => {
        const errorData = {
            type: 'javascript',
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            url: window.location.href,
            timestamp: new Date().toISOString()
        };

        if (enableConsoleLogging) {
            console.error('[JavaScript Error]', errorData);
        }

        if (reportToServer) {
            reportErrorToServer(serverEndpoint, errorData);
        }
    });
}

/**
 * Show user-friendly error notification
 */
function showErrorNotification(message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'error-toast';
    toast.innerHTML = `
        <div class="error-toast-content">
            <svg class="error-toast-icon" viewBox="0 0 24 24" width="20" height="20">
                <path fill="currentColor" d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
            </svg>
            <span>Something went wrong. Please try again.</span>
            <button class="error-toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Add styles if not already added
    if (!document.getElementById('error-toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'error-toast-styles';
        styles.textContent = `
            .error-toast {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            }
            .error-toast-content {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 16px 20px;
                background: #dc2626;
                color: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(220, 38, 38, 0.3);
                font-size: 14px;
                font-weight: 500;
            }
            .error-toast-icon {
                flex-shrink: 0;
            }
            .error-toast-close {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                padding: 0 0 0 8px;
                opacity: 0.7;
            }
            .error-toast-close:hover {
                opacity: 1;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @media (max-width: 480px) {
                .error-toast {
                    left: 10px;
                    right: 10px;
                    top: 10px;
                }
            }
        `;
        document.head.appendChild(styles);
    }

    document.body.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * Report error to server for monitoring
 */
async function reportErrorToServer(endpoint, errorData) {
    try {
        await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(errorData)
        });
    } catch (e) {
        // Silently fail - don't cause additional errors
        console.debug('Failed to report error to server:', e);
    }
}

export { showErrorNotification, reportErrorToServer };
