{{-- Mobile Sticky Action Bar - Shows on mobile only --}}
<style>
    .mobile-action-bar {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%);
        padding: 0.75rem 1rem;
        z-index: 9999;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
    }

    .mobile-action-bar-inner {
        display: flex;
        gap: 0.5rem;
        max-width: 500px;
        margin: 0 auto;
    }

    .mobile-action-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        border-radius: 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .mobile-action-btn.primary {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
    }
    .mobile-action-btn.primary span,
    .mobile-action-btn.primary i {
        color: #ffffff;
    }

    .mobile-action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }

    .mobile-action-btn.secondary {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .mobile-action-btn.secondary span,
    .mobile-action-btn.secondary i {
        color: #ffffff;
    }

    .mobile-action-btn.secondary:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .mobile-action-btn i {
        font-size: 1.1rem;
    }

    /* Force white text on mobile bar (override any global link color) */
    .mobile-action-bar .mobile-action-btn,
    .mobile-action-bar .mobile-action-btn span,
    .mobile-action-bar .mobile-action-btn i {
        color: #ffffff !important;
    }

    /* Only show on mobile/tablet */
    @media (max-width: 768px) {
        .mobile-action-bar {
            display: block;
        }

        /* Add bottom padding to body so content isn't hidden behind bar */
        body {
            padding-bottom: 80px;
        }
    }
</style>

<div class="mobile-action-bar">
    <div class="mobile-action-bar-inner">
        <a href="tel:+16462828282" class="mobile-action-btn secondary">
            <i class="bi bi-telephone-fill"></i>
            <span>Call Now</span>
        </a>
        <a href="{{ url('/login') }}" class="mobile-action-btn primary">
            <i class="bi bi-calendar-check-fill"></i>
            <span>Book Care</span>
        </a>
    </div>
</div>
