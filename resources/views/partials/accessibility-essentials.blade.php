{{--
    Accessibility Essentials Partial
    ================================
    WCAG 2.1 AAA Compliant Accessibility Features
    
    Include this partial in the <head> section of all pages:
    @include('partials.accessibility-essentials')
    
    This provides:
    - Skip navigation link styles
    - Focus visible improvements
    - Reduced motion support
    - High contrast mode support
    - Screen reader utilities
    - Touch target enhancements
    
    @version 1.0.0
    @date January 28, 2026
--}}

<style>
    /* ==========================================================================
       ACCESSIBILITY ESSENTIALS - WCAG 2.1 AAA COMPLIANT
       ========================================================================== */
    
    /* Skip Navigation Link - WCAG 2.4.1 */
    .skip-link,
    .skip-to-content {
        position: absolute;
        top: -100%;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100000;
        padding: 1rem 2rem;
        background: #1e40af;
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        text-decoration: none;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        transition: top 0.3s ease-in-out;
        white-space: nowrap;
    }
    
    .skip-link:focus,
    .skip-to-content:focus {
        top: 0;
        outline: 3px solid #f97316;
        outline-offset: 2px;
    }
    
    .skip-link:focus-visible,
    .skip-to-content:focus-visible {
        top: 0;
    }
    
    /* Focus Visible Improvements - WCAG 2.4.7 */
    :focus-visible {
        outline: 3px solid #3b82f6 !important;
        outline-offset: 2px !important;
    }
    
    /* High contrast focus for buttons */
    button:focus-visible,
    [role="button"]:focus-visible,
    a:focus-visible,
    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible {
        outline: 3px solid #1e40af !important;
        outline-offset: 2px !important;
        box-shadow: 0 0 0 6px rgba(30, 64, 175, 0.2) !important;
    }
    
    /* Remove default focus outline for mouse users */
    :focus:not(:focus-visible) {
        outline: none;
    }
    
    /* Screen Reader Only Utility */
    .sr-only,
    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    
    /* Show on focus for sr-only elements */
    .sr-only-focusable:focus,
    .sr-only-focusable:focus-within {
        position: static !important;
        width: auto !important;
        height: auto !important;
        padding: inherit !important;
        margin: inherit !important;
        overflow: visible !important;
        clip: auto !important;
        white-space: normal !important;
    }
    
    /* Reduced Motion Support - WCAG 2.3.3 */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
        
        /* Keep essential animations but make them instant */
        .skip-link,
        .skip-to-content {
            transition: none !important;
        }
    }
    
    /* High Contrast Mode Support */
    @media (prefers-contrast: high) {
        :focus-visible {
            outline: 4px solid currentColor !important;
            outline-offset: 4px !important;
        }
        
        button,
        .btn,
        a {
            border: 2px solid currentColor !important;
        }
        
        /* Ensure text contrast is maintained */
        .text-muted,
        .text-secondary,
        .text-grey {
            color: inherit !important;
            opacity: 0.8 !important;
        }
    }
    
    /* Forced Colors Mode (Windows High Contrast) */
    @media (forced-colors: active) {
        .skip-link:focus,
        button:focus,
        a:focus {
            outline: 3px solid CanvasText !important;
        }
        
        /* Ensure icons are visible */
        [class*="bi-"],
        [class*="mdi-"] {
            forced-color-adjust: none;
        }
    }
    
    /* Touch Target Sizes - WCAG 2.5.5 (AAA: 44x44px minimum) */
    @media (pointer: coarse) {
        button,
        [role="button"],
        a,
        input[type="checkbox"],
        input[type="radio"],
        select,
        .clickable,
        .touchable {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Ensure adequate spacing between touch targets */
        button + button,
        a + a,
        .btn + .btn {
            margin-left: 8px;
        }
    }
    
    /* Text Spacing Support - WCAG 1.4.12 */
    /* These values can be overridden by user stylesheets */
    body {
        line-height: 1.5;
        letter-spacing: 0.12em;
        word-spacing: 0.16em;
    }
    
    p {
        margin-bottom: 2em;
    }
    
    /* Reset for elements that shouldn't have extra spacing */
    code,
    pre,
    .monospace {
        letter-spacing: normal;
        word-spacing: normal;
    }
    
    /* Ensure content is accessible with 400% zoom - WCAG 1.4.10 */
    @media (max-width: 320px) {
        body {
            font-size: 16px;
            line-height: 1.6;
        }
        
        /* Prevent horizontal scroll */
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
        }
    }
    
    /* Focus trap indicator for modals */
    [role="dialog"],
    [role="alertdialog"],
    .modal {
        /* Visual indication that focus is trapped */
        outline: none;
    }
    
    [role="dialog"]:focus-within,
    [role="alertdialog"]:focus-within,
    .modal:focus-within {
        box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.3);
    }
    
    /* Loading state announcements */
    [aria-busy="true"]::after {
        content: attr(data-loading-text);
        position: absolute;
        left: -10000px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }
    
    /* Error state visibility */
    [aria-invalid="true"] {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2) !important;
    }
    
    /* Required field indicator */
    [aria-required="true"]::after,
    .required::after {
        content: " *";
        color: #dc2626;
        font-weight: bold;
    }
    
    /* Live region styles */
    [aria-live] {
        /* Ensure live regions are visually stable */
        min-height: 1em;
    }
    
    /* Status messages */
    [role="status"],
    [role="alert"] {
        /* Ensure these are not hidden */
        display: block;
    }
    
    /* Ensure decorative elements are hidden from screen readers */
    [aria-hidden="true"],
    .decorative {
        pointer-events: none;
        user-select: none;
    }
</style>

{{-- Screen Reader Announcement Container --}}
<div id="a11y-announcer" 
     class="sr-only" 
     aria-live="polite" 
     aria-atomic="true"
     role="status">
</div>

{{-- Assertive announcements for errors --}}
<div id="a11y-assertive" 
     class="sr-only" 
     aria-live="assertive" 
     aria-atomic="true"
     role="alert">
</div>
