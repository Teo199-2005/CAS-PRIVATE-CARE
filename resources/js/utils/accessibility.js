/**
 * Accessibility Utilities Module
 * ==============================
 * WCAG 2.1 AAA Compliant Accessibility Helpers
 * 
 * This module provides:
 * - Focus management
 * - Keyboard navigation
 * - Screen reader announcements
 * - Focus trap for modals
 * - Reduced motion detection
 * - Touch/pointer detection
 * 
 * @version 1.0.0
 * @date January 28, 2026
 */

/**
 * Announce message to screen readers
 * @param {string} message - The message to announce
 * @param {('polite'|'assertive')} priority - Announcement priority
 */
export function announce(message, priority = 'polite') {
    const container = priority === 'assertive' 
        ? document.getElementById('a11y-assertive')
        : document.getElementById('a11y-announcer');
    
    if (!container) {
        console.warn(`[A11y] Announcement container not found for priority: ${priority}`);
        return;
    }
    
    // Clear and announce (ensures screen readers pick up the change)
    container.textContent = '';
    
    // Use requestAnimationFrame to ensure the empty state is processed
    requestAnimationFrame(() => {
        container.textContent = message;
    });
}

/**
 * Announce error to screen readers (assertive)
 * @param {string} message - Error message to announce
 */
export function announceError(message) {
    announce(message, 'assertive');
}

/**
 * Announce success message
 * @param {string} message - Success message to announce
 */
export function announceSuccess(message) {
    announce(`Success: ${message}`, 'polite');
}

/**
 * Check if user prefers reduced motion
 * @returns {boolean}
 */
export function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Check if user is using a touch/coarse pointer
 * @returns {boolean}
 */
export function isTouchDevice() {
    return window.matchMedia('(pointer: coarse)').matches;
}

/**
 * Check if user prefers high contrast
 * @returns {boolean}
 */
export function prefersHighContrast() {
    return window.matchMedia('(prefers-contrast: high)').matches ||
           window.matchMedia('(forced-colors: active)').matches;
}

/**
 * Check if user prefers dark color scheme
 * @returns {boolean}
 */
export function prefersDarkMode() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

/**
 * Get all focusable elements within a container
 * @param {HTMLElement} container - Container element
 * @returns {HTMLElement[]}
 */
export function getFocusableElements(container = document) {
    const focusableSelectors = [
        'a[href]:not([disabled]):not([tabindex="-1"])',
        'button:not([disabled]):not([tabindex="-1"])',
        'input:not([disabled]):not([type="hidden"]):not([tabindex="-1"])',
        'select:not([disabled]):not([tabindex="-1"])',
        'textarea:not([disabled]):not([tabindex="-1"])',
        '[tabindex]:not([tabindex="-1"]):not([disabled])',
        'audio[controls]:not([tabindex="-1"])',
        'video[controls]:not([tabindex="-1"])',
        '[contenteditable]:not([contenteditable="false"]):not([tabindex="-1"])',
        'details > summary:first-of-type:not([tabindex="-1"])',
        'iframe:not([tabindex="-1"])',
    ].join(', ');
    
    const elements = Array.from(container.querySelectorAll(focusableSelectors));
    
    // Filter out hidden elements
    return elements.filter(el => {
        return !el.hasAttribute('hidden') &&
               !el.closest('[hidden]') &&
               el.offsetParent !== null &&
               getComputedStyle(el).visibility !== 'hidden';
    });
}

/**
 * Create a focus trap within a container
 * Useful for modals, dialogs, and dropdown menus
 * @param {HTMLElement} container - Container to trap focus within
 * @returns {{ activate: Function, deactivate: Function, updateFocusableElements: Function }}
 */
export function createFocusTrap(container) {
    let focusableElements = [];
    let firstFocusableElement = null;
    let lastFocusableElement = null;
    let previouslyFocusedElement = null;
    let isActive = false;
    
    const updateFocusableElements = () => {
        focusableElements = getFocusableElements(container);
        firstFocusableElement = focusableElements[0];
        lastFocusableElement = focusableElements[focusableElements.length - 1];
    };
    
    const handleKeyDown = (event) => {
        if (!isActive) return;
        
        if (event.key === 'Tab') {
            updateFocusableElements();
            
            if (focusableElements.length === 0) {
                event.preventDefault();
                return;
            }
            
            if (event.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstFocusableElement) {
                    event.preventDefault();
                    lastFocusableElement?.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastFocusableElement) {
                    event.preventDefault();
                    firstFocusableElement?.focus();
                }
            }
        }
        
        // Close on Escape
        if (event.key === 'Escape') {
            const closeEvent = new CustomEvent('focustrap:escape', {
                bubbles: true,
                cancelable: true
            });
            container.dispatchEvent(closeEvent);
        }
    };
    
    const activate = (options = {}) => {
        const { initialFocus = null, preventScroll = false } = options;
        
        previouslyFocusedElement = document.activeElement;
        isActive = true;
        
        updateFocusableElements();
        
        // Focus initial element or first focusable
        const elementToFocus = initialFocus || firstFocusableElement;
        if (elementToFocus) {
            elementToFocus.focus({ preventScroll });
        }
        
        document.addEventListener('keydown', handleKeyDown);
        
        // Set aria-hidden on siblings
        Array.from(document.body.children).forEach(child => {
            if (child !== container && !child.contains(container)) {
                child.setAttribute('data-inert', child.getAttribute('aria-hidden') || 'false');
                child.setAttribute('aria-hidden', 'true');
            }
        });
    };
    
    const deactivate = (options = {}) => {
        const { returnFocus = true, preventScroll = false } = options;
        
        isActive = false;
        document.removeEventListener('keydown', handleKeyDown);
        
        // Restore aria-hidden on siblings
        Array.from(document.body.children).forEach(child => {
            const originalValue = child.getAttribute('data-inert');
            if (originalValue !== null) {
                if (originalValue === 'false') {
                    child.removeAttribute('aria-hidden');
                } else {
                    child.setAttribute('aria-hidden', originalValue);
                }
                child.removeAttribute('data-inert');
            }
        });
        
        // Return focus to previously focused element
        if (returnFocus && previouslyFocusedElement) {
            previouslyFocusedElement.focus({ preventScroll });
        }
    };
    
    return {
        activate,
        deactivate,
        updateFocusableElements
    };
}

/**
 * Handle roving tabindex for composite widgets
 * (tab panels, menu bars, radio groups, etc.)
 * @param {HTMLElement} container - Container element
 * @param {Object} options - Configuration options
 */
export function createRovingTabindex(container, options = {}) {
    const {
        selector = '[role="tab"], [role="menuitem"], [role="option"]',
        orientation = 'horizontal', // 'horizontal', 'vertical', 'both'
        wrap = true,
        onSelect = null
    } = options;
    
    let items = [];
    let currentIndex = 0;
    
    const update = () => {
        items = Array.from(container.querySelectorAll(selector));
        
        // Set initial tabindex
        items.forEach((item, index) => {
            item.setAttribute('tabindex', index === currentIndex ? '0' : '-1');
        });
    };
    
    const moveFocus = (delta) => {
        let newIndex = currentIndex + delta;
        
        if (wrap) {
            if (newIndex < 0) newIndex = items.length - 1;
            if (newIndex >= items.length) newIndex = 0;
        } else {
            newIndex = Math.max(0, Math.min(items.length - 1, newIndex));
        }
        
        if (newIndex !== currentIndex) {
            items[currentIndex].setAttribute('tabindex', '-1');
            currentIndex = newIndex;
            items[currentIndex].setAttribute('tabindex', '0');
            items[currentIndex].focus();
        }
    };
    
    const handleKeyDown = (event) => {
        let handled = false;
        
        switch (event.key) {
            case 'ArrowLeft':
                if (orientation === 'horizontal' || orientation === 'both') {
                    moveFocus(-1);
                    handled = true;
                }
                break;
            case 'ArrowRight':
                if (orientation === 'horizontal' || orientation === 'both') {
                    moveFocus(1);
                    handled = true;
                }
                break;
            case 'ArrowUp':
                if (orientation === 'vertical' || orientation === 'both') {
                    moveFocus(-1);
                    handled = true;
                }
                break;
            case 'ArrowDown':
                if (orientation === 'vertical' || orientation === 'both') {
                    moveFocus(1);
                    handled = true;
                }
                break;
            case 'Home':
                items[currentIndex].setAttribute('tabindex', '-1');
                currentIndex = 0;
                items[currentIndex].setAttribute('tabindex', '0');
                items[currentIndex].focus();
                handled = true;
                break;
            case 'End':
                items[currentIndex].setAttribute('tabindex', '-1');
                currentIndex = items.length - 1;
                items[currentIndex].setAttribute('tabindex', '0');
                items[currentIndex].focus();
                handled = true;
                break;
            case 'Enter':
            case ' ':
                if (onSelect) {
                    onSelect(items[currentIndex], currentIndex);
                    handled = true;
                }
                break;
        }
        
        if (handled) {
            event.preventDefault();
            event.stopPropagation();
        }
    };
    
    const handleFocus = (event) => {
        const index = items.indexOf(event.target);
        if (index !== -1 && index !== currentIndex) {
            items[currentIndex].setAttribute('tabindex', '-1');
            currentIndex = index;
            items[currentIndex].setAttribute('tabindex', '0');
        }
    };
    
    const init = () => {
        update();
        container.addEventListener('keydown', handleKeyDown);
        container.addEventListener('focusin', handleFocus);
    };
    
    const destroy = () => {
        container.removeEventListener('keydown', handleKeyDown);
        container.removeEventListener('focusin', handleFocus);
    };
    
    return {
        init,
        destroy,
        update,
        getCurrentIndex: () => currentIndex,
        setCurrentIndex: (index) => {
            if (index >= 0 && index < items.length) {
                items[currentIndex].setAttribute('tabindex', '-1');
                currentIndex = index;
                items[currentIndex].setAttribute('tabindex', '0');
            }
        }
    };
}

/**
 * Add keyboard navigation to a list/grid
 * @param {HTMLElement} container 
 * @param {Object} options 
 */
export function enableKeyboardNavigation(container, options = {}) {
    const {
        itemSelector = '[role="listitem"], [role="gridcell"], li',
        onActivate = null
    } = options;
    
    container.addEventListener('keydown', (event) => {
        const items = Array.from(container.querySelectorAll(itemSelector));
        const currentIndex = items.indexOf(document.activeElement);
        
        if (currentIndex === -1) return;
        
        let newIndex = currentIndex;
        let handled = false;
        
        switch (event.key) {
            case 'ArrowDown':
            case 'ArrowRight':
                newIndex = Math.min(items.length - 1, currentIndex + 1);
                handled = true;
                break;
            case 'ArrowUp':
            case 'ArrowLeft':
                newIndex = Math.max(0, currentIndex - 1);
                handled = true;
                break;
            case 'Home':
                newIndex = 0;
                handled = true;
                break;
            case 'End':
                newIndex = items.length - 1;
                handled = true;
                break;
            case 'Enter':
            case ' ':
                if (onActivate) {
                    onActivate(items[currentIndex], currentIndex);
                    handled = true;
                }
                break;
        }
        
        if (handled) {
            event.preventDefault();
            if (newIndex !== currentIndex) {
                items[newIndex].focus();
            }
        }
    });
}

/**
 * Manage loading state with accessibility announcements
 * @param {HTMLElement} element 
 * @param {boolean} isLoading 
 * @param {string} loadingText 
 */
export function setLoadingState(element, isLoading, loadingText = 'Loading...') {
    if (isLoading) {
        element.setAttribute('aria-busy', 'true');
        element.setAttribute('data-loading-text', loadingText);
        announce(loadingText);
    } else {
        element.removeAttribute('aria-busy');
        element.removeAttribute('data-loading-text');
        announce('Content loaded');
    }
}

/**
 * Validate form field and announce errors
 * @param {HTMLInputElement} field 
 * @param {string|null} errorMessage 
 */
export function setFieldError(field, errorMessage) {
    const errorId = `${field.id}-error`;
    let errorElement = document.getElementById(errorId);
    
    if (errorMessage) {
        field.setAttribute('aria-invalid', 'true');
        
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.id = errorId;
            errorElement.className = 'field-error';
            errorElement.setAttribute('role', 'alert');
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = errorMessage;
        field.setAttribute('aria-describedby', errorId);
        
        announceError(errorMessage);
    } else {
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
        
        if (errorElement) {
            errorElement.remove();
        }
    }
}

/**
 * Create accessible tooltip
 * @param {HTMLElement} trigger - Element that triggers tooltip
 * @param {string} content - Tooltip content
 * @returns {{ show: Function, hide: Function, destroy: Function }}
 */
export function createTooltip(trigger, content) {
    const tooltipId = `tooltip-${Math.random().toString(36).substr(2, 9)}`;
    let tooltipElement = null;
    
    const show = () => {
        if (!tooltipElement) {
            tooltipElement = document.createElement('div');
            tooltipElement.id = tooltipId;
            tooltipElement.role = 'tooltip';
            tooltipElement.className = 'a11y-tooltip';
            tooltipElement.textContent = content;
            document.body.appendChild(tooltipElement);
        }
        
        // Position tooltip
        const rect = trigger.getBoundingClientRect();
        tooltipElement.style.cssText = `
            position: fixed;
            top: ${rect.bottom + 8}px;
            left: ${rect.left + (rect.width / 2)}px;
            transform: translateX(-50%);
            z-index: 10000;
            padding: 8px 12px;
            background: #1f2937;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            max-width: 300px;
            pointer-events: none;
        `;
        
        trigger.setAttribute('aria-describedby', tooltipId);
    };
    
    const hide = () => {
        if (tooltipElement) {
            tooltipElement.remove();
            tooltipElement = null;
        }
        trigger.removeAttribute('aria-describedby');
    };
    
    const destroy = () => {
        hide();
        trigger.removeEventListener('mouseenter', show);
        trigger.removeEventListener('mouseleave', hide);
        trigger.removeEventListener('focus', show);
        trigger.removeEventListener('blur', hide);
    };
    
    // Attach event listeners
    trigger.addEventListener('mouseenter', show);
    trigger.addEventListener('mouseleave', hide);
    trigger.addEventListener('focus', show);
    trigger.addEventListener('blur', hide);
    
    return { show, hide, destroy };
}

/**
 * Initialize skip links functionality
 */
export function initSkipLinks() {
    document.querySelectorAll('.skip-link, .skip-to-content').forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            
            if (target) {
                // Ensure target is focusable
                if (!target.hasAttribute('tabindex')) {
                    target.setAttribute('tabindex', '-1');
                }
                
                target.focus();
                target.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
            }
        });
    });
}

/**
 * Initialize all accessibility features
 */
export function initAccessibility() {
    // Initialize skip links
    initSkipLinks();
    
    // Add keyboard user detection
    let isKeyboardUser = false;
    
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            isKeyboardUser = true;
            document.body.classList.add('keyboard-user');
        }
    });
    
    document.addEventListener('mousedown', () => {
        isKeyboardUser = false;
        document.body.classList.remove('keyboard-user');
    });
    
    // Log accessibility mode info
    if (process.env.NODE_ENV === 'development') {
        console.log('[A11y] Accessibility utilities initialized');
        console.log('[A11y] Reduced motion:', prefersReducedMotion());
        console.log('[A11y] High contrast:', prefersHighContrast());
        console.log('[A11y] Touch device:', isTouchDevice());
        console.log('[A11y] Dark mode:', prefersDarkMode());
    }
}

// Export default initialization
export default {
    announce,
    announceError,
    announceSuccess,
    prefersReducedMotion,
    isTouchDevice,
    prefersHighContrast,
    prefersDarkMode,
    getFocusableElements,
    createFocusTrap,
    createRovingTabindex,
    enableKeyboardNavigation,
    setLoadingState,
    setFieldError,
    createTooltip,
    initSkipLinks,
    initAccessibility
};
