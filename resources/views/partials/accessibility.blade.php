{{-- 
    Accessibility Features Partial
    Include this at the very beginning of <body> on all pages
    
    Features:
    - Skip to main content link (WCAG 2.4.1)
    - Screen reader announcements
    - Focus management utilities
    - High contrast mode support
    - Reduced motion support
    
    @version 1.0
    @date January 28, 2026
--}}

{{-- Skip Links - Must be first focusable element in DOM --}}
<a href="#main-content" class="skip-link" id="skip-to-main">
    Skip to main content
</a>
<a href="#primary-navigation" class="skip-link">
    Skip to navigation
</a>
<a href="#footer" class="skip-link">
    Skip to footer
</a>

{{-- Live Region for Screen Reader Announcements --}}
<div 
    id="sr-announcements" 
    class="sr-only" 
    aria-live="polite" 
    aria-atomic="true"
    role="status"
></div>

{{-- Alert Region for Urgent Announcements --}}
<div 
    id="sr-alerts" 
    class="sr-only" 
    aria-live="assertive" 
    aria-atomic="true"
    role="alert"
></div>

<style>
    /* ==========================================================================
       ACCESSIBILITY STYLES
       ========================================================================== */

    /* Skip Links - Hidden by default, visible on focus */
    .skip-link {
        position: absolute;
        top: -100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--brand-primary, #0B4FA2);
        color: #ffffff;
        padding: 1rem 2rem;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        transition: top 0.2s ease-out;
        outline: none;
    }

    .skip-link:focus {
        top: 0;
        outline: 3px solid var(--color-warning, #f59e0b);
        outline-offset: 2px;
    }

    .skip-link:focus-visible {
        top: 0;
    }

    /* Screen Reader Only - Accessible but visually hidden */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Allow element to be focusable when using sr-only */
    .sr-only-focusable:focus,
    .sr-only-focusable:active {
        position: static;
        width: auto;
        height: auto;
        padding: inherit;
        margin: inherit;
        overflow: visible;
        clip: auto;
        white-space: inherit;
    }

    /* Focus Visible - Enhanced focus indicators */
    :focus-visible {
        outline: 3px solid var(--brand-primary, #0B4FA2);
        outline-offset: 2px;
    }

    /* Remove default outline when not using keyboard */
    :focus:not(:focus-visible) {
        outline: none;
    }

    /* Ensure links within text are distinguishable */
    a:not([class]) {
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    /* High Contrast Mode Support */
    @media (prefers-contrast: more) {
        :root {
            --text-primary: #000000;
            --bg-primary: #ffffff;
            --brand-primary: #0000cc;
        }

        .skip-link {
            background: #000000;
            color: #ffffff;
            border: 3px solid #ffffff;
        }

        a:not([class]) {
            text-decoration-thickness: 2px;
        }

        button, .btn, [role="button"] {
            border: 2px solid currentColor !important;
        }
    }

    /* Reduced Motion - Respect user preferences */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }

        .skip-link {
            transition: none;
        }
    }

    /* Focus trap indicator for modals */
    [data-focus-trap="active"] {
        position: relative;
    }

    [data-focus-trap="active"]::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    /* Ensure sufficient touch target size (WCAG 2.5.5 - AAA) */
    @media (pointer: coarse) {
        button,
        [role="button"],
        a,
        input[type="checkbox"],
        input[type="radio"],
        select {
            min-height: 44px;
            min-width: 44px;
        }
    }

    /* Print styles - ensure accessibility info is hidden */
    @media print {
        .skip-link,
        .sr-only,
        #sr-announcements,
        #sr-alerts {
            display: none !important;
        }
    }
</style>

<script>
    /**
     * Accessibility JavaScript Utilities
     * Provides programmatic access to accessibility features
     */
    window.A11y = {
        /**
         * Announce a message to screen readers
         * @param {string} message - The message to announce
         * @param {boolean} urgent - If true, uses assertive live region
         */
        announce: function(message, urgent = false) {
            const container = document.getElementById(urgent ? 'sr-alerts' : 'sr-announcements');
            if (container) {
                // Clear and re-set to trigger announcement
                container.textContent = '';
                requestAnimationFrame(() => {
                    container.textContent = message;
                });
            }
        },

        /**
         * Set focus to main content (useful after navigation)
         */
        focusMain: function() {
            const main = document.getElementById('main-content');
            if (main) {
                main.setAttribute('tabindex', '-1');
                main.focus();
                main.removeAttribute('tabindex');
            }
        },

        /**
         * Trap focus within an element (for modals)
         * @param {HTMLElement} element - The element to trap focus within
         * @returns {Function} - Call this function to release the trap
         */
        trapFocus: function(element) {
            const focusableSelectors = [
                'button:not([disabled])',
                'a[href]',
                'input:not([disabled])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                '[tabindex]:not([tabindex="-1"])'
            ].join(', ');

            const focusableElements = element.querySelectorAll(focusableSelectors);
            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];

            element.setAttribute('data-focus-trap', 'active');

            const trapHandler = (e) => {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            };

            element.addEventListener('keydown', trapHandler);
            firstFocusable?.focus();

            // Return function to release trap
            return () => {
                element.removeEventListener('keydown', trapHandler);
                element.removeAttribute('data-focus-trap');
            };
        },

        /**
         * Check if user prefers reduced motion
         * @returns {boolean}
         */
        prefersReducedMotion: function() {
            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        },

        /**
         * Check if user prefers high contrast
         * @returns {boolean}
         */
        prefersHighContrast: function() {
            return window.matchMedia('(prefers-contrast: more)').matches;
        }
    };

    // Auto-announce page title on load for SPA navigation
    document.addEventListener('DOMContentLoaded', function() {
        const pageTitle = document.title;
        if (pageTitle) {
            // Small delay to ensure screen reader is ready
            setTimeout(() => {
                window.A11y.announce('Page loaded: ' + pageTitle);
            }, 100);
        }
    });
</script>
