/**
 * CAS Private Care - Accessibility & Dark Mode Utilities
 * 
 * This module provides:
 * - Dark mode toggle with system preference detection
 * - Reduced motion support
 * - High contrast mode
 * - Focus management utilities
 * - Screen reader announcements
 */

class AccessibilityManager {
    constructor() {
        this.darkMode = false;
        this.highContrast = false;
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.init();
    }

    init() {
        // Check for saved preferences
        this.loadPreferences();
        
        // Apply initial settings
        this.applyDarkMode();
        this.applyHighContrast();
        
        // Listen for system preference changes
        this.setupMediaListeners();
        
        // Setup skip links
        this.setupSkipLinks();
        
        // Setup focus trap for modals
        this.setupFocusManagement();
        
        // Add ARIA live region for announcements
        this.createAnnouncementRegion();
    }

    loadPreferences() {
        const savedDarkMode = localStorage.getItem('cas-dark-mode');
        const savedHighContrast = localStorage.getItem('cas-high-contrast');
        
        if (savedDarkMode !== null) {
            this.darkMode = savedDarkMode === 'true';
        } else {
            // Use system preference
            this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        
        if (savedHighContrast !== null) {
            this.highContrast = savedHighContrast === 'true';
        }
    }

    savePreferences() {
        localStorage.setItem('cas-dark-mode', this.darkMode);
        localStorage.setItem('cas-high-contrast', this.highContrast);
    }

    setupMediaListeners() {
        // Dark mode system preference
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('cas-dark-mode') === null) {
                this.darkMode = e.matches;
                this.applyDarkMode();
            }
        });
        
        // Reduced motion system preference
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
            this.reducedMotion = e.matches;
            this.applyReducedMotion();
        });
    }

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        this.applyDarkMode();
        this.savePreferences();
        this.announce(this.darkMode ? 'Dark mode enabled' : 'Light mode enabled');
    }

    applyDarkMode() {
        document.documentElement.classList.toggle('dark-mode', this.darkMode);
        document.body.classList.toggle('dark-mode', this.darkMode);
        
        // Update meta theme color
        const metaTheme = document.querySelector('meta[name="theme-color"]');
        if (metaTheme) {
            metaTheme.content = this.darkMode ? '#1a1a2e' : '#ffffff';
        }
    }

    toggleHighContrast() {
        this.highContrast = !this.highContrast;
        this.applyHighContrast();
        this.savePreferences();
        this.announce(this.highContrast ? 'High contrast mode enabled' : 'High contrast mode disabled');
    }

    applyHighContrast() {
        document.documentElement.classList.toggle('high-contrast', this.highContrast);
        document.body.classList.toggle('high-contrast', this.highContrast);
    }

    applyReducedMotion() {
        document.documentElement.classList.toggle('reduced-motion', this.reducedMotion);
    }

    setupSkipLinks() {
        // Create skip link if not exists
        if (!document.getElementById('skip-link')) {
            const skipLink = document.createElement('a');
            skipLink.id = 'skip-link';
            skipLink.href = '#main-content';
            skipLink.className = 'skip-link';
            skipLink.textContent = 'Skip to main content';
            document.body.insertBefore(skipLink, document.body.firstChild);
        }
    }

    createAnnouncementRegion() {
        if (!document.getElementById('aria-live-region')) {
            const region = document.createElement('div');
            region.id = 'aria-live-region';
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('aria-atomic', 'true');
            region.className = 'sr-only';
            document.body.appendChild(region);
        }
    }

    announce(message) {
        const region = document.getElementById('aria-live-region');
        if (region) {
            region.textContent = '';
            // Use setTimeout to ensure the announcement is triggered
            setTimeout(() => {
                region.textContent = message;
            }, 100);
        }
    }

    setupFocusManagement() {
        // Track focus for modal dialogs
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close any open modals
                const openModals = document.querySelectorAll('[role="dialog"][aria-hidden="false"], .modal.show');
                openModals.forEach(modal => {
                    this.closeModal(modal);
                });
            }
        });
    }

    trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    lastFocusable.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    firstFocusable.focus();
                    e.preventDefault();
                }
            }
        });

        firstFocusable?.focus();
    }

    closeModal(modal) {
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('show');
        
        // Return focus to trigger element
        const triggerId = modal.getAttribute('data-trigger-id');
        if (triggerId) {
            document.getElementById(triggerId)?.focus();
        }
    }

    // Utility to ensure proper button accessibility
    static enhanceButton(button) {
        if (!button.getAttribute('role') && button.tagName !== 'BUTTON') {
            button.setAttribute('role', 'button');
        }
        if (!button.hasAttribute('tabindex')) {
            button.setAttribute('tabindex', '0');
        }
        if (button.tagName !== 'BUTTON') {
            button.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    button.click();
                }
            });
        }
    }

    // Utility to enhance form fields
    static enhanceFormField(field, label) {
        if (label && !field.getAttribute('aria-label') && !field.getAttribute('aria-labelledby')) {
            const labelId = label.id || `label-${Date.now()}`;
            label.id = labelId;
            field.setAttribute('aria-labelledby', labelId);
        }
        
        if (field.hasAttribute('required')) {
            field.setAttribute('aria-required', 'true');
        }
    }
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.accessibilityManager = new AccessibilityManager();
    });
} else {
    window.accessibilityManager = new AccessibilityManager();
}

// Export for module usage
export default AccessibilityManager;
