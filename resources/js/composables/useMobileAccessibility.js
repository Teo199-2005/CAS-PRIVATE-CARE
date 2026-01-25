/**
 * Mobile Accessibility Composable
 * ================================
 * Provides focus trap, ARIA announcements, and touch gestures
 * for mobile-first accessible components.
 * 
 * @version 1.0.0
 * @date January 24, 2026
 * 
 * Features:
 * - Focus trap for modals and drawers
 * - Screen reader announcements via ARIA live regions
 * - Touch gesture support (swipe to open/close drawer)
 * - Escape key handling
 * - Reduced motion detection
 */

import { ref, watch, onMounted, onUnmounted, nextTick, readonly } from 'vue';

/**
 * Focus Trap Composable
 * Traps focus within a container when active (for modals, drawers, etc.)
 */
export function useFocusTrap(isOpen, containerSelector, options = {}) {
    const {
        returnFocusOnClose = true,
        initialFocusSelector = null,
        escapeDeactivates = true
    } = options;

    const previousActiveElement = ref(null);
    const isTrapping = ref(false);

    const getFocusableElements = (container) => {
        if (!container) return [];
        return Array.from(container.querySelectorAll(
            'button:not([disabled]):not([tabindex="-1"]), ' +
            '[href]:not([tabindex="-1"]), ' +
            'input:not([disabled]):not([tabindex="-1"]), ' +
            'select:not([disabled]):not([tabindex="-1"]), ' +
            'textarea:not([disabled]):not([tabindex="-1"]), ' +
            '[tabindex]:not([tabindex="-1"])'
        )).filter(el => {
            // Check if element is visible
            const style = window.getComputedStyle(el);
            return style.display !== 'none' && 
                   style.visibility !== 'hidden' && 
                   el.offsetParent !== null;
        });
    };

    const handleKeyDown = (e) => {
        if (!isTrapping.value) return;

        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Handle Escape key
        if (escapeDeactivates && e.key === 'Escape') {
            e.preventDefault();
            isOpen.value = false;
            return;
        }

        // Handle Tab key for focus trapping
        if (e.key !== 'Tab') return;

        const focusables = getFocusableElements(container);
        if (focusables.length === 0) return;

        const firstElement = focusables[0];
        const lastElement = focusables[focusables.length - 1];

        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    };

    const activateTrap = async () => {
        await nextTick();

        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Store current active element
        previousActiveElement.value = document.activeElement;
        isTrapping.value = true;

        // Set initial focus
        const focusables = getFocusableElements(container);
        if (focusables.length > 0) {
            const initialElement = initialFocusSelector 
                ? container.querySelector(initialFocusSelector)
                : focusables[0];
            
            (initialElement || focusables[0])?.focus();
        }

        // Add event listener
        document.addEventListener('keydown', handleKeyDown);
    };

    const deactivateTrap = () => {
        isTrapping.value = false;
        document.removeEventListener('keydown', handleKeyDown);

        // Return focus to previous element
        if (returnFocusOnClose && previousActiveElement.value) {
            previousActiveElement.value.focus();
        }
    };

    watch(isOpen, async (open) => {
        if (open) {
            await activateTrap();
        } else {
            deactivateTrap();
        }
    }, { immediate: true });

    onUnmounted(() => {
        deactivateTrap();
    });

    return {
        isTrapping: readonly(isTrapping),
        activateTrap,
        deactivateTrap
    };
}

/**
 * ARIA Announcer Composable
 * Announces messages to screen readers via live region
 */
export function useAriaAnnouncer() {
    const announcement = ref('');
    const politeness = ref('polite'); // 'polite' or 'assertive'

    const announce = (message, priority = 'polite') => {
        // Clear first to ensure re-announcement of same message
        announcement.value = '';
        politeness.value = priority;
        
        // Use requestAnimationFrame for better screen reader support
        requestAnimationFrame(() => {
            announcement.value = message;
        });
    };

    const announcePolite = (message) => announce(message, 'polite');
    const announceAssertive = (message) => announce(message, 'assertive');

    // Clear announcement after 5 seconds to prevent stale content
    watch(announcement, (msg) => {
        if (msg) {
            setTimeout(() => {
                if (announcement.value === msg) {
                    announcement.value = '';
                }
            }, 5000);
        }
    });

    return {
        announcement: readonly(announcement),
        politeness: readonly(politeness),
        announce,
        announcePolite,
        announceAssertive
    };
}

/**
 * Touch Gesture Composable
 * Provides swipe detection for mobile navigation
 */
export function useTouchGestures(options = {}) {
    const {
        minSwipeDistance = 80,
        maxSwipeTime = 300,
        edgeThreshold = 30,
        preventVerticalScroll = false
    } = options;

    const touchStartX = ref(0);
    const touchStartY = ref(0);
    const touchStartTime = ref(0);
    const isSwiping = ref(false);

    const swipeDirection = ref(null); // 'left', 'right', 'up', 'down', or null

    const onSwipeLeft = ref(null);
    const onSwipeRight = ref(null);
    const onSwipeUp = ref(null);
    const onSwipeDown = ref(null);
    const onEdgeSwipeRight = ref(null); // Swipe from left edge

    const handleTouchStart = (e) => {
        touchStartX.value = e.touches[0].clientX;
        touchStartY.value = e.touches[0].clientY;
        touchStartTime.value = Date.now();
        isSwiping.value = true;
        swipeDirection.value = null;
    };

    const handleTouchMove = (e) => {
        if (!isSwiping.value) return;

        const deltaX = e.touches[0].clientX - touchStartX.value;
        const deltaY = e.touches[0].clientY - touchStartY.value;

        // Determine if this is a horizontal or vertical swipe
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            swipeDirection.value = deltaX > 0 ? 'right' : 'left';
            if (preventVerticalScroll) {
                e.preventDefault();
            }
        } else {
            swipeDirection.value = deltaY > 0 ? 'down' : 'up';
        }
    };

    const handleTouchEnd = (e) => {
        if (!isSwiping.value) return;
        isSwiping.value = false;

        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const touchEndTime = Date.now();

        const deltaX = touchEndX - touchStartX.value;
        const deltaY = touchEndY - touchStartY.value;
        const swipeTime = touchEndTime - touchStartTime.value;

        // Check if swipe was fast enough
        if (swipeTime > maxSwipeTime) {
            swipeDirection.value = null;
            return;
        }

        // Check if swipe distance is sufficient
        const isHorizontalSwipe = Math.abs(deltaX) > minSwipeDistance && Math.abs(deltaX) > Math.abs(deltaY);
        const isVerticalSwipe = Math.abs(deltaY) > minSwipeDistance && Math.abs(deltaY) > Math.abs(deltaX);

        if (isHorizontalSwipe) {
            // Check for edge swipe (from left edge)
            if (touchStartX.value <= edgeThreshold && deltaX > 0) {
                onEdgeSwipeRight.value?.();
            } else if (deltaX > 0) {
                onSwipeRight.value?.();
            } else {
                onSwipeLeft.value?.();
            }
        } else if (isVerticalSwipe) {
            if (deltaY > 0) {
                onSwipeDown.value?.();
            } else {
                onSwipeUp.value?.();
            }
        }

        swipeDirection.value = null;
    };

    const bindTouchEvents = (element) => {
        if (!element) return;
        
        element.addEventListener('touchstart', handleTouchStart, { passive: true });
        element.addEventListener('touchmove', handleTouchMove, { passive: !preventVerticalScroll });
        element.addEventListener('touchend', handleTouchEnd, { passive: true });
    };

    const unbindTouchEvents = (element) => {
        if (!element) return;
        
        element.removeEventListener('touchstart', handleTouchStart);
        element.removeEventListener('touchmove', handleTouchMove);
        element.removeEventListener('touchend', handleTouchEnd);
    };

    return {
        isSwiping: readonly(isSwiping),
        swipeDirection: readonly(swipeDirection),
        onSwipeLeft,
        onSwipeRight,
        onSwipeUp,
        onSwipeDown,
        onEdgeSwipeRight,
        bindTouchEvents,
        unbindTouchEvents
    };
}

/**
 * Reduced Motion Detection
 * Respects user's motion preferences
 */
export function useReducedMotion() {
    const prefersReducedMotion = ref(false);

    const updatePreference = () => {
        prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    };

    onMounted(() => {
        updatePreference();
        
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        mediaQuery.addEventListener('change', updatePreference);

        onUnmounted(() => {
            mediaQuery.removeEventListener('change', updatePreference);
        });
    });

    return {
        prefersReducedMotion: readonly(prefersReducedMotion)
    };
}

/**
 * Mobile Viewport Detection
 * Provides reactive viewport information
 */
export function useMobileViewport() {
    const isMobile = ref(false);
    const isTablet = ref(false);
    const isDesktop = ref(false);
    const viewportWidth = ref(0);
    const viewportHeight = ref(0);
    const isLandscape = ref(false);
    const hasNotch = ref(false);

    const updateViewport = () => {
        viewportWidth.value = window.innerWidth;
        viewportHeight.value = window.innerHeight;
        
        isMobile.value = viewportWidth.value <= 768;
        isTablet.value = viewportWidth.value > 768 && viewportWidth.value <= 1024;
        isDesktop.value = viewportWidth.value > 1024;
        isLandscape.value = viewportWidth.value > viewportHeight.value;

        // Check for notch (iOS devices with safe area insets)
        const safeAreaTop = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--sat') || '0');
        hasNotch.value = safeAreaTop > 20;
    };

    onMounted(() => {
        updateViewport();
        window.addEventListener('resize', updateViewport);
        window.addEventListener('orientationchange', updateViewport);

        onUnmounted(() => {
            window.removeEventListener('resize', updateViewport);
            window.removeEventListener('orientationchange', updateViewport);
        });
    });

    return {
        isMobile: readonly(isMobile),
        isTablet: readonly(isTablet),
        isDesktop: readonly(isDesktop),
        viewportWidth: readonly(viewportWidth),
        viewportHeight: readonly(viewportHeight),
        isLandscape: readonly(isLandscape),
        hasNotch: readonly(hasNotch)
    };
}

/**
 * Combined Mobile Accessibility Composable
 * A convenience wrapper that combines all mobile accessibility features
 */
export function useMobileAccessibility(options = {}) {
    const { prefersReducedMotion } = useReducedMotion();
    
    // Focus trap functions (simplified interface)
    let currentFocusTrap = null;
    
    const trapFocus = (containerSelector) => {
        const container = typeof containerSelector === 'string' 
            ? document.querySelector(containerSelector) 
            : containerSelector;
        
        if (!container) return;
        
        // Store current active element
        const previousActiveElement = document.activeElement;
        
        const getFocusableElements = () => {
            return Array.from(container.querySelectorAll(
                'button:not([disabled]):not([tabindex="-1"]), ' +
                '[href]:not([tabindex="-1"]), ' +
                'input:not([disabled]):not([tabindex="-1"]), ' +
                'select:not([disabled]):not([tabindex="-1"]), ' +
                'textarea:not([disabled]):not([tabindex="-1"]), ' +
                '[tabindex]:not([tabindex="-1"])'
            )).filter(el => {
                const style = window.getComputedStyle(el);
                return style.display !== 'none' && style.visibility !== 'hidden';
            });
        };
        
        const handleKeyDown = (e) => {
            if (e.key !== 'Tab') return;
            
            const focusable = getFocusableElements();
            if (focusable.length === 0) return;
            
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        };
        
        container.addEventListener('keydown', handleKeyDown);
        
        // Focus first focusable element
        nextTick(() => {
            const focusable = getFocusableElements();
            if (focusable.length > 0) {
                focusable[0].focus();
            }
        });
        
        currentFocusTrap = {
            container,
            handleKeyDown,
            previousActiveElement
        };
    };
    
    const releaseFocus = () => {
        if (currentFocusTrap) {
            currentFocusTrap.container.removeEventListener('keydown', currentFocusTrap.handleKeyDown);
            if (currentFocusTrap.previousActiveElement) {
                currentFocusTrap.previousActiveElement.focus();
            }
            currentFocusTrap = null;
        }
    };
    
    // Swipe gesture handler
    const handleSwipeGestures = (element, callbacks = {}) => {
        if (!element) return { cleanup: () => {} };
        
        let touchStartX = 0;
        let touchStartY = 0;
        let touchStartTime = 0;
        
        const handleTouchStart = (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            touchStartTime = Date.now();
        };
        
        const handleTouchEnd = (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            const deltaTime = Date.now() - touchStartTime;
            
            // Quick swipe detection
            if (deltaTime < 300 && Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                if (deltaX > 0 && callbacks.onSwipeRight) {
                    callbacks.onSwipeRight();
                } else if (deltaX < 0 && callbacks.onSwipeLeft) {
                    callbacks.onSwipeLeft();
                }
            }
        };
        
        element.addEventListener('touchstart', handleTouchStart, { passive: true });
        element.addEventListener('touchend', handleTouchEnd, { passive: true });
        
        return {
            cleanup: () => {
                element.removeEventListener('touchstart', handleTouchStart);
                element.removeEventListener('touchend', handleTouchEnd);
            }
        };
    };
    
    return {
        trapFocus,
        releaseFocus,
        useReducedMotion: () => prefersReducedMotion,
        handleSwipeGestures,
        prefersReducedMotion
    };
}

export default {
    useFocusTrap,
    useAriaAnnouncer,
    useTouchGestures,
    useReducedMotion,
    useMobileViewport,
    useMobileAccessibility
};
