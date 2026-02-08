<div class="mobile-footer-only">
    <!-- Mobile Footer Content -->
    <div class="mobile-footer-content">
        <!-- Logo Section -->
        <div class="mobile-footer-logo">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC" width="80" height="80">
            <p>Connection that cares</p>
        </div>

        <!-- Quick Links -->
        <div class="mobile-footer-links">
            <h4>Quick Links</h4>
            <div class="mobile-links-grid">
                <a href="{{ url('/') }}">Home</a>
                <a href="{{ url('/about') }}">About</a>
                <a href="{{ url('/') }}#services">Services</a>
                <a href="{{ url('/blog') }}">Blog</a>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/faq') }}">FAQ</a>
            </div>
        </div>

        <!-- Services -->
        <div class="mobile-footer-links">
            <h4>Our Services</h4>
            <div class="mobile-links-grid">
                <a href="{{ url('/caregiver-new-york') }}">Caregivers</a>
                <a href="{{ url('/') }}#services">Care Options</a>
                <a href="{{ url('/contractor-partner') }}">1099 Partners</a>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="mobile-footer-contact">
            <h4>Contact Us</h4>
            <a href="tel:{{ config('app.phone', '+16462828282') }}" class="mobile-contact-item">
                <i class="bi bi-telephone-fill"></i>
                <span>{{ config('app.phone', '+1 (646) 282-8282') }}</span>
            </a>
            <a href="mailto:{{ config('app.email', 'contact@casprivatecare.online') }}" class="mobile-contact-item">
                <i class="bi bi-envelope-fill"></i>
                <span>{{ config('app.email', 'contact@casprivatecare.online') }}</span>
            </a>
            <div class="mobile-contact-item">
                <i class="bi bi-geo-alt-fill"></i>
                <span>{{ config('app.address', 'New York, USA') }}</span>
            </div>
        </div>

        <!-- Social Media -->
        <div class="mobile-footer-social">
            <h4>Follow Us</h4>
            <div class="mobile-social-icons">
                <a href="https://www.facebook.com/profile.php?id=61584831099232" target="_blank" rel="noopener noreferrer" aria-label="Facebook" onclick="window.open(this.href, '_blank'); return false;">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://www.linkedin.com/in/CASprivatecare" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" onclick="window.open(this.href, '_blank'); return false;">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a href="https://www.instagram.com/casprivatecare" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
            </div>
        </div>

        <!-- Bottom Links -->
        <div class="mobile-footer-bottom">
            <p>&copy; 2026 CAS Private Care LLC</p>
            <div class="mobile-legal-links">
                <a href="{{ url('/privacy') }}">Privacy</a>
                <span>•</span>
                <a href="{{ url('/terms') }}">Terms</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile Footer - Only visible on mobile devices */
.mobile-footer-only {
    display: none;
}

@media (max-width: 768px) {
    /* Hide desktop footer on mobile */
    footer {
        display: none !important;
    }

    /* Show mobile footer */
    .mobile-footer-only {
        display: block;
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        padding: 2rem 1.5rem 1.5rem;
    }

    /* Mobile Footer Content */
    .mobile-footer-content {
        padding: 2rem 1.5rem 1.5rem;
    }

    .mobile-footer-logo {
        text-align: center;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        margin-bottom: 2rem;
    }

    .mobile-footer-logo img {
        width: 80px;
        height: 80px;
        background: white;
        padding: 8px;
        border-radius: 16px;
        margin-bottom: 0.75rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .mobile-footer-logo p {
        color: #ffffff !important;
        font-size: 0.95rem;
        margin: 0;
        font-weight: 500;
    }

    .mobile-footer-links,
    .mobile-footer-contact,
    .mobile-footer-social {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .mobile-footer-links h4,
    .mobile-footer-contact h4,
    .mobile-footer-social h4 {
        color: #ffffff;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        letter-spacing: 0.02em;
    }

    .mobile-links-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    /* Force white text on all footer links (override global blue link styles) */
    .mobile-footer-only a,
    .mobile-footer-only .mobile-links-grid a,
    .mobile-footer-only .mobile-contact-item,
    .mobile-footer-only .mobile-contact-item span {
        color: #ffffff !important;
    }

    .mobile-links-grid a {
        color: #ffffff !important;
        text-decoration: none;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        transition: all 0.3s ease;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-weight: 500;
    }

    .mobile-links-grid a:active {
        background: rgba(249, 115, 22, 0.2);
        color: #f97316 !important;
        transform: scale(0.98);
    }

    .mobile-contact-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 0;
        color: #ffffff !important;
        text-decoration: none;
        font-size: 0.95rem;
        line-height: 1.6;
        min-height: 48px;
    }

    .mobile-contact-item i {
        color: #f97316;
        font-size: 1.25rem;
        flex-shrink: 0;
        width: 28px;
        text-align: center;
    }

    .mobile-contact-item:active,
    .mobile-contact-item:active span {
        color: #f97316 !important;
    }

    .mobile-social-icons {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .mobile-social-icons a {
        width: 52px;
        height: 52px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        text-decoration: none;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .mobile-social-icons a:active {
        background: #f97316;
        border-color: #f97316;
        transform: scale(0.9);
    }

    .mobile-footer-bottom {
        text-align: center;
        padding-top: 1.5rem;
        margin-top: 0.5rem;
    }

    .mobile-footer-bottom p {
        color: #94a3b8;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }

    .mobile-legal-links {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .mobile-legal-links a {
        color: #e2e8f0 !important;
        text-decoration: none;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        min-height: 44px;
        display: flex;
        align-items: center;
    }

    .mobile-legal-links span {
        color: #64748b;
    }

    .mobile-legal-links a:active {
        color: #f97316 !important;
    }
}

/* Ensure desktop footer shows on desktop */
@media (min-width: 769px) {
    .mobile-footer-only {
        display: none !important;
    }
    
    footer {
        display: block !important;
    }
}
</style>
