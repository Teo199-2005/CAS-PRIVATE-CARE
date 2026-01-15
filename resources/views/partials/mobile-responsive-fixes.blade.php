{{-- Mobile Responsive Fixes - Include before </head> on all pages --}}
<style>
    /* ===========================================
       GLOBAL MOBILE RESPONSIVE FIXES
       Overrides inline styles for proper mobile layout
       =========================================== */

    /* ============ SPECIFIC SECTION FIXES ============ */
    
    /* Across NYC Grid - CRITICAL FIX */
    @media (max-width: 768px) {
        .across-nyc-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
    }
    
    /* Trust Bar Grid - CRITICAL FIX */
    @media (max-width: 768px) {
        .trust-bar-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1rem !important;
            text-align: center !important;
        }
        
        .trust-bar-grid > div {
            flex-direction: column !important;
            text-align: center !important;
        }
        
        .landing-trust-bar {
            padding: 1.25rem 1rem !important;
        }
    }
    
    @media (max-width: 480px) {
        .trust-bar-grid {
            grid-template-columns: 1fr !important;
        }
    }
    
    /* Reviews Grid - CRITICAL FIX */
    @media (max-width: 768px) {
        .reviews-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        
        .reviews-grid > div {
            padding: 1.5rem !important;
            width: 100% !important;
        }
    }
    
    /* Contractor Page Cards Grid */
    @media (max-width: 768px) {
        .contractor-cards-grid,
        .benefits-grid,
        .compare-grid,
        .what-we-do-grid,
        .training-programs-grid,
        .safety-trust-grid,
        .rating-highlights-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.25rem !important;
        }
        
        .contractor-cards-grid > div,
        .benefits-grid > div,
        .compare-grid > div,
        .what-we-do-grid > div,
        .training-programs-grid > div,
        .safety-trust-grid > div,
        .rating-highlights-grid > div,
        .reviews-grid > div {
            padding: 1.5rem !important;
            width: 100% !important;
        }
    }

    /* Trust Strip Mobile */
    @media (max-width: 768px) {
        .trust-strip {
            padding: 1rem !important;
        }
        
        .trust-strip-inner {
            gap: 0.6rem 1rem !important;
            justify-content: flex-start !important;
        }
        
        .trust-item {
            font-size: 0.75rem !important;
            flex: 0 0 calc(50% - 0.5rem) !important;
        }
        
        .trust-item i {
            font-size: 0.9rem !important;
        }
    }

    /* Grid Layout Fixes - Force single column on mobile */
    @media (max-width: 768px) {
        /* Two-column grids to single column */
        [style*="grid-template-columns: 1.35fr"],
        [style*="grid-template-columns: 1fr 1fr"],
        [style*="grid-template-columns: 1fr 350px"],
        [style*="grid-template-columns: repeat(2"],
        [style*="grid-template-columns: repeat(auto-fit, minmax(350px"],
        [style*="grid-template-columns: repeat(auto-fit, minmax(300px"] {
            grid-template-columns: 1fr !important;
        }
        
        /* Reduce grid gaps on mobile */
        [style*="gap: 3rem"],
        [style*="gap: 2.5rem"],
        [style*="gap: 2rem"] {
            gap: 1.5rem !important;
        }
        
        /* Review cards - smaller min-width */
        [style*="minmax(350px"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* "Across NYC" Section Fixes */
    @media (max-width: 768px) {
        /* Force the content + benefits grid to stack */
        .across-nyc-grid,
        [style*="grid-template-columns: 1.35fr 0.65fr"] {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        
        /* Section headers smaller on mobile */
        [style*="font-size: 2.2rem"],
        [style*="font-size: 2.25rem"],
        [style*="font-size: 2.5rem"] {
            font-size: 1.75rem !important;
        }
        
        [style*="font-size: 3rem"] {
            font-size: 2rem !important;
        }
        
        /* Section padding reductions */
        [style*="padding: 6rem"],
        [style*="padding: 6.5rem"],
        [style*="padding: 7rem"] {
            padding: 3rem 1.25rem !important;
        }
        
        [style*="padding: 5rem"],
        [style*="padding: 4.5rem"],
        [style*="padding: 4rem"] {
            padding: 2.5rem 1.25rem !important;
        }
    }

    /* Trust Bar Mobile Fix */
    @media (max-width: 768px) {
        [style*="gap: 2rem 3.5rem"] {
            gap: 1rem 1.5rem !important;
        }
        
        /* Trust bar items - 2 per row on mobile */
        [style*="gap: 2rem 3.5rem"] > div {
            flex: 0 0 calc(50% - 0.75rem) !important;
        }
        
        /* Smaller trust bar icons */
        [style*="width: 44px"][style*="height: 44px"] {
            width: 36px !important;
            height: 36px !important;
        }
        
        [style*="font-size: 0.95rem"] {
            font-size: 0.85rem !important;
        }
        
        [style*="font-size: 0.85rem"] {
            font-size: 0.75rem !important;
        }
    }

    /* Testimonial Cards Mobile */
    @media (max-width: 768px) {
        /* Force review grid to stack */
        [style*="repeat(auto-fit, minmax(350px"] {
            grid-template-columns: 1fr !important;
        }
        
        /* Review card padding */
        [style*="padding: 2.1rem"],
        [style*="padding: 2rem"] {
            padding: 1.5rem !important;
        }
        
        /* Avatar sizes */
        [style*="width: 52px"][style*="height: 52px"],
        [style*="width: 48px"][style*="height: 48px"] {
            width: 42px !important;
            height: 42px !important;
        }
        
        /* Review text */
        [style*="font-size: 0.95rem"][style*="italic"] {
            font-size: 0.9rem !important;
        }
    }

    /* Feature Cards Grid Mobile */
    @media (max-width: 768px) {
        /* Rating highlights - single column */
        [style*="repeat(auto-fit, minmax(250px"] {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
    }

    /* Badge Pills Mobile */
    @media (max-width: 768px) {
        /* Smaller pills on mobile */
        [style*="padding: 0.6rem 0.9rem"][style*="border-radius: 999px"],
        [style*="padding: 0.55rem 1rem"][style*="border-radius: 999px"] {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.8rem !important;
        }
        
        [style*="width: 34px"][style*="height: 34px"] {
            width: 28px !important;
            height: 28px !important;
        }
        
        [style*="width: 34px"] i,
        [style*="height: 34px"] i {
            font-size: 0.9rem !important;
        }
    }

    /* Container max-width overrides */
    @media (max-width: 768px) {
        [style*="max-width: 1200px"],
        [style*="max-width: 1100px"],
        [style*="max-width: 1000px"],
        [style*="max-width: 980px"] {
            max-width: 100% !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    /* Button Stacking on Mobile */
    @media (max-width: 576px) {
        [style*="display: flex"][style*="gap: 1rem"]:has(a[class*="btn"]),
        .hero-buttons,
        .cta-buttons {
            flex-direction: column !important;
            width: 100% !important;
        }
        
        .hero-buttons a,
        .cta-buttons a,
        [style*="display: flex"][style*="gap: 1rem"] a[class*="btn"] {
            width: 100% !important;
            text-align: center !important;
            justify-content: center !important;
        }
    }

    /* Form Row Grid Mobile */
    @media (max-width: 576px) {
        .form-row,
        [style*="grid-template-columns: 1fr 1fr"]:has(input),
        [style*="grid-template-columns: 1fr 1fr"]:has(select) {
            grid-template-columns: 1fr !important;
        }
    }

    /* Flex Wrap Improvements */
    @media (max-width: 768px) {
        [style*="display: flex"][style*="flex-wrap: wrap"] {
            gap: 0.75rem !important;
        }
    }

    /* Sidebar Layout Fix (Blog) */
    @media (max-width: 768px) {
        .blog-layout,
        [style*="grid-template-columns: 1fr 350px"] {
            grid-template-columns: 1fr !important;
        }
        
        .sidebar {
            order: -1;
        }
    }

    /* "How It Works" Step Cards - 2 per row on tablet, 1 on mobile */
    @media (max-width: 576px) {
        .steps-container {
            grid-template-columns: 1fr !important;
        }
    }

    /* Section Dividers - Thinner on mobile */
    @media (max-width: 768px) {
        .section-divider {
            padding: 0.5rem 0 !important;
        }
    }

    /* Service Cards - Single Column on Mobile */
    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: 1fr !important;
        }
        
        .service-card {
            max-width: 100% !important;
        }
    }

    /* About Page Values Grid */
    @media (max-width: 768px) {
        [style*="repeat(3, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* FAQ Accordion Mobile */
    @media (max-width: 768px) {
        .faq-item summary {
            padding: 1rem !important;
        }
        
        .faq-item .faq-a {
            padding: 1rem !important;
        }
        
        .faq-q {
            font-size: 0.95rem !important;
        }
    }

    /* Contact Page - Info Cards */
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr !important;
        }
        
        .contact-form-side,
        .contact-info-side {
            padding: 1.5rem !important;
        }
    }

    /* Training Center Course Cards */
    @media (max-width: 768px) {
        [style*="repeat(3, 1fr)"],
        [style*="repeat(auto-fit, minmax(320px"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* Hero Section Mobile Optimizations */
    @media (max-width: 768px) {
        .hero,
        [class*="hero"] {
            min-height: auto !important;
            padding-top: 4rem !important;
            padding-bottom: 3rem !important;
        }
        
        .hero h1,
        [class*="hero"] h1 {
            font-size: 2rem !important;
            line-height: 1.2 !important;
        }
        
        .hero p,
        [class*="hero"] > p,
        .hero-content p {
            font-size: 1rem !important;
        }
    }

    /* Very Small Mobile (< 400px) */
    @media (max-width: 400px) {
        body {
            font-size: 14px !important;
        }
        
        [style*="font-size: 1.75rem"] {
            font-size: 1.4rem !important;
        }
        
        [style*="font-size: 2rem"] {
            font-size: 1.5rem !important;
        }
        
        [style*="padding: 2rem"],
        [style*="padding: 2.5rem"] {
            padding: 1.25rem !important;
        }
        
        /* Trust bar - single column on tiny screens */
        [style*="gap: 2rem 3.5rem"] > div {
            flex: 0 0 100% !important;
        }
    }

    /* Contractor Partner Page - Testimonial Fixes */
    @media (max-width: 768px) {
        /* Force column layout for side-by-side testimonials */
        [style*="display: grid"][style*="repeat(2, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* Newsletter Form Mobile */
    @media (max-width: 576px) {
        .newsletter-form {
            flex-direction: column !important;
        }
        
        .newsletter-form input,
        .newsletter-form button {
            width: 100% !important;
        }
    }

    /* CTA Banner Mobile */
    @media (max-width: 768px) {
        .cta-banner,
        [class*="cta-banner"] {
            padding: 2rem 1.5rem !important;
            border-radius: 16px !important;
        }
        
        .cta-banner h2 {
            font-size: 1.5rem !important;
        }
        
        .cta-banner p {
            font-size: 0.95rem !important;
        }
    }

    /* Footer Mobile */
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr !important;
            text-align: center !important;
        }
        
        .footer-section ul {
            padding-left: 0 !important;
        }
        
        .footer-social {
            justify-content: center !important;
        }
    }
</style>
