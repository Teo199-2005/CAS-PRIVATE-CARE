/**
 * Custom Vue Directives Index
 * 
 * Exports all custom directives for easy importing.
 * 
 * Usage in app.js:
 *   import { vSwipe, vLongpress, vPullRefresh } from './directives';
 *   app.directive('swipe', vSwipe);
 *   app.directive('longpress', vLongpress);
 *   app.directive('pull-refresh', vPullRefresh);
 */

// Touch gesture directives (mobile support)
export { vSwipe, vLongpress, vPullRefresh } from './touch.js';

// Accessibility directives
import accessibilityDirectives, { 
    vFocusTrap, 
    vAnnounce, 
    vSkipLink, 
    vKeyboardNav, 
    vRovingTabindex,
    registerAccessibilityDirectives 
} from './accessibility.js';

export { 
    vFocusTrap, 
    vAnnounce, 
    vSkipLink, 
    vKeyboardNav, 
    vRovingTabindex,
    registerAccessibilityDirectives 
};

// Re-export all for convenience
import { vSwipe, vLongpress, vPullRefresh } from './touch.js';

export default {
    // Touch gestures
    swipe: vSwipe,
    longpress: vLongpress,
    'pull-refresh': vPullRefresh,
    
    // Accessibility
    'focus-trap': vFocusTrap,
    announce: vAnnounce,
    'skip-link': vSkipLink,
    'keyboard-nav': vKeyboardNav,
    'roving-tabindex': vRovingTabindex,
};

/**
 * Plugin to register all custom directives globally
 * 
 * Usage:
 *   import { DirectivesPlugin } from './directives';
 *   app.use(DirectivesPlugin);
 */
export const DirectivesPlugin = {
    install(app) {
        // Touch gestures
        app.directive('swipe', vSwipe);
        app.directive('longpress', vLongpress);
        app.directive('pull-refresh', vPullRefresh);
        
        // Accessibility (use provided function)
        registerAccessibilityDirectives(app);
    }
};
