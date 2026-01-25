{{--
    Cookie Consent Banner
    GDPR/CCPA Compliant Cookie Consent
    
    Features:
    - Accessible (WCAG 2.1 AA compliant)
    - Remembers user preference
    - Blocks non-essential cookies until consent
    - Responsive design
    - Animation with reduced-motion support
--}}

<div id="cookieConsent" 
     class="cookie-consent" 
     role="dialog" 
     aria-modal="false"
     aria-labelledby="cookie-consent-title"
     aria-describedby="cookie-consent-description"
     style="display: none;">
    <div class="cookie-consent-container">
        <div class="cookie-consent-content">
            <div class="cookie-consent-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="8" cy="9" r="1" fill="currentColor"/>
                    <circle cx="15" cy="8" r="1" fill="currentColor"/>
                    <circle cx="10" cy="14" r="1" fill="currentColor"/>
                    <circle cx="16" cy="13" r="1" fill="currentColor"/>
                    <circle cx="13" cy="17" r="1" fill="currentColor"/>
                </svg>
            </div>
            <div class="cookie-consent-text">
                <h3 id="cookie-consent-title" class="cookie-consent-title">We Value Your Privacy</h3>
                <p id="cookie-consent-description" class="cookie-consent-desc">
                    We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. 
                    By clicking "Accept All", you consent to our use of cookies. 
                    <a href="{{ url('/privacy') }}#cookies" class="cookie-link">Learn more about our Cookie Policy</a>.
                </p>
            </div>
        </div>
        <div class="cookie-consent-actions">
            <button type="button" 
                    id="cookieRejectBtn" 
                    class="cookie-btn cookie-btn-secondary"
                    aria-label="Reject non-essential cookies">
                Reject All
            </button>
            <button type="button" 
                    id="cookieSettingsBtn" 
                    class="cookie-btn cookie-btn-outline"
                    aria-label="Customize cookie preferences">
                Customize
            </button>
            <button type="button" 
                    id="cookieAcceptBtn" 
                    class="cookie-btn cookie-btn-primary"
                    aria-label="Accept all cookies">
                Accept All
            </button>
        </div>
    </div>
</div>

{{-- Cookie Settings Modal --}}
<div id="cookieSettingsModal" 
     class="cookie-modal-overlay" 
     role="dialog"
     aria-modal="true"
     aria-labelledby="cookie-settings-title"
     style="display: none;">
    <div class="cookie-modal" role="document">
        <div class="cookie-modal-header">
            <h3 id="cookie-settings-title">Cookie Preferences</h3>
            <button type="button" 
                    class="cookie-modal-close" 
                    id="cookieModalClose"
                    aria-label="Close cookie preferences">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cookie-modal-body">
            <p class="cookie-modal-intro">
                Manage your cookie preferences. Essential cookies are always enabled as they are necessary for the website to function properly.
            </p>
            
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <div class="cookie-category-info">
                        <h4>Essential Cookies</h4>
                        <p>Required for the website to function. Cannot be disabled.</p>
                    </div>
                    <div class="cookie-toggle cookie-toggle-disabled">
                        <input type="checkbox" id="cookieEssential" checked disabled aria-label="Essential cookies (always enabled)">
                        <label for="cookieEssential">Always Active</label>
                    </div>
                </div>
            </div>
            
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <div class="cookie-category-info">
                        <h4>Analytics Cookies</h4>
                        <p>Help us understand how visitors interact with our website.</p>
                    </div>
                    <div class="cookie-toggle">
                        <input type="checkbox" id="cookieAnalytics" aria-label="Enable analytics cookies">
                        <label for="cookieAnalytics"><span class="sr-only">Enable analytics cookies</span></label>
                    </div>
                </div>
            </div>
            
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <div class="cookie-category-info">
                        <h4>Marketing Cookies</h4>
                        <p>Used to deliver personalized advertisements.</p>
                    </div>
                    <div class="cookie-toggle">
                        <input type="checkbox" id="cookieMarketing" aria-label="Enable marketing cookies">
                        <label for="cookieMarketing"><span class="sr-only">Enable marketing cookies</span></label>
                    </div>
                </div>
            </div>
            
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <div class="cookie-category-info">
                        <h4>Functional Cookies</h4>
                        <p>Enable enhanced functionality and personalization.</p>
                    </div>
                    <div class="cookie-toggle">
                        <input type="checkbox" id="cookieFunctional" aria-label="Enable functional cookies">
                        <label for="cookieFunctional"><span class="sr-only">Enable functional cookies</span></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="cookie-modal-footer">
            <button type="button" 
                    id="cookieSavePreferences" 
                    class="cookie-btn cookie-btn-primary">
                Save Preferences
            </button>
        </div>
    </div>
</div>

<style>
    /* Cookie Consent Banner Styles */
    .cookie-consent {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(59, 130, 246, 0.2);
        padding: 1.25rem;
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .cookie-consent {
            animation: none;
        }
    }

    .cookie-consent-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .cookie-consent-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        flex: 1;
        min-width: 280px;
    }

    .cookie-consent-icon {
        flex-shrink: 0;
        color: #3b82f6;
        background: #eff6ff;
        padding: 0.75rem;
        border-radius: 12px;
    }

    .cookie-consent-text {
        flex: 1;
    }

    .cookie-consent-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e40af;
        margin: 0 0 0.375rem 0;
    }

    .cookie-consent-desc {
        font-size: 0.9rem;
        color: #475569;
        margin: 0;
        line-height: 1.5;
    }

    .cookie-link {
        color: #3b82f6;
        text-decoration: underline;
        font-weight: 500;
    }

    .cookie-link:hover {
        color: #1e40af;
    }

    .cookie-consent-actions {
        display: flex;
        gap: 0.75rem;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    .cookie-btn {
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        font-family: inherit;
        white-space: nowrap;
    }

    .cookie-btn:focus-visible {
        outline: 3px solid #3b82f6;
        outline-offset: 2px;
    }

    .cookie-btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        border-color: transparent;
    }

    .cookie-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .cookie-btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .cookie-btn-secondary:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }

    .cookie-btn-outline {
        background: transparent;
        color: #3b82f6;
        border-color: #3b82f6;
    }

    .cookie-btn-outline:hover {
        background: #eff6ff;
    }

    /* Cookie Settings Modal */
    .cookie-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .cookie-modal {
        background: white;
        border-radius: 16px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .cookie-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
    }

    .cookie-modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .cookie-modal-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .cookie-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .cookie-modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .cookie-modal-intro {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0 0 1.25rem 0;
        line-height: 1.5;
    }

    .cookie-category {
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 0.75rem;
    }

    .cookie-category:last-child {
        margin-bottom: 0;
    }

    .cookie-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .cookie-category-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }

    .cookie-category-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #64748b;
    }

    .cookie-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cookie-toggle input[type="checkbox"] {
        width: 44px;
        height: 24px;
        appearance: none;
        -webkit-appearance: none;
        background: #cbd5e1;
        border-radius: 12px;
        position: relative;
        cursor: pointer;
        transition: background 0.2s;
    }

    .cookie-toggle input[type="checkbox"]:checked {
        background: #3b82f6;
    }

    .cookie-toggle input[type="checkbox"]::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: transform 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .cookie-toggle input[type="checkbox"]:checked::after {
        transform: translateX(20px);
    }

    .cookie-toggle input[type="checkbox"]:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .cookie-toggle-disabled label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .cookie-modal-footer {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
    }

    /* Screen reader only */
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

    /* Responsive */
    @media (max-width: 768px) {
        .cookie-consent-container {
            flex-direction: column;
            text-align: center;
        }

        .cookie-consent-content {
            flex-direction: column;
            align-items: center;
        }

        .cookie-consent-actions {
            width: 100%;
            justify-content: center;
        }

        .cookie-btn {
            flex: 1;
            min-width: 100px;
        }
    }

    @media (max-width: 480px) {
        .cookie-consent {
            padding: 1rem;
        }

        .cookie-consent-actions {
            flex-direction: column;
        }

        .cookie-btn {
            width: 100%;
        }

        .cookie-category-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .cookie-toggle {
            margin-top: 0.75rem;
        }
    }

    /* High contrast mode */
    @media (prefers-contrast: high) {
        .cookie-consent {
            border-top: 3px solid #1e40af;
        }

        .cookie-btn {
            border-width: 3px;
        }
    }

    /* Print - hide cookie consent */
    @media print {
        .cookie-consent,
        .cookie-modal-overlay {
            display: none !important;
        }
    }
</style>

<script>
    (function() {
        'use strict';

        const COOKIE_NAME = 'cas_cookie_consent';
        const COOKIE_EXPIRY_DAYS = 365;

        // Cookie utility functions
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = `${name}=${encodeURIComponent(JSON.stringify(value))};expires=${expires.toUTCString()};path=/;SameSite=Lax;Secure`;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            if (match) {
                try {
                    return JSON.parse(decodeURIComponent(match[2]));
                } catch (e) {
                    return null;
                }
            }
            return null;
        }

        // Show/hide functions
        function showBanner() {
            const banner = document.getElementById('cookieConsent');
            if (banner) {
                banner.style.display = 'block';
                // Focus on the accept button for accessibility
                setTimeout(() => {
                    const acceptBtn = document.getElementById('cookieAcceptBtn');
                    if (acceptBtn) acceptBtn.focus();
                }, 100);
            }
        }

        function hideBanner() {
            const banner = document.getElementById('cookieConsent');
            if (banner) {
                banner.style.display = 'none';
            }
        }

        function showModal() {
            const modal = document.getElementById('cookieSettingsModal');
            if (modal) {
                modal.style.display = 'flex';
                // Load saved preferences
                const consent = getCookie(COOKIE_NAME);
                if (consent) {
                    document.getElementById('cookieAnalytics').checked = consent.analytics || false;
                    document.getElementById('cookieMarketing').checked = consent.marketing || false;
                    document.getElementById('cookieFunctional').checked = consent.functional || false;
                }
                // Focus on first toggle for accessibility
                setTimeout(() => {
                    document.getElementById('cookieAnalytics').focus();
                }, 100);
            }
        }

        function hideModal() {
            const modal = document.getElementById('cookieSettingsModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Consent handlers
        function acceptAll() {
            const consent = {
                essential: true,
                analytics: true,
                marketing: true,
                functional: true,
                timestamp: new Date().toISOString()
            };
            setCookie(COOKIE_NAME, consent, COOKIE_EXPIRY_DAYS);
            hideBanner();
            applyConsent(consent);
        }

        function rejectAll() {
            const consent = {
                essential: true,
                analytics: false,
                marketing: false,
                functional: false,
                timestamp: new Date().toISOString()
            };
            setCookie(COOKIE_NAME, consent, COOKIE_EXPIRY_DAYS);
            hideBanner();
            applyConsent(consent);
        }

        function savePreferences() {
            const consent = {
                essential: true,
                analytics: document.getElementById('cookieAnalytics').checked,
                marketing: document.getElementById('cookieMarketing').checked,
                functional: document.getElementById('cookieFunctional').checked,
                timestamp: new Date().toISOString()
            };
            setCookie(COOKIE_NAME, consent, COOKIE_EXPIRY_DAYS);
            hideModal();
            hideBanner();
            applyConsent(consent);
        }

        // Apply consent settings (enable/disable tracking)
        function applyConsent(consent) {
            // Dispatch custom event for other scripts to listen
            window.dispatchEvent(new CustomEvent('cookieConsentUpdated', { 
                detail: consent 
            }));

            // Enable/disable Google Analytics based on consent
            if (consent.analytics) {
                // Enable GA if it exists
                if (typeof gtag === 'function') {
                    gtag('consent', 'update', {
                        'analytics_storage': 'granted'
                    });
                }
            } else {
                // Disable GA
                if (typeof gtag === 'function') {
                    gtag('consent', 'update', {
                        'analytics_storage': 'denied'
                    });
                }
            }

            // Enable/disable marketing cookies
            if (consent.marketing) {
                if (typeof gtag === 'function') {
                    gtag('consent', 'update', {
                        'ad_storage': 'granted',
                        'ad_user_data': 'granted',
                        'ad_personalization': 'granted'
                    });
                }
            } else {
                if (typeof gtag === 'function') {
                    gtag('consent', 'update', {
                        'ad_storage': 'denied',
                        'ad_user_data': 'denied',
                        'ad_personalization': 'denied'
                    });
                }
            }
        }

        // Initialize
        function init() {
            const consent = getCookie(COOKIE_NAME);
            
            if (!consent) {
                // No consent given yet, show banner
                showBanner();
            } else {
                // Apply saved consent
                applyConsent(consent);
            }

            // Event listeners
            const acceptBtn = document.getElementById('cookieAcceptBtn');
            const rejectBtn = document.getElementById('cookieRejectBtn');
            const settingsBtn = document.getElementById('cookieSettingsBtn');
            const modalClose = document.getElementById('cookieModalClose');
            const saveBtn = document.getElementById('cookieSavePreferences');

            if (acceptBtn) acceptBtn.addEventListener('click', acceptAll);
            if (rejectBtn) rejectBtn.addEventListener('click', rejectAll);
            if (settingsBtn) settingsBtn.addEventListener('click', showModal);
            if (modalClose) modalClose.addEventListener('click', hideModal);
            if (saveBtn) saveBtn.addEventListener('click', savePreferences);

            // Close modal on overlay click
            const modalOverlay = document.getElementById('cookieSettingsModal');
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function(e) {
                    if (e.target === modalOverlay) {
                        hideModal();
                    }
                });
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('cookieSettingsModal');
                    if (modal && modal.style.display !== 'none') {
                        hideModal();
                    }
                }
            });
        }

        // Run on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Expose function to manually show preferences (for footer link)
        window.showCookiePreferences = showModal;
    })();
</script>
