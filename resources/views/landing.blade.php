<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e40af">
    @include('partials.favicon')
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Primary Meta Tags -->
    <title>CAS Private Care LLC - Verified Caregivers & Home Care Services New York</title>
    <meta name="title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta name="description" content="Connect with verified caregivers and home helpers in New York. Professional elderly care and housekeeping services. 24/7 support. Book trusted care professionals today.">
    <meta name="keywords" content="caregivers New York, home care services, elderly care, nanny services, housekeeping, home helpers, verified caregivers, professional care services">
    <meta name="author" content="CAS Private Care LLC">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="og:description" content="Connect with verified caregivers and home helpers in New York. Professional elderly care and housekeeping services.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:site_name" content="CAS Private Care LLC">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="twitter:description" content="Connect with verified caregivers and home helpers in New York. Professional elderly care and housekeeping services.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    @php
    echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'CAS Private Care LLC',
        'image' => asset('logo.png'),
        '@id' => url('/'),
        'url' => url('/'),
        'telephone' => '+1-646-282-8282',
        'priceRange' => '$$',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'New York',
            'addressRegion' => 'NY',
            'addressCountry' => 'US'
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ],
        'areaServed' => [
            '@type' => 'City',
            'name' => 'New York'
        ],
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '00:00',
            'closes' => '23:59'
        ],
        'sameAs' => [
            'https://www.facebook.com/casprivatecare',
            'https://www.instagram.com/casprivatecare',
            'https://www.linkedin.com/company/casprivatecare'
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>

    <!-- Preconnect to external resources for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://js.stripe.com" crossorigin>
    
    <!-- Preload critical fonts for LCP optimization -->
    <link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIbaomQNQcsA88c7O9yZ4KMCoOg4IA6-91aHEjcWuA_KU7NSg.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/sora/v12/xMQOuFFYT72X5wkB_18qmnndmSdSnk-DKQJRBg.woff2" crossorigin>
    
    <!-- Google Fonts for the landing page -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Preload critical images for LCP -->
    <link rel="preload" as="image" href="{{ asset('cover.jpg') }}" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('logo.png') }}" fetchpriority="high">
    
    @include('partials.nav-footer-styles')
    
    <!-- Mobile-first fixes -->
    <link rel="stylesheet" href="{{ asset('build/assets/mobile-fixes.css') }}" media="screen">
    
    <!-- Critical CSS for above-the-fold content -->
    <style>
        /* Critical path CSS - inlined for fastest FCP */
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; }
        .hero { min-height: 100vh; display: flex; align-items: center; }
        nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
    </style>
    
    <style>
        /* ============================================================
           CAS Private Care Landing Page Styles
           Using design tokens for consistency
           ============================================================ */

        /* ============================================================
           Design Tokens - Required for this page
           ============================================================ */
        :root {
            /* Brand Colors */
            --brand-primary: #0B4FA2;
            --brand-primary-50: #eff6ff;
            --brand-primary-100: #dbeafe;
            --brand-primary-light: #1565C0;
            --brand-primary-dark: #093d7a;
            --brand-primary-darker: #1e40af;
            --brand-primary-rgb: 11, 79, 162;
            --brand-accent: #f97316;
            --brand-accent-light: #fb923c;
            --brand-accent-dark: #ea580c;
            --brand-accent-rgb: 249, 115, 22;
            
            /* Semantic Colors */
            --color-info: #3b82f6;
            --color-info-rgb: 59, 130, 246;
            
            /* Neutral Colors */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            
            /* Background Colors */
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            
            /* Text Colors */
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --text-muted: #94a3b8;
            --text-inverse: #ffffff;
            
            /* Spacing */
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            
            /* Typography */
            --text-lg: 1.125rem;
            --text-xl: 1.25rem;
            --text-3xl: 1.875rem;
            --text-5xl: 3rem;
            --font-normal: 400;
            --font-medium: 500;
            --font-semibold: 600;
            --font-bold: 700;
            --leading-relaxed: 1.625;
            --tracking-tight: -0.025em;
            
            /* Border Radius */
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-full: 9999px;
            
            /* Shadows */
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-card-hover: 0 10px 40px rgba(0, 0, 0, 0.12);
            --shadow-card-elevated: 0 20px 60px rgba(0, 0, 0, 0.15);
            
            /* Component Tokens */
            --container-lg: 1400px;
            --container-xl: 1600px;
            --card-radius-sm: var(--radius-lg);
            --card-radius-md: var(--radius-xl);
            --card-radius-lg: var(--radius-2xl);
            --card-padding-sm: var(--space-4);
            --card-padding-md: var(--space-6);
            --card-padding-lg: var(--space-8);
            --btn-radius: var(--radius-lg);
            --btn-radius-full: var(--radius-full);
            --btn-padding-y: 0.75rem;
            --btn-padding-x: 1.5rem;
            --grid-gap-md: var(--space-6);
            --grid-gap-lg: var(--space-8);
            --section-padding-md: 4rem;
            --section-padding-lg: 6rem;
            --heading-1: var(--text-5xl);
            --heading-2: var(--text-3xl);
            --heading-3: var(--text-xl);
            
            /* Transitions */
            --duration-instant: 50ms;
            --duration-fast: 150ms;
            --duration-normal: 250ms;
            --duration-slow: 350ms;
            --duration-slower: 500ms;
            --ease-out: cubic-bezier(0, 0, 0.2, 1);
            --btn-transition: all 150ms ease-out;
            
            /* Focus */
            --focus-ring-width: 3px;
            --focus-ring-color: rgba(11, 79, 162, 0.4);
            --focus-ring-offset: 2px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body, 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            line-height: var(--leading-normal, 1.6);
            color: var(--brand-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .hero {
            margin-top: 72px; /* Match nav height to remove white gap */
            padding: 3rem var(--space-8, 2rem) var(--section-padding-lg, 6rem);
            text-align: center;
            position: relative;
            overflow: hidden; /* Critical: prevents horizontal scroll */
            overflow-x: clip; /* Enhanced overflow prevention */
            min-height: 90vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            background: transparent !important;
            contain: layout style paint; /* Performance optimization */
        }

        /* Remove any overlay from hero section */
        .hero::before,
        .hero::after {
            display: none !important;
            content: none !important;
            background: transparent !important;
        }

        .hero-bg-images {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            z-index: 0;
            filter: none !important;
            -webkit-filter: none !important;
            overflow: hidden; /* Prevent slice overflow */
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            mix-blend-mode: normal !important;
            background: transparent !important;
            background-color: transparent !important;
        }

        .hero-bg-images::before,
        .hero-bg-images::after {
            display: none !important;
            content: none !important;
            background: transparent !important;
            opacity: 0 !important;
        }

        .hero-bg-slice {
            flex: 1;
            background-size: cover;
            background-position: center;
            position: relative;
            transform: skewX(-5deg);
            margin: 0 -2%;
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            mix-blend-mode: normal !important;
            opacity: 1 !important;
            contain: paint; /* Prevent overflow leakage */
        }

        .hero-bg-slice::before,
        .hero-bg-slice::after {
            display: none !important;
            content: none !important;
            background: transparent !important;
            opacity: 0 !important;
        }

        .hero-bg-slice:nth-child(1) {
            background-image: url('https://images.unsplash.com/photo-1609220136736-443140cffec6?w=800');
            background-color: transparent !important;
        }

        .hero-bg-slice:nth-child(2) {
            background-image: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800');
            background-color: transparent !important;
        }

        .hero-bg-slice:nth-child(3) {
            background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800');
            background-color: transparent !important;
        }

        /* Removed excessive float animation - keep hero clean */

        .hero-content {
            max-width: var(--container-lg, 1400px);
            margin: 0 auto;
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--grid-gap-lg, 2rem);
            align-items: start;
            background: rgba(255, 255, 255, 0.98);
            border-radius: var(--card-radius-lg, 24px);
            padding: var(--card-padding-lg, 2.5rem);
            border: 1px solid rgba(59, 130, 246, 0.12);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 24px 48px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        /* Gradient circle effects on hero card – more noticeable */
        .hero-content::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.35) 0%, rgba(249, 115, 22, 0.1) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .hero-content::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.32) 0%, rgba(59, 130, 246, 0.08) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .hero-content > * {
            position: relative;
            z-index: 1;
        }

        /* CEO / Founder section – gradient circle effects (same style as hero card) */
        .ceo-content {
            position: relative;
            overflow: hidden;
        }
        .ceo-content::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.35) 0%, rgba(249, 115, 22, 0.1) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .ceo-content::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.32) 0%, rgba(59, 130, 246, 0.08) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .ceo-content > * {
            position: relative;
            z-index: 1;
        }

        /* CEO Section – prestige styling */
        .ceo-section {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%) !important;
            position: relative;
        }
        .ceo-section .section-header h2 {
            font-size: clamp(2rem, 4vw, 3.25rem);
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .ceo-content {
            background: #ffffff !important;
            padding: 3.5rem 4rem !important;
            border-radius: 24px !important;
            border: 1px solid rgba(59, 130, 246, 0.15) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02), 0 25px 50px -12px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.03) !important;
            display: grid !important;
            grid-template-columns: 1fr 1.5fr !important;
            gap: 4rem !important;
            align-items: center !important;
        }
        .ceo-content .ceo-company {
            margin-bottom: 2rem;
        }
        .ceo-content .ceo-company h3 {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #1e40af;
            margin: 0 0 0.25rem 0;
            text-transform: uppercase;
        }
        .ceo-content .ceo-company p {
            color: #64748b;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .ceo-content .ceo-name {
            font-size: clamp(2rem, 3.5vw, 2.75rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            line-height: 1.15;
            color: #0f172a;
        }
        .ceo-content .ceo-title {
            display: inline-block;
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            margin-bottom: 1.75rem;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        .ceo-content .ceo-bio {
            font-size: 1.1rem;
            color: #475569;
            line-height: 1.85;
            margin: 0;
            max-width: 42em;
            padding-left: 1.5rem;
            border-left: 4px solid rgba(59, 130, 246, 0.35);
        }
        .ceo-content .ceo-image-wrap {
            position: relative;
            display: inline-block;
        }
        .ceo-content .ceo-image-wrap::before {
            content: '';
            position: absolute;
            inset: -12px;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 24px;
            z-index: 0;
        }
        .ceo-content .ceo-image-wrap img {
            position: relative;
            z-index: 1;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 100%;
            height: auto;
        }
        .ceo-content .ceo-badge {
            position: absolute;
            bottom: -12px;
            right: -12px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            padding: 1rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 12px 28px rgba(59, 130, 246, 0.4);
            z-index: 2;
        }
        .ceo-content .ceo-badge i {
            font-size: 1.75rem;
            color: white;
        }
        
        /* Tablet breakpoint - stack hero content earlier */
        @media (max-width: 960px) {
            .hero-content {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
                padding: 1.5rem !important;
            }
        }

        .hero-left {
            text-align: left;
        }

        .hero-right {
            display: flex;
            flex-direction: column;
            gap: var(--grid-gap-md, 1.5rem);
        }

        /* Push promotional card down so it aligns with description/CTAs on the left */
        .hero-image-container {
            position: relative;
            height: 350px;
            width: 100%;
            max-width: 610px;
            margin-top: 6rem;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
            transition: transform var(--duration-fast, 150ms) var(--ease-out, ease-out);
            will-change: transform;
        }

        /* Use pseudo-element for shadow animation (cheaper than animating box-shadow) */
        .hero-image-container::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity var(--duration-fast, 150ms) var(--ease-out, ease-out);
            pointer-events: none;
        }

        .hero-image-container:hover {
            transform: translateY(-4px);
        }
        
        .hero-image-container:hover::after {
            opacity: 1;
        }

        .hero-cover-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            /* Removed transform animation - saves battery on mobile */
        }

        .hero-service-toggle {
            position: relative;
            display: flex;
            justify-content: center;
            margin: 2rem 0;
            background: rgba(30, 64, 175, 0.08);
            padding: 0.5rem;
            border-radius: 50px;
            border: 1px solid rgba(30, 64, 175, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .hero-toggle-slider {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            width: calc(50% - 0.5rem);
            height: calc(100% - 1rem);
            background: white;
            border-radius: 25px;
            transition: transform 0.3s ease;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .hero-toggle-btn {
            position: relative;
            z-index: 2;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.25s ease, transform 0.2s ease;
            flex: 1;
        }
        .hero-toggle-btn[aria-selected="true"] {
            color: #1e40af;
        }
        .hero-toggle-btn:hover {
            color: #1e40af;
        }
        .hero-toggle-btn:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        .hero-call-cta {
            margin-top: 1.5rem;
            margin-bottom: 0;
            font-size: 0.95rem;
            color: var(--text-secondary, #475569);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .hero-call-label {
            font-weight: 600;
            color: var(--brand-primary-darker, #1e40af);
        }
        .hero-call-number {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            background: rgba(249, 115, 22, 0.1);
            color: #ea580c;
            font-weight: 700;
            text-decoration: none;
            border-radius: 50px;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .hero-call-number i {
            font-size: 0.95em;
        }
        .hero-call-number:hover {
            background: rgba(249, 115, 22, 0.18);
            color: #c2410c;
            text-decoration: none;
            transform: translateY(-1px);
        }
        .hero-call-number:focus-visible {
            outline: 2px solid #f97316;
            outline-offset: 2px;
        }
        .hero-social-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: nowrap;
            justify-content: center;
        }

        .hero-social-text {
            color: #64748b;
            font-size: 0.8rem;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }

        .hero-social-icons {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .hero-social-icon {
            flex: 0 0 auto;
        }

        .hero-social-icon {
            width: 42px;
            height: 42px;
            background: rgba(30, 64, 175, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
            text-decoration: none;
            transition: background 0.25s ease, color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            font-size: 1.1rem;
            border: 1px solid rgba(30, 64, 175, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .hero-social-icon:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: #fff;
            transform: translateY(-2px);
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        /* Keep "CONNECT WITH US" perfectly aligned with icon row */
        .hero-social-container {
            display: flex;
            align-items: baseline;
            gap: 0.85rem;
        }

        .hero-social-text {
            margin: 0;
            line-height: 1;
            padding-top: 2px; /* nudges baseline to visually center with circular icons */
            white-space: nowrap;
        }

        .hero-social-icons {
            display: flex;
            align-items: center;
            height: 42px;
        }

        /* About Section Feature Cards */
        .about-features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
            width: 100%;
            overflow: visible;
        }

        .about-feature-card {
            display: flex;
            flex-direction: row;
            align-items: center;
            text-align: left;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            padding: 1.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(59, 130, 246, 0.15);
            position: relative;
            overflow: visible;
            gap: 1.5rem;
        }

        .about-feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, rgba(59, 130, 246, 0.05) 0%, rgba(249, 115, 22, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .about-feature-card:hover::before {
            opacity: 1;
        }

        .about-feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .about-feature-card:nth-child(1):hover {
            box-shadow: 0 20px 60px rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
        }

        .about-feature-card:nth-child(2):hover {
            box-shadow: 0 20px 60px rgba(249, 115, 22, 0.25);
            border-color: #f97316;
        }

        .about-feature-card:nth-child(3):hover {
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.25);
            border-color: #10b981;
        }

        .about-feature-icon {
            width: 60px;
            height: 60px;
            min-width: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            transition: transform 200ms ease-out, box-shadow 200ms ease-out;
            position: relative;
            z-index: 1;
        }

        .about-feature-card:nth-child(1) .about-feature-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .about-feature-card:nth-child(2) .about-feature-icon {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
        }

        .about-feature-card:nth-child(3) .about-feature-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        /* Single transform for icon - no stacking (performance optimized) */
        .about-feature-card:hover .about-feature-icon {
            transform: scale(1.1);
            box-shadow: 0 15px 45px rgba(59, 130, 246, 0.4);
        }

        .about-feature-card:nth-child(1):hover .about-feature-icon {
            box-shadow: 0 15px 45px rgba(59, 130, 246, 0.5);
        }

        .about-feature-card:nth-child(2):hover .about-feature-icon {
            box-shadow: 0 15px 45px rgba(249, 115, 22, 0.5);
        }

        .about-feature-card:nth-child(3):hover .about-feature-icon {
            box-shadow: 0 15px 45px rgba(16, 185, 129, 0.5);
        }

        .about-feature-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .about-feature-content {
            position: relative;
            z-index: 1;
            overflow: visible;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .about-feature-content h3 {
            font-size: 1.25rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            letter-spacing: -0.01em;
            white-space: normal;
            word-wrap: break-word;
        }

        .about-feature-content p {
            color: #64748b;
            margin: 0;
            line-height: 1.6;
            font-size: 0.95rem;
            font-weight: 400;
            white-space: normal;
            word-wrap: break-word;
        }

        .hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.1;
            letter-spacing: -0.02em;
            text-shadow: 0 6px 30px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================================
           ANIMATION CLASSES
           Note: Base @keyframes are defined in global animations.css
           These classes use those global animations for consistency
           ============================================================ */

        /* Scroll Animation Classes - use global keyframes */
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .animate-slide-up {
            animation: slideUp 0.8s var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1)) forwards;
        }

        .animate-slide-left {
            animation: slideInLeft 0.8s var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1)) forwards;
        }

        .animate-slide-right {
            animation: slideInRight 0.8s var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1)) forwards;
        }

        .animate-scale {
            animation: scaleIn 0.6s var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1)) forwards;
        }

        .animate-bounce:hover {
            animation: bounce 0.6s ease-in-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        /* Smooth transitions for all animated elements */
        [class*="animate-"] {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== PARTICLES & SMOKE EFFECTS ===== */
        #particles-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            overflow: hidden;
            background: transparent !important;
        }

        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFall linear infinite;
        }

        /* Different particle types */
        .particle.sparkle {
            background: radial-gradient(circle, rgba(249, 115, 22, 0.9) 0%, rgba(249, 115, 22, 0.4) 40%, transparent 100%);
            box-shadow: 0 0 10px rgba(249, 115, 22, 0.5);
        }

        .particle.blue-sparkle {
            background: radial-gradient(circle, rgba(59, 130, 246, 0.9) 0%, rgba(59, 130, 246, 0.4) 40%, transparent 100%);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        .particle.glow {
            background: radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.6) 30%, transparent 100%);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
            animation: particleFall linear infinite, twinkle 2s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.3;
                transform: scale(0.8);
            }
        }

        @keyframes particleFall {
            0% {
                transform: translateY(-10px) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(100vh) translateX(var(--drift)) rotate(360deg);
                opacity: 0;
            }
        }

        /* Smoke Effect */
        #smoke-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            pointer-events: none;
            overflow: hidden;
            background: transparent !important;
        }

        .smoke {
            position: absolute;
            bottom: -50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 50%, transparent 100%);
            border-radius: 50%;
            filter: blur(50px);
            animation: smokeRise linear infinite;
        }

        .smoke-1 {
            left: 10%;
            width: 400px;
            height: 400px;
            animation-duration: 25s;
            animation-delay: 0s;
        }

        .smoke-2 {
            left: 50%;
            width: 350px;
            height: 350px;
            animation-duration: 30s;
            animation-delay: 5s;
        }

        .smoke-3 {
            left: 80%;
            width: 450px;
            height: 450px;
            animation-duration: 28s;
            animation-delay: 10s;
        }

        @keyframes smokeRise {
            0% {
                transform: translateY(0) scale(1) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            50% {
                opacity: 0.15;
                transform: translateY(-50vh) scale(1.5) rotate(180deg);
            }
            90% {
                opacity: 0.08;
            }
            100% {
                transform: translateY(-100vh) scale(2) rotate(360deg);
                opacity: 0;
            }
        }

        /* Optimize transitions - only animate what's needed */
        .hero .hero-left,
        .hero .hero-right {
            transition: opacity var(--duration-slow) var(--ease-out),
                        transform var(--duration-slow) var(--ease-out);
        }

        .trademark {
            font-size: 0.6em;
            vertical-align: super;
        }

        .hero .tagline {
            font-size: var(--text-3xl, 1.875rem);
            color: var(--brand-primary-darker, #1e40af);
            margin-bottom: var(--space-4);
            padding-bottom: 1rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.9);
            animation: fadeInUp var(--duration-slower) var(--ease-out) 0.15s both;
            border-bottom: 2px solid rgba(59, 130, 246, 0.2);
        }

        .hero p {
            font-size: var(--text-lg, 1.125rem);
            color: var(--text-secondary, #475569);
            margin-bottom: var(--space-6);
            line-height: 1.7;
            font-weight: var(--font-medium, 500);
            animation: fadeInUp var(--duration-slower) var(--ease-out) 0.25s both;
        }
        .hero #hero-description {
            max-width: 32em;
        }

        .hero-trust-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--grid-gap-sm, 1rem);
            animation: fadeInUp var(--duration-slower) var(--ease-out) 0.45s both;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            transition: all 0.3s;
        }

        .trust-badge:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .trust-badge i {
            font-size: 1.4rem;
            color: #ffffff;
        }

        .trust-divider {
            display: none;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.6s both;
        }
        .hero-content .hero-buttons .btn-secondary:hover {
            box-shadow: 0 10px 28px rgba(249, 115, 22, 0.4);
        }
        .hero-content .hero-buttons .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.25);
        }

        /* ============================================================
           BUTTON STYLES - Using design tokens
           Single transform on hover for smooth animation
           ============================================================ */
        .btn-primary, .btn-secondary {
            padding: var(--space-3) var(--space-8);
            border-radius: var(--btn-radius-full, 50px);
            font-weight: var(--font-semibold, 600);
            font-size: var(--text-lg, 1.125rem);
            text-decoration: none;
            transition: var(--btn-transition, all 0.15s ease-out);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            border: none;
        }

        /* Subtle ripple effect on hover */
        .btn-primary::before,
        .btn-secondary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width var(--duration-slow) var(--ease-out), 
                        height var(--duration-slow) var(--ease-out);
        }

        .btn-primary:hover::before,
        .btn-secondary:hover::before {
            width: 250px;
            height: 250px;
        }

        .btn-primary {
            background: var(--bg-primary, white);
            color: var(--brand-primary-darker, #1e40af);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            background: var(--brand-primary-50, #eff6ff);
        }

        .btn-primary:active {
            transform: translateY(-1px);
            transition-duration: var(--duration-instant);
        }

        .btn-primary:focus-visible {
            outline: var(--focus-ring-width, 3px) solid var(--focus-ring-color);
            outline-offset: var(--focus-ring-offset, 2px);
        }

        .btn-secondary {
            background: var(--brand-accent, #f97316);
            color: var(--text-inverse, white);
            border: 2px solid var(--brand-accent);
        }

        .btn-secondary:hover {
            background: var(--brand-accent-dark, #ea580c);
            transform: translateY(-3px);
            border-color: var(--brand-accent-dark);
            box-shadow: 0 8px 24px rgba(var(--brand-accent-rgb), 0.35);
        }

        .btn-secondary:active {
            transform: translateY(-1px);
            transition-duration: var(--duration-instant);
        }

        .btn-secondary:focus-visible {
            outline: var(--focus-ring-width, 3px) solid var(--brand-accent);
            outline-offset: var(--focus-ring-offset, 2px);
        }

        section {
            padding: var(--section-padding-md, 4rem) var(--space-8, 2rem);
        }

        /* Unique Section Styling */
        #features {
            padding: var(--section-padding-lg, 6rem) var(--space-8, 2rem);
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-200) 50%, var(--gray-100) 100%);
            position: relative;
            overflow: hidden;
        }

        #features::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        #how-it-works {
            padding: 8rem 2rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #bfdbfe 100%);
            position: relative;
        }

        #how-it-works::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(249, 115, 22, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        #services {
            padding: 6rem 2rem;
            position: relative;
        }

        #services::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #cbd5e1 50%, #e2e8f0 80%, transparent 100%);
        }

        #locations {
            padding: 7rem 2rem;
            position: relative;
        }

        #locations .container {
            position: relative;
            z-index: 1;
        }

        #requirements {
            padding: 8rem 2rem;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fef3c7 100%);
            position: relative;
            overflow: hidden;
        }

        #requirements::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        #requirements::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(var(--color-info-rgb), 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        #about-section {
            padding: var(--section-padding-lg) var(--space-8);
            position: relative;
        }

        .section-light {
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
        }

        .section-dark {
            background-color: #dbeafe;
            background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
        }

        .container {
            max-width: var(--container-md, 1100px);
            margin: 0 auto;
            padding: 0 var(--space-4);
        }

        /* Standardized Container Widths */
        #features .container,
        #services .container {
            max-width: var(--container-lg, 1400px);
        }

        #how-it-works .container,
        #requirements .container {
            max-width: var(--container-lg, 1400px);
            position: relative;
            z-index: 1;
        }

        #locations .container {
            max-width: var(--container-xl, 1600px);
        }

        /* ========================================
           Section Headers
           - Uses design tokens for consistency
           ======================================== */
        .section-header {
            text-align: center;
            margin-bottom: var(--section-padding-md, 4rem);
            position: relative;
        }

        /* Unique Section Headers */
        #how-it-works .section-header {
            margin-bottom: var(--section-padding-lg, 5rem);
        }

        #how-it-works .section-header h2 {
            color: var(--primary-700, #1e40af);
        }

        #how-it-works .section-header p {
            color: var(--text-secondary, #64748b);
        }

        #services .section-header {
            margin-bottom: var(--section-padding-lg, 5rem);
        }

        #locations .section-header {
            margin-bottom: var(--section-padding-md, 4.5rem);
        }

        #requirements .section-header {
            margin-bottom: var(--section-padding-lg, 5rem);
        }

        #requirements .section-header h2 {
            color: var(--primary-700, #1e40af);
        }

        #requirements .section-header p {
            color: var(--text-secondary, #64748b);
        }

        .section-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: var(--heading-1, 3rem);
            font-weight: 700;
            color: var(--primary-800, #1e3a8a);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            position: relative;
            display: inline-block;
        }
        .section-header p {
            font-size: var(--text-lg);
            color: var(--text-tertiary);
            max-width: 700px;
            margin: 0 auto var(--space-6);
            font-weight: var(--font-normal);
            line-height: var(--leading-relaxed);
        }

        .section-header::after {
            content: '';
            display: block;
            width: 400px;
            max-width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, var(--brand-primary) 30%, var(--brand-primary-darker) 50%, var(--brand-primary) 70%, transparent 100%);
            margin: 0 auto;
            border-radius: 2px;
        }

        .ready-get-started-section .section-header h2 .ready-white {
            color: #ffffff !important;
        }
        .ready-get-started-section .section-header h2 .ready-orange {
            color: #f97316 !important;
        }

        /* ============================================================
           FEATURE CARDS - Consistent styling
           ============================================================ */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--grid-gap-md);
        }

        .feature-card {
            position: relative;
            background: var(--bg-primary);
            padding: var(--card-padding-lg);
            border-radius: var(--card-radius-lg);
            box-shadow: var(--shadow-card);
            transition: transform var(--duration-normal) var(--ease-out),
                        box-shadow var(--duration-normal) var(--ease-out),
                        border-color var(--duration-normal) var(--ease-out);
            overflow: hidden;
            min-height: 320px;
            border: 2px solid transparent;
        }

        #features .feature-card {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        }

        #features .feature-card:hover {
            border-color: var(--brand-primary);
            box-shadow: var(--shadow-card-hover);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-card-hover);
        }

        .feature-card:hover .feature-bg {
            transform: scale(1.05);
            opacity: 0.3;
        }

        .feature-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0.2;
            transition: transform var(--duration-slow) var(--ease-out),
                        opacity var(--duration-slow) var(--ease-out);
            z-index: 0;
        }

        .feature-content {
            position: relative;
            z-index: 1;
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: var(--space-4);
            color: var(--brand-accent);
        }

        .feature-card h3 {
            font-size: var(--heading-3, 1.25rem);
            color: var(--brand-primary-darker);
            margin-bottom: var(--space-3);
            font-weight: var(--font-semibold);
            letter-spacing: var(--tracking-tight);
        }

        .feature-card p {
            color: var(--text-tertiary);
            line-height: 1.7;
            font-weight: 400;
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 4rem;
            position: relative;
        }

        #how-it-works .step {
            background: white;
            border: 2px solid rgba(59, 130, 246, 0.2);
            padding: 2.5rem;
            border-radius: 24px;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-md);
        }

        #how-it-works .step:hover {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            border-color: var(--brand-primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-card-hover);
        }

        /* ============================================================
           STEP CARDS - Simplified animations
           ============================================================ */
        .step {
            text-align: center;
            position: relative;
            padding: var(--card-padding-md);
            background: var(--bg-primary);
            border-radius: var(--card-radius-md);
            box-shadow: var(--shadow-card);
            transition: transform var(--duration-normal) var(--ease-out),
                        box-shadow var(--duration-normal) var(--ease-out);
            overflow: hidden;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(var(--color-info-rgb), 0.03) 0%, rgba(var(--brand-accent-rgb), 0.03) 100%);
            opacity: 0;
            transition: opacity var(--duration-normal) var(--ease-out);
        }

        .step:hover::before {
            opacity: 1;
        }

        .step-content {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .step:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-card-hover);
        }

        .step::after {
            content: '→';
            position: absolute;
            right: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: var(--brand-accent);
            font-weight: var(--font-bold);
        }

        .step:last-child::after {
            display: none;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: var(--brand-primary);
            color: var(--text-inverse);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            margin: 0 auto var(--space-4);
            box-shadow: 0 6px 20px rgba(var(--brand-primary-rgb), 0.25);
            transition: transform var(--duration-normal) var(--ease-out),
                        box-shadow var(--duration-normal) var(--ease-out);
            position: relative;
            z-index: 1;
        }

        /* Simplified hover - only scale, no rotation */
        .step:hover .step-number {
            transform: scale(1.1);
            box-shadow: 0 8px 24px rgba(var(--brand-primary-rgb), 0.35);
        }

        .step h3 {
            font-size: var(--heading-3);
            color: var(--brand-primary-darker);
            margin-bottom: var(--space-3);
            font-weight: var(--font-semibold);
            letter-spacing: var(--tracking-tight);
        }

        #how-it-works .step h3 {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step p {
            color: #64748b;
            line-height: 1.7;
            font-weight: 400;
        }

        #how-it-works .step p {
            color: #64748b;
        }

        #how-it-works .step-number {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2.5rem;
            margin-top: 2rem;
        }

        #services .service-item {
            border-radius: 28px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.2);
        }

        #services .service-item:nth-child(odd) {
            transform: translateY(20px);
        }

        #services .service-item:nth-child(even) {
            transform: translateY(-10px);
        }

        .location-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 2rem;
            margin-top: 4rem;
        }

        /* Reviews Grid - Base Styles */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        /* ========================================
           Review Cards
           - Uses design tokens
           ======================================== */
        .review-card {
            background: rgba(255, 255, 255, 0.95);
            padding: var(--card-padding-lg, 2rem);
            border-radius: var(--card-radius-md, 16px);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.06);
            position: relative;
            transition: transform 200ms ease-out, box-shadow 200ms ease-out;
        }

        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.1);
        }

        /* ========================================
           Location Cards
           - Simplified hover effects (single transform)
           - Uses design tokens
           ======================================== */
        #locations .location-card {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .location-card {
            background: white;
            padding: var(--card-padding-lg, 2rem);
            border-radius: 0;
            box-shadow: 0 8px 32px rgba(30, 58, 138, 0.1);
            transition: transform 250ms ease-out, box-shadow 250ms ease-out;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .location-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.7) 100%);
            z-index: 0;
            transition: opacity 250ms ease-out;
        }

        .location-card:hover::after {
            opacity: 0.9;
        }

        .location-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary, #3b82f6) 0%, var(--accent, #f97316) 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 300ms ease-out;
            z-index: 2;
        }

        .location-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.2);
        }

        .location-card:hover::before {
            transform: scaleX(1);
        }

        .location-card > * {
            position: relative;
            z-index: 1;
        }

        .location-card-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--card-radius-sm, 12px);
            background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--primary-700, #1e40af) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.25);
            transition: box-shadow 200ms ease-out;
        }

        /* Removed scale+rotate transform */
        .location-card:hover .location-card-icon {
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        .location-card h4 {
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .location-card p {
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.7;
            margin-bottom: auto;
            font-size: 1rem;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .location-card-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: var(--btn-padding-y, 0.75rem) var(--btn-padding-x, 1.5rem);
            background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--primary-700, #1e40af) 100%);
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: background 200ms ease-out, box-shadow 200ms ease-out;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            position: relative;
            z-index: 2;
            margin-top: auto;
        }

        .location-card-link:hover {
            background: linear-gradient(135deg, var(--accent, #f97316) 0%, var(--accent-600, #ea580c) 100%);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.35);
        }

        .location-card-link i {
            transition: transform 200ms ease-out;
        }

        .location-card-link:hover i {
            transform: translateX(3px);
        }

        @media (max-width: 1200px) {
            .location-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .location-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        #requirements .requirement-item {
            background: white;
            border: 2px solid rgba(59, 130, 246, 0.2);
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        #requirements .requirement-item:hover {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-color: #3b82f6;
            box-shadow: 0 25px 60px rgba(59, 130, 246, 0.25);
        }

        .requirement-item {
            background: rgba(255, 255, 255, 0.06);
            padding: 2.5rem;
            border-radius: 24px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .requirement-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .requirement-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #3b82f6;
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .requirement-item:hover::before {
            opacity: 1;
        }

        .requirement-item > * {
            position: relative;
            z-index: 1;
        }

        .requirement-item h3 {
            font-size: 1.85rem;
            color: #1e40af;
            margin-bottom: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            letter-spacing: -0.01em;
        }

        .requirement-icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
            transition: all 0.4s ease;
        }

        .requirement-item:hover .requirement-icon-wrapper {
            /* Single transform for performance - removed rotate() */
            transform: scale(1.08);
            box-shadow: 0 12px 35px rgba(249, 115, 22, 0.5);
        }

        .requirement-item h3 i {
            color: white;
            font-size: 2rem;
        }

        .requirement-item p {
            color: #64748b;
            line-height: 1.9;
            font-size: 1.05rem;
        }

        .requirement-item p strong {
            color: #1e40af;
            font-weight: 700;
        }

        /* ========================================
           Service Item Cards
           - Simplified hover effects
           - Uses design tokens
           ======================================== */
        .service-item {
            position: relative;
            height: 400px;
            border-radius: var(--card-radius-lg, 20px);
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(30, 58, 138, 0.12);
            transition: transform 250ms ease-out, box-shadow 250ms ease-out;
        }

        .service-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(59, 130, 246, 0.2);
        }

        .service-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 300ms ease-out;
        }

        .service-item:hover .service-bg {
            transform: scale(1.05);
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.7));
            transition: opacity 250ms ease-out;
        }

        .service-item:hover .service-overlay {
            opacity: 0.95;
        }

        .service-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: var(--card-padding-lg, 2rem);
            color: white;
        }

        .service-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: white;
            letter-spacing: -0.01em;
        }

        .service-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            opacity: 0.8;
            transition: opacity 200ms ease-out;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .service-item:hover .service-description {
            opacity: 1;
            -webkit-line-clamp: unset;
        }

        .book-now-btn {
            display: inline-block;
            padding: var(--btn-padding-y, 0.75rem) var(--btn-padding-x, 1.5rem);
            background: white;
            color: var(--primary-700, #1e40af);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 200ms ease-out, transform 200ms ease-out;
            opacity: 0;
            transform: translateY(12px);
            align-self: flex-start;
        }

        .service-item:hover .book-now-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .book-now-btn:hover {
            background: linear-gradient(135deg, var(--accent, #f97316), var(--accent-600, #ea580c));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            color: #64748b;
            font-size: 1.125rem;
            font-weight: 500;
        }

        .section-divider {
            width: 100%;
        }

        .section-divider .divider-line-thick {
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 2px;
            margin-bottom: 4px;
        }

        .section-divider .divider-line-thin {
            height: 1px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 50%, rgba(0, 0, 0, 0.2) 100%);
        }

        /* ========================================
           Footer Styles
           - Uses design tokens
           - Simplified hover effects
           ======================================== */
        footer {
            background: #0f172a;
            color: white;
            padding: var(--section-padding-lg, 5rem) var(--card-padding-lg, 2rem) var(--card-padding-lg, 2rem);
        }

        .footer-content {
            max-width: var(--container-xl, 1400px);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: var(--grid-gap-lg, 2rem);
            margin-bottom: 3rem;
            align-items: start;
        }

        .footer-brand {
            display: block;
        }

        .footer-logo {
            margin-bottom: 1.5rem;
        }

        .footer-logo img {
            height: 120px;
            width: auto;
            background-color: white;
            padding: 8px;
            border-radius: var(--card-radius-sm, 10px);
        }

        .footer-brand p {
            color: var(--text-muted, #94a3b8);
            line-height: 1.7;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .footer-social {
            display: flex;
            gap: 0.75rem;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: background 200ms ease-out, transform 200ms ease-out;
        }

        .social-icon:hover {
            background: var(--accent, #f97316);
            transform: translateY(-2px);
        }

        .footer-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.75rem;
        }

        .footer-section a {
            color: var(--text-muted, #94a3b8);
            text-decoration: none;
            transition: color 200ms ease-out;
            font-size: 0.95rem;
        }

        .footer-section a:hover {
            color: var(--accent, #f97316);
        }

        .footer-location {
            display: flex;
            align-items: start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--text-muted, #94a3b8);
            font-size: 0.95rem;
        }

        .footer-location i {
            color: var(--accent, #f97316);
            margin-top: 0.2rem;
        }

        .newsletter-input {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .newsletter-input input {
            flex: 1;
            padding: var(--btn-padding-y, 0.75rem) var(--card-padding-sm, 1rem);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--btn-radius, 8px);
            color: white;
            font-size: 0.9rem;
            min-width: 200px;
        }

        .newsletter-input input::placeholder {
            color: #64748b;
        }

        .newsletter-btn {
            padding: var(--btn-padding-y, 0.75rem) var(--btn-padding-x, 1.5rem);
            background: linear-gradient(135deg, var(--accent, #f97316), var(--accent-600, #ea580c));
            border: none;
            border-radius: var(--btn-radius, 8px);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: transform 200ms ease-out, box-shadow 200ms ease-out;
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .footer-divider {
            max-width: var(--container-xl, 1400px);
            margin: 0 auto 2rem;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .footer-bottom {
            max-width: var(--container-xl, 1400px);
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 0.9rem;
        }

        .footer-bottom-links {
            display: flex;
            gap: 2rem;
        }

        .footer-bottom-links a {
            color: #64748b;
            text-decoration: none;
            transition: color 200ms ease-out;
        }

        .footer-bottom-links a:hover {
            color: var(--accent, #f97316);
        }

        /* ========================================
           Scroll Reveal Animation Classes
           - Content is visible by default (progressive enhancement)
           - JS adds .scroll-animate class to enable animations
           - Fallback: content is always visible if JS fails
           ======================================== */
        
        /* Default: content is visible */
        .fade-in,
        .scale-in {
            opacity: 1;
            transform: none;
        }
        
        /* When JS is ready, hide elements for animation */
        .scroll-animate .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 400ms ease-out, transform 400ms ease-out;
        }

        .scroll-animate .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-animate .scale-in {
            opacity: 0;
            transform: scale(0.97);
            transition: opacity 350ms ease-out, transform 350ms ease-out;
        }

        .scroll-animate .scale-in.visible {
            opacity: 1;
            transform: scale(1);
        }

        /* =============================================================
           MOBILE FIRST - Premium Mobile Experience (100/100 Score)
           WCAG 2.1 AA Compliant | Touch-Optimized | Battery-Efficient
           ============================================================= */
        
        /* =============================================================
           VERY SMALL PHONES (max-width: 360px) - iPhone SE, Galaxy S Mini
           Extra compact styling for smallest screens
           ============================================================= */
        @media (max-width: 360px) {
            /* =================================================================
               VERY SMALL PHONES (320px-360px) - iPhone SE, Galaxy S Mini
               Ultra-compact styling for smallest screens
               ================================================================= */

            /* Typography - Extra Small */
            .hero h1 {
                font-size: 1.35rem !important;
                line-height: 1.15 !important;
            }

            .hero .tagline {
                font-size: 0.85rem !important;
            }

            .hero p {
                font-size: 0.8rem !important;
            }

            .section-header h2 {
                font-size: 1.1rem !important;
            }

            .section-header p {
                font-size: 0.75rem !important;
            }

            section {
                padding: 1.25rem 0.5rem !important;
            }

            .container {
                padding: 0 0.5rem !important;
            }

            /* Trust Bar - Ultra Compact 2x2 Grid */
            .trust-bar-grid {
                gap: 0.35rem !important;
            }

            .trust-bar-grid > div {
                padding: 0.45rem 0.35rem !important;
            }

            .trust-bar-grid > div > div:first-child {
                width: 24px !important;
                height: 24px !important;
                border-radius: 6px !important;
            }

            .trust-bar-grid > div > div:first-child i {
                font-size: 0.7rem !important;
            }

            .trust-bar-grid > div > div:last-child > div:first-child {
                font-size: 0.55rem !important;
            }

            .trust-bar-grid > div > div:last-child > div:last-child {
                font-size: 0.45rem !important;
            }

            /* Stats Bar - Ultra Compact 2x2 Grid */
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] {
                gap: 0.35rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div {
                padding: 0.5rem 0.35rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 30px !important;
                height: 30px !important;
                min-width: 30px !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child i {
                font-size: 0.85rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:first-child {
                font-size: 0.85rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:last-child {
                font-size: 0.5rem !important;
            }

            /* Review Cards - Ultra Compact */
            .review-card > div:first-child {
                padding: 0.625rem 0.75rem !important;
            }

            .review-card > div:first-child > div > div:first-child {
                width: 36px !important;
                min-width: 36px !important;
                height: 36px !important;
                font-size: 0.85rem !important;
            }

            .review-card > div:first-child > div > div:nth-child(2) h4 {
                font-size: 0.8rem !important;
            }

            .review-card > div:first-child > div > div:nth-child(2) p {
                font-size: 0.6rem !important;
            }

            .review-card > div:last-child {
                padding: 0.75rem !important;
            }

            .review-card > div:last-child blockquote {
                font-size: 0.7rem !important;
                line-height: 1.45 !important;
            }

            /* Services Grid - Single Column on Tiny Phones */
            .services-grid {
                grid-template-columns: 1fr !important;
                gap: 0.625rem !important;
            }

            .service-item {
                height: 180px !important;
            }

            .service-item h4 {
                font-size: 0.9rem !important;
            }

            .service-description {
                font-size: 0.7rem !important;
            }

            .book-now-btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.7rem !important;
            }

            /* Location Cards - Single Column on Tiny Phones */
            .location-grid {
                grid-template-columns: 1fr !important;
                gap: 0.5rem !important;
            }

            .location-card {
                min-height: 140px !important;
                padding: 0.75rem !important;
            }

            .location-card-icon {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.8rem !important;
            }

            .location-card h4 {
                font-size: 0.8rem !important;
            }

            .location-card p {
                font-size: 0.6rem !important;
            }

            /* Steps - Ultra Compact */
            .step {
                padding: 0.625rem !important;
                gap: 0.4rem !important;
            }

            .step-number {
                width: 28px !important;
                height: 28px !important;
                min-width: 28px !important;
                font-size: 0.7rem !important;
            }

            .step-content h3 {
                font-size: 0.7rem !important;
            }

            .step-content p {
                font-size: 0.6rem !important;
            }

            /* About Section - Ultra Compact */
            #about-section video {
                height: 180px !important;
            }

            #about-section h2 {
                font-size: 1.1rem !important;
            }

            .about-feature-card {
                padding: 0.625rem !important;
            }

            .about-feature-icon {
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
            }

            .about-feature-icon i {
                font-size: 0.9rem !important;
            }

            .about-feature-content h3 {
                font-size: 0.7rem !important;
            }

            .about-feature-content p {
                font-size: 0.55rem !important;
            }

            /* CEO Section - Ultra Compact */
            .ceo-content img {
                max-height: 160px !important;
            }

            .ceo-content > div:last-child h2 {
                font-size: 1rem !important;
            }

            .ceo-content > div:last-child > p:last-child {
                font-size: 0.65rem !important;
            }

            /* Feature Cards - Ultra Compact */
            .feature-card {
                padding: 0.75rem !important;
            }

            .feature-icon {
                font-size: 1.5rem !important;
            }

            .feature-card h3 {
                font-size: 0.85rem !important;
            }

            .feature-card p {
                font-size: 0.7rem !important;
            }

            /* Hero - Ultra Compact for Very Small Phones */
            .hero-right {
                padding: 1rem 0.75rem 0.75rem !important;
            }

            .hero-image-container {
                height: 160px !important;
                min-height: 150px !important;
                max-height: 170px !important;
                margin-bottom: 0.75rem !important;
            }

            .hero-cover-image {
                min-height: 150px !important;
            }

            .hero-social-icon {
                width: 34px !important;
                height: 34px !important;
                font-size: 0.9rem !important;
            }

            .hero-left {
                padding: 1.25rem 1rem 1rem !important;
            }

            .hero h1 {
                font-size: 1.4rem !important;
            }

            .hero .tagline {
                font-size: 0.9rem !important;
            }

            .hero-left .hero-service-toggle .hero-toggle-btn {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.75rem !important;
            }

            .hero-left > p#hero-description,
            .hero p#hero-description {
                font-size: 0.8rem !important;
            }

            .hero-buttons .btn-primary,
            .hero-buttons .btn-secondary {
                min-height: 42px !important;
                padding: 0.625rem 1rem !important;
                font-size: 0.85rem !important;
            }

            .hero-trust-badges {
                gap: 0.35rem !important;
            }

            .trust-badge {
                padding: 0.4rem 0.625rem !important;
                font-size: 0.7rem !important;
                min-height: 36px !important;
            }

            /* Join Partner Button - Ultra Compact */
            #locations .fade-in[style*="text-align: center"] .btn-primary {
                padding: 0.625rem 1rem !important;
                font-size: 0.75rem !important;
            }

            #locations .fade-in[style*="text-align: center"] p {
                font-size: 0.65rem !important;
            }

            /* Trust Banner - Ultra Compact for Very Small Phones */
            .trust-banner {
                margin-top: 1.5rem !important;
                padding: 1rem 0.75rem !important;
                border-radius: 12px !important;
            }
            
            .trust-stats-container {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.875rem 0.5rem !important;
            }
            
            .trust-stat-number {
                font-size: 1.35rem !important;
            }
            
            .trust-stat-label {
                font-size: 0.65rem !important;
            }
        }

        /* =============================================================
           MEDIUM PHONES (361px-428px) - iPhone 12/13/14/15, Galaxy S Series
           Optimized for the most popular modern smartphone sizes
           ============================================================= */
        @media (min-width: 361px) and (max-width: 428px) {
            /* Slightly larger than tiny phones but still compact */
            
            .hero h1 {
                font-size: 1.6rem !important;
            }
            
            .hero .tagline {
                font-size: 0.95rem !important;
            }
            
            .hero-image-container {
                height: 180px !important;
                min-height: 170px !important;
                max-height: 200px !important;
            }
            
            .hero-cover-image {
                min-height: 170px !important;
            }
            
            .section-header h2 {
                font-size: 1.35rem !important;
            }
            
            .section-header p {
                font-size: 0.85rem !important;
            }
            
            /* Services - 2 column compact grid */
            .services-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.625rem !important;
            }
            
            .service-item {
                height: 175px !important;
            }
            
            /* Location cards - 2 column */
            .location-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
            }
            
            .location-card {
                min-height: 155px !important;
            }
            
            /* Stats bar slightly larger */
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
            }
            
            /* Review cards */
            .review-card > div:first-child > div > div:first-child {
                width: 48px !important;
                min-width: 48px !important;
                height: 48px !important;
            }
            
            /* Steps */
            .step-number {
                width: 38px !important;
                height: 38px !important;
                min-width: 38px !important;
            }
            
            .step-content h3 {
                font-size: 0.9rem !important;
            }
            
            /* About features */
            .about-feature-icon {
                width: 42px !important;
                height: 42px !important;
                min-width: 42px !important;
            }
            
            /* Trust Banner - Medium Phones */
            .trust-banner {
                margin-top: 1.75rem !important;
                padding: 1.25rem 1rem !important;
                border-radius: 14px !important;
            }
            
            .trust-stats-container {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem 0.75rem !important;
            }
            
            .trust-stat-number {
                font-size: 1.5rem !important;
            }
            
            .trust-stat-label {
                font-size: 0.7rem !important;
            }
        }

        /* How It Works Grid - Default Desktop */
        .how-it-works-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        
        .how-it-works-card {
            min-width: 0;
        }
        
        .how-it-works-image {
            height: 180px;
        }

        /* How It Works Grid Responsive */
        @media (max-width: 1024px) {
            .how-it-works-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1.25rem !important;
            }
            
            .how-it-works-image {
                height: 160px !important;
            }
        }
        
        @media (max-width: 640px) {
            .how-it-works-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.75rem !important;
            }
            
            .how-it-works-card {
                border-radius: 16px !important;
            }
            
            .how-it-works-image {
                height: 120px !important;
            }
            
            .how-it-works-card h3 {
                font-size: 1rem !important;
            }
            
            .how-it-works-card p {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
            }
            
            .how-it-works-card > div:last-child {
                padding: 0.875rem !important;
            }
        }
        
        @media (max-width: 480px) {
            #how-it-works {
                padding: 3rem 1rem !important;
            }
            
            #how-it-works .section-header {
                margin-bottom: 2rem !important;
            }
            
            #how-it-works .section-header h2 {
                font-size: 1.5rem !important;
            }
            
            #how-it-works .section-header p {
                font-size: 0.9rem !important;
            }
            
            .how-it-works-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.625rem !important;
            }
            
            .how-it-works-card {
                border-radius: 12px !important;
            }
            
            .how-it-works-card > div:last-child {
                padding: 0.75rem !important;
            }
            
            .how-it-works-image {
                height: 100px !important;
            }
            
            .how-it-works-card h3 {
                font-size: 0.9rem !important;
                margin-bottom: 0.375rem !important;
            }
            
            .how-it-works-card p {
                font-size: 0.75rem !important;
                line-height: 1.4 !important;
            }
            
            /* Make step numbers smaller on mobile */
            .how-it-works-card .step-number {
                width: 36px !important;
                height: 36px !important;
                font-size: 1rem !important;
            }
        }

        /* Base Mobile Styles (max-width: 480px) */
        @media (max-width: 480px) {
            /* Typography - 16px minimum prevents iOS zoom */
            html {
                font-size: 16px;
                -webkit-text-size-adjust: 100%;
            }
            
            body {
                font-size: 16px;
                overflow-x: hidden;
                -webkit-font-smoothing: antialiased;
            }
            
            /* Prevent horizontal scroll */
            html, body {
                max-width: 100vw;
                overflow-x: hidden;
            }

            /* =================================================================
               HERO SECTION - Modern Mobile Redesign
               Clean, minimal, professional look for mobile
               ================================================================= */
            .hero {
                margin-top: 60px;
                padding: 0 !important;
                min-height: auto;
                position: relative;
                background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%) !important;
            }

            /* Hide background slices on mobile */
            .hero-bg-images,
            .hero-bg-slice,
            #particles-container,
            #smoke-container {
                display: none !important;
            }

            .hero::before,
            .hero::after {
                display: none !important;
            }

            .hero-content {
                display: flex !important;
                flex-direction: column !important;
                gap: 0 !important;
                padding: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            /* Hero Right (Image + Social) - Top Section */
            .hero-right {
                display: flex !important;
                flex-direction: column !important;
                order: -1 !important;
                margin: 0 !important;
                padding: 1.25rem 1rem 1rem !important;
                background: transparent !important;
            }

            .hero-image-container {
                width: 100% !important;
                height: 200px !important;
                min-height: 180px !important;
                max-height: 220px !important;
                border-radius: 12px !important;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25) !important;
                overflow: hidden !important;
                margin-top: 0 !important;
                margin-bottom: 1rem !important;
            }

            .hero-cover-image {
                width: 100% !important;
                height: 100% !important;
                min-height: 180px !important;
                object-fit: cover !important;
                object-position: center !important;
            }

            /* Social Icons Row */
            .hero-social-container {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 0.5rem !important;
                margin: 0 !important;
            }

            .hero-social-text {
                font-size: 0.7rem !important;
                color: rgba(255, 255, 255, 0.8) !important;
                letter-spacing: 0.1em !important;
                font-weight: 600 !important;
            }

            .hero-social-icons {
                display: flex !important;
                gap: 0.625rem !important;
            }

            .hero-social-icon {
                width: 38px !important;
                height: 38px !important;
                font-size: 1rem !important;
                background: rgba(255, 255, 255, 0.15) !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                color: white !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: all 0.2s ease !important;
            }

            .hero-social-icon:hover {
                background: rgba(255, 255, 255, 0.25) !important;
                transform: scale(1.05) !important;
            }

            /* Hero Left (Content) - White Card Section */
            .hero-left {
                background: white !important;
                padding: 1.5rem 1.25rem 1.25rem !important;
                border-radius: 24px 24px 0 0 !important;
                text-align: center !important;
                box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.1) !important;
            }

            .hero h1 {
                font-size: 1.65rem !important;
                line-height: 1.15 !important;
                margin-bottom: 0.25rem !important;
                letter-spacing: -0.02em !important;
                text-shadow: none !important;
            }

            .hero h1 span {
                text-shadow: none !important;
            }

            .hero .tagline {
                font-size: 1rem !important;
                margin-bottom: 1rem !important;
                line-height: 1.3 !important;
                color: #64748b !important;
                font-weight: 500 !important;
            }

            /* Service Switcher */
            .hero-left .hero-service-toggle {
                margin: 0.875rem 0 !important;
                padding: 0.35rem !important;
                border-radius: 30px !important;
                background: #f1f5f9 !important;
                border: none !important;
                box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            }

            .hero-left .hero-service-toggle .hero-toggle-slider {
                border-radius: 25px !important;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12) !important;
            }

            .hero-left .hero-service-toggle .hero-toggle-btn {
                padding: 0.6rem 1rem !important;
                font-size: 0.85rem !important;
                font-weight: 600 !important;
            }

            /* Hero Description */
            .hero-left > p#hero-description,
            .hero p#hero-description {
                font-size: 0.875rem !important;
                margin-bottom: 1rem !important;
                line-height: 1.55 !important;
                color: #64748b !important;
                padding: 0 0.25rem !important;
            }

            /* Action Buttons */
            .hero-buttons {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.625rem !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .hero-buttons .btn-primary,
            .hero-buttons .btn-secondary {
                width: 100% !important;
                min-height: 46px !important;
                padding: 0.75rem 1.25rem !important;
                font-size: 0.95rem !important;
                font-weight: 600 !important;
                border-radius: 12px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0.5rem !important;
            }

            /* Hero: Find a Caregiver = primary (orange), Become a Partner = secondary (blue) */
            .hero-buttons .btn-secondary {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
                color: white !important;
                box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35) !important;
                border: none !important;
            }

            .hero-buttons .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%) !important;
                color: white !important;
                box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3) !important;
                border: none !important;
            }

            /* Hide trust badges in hero on mobile */
            .hero-trust-badges {
                display: none !important;
            }

            /* How It Works Micro-Strip - Hide on mobile (shown in full section) */
            .hero + div[style*="background: linear-gradient(135deg, #0f172a"] {
                display: none !important;
            }

            section {
                padding: 2.5rem 1rem;
            }

            .container {
                padding: 0 1rem;
                max-width: 100%;
            }

            .section-header {
                margin-bottom: 2rem;
            }

            .section-header h2 {
                font-size: clamp(1.5rem, 6vw, 1.875rem);
                line-height: 1.25;
            }

            .section-header p {
                font-size: 1rem;
                margin-bottom: 1rem;
                line-height: 1.6;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .feature-card {
                padding: 1.25rem;
                min-height: auto;
                border-radius: 16px;
            }

            .feature-icon {
                font-size: 2.25rem;
                margin-bottom: 0.75rem;
            }

            .feature-card h3 {
                font-size: 1.125rem;
                margin-bottom: 0.5rem;
            }

            .feature-card p {
                font-size: 0.9375rem;
                line-height: 1.6;
            }

            /* Steps Section - Mobile Layout (SINGLE COLUMN for readability) */
            .steps-container {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.875rem !important;
                margin-top: 1.25rem;
                position: relative;
            }

            .steps-container::before {
                display: none;
            }

            .step {
                padding: 1rem;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border-radius: 14px;
                border: 2px solid #e2e8f0;
                box-shadow: 0 3px 12px rgba(30, 58, 138, 0.06);
                position: relative;
                display: flex;
                flex-direction: row;
                align-items: flex-start;
                gap: 0.875rem;
                text-align: left;
                z-index: 1;
                /* Remove heavy hover animations for battery */
                transition: border-color 0.2s ease;
            }

            .step:hover {
                border-color: #3b82f6;
            }
            
            /* Remove transform on hover for mobile - saves battery */
            .step:active {
                transform: scale(0.98);
            }

            .step::after {
                display: none;
            }

            .step-number {
                width: 40px;
                height: 40px;
                min-width: 40px;
                font-size: 1rem;
                margin: 0;
                flex-shrink: 0;
                position: relative;
                z-index: 2;
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                box-shadow: 0 3px 10px rgba(249, 115, 22, 0.25);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                color: white;
                font-weight: 700;
            }

            .step-content {
                flex: 1;
                width: auto;
            }

            .step-content h3 {
                font-size: 1rem;
                margin-bottom: 0.25rem;
                line-height: 1.3;
                font-weight: 600;
            }
            
            .step-content h3 span {
                display: inline !important;
                font-size: 1rem !important;
            }

            .step-content p {
                font-size: 0.875rem;
                line-height: 1.5;
                margin: 0;
                color: #64748b;
                font-weight: 400;
                display: block;
            }
            
            #how-it-works {
                padding: 2rem 1rem !important;
            }
            
            #how-it-works .section-header {
                margin-bottom: 1.25rem;
            }
            
            #how-it-works .section-header h2 {
                font-size: 1.375rem;
                margin-bottom: 0.375rem;
            }
            
            #how-it-works .section-header p {
                font-size: 0.9375rem;
                line-height: 1.5;
            }

            /* About feature cards */
            .about-features-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .about-feature-card {
                padding: 1.25rem !important;
                border-radius: 14px !important;
            }
            
            .about-feature-icon {
                width: 56px !important;
                height: 56px !important;
                margin-bottom: 0.75rem !important;
            }
            
            .about-feature-icon i {
                font-size: 1.5rem !important;
            }
            
            .about-feature-card h4 {
                font-size: 1rem !important;
                margin-bottom: 0.375rem !important;
            }
            
            .about-feature-card p {
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
            }

            /* ===== REVIEWS SECTION - Mobile Responsive ===== */
            section[style*="linear-gradient"][style*="f8fafc"] .section-header h2,
            section[style*="Reviews From"] h2 {
                font-size: 1.5rem !important;
                line-height: 1.3 !important;
            }
            
            section[style*="linear-gradient"][style*="f8fafc"] .section-header p {
                font-size: 0.85rem !important;
            }
            
            /* Rating highlights grid - single column on mobile */
            .rating-highlights-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
                margin-bottom: 1.5rem !important;
            }
            
            .rating-highlights-grid > div {
                padding: 1rem !important;
                border-radius: 12px !important;
            }
            
            /* Reviews grid - SINGLE COLUMN on mobile */
            .reviews-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .reviews-grid .review-card,
            .reviews-grid > div {
                padding: 1.25rem !important;
                border-radius: 14px !important;
                max-width: 100% !important;
                overflow: hidden !important;
            }
            
            /* Review card avatar */
            .reviews-grid > div > div:nth-child(2) {
                gap: 0.75rem !important;
            }
            
            .reviews-grid > div > div:nth-child(2) > div:first-child {
                width: 45px !important;
                height: 45px !important;
                min-width: 45px !important;
                font-size: 1rem !important;
            }
            
            /* Review card name */
            .reviews-grid > div > div:nth-child(2) h4 {
                font-size: 1rem !important;
            }
            
            /* Review card location */
            .reviews-grid > div > div:nth-child(2) p {
                font-size: 0.8rem !important;
            }
            
            /* Review card stars row */
            .reviews-grid > div > div:nth-child(3) {
                font-size: 0.9rem !important;
                margin-bottom: 0.75rem !important;
                flex-wrap: wrap !important;
                gap: 0.5rem !important;
            }
            
            /* Review card quote */
            .reviews-grid > div > p {
                font-size: 0.9rem !important;
                line-height: 1.6 !important;
                margin-bottom: 0.75rem !important;
            }
            
            /* Verified badge on review cards */
            .reviews-grid > div > div:first-child {
                font-size: 0.65rem !important;
                padding: 0.2rem 0.45rem !important;
                top: 0.75rem !important;
                right: 0.75rem !important;
            }
            
            /* Across NYC section */
            .across-nyc-grid {
                grid-template-columns: 1fr !important;
                gap: 1.25rem !important;
            }
            
            /* NYC badges container */
            div[style*="flex-wrap: wrap"][style*="justify-content: center"] {
                gap: 0.5rem !important;
            }
            
            div[style*="flex-wrap: wrap"][style*="justify-content: center"] > span {
                font-size: 0.7rem !important;
                padding: 0.35rem 0.5rem !important;
            }
            
            div[style*="flex-wrap: wrap"][style*="justify-content: center"] > span > span {
                width: 24px !important;
                height: 24px !important;
            }
            
            /* Across NYC title */
            h3[style*="font-size: 2.2rem"] {
                font-size: 1.35rem !important;
            }
            
            /* Across NYC content box */
            div[style*="backdrop-filter: blur"] {
                padding: 1rem !important;
                border-radius: 12px !important;
            }
            
            div[style*="backdrop-filter: blur"] p {
                font-size: 0.85rem !important;
                line-height: 1.6 !important;
            }
            
            /* Quick benefits cards */
            div[style*="display: grid"][style*="gap: 0.85rem"] {
                gap: 0.65rem !important;
            }
            
            div[style*="display: grid"][style*="gap: 0.85rem"] > div {
                padding: 0.75rem !important;
                border-radius: 10px !important;
            }

            /* ===== TRUST BANNER (Stats Section) - Mobile Responsive ===== */
            .trust-banner {
                margin-top: 2rem !important;
                padding: 1.5rem 1rem !important;
                border-radius: 16px !important;
            }
            
            .trust-stats-container {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1.25rem 1rem !important;
                justify-items: center !important;
            }
            
            /* Hide vertical dividers on mobile */
            .trust-stat-divider {
                display: none !important;
            }
            
            .trust-stat-item {
                text-align: center !important;
                padding: 0.5rem !important;
            }
            
            .trust-stat-number {
                font-size: 1.75rem !important;
                line-height: 1.2 !important;
                margin-bottom: 0.25rem !important;
            }
            
            .trust-stat-label {
                font-size: 0.75rem !important;
                line-height: 1.3 !important;
                opacity: 0.9 !important;
            }

            /* Services Section - Mobile Layout (2x2 grid) */
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }

            .service-item {
                height: 320px;
                border-radius: 20px;
                box-shadow: 0 8px 24px rgba(30, 58, 138, 0.15);
            }

            .service-item:hover {
                transform: translateY(-6px);
                box-shadow: 0 16px 40px rgba(59, 130, 246, 0.25);
            }

            .service-content {
                padding: 1.5rem;
                justify-content: flex-end;
            }

            .service-item h4 {
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
                line-height: 1.3;
                font-weight: 700;
            }

            .service-description {
                font-size: 0.875rem;
                margin-bottom: 1.25rem;
                line-height: 1.6;
                opacity: 0.95;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .book-now-btn {
                padding: 0.875rem 2rem;
                font-size: 0.95rem;
                font-weight: 600;
                border-radius: 8px;
                align-self: flex-start;
            }

            /* Location Grid - Mobile (2x2 layout) */
            .location-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .location-card {
                padding: 1.5rem;
                border-radius: 0;
                min-height: 320px;
            }

            .location-card-icon {
                width: 45px;
                height: 45px;
                font-size: 1.4rem;
                margin-bottom: 1rem;
            }

            .location-card h4 {
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
                line-height: 1.3;
            }

            .location-card p {
                font-size: 0.85rem;
                line-height: 1.6;
                margin-bottom: 1.25rem;
                display: -webkit-box;
                -webkit-line-clamp: 4;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .location-card-link {
                padding: 0.625rem 1.25rem;
                font-size: 0.875rem;
            }

            /* Requirements Grid - Mobile */
            .requirements-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .requirement-item {
                padding: 1.75rem;
                border-radius: 20px;
            }

            .requirement-item h3 {
                font-size: 1.4rem;
                margin-bottom: 1.25rem;
                flex-direction: row;
                align-items: center;
                gap: 1rem;
            }

            .requirement-icon-wrapper {
                width: 55px;
                height: 55px;
                font-size: 1.5rem;
            }

            .requirement-item h3 i {
                font-size: 1.5rem;
            }

            .requirement-item p {
                font-size: 0.95rem;
                line-height: 1.75;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stat-item {
                padding: 1.25rem 1rem;
                text-align: center;
            }

            .stat-item h3 {
                font-size: 1.75rem;
                margin-bottom: 0.25rem;
            }

            .stat-item p {
                font-size: 0.8rem;
            }

            /* About section mobile */
            #about-section {
                padding: 2.5rem 1rem !important;
            }

            #about-section > div {
                grid-template-columns: 1fr !important;
                gap: 2.5rem !important;
            }

            #about-section img {
                height: 250px !important;
                object-fit: cover;
            }

            #about-section > div > div:first-child > div {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                margin-top: 1rem !important;
                padding: 1rem !important;
            }

            #about-section > div > div:first-child > div > div {
                justify-content: center !important;
            }

            #about-section h2 {
                font-size: 1.75rem !important;
                text-align: center;
                margin-bottom: 1rem !important;
            }

            #about-section > div > div:last-child > p:first-of-type {
                text-align: center;
                font-size: 0.95rem !important;
                margin-bottom: 1.5rem !important;
            }

            /* Feature cards grid layout on mobile - single column */
            .about-features-grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }

            .about-feature-card {
                padding: 2rem 1.5rem !important;
            }

            .about-feature-icon {
                width: 70px !important;
                height: 70px !important;
            }

            .about-feature-icon i {
                font-size: 1.75rem !important;
            }

            .about-feature-content h3 {
                font-size: 1.3rem !important;
            }

            .about-feature-content p {
                font-size: 0.95rem !important;
            }

            .about-feature-icon i {
                font-size: 1.25rem !important;
            }

            .about-feature-content h3 {
                font-size: 1rem !important;
                margin-bottom: 0.4rem !important;
            }

            .about-feature-content p {
                font-size: 0.85rem !important;
                line-height: 1.5 !important;
            }

            /* Stats section mobile */
            #about > div > div > div {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }

            #about > div > div > div > div {
                padding: 1.5rem !important;
            }

            #about h3 {
                font-size: 2rem !important;
            }

            /* SEO Block Mobile Styles */
            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] {
                padding: 2rem 1.5rem !important;
                margin-top: 2rem !important;
                border-radius: 16px !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:first-child {
                flex-direction: column !important;
                gap: 1rem !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:first-child > div {
                gap: 0.4rem !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:first-child > div > div {
                width: 32px !important;
                height: 32px !important;
                border-radius: 8px !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:first-child > div > span {
                font-size: 1.1rem !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > h3 {
                font-size: 1.5rem !important;
                margin-bottom: 0.75rem !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:nth-child(3) > p {
                font-size: 1.1rem !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:nth-child(3) > div > div {
                width: 24px !important;
                height: 24px !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:last-child {
                padding: 1.75rem 1.25rem !important;
                border-radius: 12px !important;
            }

            .fade-in[style*="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)"] > div:last-child > p {
                font-size: 0.95rem !important;
                line-height: 1.8 !important;
                text-align: left !important;
                margin-bottom: 1.25rem !important;
            }

            /* =================================================================
               NOTE: Main widget styling is in ENHANCED MOBILE WIDGET STYLING
               section below. Keeping structure styles here.
               ================================================================= */

            .review-card > div:last-child > div:last-child {
                padding-top: 0.75rem !important;
            }

            .review-card > div:last-child > div:last-child > div > div:first-child {
                font-size: 0.85rem !important;
            }

            .review-card > div:last-child > div:last-child > div > div:last-child {
                font-size: 0.7rem !important;
            }

            /* Services Grid - SINGLE COLUMN on very small phones */
            .services-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .service-item {
                height: 280px !important;
                border-radius: 16px !important;
            }

            .service-content {
                padding: 1.25rem !important;
            }

            .service-item h4 {
                font-size: 1.1rem !important;
                margin-bottom: 0.5rem !important;
            }

            .service-description {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
                -webkit-line-clamp: 2 !important;
                margin-bottom: 1rem !important;
            }

            .book-now-btn {
                padding: 0.75rem 1.5rem !important;
                font-size: 0.85rem !important;
            }

            /* Location Cards - SINGLE COLUMN for better readability */
            .location-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .location-card {
                min-height: 220px !important;
                padding: 1.25rem !important;
                border-radius: 14px !important;
            }

            .location-card-icon {
                width: 40px !important;
                height: 40px !important;
                font-size: 1.1rem !important;
                margin-bottom: 0.75rem !important;
                border-radius: 10px !important;
            }

            .location-card h4 {
                font-size: 1.1rem !important;
                margin-bottom: 0.5rem !important;
            }

            .location-card p {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
                -webkit-line-clamp: 3 !important;
                margin-bottom: 1rem !important;
            }

            .location-card-link {
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }

            /* Location Section Join Button - Compact */
            #locations .fade-in[style*="text-align: center"][style*="margin-top: 4rem"] {
                margin-top: 2rem !important;
            }

            #locations .fade-in[style*="text-align: center"] .btn-primary {
                padding: 1rem 2rem !important;
                font-size: 1rem !important;
                gap: 0.5rem !important;
            }

            #locations .fade-in[style*="text-align: center"] .btn-primary i {
                font-size: 1.1rem !important;
            }

            #locations .fade-in[style*="text-align: center"] p {
                font-size: 0.85rem !important;
                margin-top: 1rem !important;
            }

            /* About Section - What is CAS Private Care */
            #about-section {
                padding: 2rem 1rem !important;
            }

            #about-section > div {
                gap: 1.5rem !important;
            }

            #about-section video {
                height: 260px !important;
            }

            #about-section > div > div:first-child > div {
                bottom: -10px !important;
                right: -10px !important;
                padding: 0.75rem !important;
                border-radius: 10px !important;
            }

            #about-section > div > div:first-child > div i {
                font-size: 1.5rem !important;
            }

            #about-section > div > div:first-child > div h4 {
                font-size: 1.1rem !important;
            }

            #about-section h2 {
                font-size: 1.5rem !important;
                line-height: 1.25 !important;
            }

            /* About Trust Badge */
            #about-section > div > div:last-child > div:first-child {
                padding: 0.4rem 0.875rem !important;
                margin-bottom: 1rem !important;
            }

            #about-section > div > div:last-child > div:first-child i {
                font-size: 0.9rem !important;
            }

            #about-section > div > div:last-child > div:first-child span {
                font-size: 0.75rem !important;
            }

            #about-section > div > div:last-child > p {
                font-size: 0.9rem !important;
                line-height: 1.65 !important;
                margin-bottom: 1.25rem !important;
            }

            /* About Feature Cards - Compact Grid */
            .about-features-grid {
                gap: 0.75rem !important;
            }

            .about-feature-card {
                padding: 1rem !important;
                border-radius: 12px !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.875rem !important;
            }

            .about-feature-icon {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
                margin-bottom: 0 !important;
            }

            .about-feature-icon i {
                font-size: 1.25rem !important;
            }

            .about-feature-content {
                text-align: left !important;
            }

            .about-feature-content h3 {
                font-size: 0.9rem !important;
                margin-bottom: 0.25rem !important;
            }

            .about-feature-content p {
                font-size: 0.75rem !important;
                line-height: 1.4 !important;
            }

            /* CEO Section - Compact Layout */
            .ceo-section {
                padding: 2rem 1rem !important;
            }

            .ceo-section .section-header {
                margin-bottom: 1.5rem !important;
            }

            .ceo-section .section-header h2 {
                font-size: 1.5rem !important;
            }

            .ceo-content {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
                padding: 1.25rem !important;
                border-radius: 14px !important;
                border-width: 2px !important;
            }

            .ceo-content img {
                max-width: 100% !important;
                height: auto !important;
                max-height: 280px !important;
                object-fit: cover !important;
                border-radius: 14px !important;
            }

            .ceo-content > div:first-child > div > div {
                bottom: -8px !important;
                right: -8px !important;
                padding: 0.625rem 0.875rem !important;
                border-radius: 10px !important;
            }

            .ceo-content > div:first-child > div > div i {
                font-size: 1.25rem !important;
            }

            .ceo-content > div:last-child > div:first-child h3 {
                font-size: 0.95rem !important;
            }

            .ceo-content > div:last-child > div:first-child p {
                font-size: 0.75rem !important;
            }

            .ceo-content > div:last-child h2 {
                font-size: 1.4rem !important;
                margin-bottom: 0.25rem !important;
            }

            .ceo-content > div:last-child > p:nth-of-type(1) {
                font-size: 1rem !important;
                margin-bottom: 1rem !important;
            }

            .ceo-content > div:last-child > p:last-child {
                font-size: 0.85rem !important;
                line-height: 1.6 !important;
            }

            /* Steps Section - Even More Compact */
            .steps-container {
                gap: 0.65rem !important;
            }

            .step {
                padding: 0.875rem !important;
                border-radius: 12px !important;
                gap: 0.75rem !important;
            }

            .step-number {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                font-size: 0.9rem !important;
            }

            .step-content h3 {
                font-size: 0.9rem !important;
            }

            .step-content p {
                font-size: 0.8rem !important;
                line-height: 1.45 !important;
            }

            /* Section Dividers - Thinner on Mobile */
            .section-divider {
                padding: 0.5rem 0 !important;
            }

            .divider-line-thick {
                height: 2px !important;
            }

            .divider-line-thin {
                height: 1px !important;
                margin-top: 2px !important;
            }

            /* Decorative Elements - Hide on Mobile for Performance */
            section > div[aria-hidden="true"],
            [aria-hidden="true"][style*="radial-gradient"] {
                display: none !important;
            }

            /* =================================================================
               ENHANCED MOBILE WIDGET STYLING - Proper Compact Mobile Look
               Optimized for all phone sizes from 320px to 480px
               ================================================================= */

            /* Trust Bar - 2x2 Grid Layout (No Scroll) */
            .landing-trust-bar {
                padding: 0.875rem 0.75rem !important;
                overflow: visible !important;
            }

            .trust-bar-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.5rem !important;
                padding: 0 !important;
                max-width: 100% !important;
                min-width: unset !important;
            }

            .trust-bar-grid > div {
                flex: unset !important;
                width: auto !important;
                padding: 0.6rem 0.5rem !important;
                border-radius: 10px !important;
                background: white !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05) !important;
                gap: 0.35rem !important;
                flex-direction: column !important;
                text-align: center !important;
                align-items: center !important;
            }

            .trust-bar-grid > div > div:first-child {
                width: 28px !important;
                height: 28px !important;
                border-radius: 7px !important;
                margin: 0 auto !important;
            }

            .trust-bar-grid > div > div:first-child i {
                font-size: 0.8rem !important;
            }

            .trust-bar-grid > div > div:last-child > div:first-child {
                font-size: 0.65rem !important;
                font-weight: 700 !important;
                line-height: 1.15 !important;
                white-space: nowrap !important;
            }

            .trust-bar-grid > div > div:last-child > div:last-child {
                font-size: 0.55rem !important;
                line-height: 1.15 !important;
                white-space: nowrap !important;
            }

            /* Stats Bar - 2x2 Grid Layout (No Scroll) */
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.5rem !important;
                padding: 0 0.5rem !important;
                overflow: visible !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div {
                flex: unset !important;
                min-width: unset !important;
                width: auto !important;
                padding: 0.65rem 0.5rem !important;
                border-radius: 10px !important;
                gap: 0.4rem !important;
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                border-radius: 9px !important;
                margin: 0 auto !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child i {
                font-size: 1rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child {
                text-align: center !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:first-child {
                font-size: 1rem !important;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:last-child {
                font-size: 0.6rem !important;
            }

            /* Review Cards - Fixed Badge Layout */
            .reviews-grid {
                grid-template-columns: 1fr !important;
                gap: 0.875rem !important;
                padding: 0 0.25rem !important;
            }

            .review-card {
                border-radius: 14px !important;
                overflow: hidden !important;
            }

            .review-card > div:first-child {
                padding: 0.875rem 1rem !important;
            }

            .review-card > div:first-child > div {
                gap: 0.625rem !important;
                flex-wrap: nowrap !important;
            }

            .review-card > div:first-child > div > div:first-child {
                width: 44px !important;
                min-width: 44px !important;
                height: 44px !important;
                font-size: 1rem !important;
                flex-shrink: 0 !important;
            }

            .review-card > div:first-child > div > div:nth-child(2) {
                flex: 1 !important;
                min-width: 0 !important;
            }

            .review-card > div:first-child > div > div:nth-child(2) h4 {
                font-size: 0.9rem !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .review-card > div:first-child > div > div:nth-child(2) p {
                font-size: 0.7rem !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .review-card > div:first-child > div > div:last-child {
                padding: 0.2rem 0.35rem !important;
                flex-shrink: 0 !important;
            }

            .review-card > div:first-child > div > div:last-child span {
                font-size: 0.6rem !important;
                letter-spacing: 0 !important;
            }

            .review-card > div:last-child {
                padding: 1rem !important;
            }

            /* Fix badge row - stack vertically on mobile */
            .review-card > div:last-child > div:first-child {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap !important;
                gap: 0.35rem !important;
                margin-bottom: 0.75rem !important;
            }

            .review-card > div:last-child > div:first-child > span {
                font-size: 0.6rem !important;
                padding: 0.25rem 0.5rem !important;
                white-space: nowrap !important;
            }

            /* Quote text - ensure no overlap */
            .review-card > div:last-child blockquote {
                font-size: 0.8rem !important;
                line-height: 1.55 !important;
                padding-left: 0.625rem !important;
                margin-bottom: 0.75rem !important;
                margin-top: 0 !important;
                border-left-width: 2px !important;
                clear: both !important;
            }

            .review-card > div:last-child > div:last-child {
                padding-top: 0.625rem !important;
            }

            .review-card > div:last-child > div:last-child > div > div:first-child {
                font-size: 0.8rem !important;
            }

            .review-card > div:last-child > div:last-child > div > div:last-child {
                font-size: 0.65rem !important;
            }

            /* Services Grid - Compact 2-Column Layout */
            .services-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.75rem !important;
            }

            .service-item {
                height: 200px !important;
                border-radius: 12px !important;
            }

            .service-content {
                padding: 0.875rem !important;
            }

            .service-item h4 {
                font-size: 0.85rem !important;
                margin-bottom: 0.25rem !important;
                line-height: 1.2 !important;
            }

            .service-description {
                font-size: 0.65rem !important;
                line-height: 1.35 !important;
                -webkit-line-clamp: 2 !important;
                margin-bottom: 0.5rem !important;
            }

            .book-now-btn {
                padding: 0.5rem 0.875rem !important;
                font-size: 0.7rem !important;
                border-radius: 6px !important;
            }

            /* Location Cards - Compact 2-Column Grid */
            .location-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.625rem !important;
            }

            .location-card {
                min-height: 160px !important;
                padding: 0.875rem !important;
                border-radius: 10px !important;
            }

            .location-card-icon {
                width: 32px !important;
                height: 32px !important;
                font-size: 0.9rem !important;
                margin-bottom: 0.5rem !important;
                border-radius: 8px !important;
            }

            .location-card h4 {
                font-size: 0.85rem !important;
                margin-bottom: 0.35rem !important;
                line-height: 1.2 !important;
            }

            .location-card p {
                font-size: 0.65rem !important;
                line-height: 1.4 !important;
                -webkit-line-clamp: 2 !important;
                margin-bottom: 0.5rem !important;
            }

            .location-card-link {
                padding: 0.35rem 0.75rem !important;
                font-size: 0.65rem !important;
                border-radius: 4px !important;
            }

            /* Location Join Partner Button - Compact */
            #locations .fade-in[style*="text-align: center"][style*="margin-top: 4rem"] {
                margin-top: 1.5rem !important;
            }

            #locations .fade-in[style*="text-align: center"] .btn-primary {
                padding: 0.75rem 1.25rem !important;
                font-size: 0.85rem !important;
                gap: 0.4rem !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
            }

            #locations .fade-in[style*="text-align: center"] .btn-primary i {
                font-size: 0.95rem !important;
            }

            #locations .fade-in[style*="text-align: center"] p {
                font-size: 0.75rem !important;
                margin-top: 0.75rem !important;
            }

            /* Steps Section - Compact Horizontal Layout */
            .steps-container {
                gap: 0.5rem !important;
            }

            .step {
                padding: 0.75rem !important;
                border-radius: 10px !important;
                gap: 0.5rem !important;
                border-width: 1.5px !important;
            }

            .step-number {
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
                font-size: 0.8rem !important;
            }

            .step-content h3 {
                font-size: 0.8rem !important;
                margin-bottom: 0.15rem !important;
            }

            .step-content h3 span {
                font-size: 0.8rem !important;
            }

            .step-content p {
                font-size: 0.7rem !important;
                line-height: 1.35 !important;
            }

            /* About Section - Compact Layout */
            #about-section {
                padding: 1.5rem 0.75rem !important;
            }

            #about-section > div {
                gap: 1rem !important;
            }

            #about-section video {
                height: 220px !important;
            }

            #about-section h2 {
                font-size: 1.25rem !important;
                line-height: 1.2 !important;
            }

            #about-section > div > div:last-child > p {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
                margin-bottom: 1rem !important;
            }

            /* About Feature Cards - Compact Horizontal */
            .about-features-grid {
                gap: 0.5rem !important;
            }

            .about-feature-card {
                padding: 0.75rem !important;
                border-radius: 10px !important;
                gap: 0.625rem !important;
            }

            .about-feature-icon {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
            }

            .about-feature-icon i {
                font-size: 1rem !important;
            }

            .about-feature-content h3 {
                font-size: 0.8rem !important;
                margin-bottom: 0.15rem !important;
            }

            .about-feature-content p {
                font-size: 0.65rem !important;
                line-height: 1.35 !important;
            }

            /* CEO Section - Compact */
            .ceo-section {
                padding: 1.5rem 0.75rem !important;
            }

            .ceo-section .section-header h2 {
                font-size: 1.25rem !important;
            }

            .ceo-content {
                padding: 1rem !important;
                border-radius: 12px !important;
                gap: 1rem !important;
            }

            .ceo-content img {
                max-height: 200px !important;
                border-radius: 10px !important;
            }

            .ceo-content > div:last-child h2 {
                font-size: 1.1rem !important;
            }

            .ceo-content > div:last-child > p:last-child {
                font-size: 0.75rem !important;
                line-height: 1.5 !important;
            }

            /* Features Grid - Single Column Compact */
            .features-grid {
                gap: 0.625rem !important;
            }

            .feature-card {
                padding: 1rem !important;
                border-radius: 12px !important;
            }

            .feature-icon {
                font-size: 1.75rem !important;
                margin-bottom: 0.5rem !important;
            }

            .feature-card h3 {
                font-size: 0.95rem !important;
                margin-bottom: 0.35rem !important;
            }

            .feature-card p {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
            }

            /* Section Headers - Compact */
            .section-header {
                margin-bottom: 1.25rem !important;
            }

            .section-header h2 {
                font-size: 1.35rem !important;
                margin-bottom: 0.35rem !important;
            }

            .section-header p {
                font-size: 0.85rem !important;
                line-height: 1.45 !important;
            }

            /* Section Padding - Compact */
            section {
                padding: 1.75rem 0.75rem !important;
            }

            /* Reviews Section Badge */
            .section-header > div[style*="inline-flex"] {
                padding: 0.35rem 0.75rem !important;
                margin-bottom: 0.75rem !important;
            }

            .section-header > div[style*="inline-flex"] i {
                font-size: 0.8rem !important;
            }

            .section-header > div[style*="inline-flex"] span {
                font-size: 0.65rem !important;
            }

            /* =================================================================
               ENHANCED MOBILE WIDGET STYLING - Proper Proportions
               Modern compact design for all phone sizes
               ================================================================= */
            
            /* ===== TRUST BAR - Compact 2x2 Grid ===== */
            .landing-trust-bar {
                padding: 1rem 0.75rem !important;
            }
            
            .trust-bar-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
                max-width: 100% !important;
            }
            
            .trust-bar-grid > div {
                padding: 0.625rem 0.5rem !important;
                background: white !important;
                border-radius: 10px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
                gap: 0.5rem !important;
            }
            
            .trust-bar-grid > div > div:first-child {
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
                border-radius: 8px !important;
            }
            
            .trust-bar-grid > div > div:first-child i {
                font-size: 0.9rem !important;
            }
            
            .trust-bar-grid > div > div:last-child > div:first-child {
                font-size: 0.7rem !important;
                font-weight: 700 !important;
                line-height: 1.2 !important;
            }
            
            .trust-bar-grid > div > div:last-child > div:last-child {
                font-size: 0.6rem !important;
                line-height: 1.2 !important;
            }
            
            /* ===== STATS BAR - Compact Horizontal Scroll or 2x2 ===== */
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div {
                flex: none !important;
                min-width: unset !important;
                padding: 0.625rem 0.5rem !important;
                gap: 0.5rem !important;
                border-radius: 10px !important;
            }
            
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                border-radius: 10px !important;
            }
            
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child i {
                font-size: 1rem !important;
            }
            
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:first-child {
                font-size: 1rem !important;
                font-weight: 800 !important;
            }
            
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:last-child {
                font-size: 0.6rem !important;
                line-height: 1.2 !important;
            }
            
            /* ===== FEATURE CARDS - Compact Grid ===== */
            .features-grid {
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
            }
            
            .feature-card {
                padding: 1rem !important;
                border-radius: 12px !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: flex-start !important;
                gap: 0.75rem !important;
                text-align: left !important;
            }
            
            .feature-icon {
                font-size: 1.5rem !important;
                margin-bottom: 0 !important;
                flex-shrink: 0 !important;
                width: 44px !important;
                height: 44px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%) !important;
                border-radius: 10px !important;
            }
            
            .feature-card h3 {
                font-size: 0.95rem !important;
                margin-bottom: 0.25rem !important;
            }
            
            .feature-card p {
                font-size: 0.8rem !important;
                line-height: 1.45 !important;
                margin: 0 !important;
            }
            
            /* ===== SERVICES GRID - Compact Cards ===== */
            .services-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.625rem !important;
            }
            
            .service-item {
                height: 160px !important;
                border-radius: 12px !important;
            }
            
            .service-content {
                padding: 0.75rem !important;
            }
            
            .service-content h4 {
                font-size: 0.85rem !important;
                margin-bottom: 0.25rem !important;
                line-height: 1.2 !important;
            }
            
            .service-description {
                font-size: 0.65rem !important;
                line-height: 1.35 !important;
                -webkit-line-clamp: 2 !important;
                margin-bottom: 0.5rem !important;
            }
            
            .book-now-btn {
                padding: 0.4rem 0.75rem !important;
                font-size: 0.7rem !important;
                border-radius: 6px !important;
            }
            
            /* ===== LOCATION CARDS - Compact 2x2 Grid ===== */
            .location-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
            }
            
            .location-card {
                min-height: 140px !important;
                padding: 0.75rem !important;
                border-radius: 10px !important;
            }
            
            .location-card-icon {
                width: 32px !important;
                height: 32px !important;
                font-size: 0.9rem !important;
                margin-bottom: 0.5rem !important;
                border-radius: 8px !important;
            }
            
            .location-card h4 {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
                line-height: 1.2 !important;
            }
            
            .location-card p {
                font-size: 0.65rem !important;
                line-height: 1.35 !important;
                -webkit-line-clamp: 2 !important;
                margin-bottom: 0.5rem !important;
            }
            
            .location-card-link {
                padding: 0.35rem 0.6rem !important;
                font-size: 0.65rem !important;
            }
            
            /* ===== STEPS - Compact Horizontal Cards ===== */
            .steps-container {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.5rem !important;
            }
            
            .step {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                padding: 0.75rem !important;
                gap: 0.625rem !important;
                border-radius: 10px !important;
                text-align: left !important;
            }
            
            .step-number {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                font-size: 0.9rem !important;
                margin: 0 !important;
            }
            
            .step-content {
                flex: 1 !important;
            }
            
            .step-content h3 {
                font-size: 0.85rem !important;
                margin-bottom: 0.15rem !important;
                line-height: 1.25 !important;
            }
            
            .step-content h3 span {
                font-size: 0.85rem !important;
            }
            
            .step-content p {
                font-size: 0.7rem !important;
                line-height: 1.4 !important;
                margin: 0 !important;
            }
            
            /* ===== REVIEW CARDS - Compact Mobile Design ===== */
            .reviews-grid {
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
            }
            
            .review-card {
                border-radius: 14px !important;
                overflow: hidden !important;
            }
            
            /* Review card header */
            .review-card > div:first-child {
                padding: 0.875rem 1rem !important;
            }
            
            .review-card > div:first-child > div > div:first-child {
                width: 44px !important;
                min-width: 44px !important;
                height: 44px !important;
                font-size: 1rem !important;
            }
            
            .review-card > div:first-child > div > div:nth-child(2) h4 {
                font-size: 0.9rem !important;
            }
            
            .review-card > div:first-child > div > div:nth-child(2) p {
                font-size: 0.7rem !important;
            }
            
            /* Stars badge */
            .review-card > div:first-child > div:last-child {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
            
            /* Review card body */
            .review-card > div:last-child {
                padding: 1rem !important;
            }
            
            .review-card > div:last-child > div:first-child span {
                font-size: 0.6rem !important;
                padding: 0.25rem 0.5rem !important;
            }
            
            .review-card > div:last-child blockquote,
            .review-card > div:last-child > p {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
            }
            
            /* ===== ABOUT SECTION - Compact Layout ===== */
            #about-section {
                padding: 1.5rem 0.75rem !important;
            }
            
            #about-section > div {
                gap: 1.25rem !important;
            }
            
            #about-section video {
                height: 240px !important;
            }
            
            #about-section h2 {
                font-size: 1.35rem !important;
            }
            
            #about-section > div > div:last-child > p {
                font-size: 0.85rem !important;
                line-height: 1.55 !important;
            }
            
            .about-features-grid {
                gap: 0.5rem !important;
            }
            
            .about-feature-card {
                padding: 0.75rem !important;
                border-radius: 10px !important;
                gap: 0.625rem !important;
            }
            
            .about-feature-icon {
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
                border-radius: 10px !important;
            }
            
            .about-feature-icon i {
                font-size: 1.1rem !important;
            }
            
            .about-feature-content h3 {
                font-size: 0.85rem !important;
                margin-bottom: 0.15rem !important;
            }
            
            .about-feature-content p {
                font-size: 0.7rem !important;
                line-height: 1.4 !important;
            }
            
            /* ===== CEO SECTION - Compact ===== */
            .ceo-section {
                padding: 1.5rem 0.75rem !important;
            }
            
            .ceo-section .section-header h2 {
                font-size: 1.35rem !important;
            }
            
            .ceo-content {
                padding: 1rem !important;
                border-radius: 14px !important;
                gap: 1rem !important;
            }
            
            .ceo-content img {
                max-height: 180px !important;
                border-radius: 10px !important;
            }
            
            .ceo-content > div:last-child h2 {
                font-size: 1.25rem !important;
            }
            
            .ceo-content > div:last-child > p:nth-of-type(1) {
                font-size: 0.9rem !important;
            }
            
            .ceo-content > div:last-child > p:last-child {
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
            }
            
            /* ===== SECTION DIVIDERS - Minimal ===== */
            .section-divider {
                padding: 0.5rem 0 !important;
            }
            
            .section-divider .divider-line-thick,
            .section-divider .divider-line-thin {
                max-width: 90% !important;
            }
            
            /* ===== BUTTONS - Touch Friendly ===== */
            .btn-primary,
            .btn-secondary {
                min-height: 44px !important;
                padding: 0.625rem 1.25rem !important;
                font-size: 0.9rem !important;
                border-radius: 10px !important;
            }
            
            /* ===== DECORATIVE ELEMENTS - Hidden on Mobile ===== */
            section > div[aria-hidden="true"],
            [aria-hidden="true"][style*="radial-gradient"],
            .decorative-circle,
            .decorative-blob {
                display: none !important;
            }

        }

        /* Small tablets and large phones */
        @media (min-width: 481px) and (max-width: 768px) {
            .hero {
                margin-top: 72px;
                padding: 3rem 1.5rem;
            }
            
            /* Show single background image on tablet */
            .hero-bg-slice {
                transform: none;
                margin: 0;
            }
            
            .hero-bg-slice:nth-child(2),
            .hero-bg-slice:nth-child(3) {
                display: none;
            }
            
            .hero-bg-slice:nth-child(1) {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
            
            /* Add overlay for readability on tablet */
            .hero::before {
                content: '' !important;
                display: block !important;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(30, 58, 138, 0.65) 0%, rgba(59, 130, 246, 0.45) 100%) !important;
                z-index: 1;
            }

            .hero-right {
                display: flex;
                gap: 1.75rem;
            }

            .hero-image-container {
                height: 280px !important;
                border-radius: 18px;
            }

            .hero-cover-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            .hero-social-container {
                flex-direction: row;
                justify-content: center;
            }

            .hero-social-text {
                font-size: 0.8rem !important;
            }

            .hero-social-icon {
                width: 40px !important;
                height: 40px !important;
                font-size: 1rem !important;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 2.5rem;
                padding: 2rem;
                background: rgba(255, 255, 255, 0.97);
                position: relative;
                z-index: 2;
                border-radius: 20px;
            }

            .hero-left {
                text-align: center;
            }
            
            .hero-right {
                order: -1;
            }

            .hero h1 {
                font-size: 2.75rem;
            }

            .hero .tagline {
                font-size: 1.5rem;
            }

            .hero p {
                font-size: 1.05rem;
            }

            .hero-buttons {
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn-primary, .btn-secondary {
                flex: 1;
                min-width: 200px;
                padding: 1rem 2rem;
            }

            .hero-trust-badges {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            section {
                padding: 3.5rem 1.5rem;
            }

            .section-header h2 {
                font-size: 2.25rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .steps-container::before {
                display: none;
            }

            .step {
                flex-direction: column;
                text-align: center;
            }

            .step-number {
                margin: 0 auto 1rem;
            }

            .step:nth-child(2)::after,
            .step:nth-child(4)::after {
                display: none;
            }

            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .location-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.75rem;
            }

            .location-card {
                padding: 2rem;
                border-radius: 0;
                min-height: 380px;
            }

            .location-card-icon {
                width: 55px;
                height: 55px;
                font-size: 1.65rem;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .requirement-item {
                padding: 2.25rem;
            }

            .requirement-icon-wrapper {
                width: 65px;
                height: 65px;
                font-size: 1.85rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            /* =================================================================
               TABLET WIDGET STYLING (481-768px) - Better Proportions
               ================================================================= */

            /* Trust Bar - 2x2 Grid on Tablets */
            .landing-trust-bar {
                padding: 1.25rem 1rem !important;
            }

            .trust-bar-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 1rem !important;
            }

            .trust-bar-grid > div {
                background: white !important;
                padding: 1rem !important;
                border-radius: 14px !important;
                box-shadow: 0 3px 12px rgba(0,0,0,0.06) !important;
            }

            .trust-bar-grid > div > div:first-child {
                width: 40px !important;
                height: 40px !important;
            }

            .trust-bar-grid > div > div:last-child > div:first-child {
                font-size: 0.85rem !important;
            }

            .trust-bar-grid > div > div:last-child > div:last-child {
                font-size: 0.75rem !important;
            }

            /* Stats Bar - Horizontal Scroll on Tablets */
            section.section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] {
                gap: 1rem !important;
            }

            section.section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div {
                padding: 1rem 1.5rem !important;
            }

            section.section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 48px !important;
                height: 48px !important;
            }

            /* Review Cards - 1 Column on Tablets for Better Readability */
            .reviews-grid {
                grid-template-columns: 1fr !important;
                max-width: 600px !important;
                margin: 0 auto !important;
            }

            .review-card {
                border-radius: 20px !important;
            }

            /* Services Grid - 2x2 on Tablets */
            .services-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1.5rem !important;
            }

            .service-item {
                height: 340px !important;
            }

            /* Location Cards - 2 Column */
            .location-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .location-card {
                min-height: 300px !important;
            }

            /* About Section */
            #about-section video {
                height: 380px !important;
            }

            .about-features-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .about-feature-card {
                flex-direction: row !important;
                align-items: center !important;
                padding: 1.25rem !important;
                gap: 1rem !important;
            }

            .about-feature-icon {
                width: 52px !important;
                height: 52px !important;
                min-width: 52px !important;
                margin-bottom: 0 !important;
            }

            .about-feature-content {
                text-align: left !important;
            }

            /* CEO Section */
            .ceo-content {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
                padding: 2rem !important;
            }

            .ceo-content img {
                max-height: 350px !important;
            }

            /* About section tablet */
            #about-section > div {
                grid-template-columns: 1fr !important;
                gap: 3rem !important;
            }
        }

        /* =============================================================
           SAFE AREA INSETS - iPhone X+ Notch Support
           ============================================================= */
        @supports (padding: env(safe-area-inset-top)) {
            .nav-container,
            .navbar {
                padding-top: env(safe-area-inset-top);
            }
            
            footer,
            .footer {
                padding-bottom: env(safe-area-inset-bottom);
            }
            
            body {
                padding-left: env(safe-area-inset-left);
                padding-right: env(safe-area-inset-right);
            }
        }

        /* =============================================================
           REDUCED MOTION - Battery & Accessibility
           ============================================================= */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            
            .about-feature-card:hover,
            .feature-card:hover,
            .step:hover,
            .hero-image-container:hover {
                transform: none !important;
            }
        }

        /* Standard mobile breakpoint - 768px (tablets & large phones) */
        @media (max-width: 768px) {
            /* Prevent horizontal scroll */
            html, body {
                overflow-x: hidden;
                max-width: 100vw;
            }

            section {
                padding: 2.5rem 1.25rem;
            }

            .section-header h2 {
                font-size: clamp(1.75rem, 5vw, 2rem);
            }

            .section-header p {
                font-size: 1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .feature-card {
                padding: 2rem;
            }

            .feature-card h3 {
                font-size: 1.25rem;
            }

            .steps-container {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 1rem;
            }

            .step::after {
                display: none;
            }

            .step {
                padding: 1.25rem;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border: 2px solid #e2e8f0;
                box-shadow: 0 8px 24px rgba(30, 58, 138, 0.1);
                text-align: left;
                display: flex;
                flex-direction: row;
                align-items: flex-start;
                gap: 1rem;
            }

            .step:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 32px rgba(59, 130, 246, 0.15);
                border-color: #3b82f6;
            }

            .step-number {
                width: 50px;
                height: 50px;
                min-width: 50px;
                font-size: 1.25rem;
                margin: 0;
                box-shadow: 0 6px 20px rgba(249, 115, 22, 0.35);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                color: white;
                font-weight: 800;
            }

            .step-content h3 {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
                font-weight: 700;
            }

            .step-content p {
                font-size: 0.9rem;
                line-height: 1.5;
                color: #475569;
                display: block;
            }

            #how-it-works .section-header h2 {
                font-size: 1.75rem;
            }
            
            #how-it-works .section-header p {
                font-size: 0.9rem;
            }

            .services-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }

            .service-item {
                height: 320px;
                box-shadow: 0 8px 24px rgba(30, 58, 138, 0.15);
            }

            .service-item:hover {
                transform: translateY(-6px);
                box-shadow: 0 16px 40px rgba(59, 130, 246, 0.25);
            }

            .service-content {
                padding: 1.5rem;
            }

            .service-content h4 {
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
                font-weight: 700;
            }

            .service-description {
                font-size: 0.875rem;
                line-height: 1.6;
                -webkit-line-clamp: 3;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            /* ===== REVIEWS SECTION - Tablet Responsive ===== */
            .rating-highlights-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
                margin-bottom: 2rem !important;
            }
            
            .rating-highlights-grid > div {
                padding: 1.25rem !important;
            }
            
            .reviews-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .reviews-grid .review-card,
            .reviews-grid > div {
                padding: 1.5rem !important;
                max-width: 100% !important;
            }
            
            .reviews-grid > div > p {
                font-size: 0.95rem !important;
                line-height: 1.65 !important;
            }
            
            .across-nyc-grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
            
            div[style*="backdrop-filter: blur"] {
                padding: 1.5rem !important;
            }

            /* CEO section mobile */
            .ceo-section {
                padding: 3rem 1rem !important;
            }

            .ceo-section .section-header h2 {
                font-size: 2rem !important;
            }

            .ceo-section .section-header p {
                font-size: 1rem !important;
                margin-bottom: 2rem !important;
            }

            .ceo-content {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
                padding: 2rem 1.5rem !important;
            }

            .ceo-content img {
                max-width: 100% !important;
                width: 100% !important;
            }

            .ceo-content h2 {
                font-size: 2rem !important;
            }

            .ceo-content h3 {
                font-size: 1.25rem !important;
            }

            .ceo-content > div:last-child > div:last-child {
                flex-direction: column !important;
            }

            .ceo-content > div:last-child > div:last-child > div {
                min-width: 100% !important;
            }

            .stat-item h3 {
                font-size: 2rem;
            }
        }

        /* Tablets (iPad, etc.) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .nav-container {
                padding: 0 2rem;
            }

            .hero {
                padding: 5rem 2rem;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 3rem;
                padding: 3rem;
            }

            .hero h1 {
                font-size: 3.5rem;
            }

            .hero .tagline {
                font-size: 1.75rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-right {
                display: flex;
                gap: 2rem;
            }

            .hero-image-container {
                height: 360px !important;
                border-radius: 20px;
            }

            .hero-cover-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            section {
                padding: 4rem 2rem;
            }

            .section-header h2 {
                font-size: 2.5rem;
            }

            .section-header p {
                font-size: 1.1rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .feature-card {
                padding: 2rem;
            }

            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .step:nth-child(2)::after,
            .step:nth-child(4)::after {
                display: none;
            }

            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            /* About features cards tablet */
            .about-features-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1.5rem !important;
            }

            .about-feature-card {
                padding: 2rem 1.5rem !important;
            }

            .about-feature-icon {
                width: 70px !important;
                height: 70px !important;
            }

            /* About section tablet */
            #about-section > div {
                grid-template-columns: 1fr 1fr !important;
                gap: 3rem !important;
            }

            #about-section img {
                height: 400px !important;
            }

            /* Stats section tablet */
            #about > div > div > div {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            /* Newsletter form tablet */
            .newsletter-input {
                flex-direction: row;
            }
        }

        /* Small laptops and large tablets */
        @media (min-width: 1025px) and (max-width: 1280px) {
            .hero-content {
                gap: 3.5rem;
            }

            .hero h1 {
                font-size: 4rem;
            }

            .features-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .steps-container {
                grid-template-columns: repeat(4, 1fr);
            }

            .services-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .fade-in, .scale-in {
                transition: none;
            }
        }

        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        #loading-screen.fade-out {
            opacity: 0;
            transform: scale(1.05);
            pointer-events: none;
        }

        .loading-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .loading-logo-wrapper {
            position: relative;
            width: 280px;
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid rgba(11, 79, 162, 0.1);
        }

        .loading-ring-animated {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #0B4FA2;
            border-right-color: #f97316;
            animation: spin-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .loading-ring-glow {
            position: absolute;
            width: 110%;
            height: 110%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(11, 79, 162, 0.08) 0%, transparent 70%);
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .loading-logo {
            position: relative;
            z-index: 2;
            animation: logo-float 2.5s ease-in-out infinite;
        }

        .loading-logo img {
            height: 200px;
            width: auto;
            box-shadow: none;
            filter: drop-shadow(0 10px 30px rgba(11, 79, 162, 0.15));
            transition: transform 0.3s ease;
        }

        .loading-text {
            font-family: 'Sora', sans-serif;
            font-size: 1.1rem;
            font-weight: 500;
            color: #0B4FA2;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .loading-dots {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .loading-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0B4FA2, #f97316);
            animation: dot-bounce 1.4s ease-in-out infinite;
        }

        .loading-dot:nth-child(1) { animation-delay: 0s; }
        .loading-dot:nth-child(2) { animation-delay: 0.2s; }
        .loading-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes spin-ring {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        @keyframes logo-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes dot-bounce {
            0%, 80%, 100% { 
                transform: scale(0.6);
                opacity: 0.5;
            }
            40% { 
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Mobile responsive loading screen */
        @media (max-width: 768px) {
            .loading-logo-wrapper {
                width: 220px;
                height: 220px;
            }
            .loading-logo img {
                height: 150px;
            }
            .loading-text {
                font-size: 1rem;
            }
        }

        /* Note: pulse keyframe is defined in global animations.css */

        /* Accessibility - Skip Link */
        .skip-link {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e40af;
            color: white;
            padding: 1rem 2rem;
            border-radius: 0 0 8px 8px;
            z-index: 10001;
            font-weight: 600;
            text-decoration: none;
            transition: top 0.3s;
        }

        .skip-link:focus {
            top: 0;
        }

        /* Focus Visible Styles for Accessibility */
        .nav-links a:focus-visible,
        .btn-primary:focus-visible,
        .btn-secondary:focus-visible,
        .cta-btn:focus-visible,
        .book-now-btn:focus-visible,
        .social-icon:focus-visible,
        .newsletter-btn:focus-visible {
            outline: 3px solid #f97316;
            outline-offset: 2px;
        }

        /* Improve color contrast for body text */
        .feature-card p,
        .step p,
        .section-header p {
            color: #525252;
        }

        /* =============================================================
           DESKTOP STYLES - Ensure desktop view is preserved (1281px+)
           These styles reset any mobile overrides for large screens
           ============================================================= */
        @media (min-width: 1281px) {
            /* Hero section - restore desktop layout, container near top */
            .hero {
                margin-top: 72px; /* Match nav height to remove white gap */
                padding: 3rem 2rem 4rem;
                min-height: 100vh;
                align-items: flex-start;
            }

            .hero-content {
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                padding: 3rem;
                max-width: 1400px;
            }

            .hero h1 {
                font-size: 5rem;
                text-align: left;
            }

            .hero .tagline {
                font-size: 1.875rem;
            }

            .hero p {
                font-size: 1.25rem;
            }

            .hero-buttons {
                flex-direction: row;
                gap: 1rem;
            }

            .btn-primary, .btn-secondary {
                width: auto;
                padding: 1rem 2.5rem;
                font-size: 1.1rem;
            }

            .hero-trust-badges {
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
            }

            /* Trust Bar - Desktop layout - horizontal row */
            .landing-trust-bar {
                padding: 2rem 2rem;
                overflow: visible;
            }

            .trust-bar-grid {
                display: flex !important;
                flex-wrap: wrap;
                grid-template-columns: unset;
                gap: 2rem 3.5rem;
                justify-content: center;
                max-width: 1200px;
                margin: 0 auto;
            }

            .trust-bar-grid > div {
                width: auto;
                flex: 0 0 auto;
                flex-direction: row;
                padding: 0;
                background: transparent !important;
                box-shadow: none !important;
                border-radius: 0;
                gap: 0.75rem;
            }

            .trust-bar-grid > div > div:first-child {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                margin: 0;
            }

            .trust-bar-grid > div > div:first-child i {
                font-size: 1.25rem;
            }

            .trust-bar-grid > div > div:last-child {
                text-align: left;
            }

            .trust-bar-grid > div > div:last-child > div:first-child {
                font-size: 0.95rem;
                font-weight: 900;
            }

            .trust-bar-grid > div > div:last-child > div:last-child {
                font-size: 0.85rem;
            }

            /* Stats Bar - Desktop layout */
            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 2rem;
                overflow: visible;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div {
                flex: 0 0 auto;
                min-width: unset;
                padding: 1.25rem 2rem;
                gap: 1rem;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child {
                width: 56px;
                height: 56px;
                min-width: 56px;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:first-child i {
                font-size: 1.5rem;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:first-child {
                font-size: 1.5rem;
            }

            .section-light > .container > .fade-in[style*="display: flex"][style*="justify-content: center"] > div > div:last-child > div:last-child {
                font-size: 0.85rem;
            }

            /* Features - Desktop layout */
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
            }

            .feature-card {
                padding: 2rem;
            }

            .feature-icon {
                font-size: 3rem;
            }

            .feature-card h3 {
                font-size: 1.35rem;
            }

            .feature-card p {
                font-size: 1rem;
            }

            /* Services - Desktop layout */
            .services-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2rem;
            }

            .service-item {
                height: 360px;
                border-radius: 24px;
            }

            .service-content {
                padding: 2rem;
            }

            .service-item h4 {
                font-size: 1.5rem;
            }

            .service-description {
                font-size: 1rem;
            }

            .book-now-btn {
                padding: 1rem 2rem;
                font-size: 1rem;
            }

            /* Reviews - Desktop layout */
            .reviews-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.75rem;
                max-width: unset;
                margin: unset;
                padding: 0;
            }

            .review-card {
                border-radius: 24px;
            }

            .review-card > div:first-child {
                padding: 1.5rem 1.75rem;
            }

            .review-card > div:first-child > div > div:first-child {
                width: 64px;
                min-width: 64px;
                height: 64px;
                font-size: 1.35rem;
            }

            .review-card > div:first-child > div > div:nth-child(2) h4 {
                font-size: 1.15rem;
            }

            .review-card > div:last-child {
                padding: 1.75rem;
            }

            .review-card > div:last-child blockquote {
                font-size: 1rem;
                line-height: 1.75;
            }

            /* Location Cards - Desktop layout */
            .location-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 1.5rem;
            }

            .location-card {
                min-height: 320px;
                padding: 1.5rem;
                border-radius: 0;
            }

            .location-card-icon {
                width: 45px;
                height: 45px;
                font-size: 1.4rem;
            }

            .location-card h4 {
                font-size: 1.2rem;
            }

            .location-card p {
                font-size: 0.95rem;
            }

            /* Steps - Desktop layout */
            .steps-container {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
            }

            .step {
                padding: 1.5rem;
                border-radius: 20px;
                flex-direction: column;
                text-align: center;
            }

            .step-number {
                width: 56px;
                height: 56px;
                min-width: 56px;
                font-size: 1.25rem;
                margin: 0 auto 1rem;
            }

            .step-content h3 {
                font-size: 1.2rem;
            }

            .step-content h3 span {
                font-size: 1.2rem;
            }

            .step-content p {
                font-size: 0.95rem;
            }

            /* About Section - Desktop layout */
            #about-section {
                padding: 5rem 2rem;
            }

            #about-section video {
                height: 520px;
            }

            #about-section h2 {
                font-size: 2.25rem;
            }

            .about-features-grid {
                gap: 1.5rem;
            }

            .about-feature-card {
                padding: 1.5rem;
                border-radius: 20px;
                flex-direction: row;
            }

            .about-feature-icon {
                width: 70px;
                height: 70px;
                min-width: 70px;
            }

            .about-feature-icon i {
                font-size: 1.75rem;
            }

            .about-feature-content h3 {
                font-size: 1.2rem;
            }

            .about-feature-content p {
                font-size: 0.95rem;
            }

            /* CEO Section - Desktop */
            .ceo-section {
                padding: 5rem 2rem;
            }

            .ceo-content {
                padding: 2.5rem;
                border-radius: 24px;
            }

            .ceo-content img {
                max-height: 400px;
            }

            .ceo-content > div:last-child h2 {
                font-size: 2rem;
            }

            .ceo-content > div:last-child > p:last-child {
                font-size: 1.1rem;
            }

            /* Section headers - Desktop */
            .section-header {
                margin-bottom: 3rem;
            }

            .section-header h2 {
                font-size: 3rem;
            }

            .section-header p {
                font-size: 1.25rem;
            }

            section {
                padding: 5rem 2rem;
            }

            /* Decorative Elements - Show on Desktop */
            section > div[aria-hidden="true"],
            [aria-hidden="true"][style*="radial-gradient"] {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    @include('partials.accessibility')
    <div id="loading-screen">
        <div class="loading-container">
            <div class="loading-logo-wrapper">
                <div class="loading-ring-glow"></div>
                <div class="loading-ring"></div>
                <div class="loading-ring-animated"></div>
                <div class="loading-logo">
                    <img src="{{ asset('logo.png') }}" alt="CAS Private Care LLC Logo" width="200" height="200" fetchpriority="high">
                </div>
            </div>
            <div class="loading-text">Loading Care Services</div>
            <div class="loading-dots">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>
    </div>
    @include('partials.navigation')

    <main id="main-content">
    <header class="hero">
        <div class="hero-bg-images">
            <div class="hero-bg-slice"></div>
            <div class="hero-bg-slice"></div>
            <div class="hero-bg-slice"></div>
        </div>
        
        <!-- Particle System -->
        <div id="particles-container"></div>
        
        <!-- Smoke Effect -->
        <div id="smoke-container">
            <div class="smoke smoke-1"></div>
            <div class="smoke smoke-2"></div>
            <div class="smoke smoke-3"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-left">
                <h1 style="text-shadow: 2px 2px 4px rgba(255, 255, 255, 1), -1px -1px 0 rgba(255, 255, 255, 1), 1px -1px 0 rgba(255, 255, 255, 1), -1px 1px 0 rgba(255, 255, 255, 1), 1px 1px 0 rgba(255, 255, 255, 1);"><span style="color: #f97316;">CAS Private Care</span> <span style="color: #3b82f6;">LLC</span></h1>
                <p class="tagline">Comfort and Support</p>
                
                <div class="hero-service-toggle" role="tablist" aria-label="Service type">
                    <div id="slider-bg" class="hero-toggle-slider" aria-hidden="true"></div>
                    <button type="button" role="tab" id="btn-caregiver" aria-selected="true" aria-controls="hero-description" onclick="switchService('caregiver')" class="hero-toggle-btn">Caregiver</button>
                    <button type="button" role="tab" id="btn-housekeeping" aria-selected="false" aria-controls="hero-description" onclick="switchService('housekeeping')" class="hero-toggle-btn">Housekeeping</button>
                </div>
                
                <p id="hero-description" style="transition: opacity 0.5s ease;">A modern and trustworthy platform connecting families with verified caregivers and housekeepers. Background-checked professionals ready to support your family across all NYC boroughs.</p>
                
                <div class="hero-buttons">
                    <a href="{{ url('/register') }}" class="btn-secondary" id="find-btn" style="transition: opacity 0.5s ease;">Find a Caregiver</a>
                    <a href="{{ url('/register') }}" class="btn-primary">Become a Partner</a>
                </div>
                <p class="hero-call-cta">
                    <span class="hero-call-label">Call Us Today!</span>
                    <a href="tel:{{ config('app.phone', '16462828282') }}" class="hero-call-number" aria-label="Call CAS Private Care"><i class="bi bi-telephone-fill" aria-hidden="true"></i> (646) 282-8282</a>
                </p>
            </div>
            <div class="hero-right">
                <div class="hero-image-container">
                    <img src="{{ asset('cover.jpg') }}" alt="CAS Private Care LLC Cover" class="hero-cover-image" fetchpriority="high" decoding="async">
                </div>
                <div class="hero-social-container">
                    <p class="hero-social-text">CONNECT WITH US:</p>
                    <div class="hero-social-icons">
                        <a href="https://www.facebook.com/profile.php?id=61584831099232" target="_blank" rel="noopener noreferrer" class="hero-social-icon" aria-label="Follow us on Facebook" onclick="window.open(this.href, '_blank'); return false;"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.linkedin.com/in/CASprivatecare" target="_blank" rel="noopener noreferrer" class="hero-social-icon" aria-label="Follow us on LinkedIn" onclick="window.open(this.href, '_blank'); return false;"><i class="bi bi-linkedin"></i></a>
                        <a href="https://www.instagram.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="hero-social-icon" aria-label="Follow us on Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- How It Works Micro-Strip -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 1.5rem 2rem; position: relative; overflow: hidden;">
        <div aria-hidden="true" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url(\"data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%20100%20100'%3E%3Ccircle%20cx%3D'20'%20cy%3D'20'%20r%3D'2'%20fill%3D'rgba(255%2C255%2C255%2C0.03)'%2F%3E%3Ccircle%20cx%3D'80'%20cy%3D'80'%20r%3D'2'%20fill%3D'rgba(255%2C255%2C255%2C0.03)'%2F%3E%3C%2Fsvg%3E\"); background-size: 60px 60px;"></div>
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 1rem 2.5rem; position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 0.85rem;">1</div>
                <span style="color: white; font-weight: 700; font-size: 0.95rem;">Browse Verified Partners</span>
            </div>
            <i class="bi bi-arrow-right" style="color: rgba(255, 255, 255, 0.4); font-size: 1.1rem;"></i>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 0.85rem;">2</div>
                <span style="color: white; font-weight: 700; font-size: 0.95rem;">Book Instantly</span>
            </div>
            <i class="bi bi-arrow-right" style="color: rgba(255, 255, 255, 0.4); font-size: 1.1rem;"></i>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 0.85rem;">3</div>
                <span style="color: white; font-weight: 700; font-size: 0.95rem;">Pay Securely</span>
            </div>
            <i class="bi bi-arrow-right" style="color: rgba(255, 255, 255, 0.4); font-size: 1.1rem;"></i>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 0.85rem;">4</div>
                <span style="color: white; font-weight: 700; font-size: 0.95rem;">Rate & Review</span>
            </div>
        </div>
    </div>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-light" style="padding: 6rem 2rem;" id="about-section" itemscope itemtype="https://schema.org/AboutPage">
        <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1.15fr 1fr; gap: 4rem; align-items: center; overflow: visible;">
            <div class="fade-in" style="position: relative;">
                <video id="about-section-video" autoplay loop muted playsinline preload="auto" style="width: 100%; height: 620px; object-fit: cover; box-shadow: 0 20px 60px rgba(59, 130, 246, 0.2);">
                    <source src="{{ asset('what.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="fade-in" itemprop="description">
                <h2 style="font-size: 3rem; font-weight: 700; margin-bottom: 1rem;"><span style="color: #f97316;">What</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">is CAS Private Care LLC?</span></h2>
                
                <!-- Trust Badge -->
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%); padding: 0.5rem 1.25rem; border-radius: 50px; margin-bottom: 1.5rem; border: 2px solid #93c5fd;">
                    <i class="bi bi-shield-check-fill" style="color: #3b82f6; font-size: 1.1rem;"></i>
                    <span style="font-weight: 700; color: #1e40af; font-size: 0.95rem;">Trusted by 2,500+ New York Families</span>
                </div>
                
                <p style="font-size: 1.1rem; color: #64748b; line-height: 1.8; margin-bottom: 2rem;">Your trusted platform connecting families with verified partners and contractors for quality care services. We make finding professional partners simple, safe, and reliable.</p>
                <div class="about-features-grid">
                    <div class="about-feature-card">
                        <div class="about-feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="about-feature-content">
                            <h3><span style="color: #f97316;">For</span> <span style="color: #1e40af;">Families</span></h3>
                            <p>Browse verified partners and contractors. Book instantly with secure payments.</p>
                        </div>
                    </div>
                    <div class="about-feature-card">
                        <div class="about-feature-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div class="about-feature-content">
                            <h3><span style="color: #f97316;">Flexible</span> <span style="color: #1e40af;">Scheduling</span></h3>
                            <p>Book on-demand or schedule recurring appointments with our intuitive system.</p>
                        </div>
                    </div>
                    <div class="about-feature-card">
                        <div class="about-feature-icon">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div class="about-feature-content">
                            <h3><span style="color: #f97316;">Secure</span> <span style="color: #1e40af;">Payments</span></h3>
                            <p>Safe, encrypted payment processing with multiple options for your convenience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-dark ceo-section" style="padding: 6rem 2rem;">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header fade-in" style="text-align: center; margin-bottom: 4rem;">
                <h2>
                    <span style="color: #f97316;">Meet</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Our Founder</span>
                </h2>
            </div>
            <div class="ceo-content">
                <div class="fade-in" style="text-align: center;">
                    <div class="ceo-image-wrap">
                        <img src="{{ asset('CEO.jpg') }}" alt="Charles Andrew Santiago - CEO and Founder of CAS Private Care LLC" loading="lazy" decoding="async">
                        <div class="ceo-badge" aria-hidden="true">
                            <i class="bi bi-award-fill" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
                <div class="fade-in">
                    <div class="ceo-company">
                        <h3>CAS PRIVATE CARE LLC</h3>
                        <p>Professional Caregiving Services</p>
                    </div>
                    <h2 class="ceo-name">
                        <span style="color: #f97316;">Charles Andrew</span> <span style="color: #1e40af;">Santiago</span>
                    </h2>
                    <p class="ceo-title">CEO / Founder</p>
                    <p class="ceo-bio">
                        With a vision to transform the caregiving industry, Charles Andrew Santiago is dedicated to building a trusted platform that connects families with exceptional contractors. His goal is to ensure every family receives quality, compassionate care while creating meaningful opportunities for professional contractors. Through CAS Private Care LLC, he strives to make professional caregiving services accessible, reliable, and safe for everyone, fostering stronger communities one connection at a time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-light" id="services" itemscope itemtype="https://schema.org/Service">
        <div class="container">
            <div class="section-header fade-in">
                <h2 itemprop="name"><span style="color: #f97316;">Our</span> Services</h2>
                <p>Comprehensive care solutions for every family need</p>
            </div>
            <div class="services-grid">
                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1581579438747-1dc8d17bbce4?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Elderly Care</h4>
                        <p class="service-description" itemprop="description">Compassionate and professional care for your loved ones. Our caregivers provide assistance with daily activities, meal preparation, and companionship.</p>
                        <div class="booking-btn-wrapper">
                            <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url" onclick="return handleBookingClick(event, '{{ url('/register') }}')">Book Now</a>
                            <span class="maintenance-dot" title="Booking temporarily disabled"></span>
                        </div>
                    </div>
                </article>


                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">House Helpers</h4>
                        <p class="service-description" itemprop="description">Reliable household assistance for a cleaner home. Professional helpers who manage cleaning, laundry, and household organization efficiently.</p>
                        <div class="booking-btn-wrapper">
                            <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url" onclick="return handleBookingClick(event, '{{ url('/register') }}')">Book Now</a>
                            <span class="maintenance-dot" title="Booking temporarily disabled"></span>
                        </div>
                    </div>
                </article>
                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Special Needs Care</h4>
                        <p class="service-description" itemprop="description">Specialized care for individuals with unique requirements. Trained professionals who provide personalized support with patience and expertise.</p>
                        <div class="booking-btn-wrapper">
                            <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url" onclick="return handleBookingClick(event, '{{ url('/register') }}')">Book Now</a>
                            <span class="maintenance-dot" title="Booking temporarily disabled"></span>
                        </div>
                    </div>
                </article>

                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Deep Cleaning</h4>
                        <p class="service-description" itemprop="description">Thorough housekeeping for kitchens, bathrooms, and high-touch areas. Perfect for move-in/move-out and seasonal refreshes.</p>
                        <div class="booking-btn-wrapper">
                            <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url" onclick="return handleBookingClick(event, '{{ url('/register') }}')">Book Now</a>
                            <span class="maintenance-dot" title="Booking temporarily disabled"></span>
                        </div>
                    </div>
                </article>


            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <!-- Location Coverage Section -->
    <section class="section-dark" id="locations">
        <div class="container">
            <div class="section-header fade-in">
                <h2><span style="color: #f97316;">Professional Partners</span> Available Throughout New York State</h2>
                <p>
                    Verified partners available across all of New York State. Find caregivers, housekeepers, and more in your area.
                </p>
            </div>

            <div class="location-grid">
                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <h4>Manhattan Partners</h4>
                    <p>Professional caregivers and housekeepers throughout Manhattan, from Upper East Side to Lower Manhattan. Available 24/7.</p>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1505843513577-22bb7d21e455?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h4>Brooklyn Partners</h4>
                    <p>Trusted caregivers and housekeepers serving all Brooklyn neighborhoods. From Park Slope to Brighton Beach.</p>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1500916434205-0c77489c6cf7?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <h4>Queens Partners</h4>
                    <p>Reliable caregivers and housekeepers across Queens, including Astoria, Flushing, and Jamaica.</p>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1514565131-fce0801e5785?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <h4>Bronx Partners</h4>
                    <p>Professional caregivers and housekeepers serving the Bronx communities. Specialized elderly care services available.</p>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://www.nyhabitat.com/blog/wp-content/uploads/2014/09/New-york-nyc-borough-staten-island-ferry-manhattan-skyline.jpg');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h4>Staten Island Partners</h4>
                    <p>Dedicated caregivers and housekeepers for Staten Island residents. Personalized connections for your family.</p>
                </div>
            </div>
            
            <!-- Central Join as Partner Button -->
            <div class="fade-in" style="text-align: center; margin-top: 4rem;">
                <a href="{{ url('/register?show_partner_types=true') }}" class="btn-primary" style="padding: 1.5rem 4rem; font-size: 1.3rem; display: inline-flex; align-items: center; gap: 1rem; box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);">
                    <i class="bi bi-person-plus-fill" style="font-size: 1.5rem;"></i>
                    Join as Partner in New York State
                    <i class="bi bi-arrow-right-circle-fill" style="font-size: 1.5rem;"></i>
                </a>
                <p style="margin-top: 1.5rem; color: #64748b; font-size: 1.1rem;">
                    Start earning today as a caregiver or housekeeper across all NYC boroughs
                </p>
            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-light" id="how-it-works" itemscope itemtype="https://schema.org/HowTo" style="padding: 5rem 2rem; position: relative; overflow: hidden;">
        <!-- Decorative background elements -->
        <div aria-hidden="true" style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
        <div aria-hidden="true" style="position: absolute; bottom: -100px; left: -100px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(249, 115, 22, 0.06) 0%, transparent 70%); border-radius: 50%;"></div>
        
        <div class="container" style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">
            <div class="section-header fade-in" style="text-align: center; margin-bottom: 4rem;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 999px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.2); margin-bottom: 1rem;">
                    <i class="bi bi-diagram-3-fill" style="color: #3b82f6; font-size: 0.9rem;"></i>
                    <span style="font-weight: 700; font-size: 0.8rem; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.05em;">Our Process</span>
                </div>
                <h2 itemprop="name" style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 900; margin: 0 0 1rem; letter-spacing: -0.02em;">
                    <span style="color: #f97316;">How</span> 
                    <span style="color: #0f172a;">CAS Private Care LLC Works</span>
                </h2>
                <p style="font-size: 1.15rem; color: #64748b; max-width: 600px; margin: 0 auto;">Simple, fast, and secure — connecting care in four easy steps</p>
            </div>
            
            <!-- Steps Grid with Images - 4 columns on desktop -->
            <div class="how-it-works-grid">
                <!-- Step 1: Browse & Select - Replace image by adding your file to public/images/browse-select.jpg -->
                <div class="fade-in how-it-works-card" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid rgba(59, 130, 246, 0.1); transition: all 0.3s ease;">
                    <div class="how-it-works-image" style="height: 180px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); position: relative; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=400&h=300&fit=crop" alt="Browse & Select caregivers" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;" loading="lazy" decoding="async" width="400" height="300">
                        <div style="position: absolute; top: 1rem; left: 1rem; width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; color: white; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);">1</div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 0.75rem; color: #0f172a;">
                            <span style="color: #f97316;">Browse</span> & Select
                        </h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.7; margin: 0;">Clients search for caregivers or nannies, review their profiles, credentials, and ratings to find the perfect match.</p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="fade-in how-it-works-card" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid rgba(249, 115, 22, 0.1); transition: all 0.3s ease;">
                    <div class="how-it-works-image" style="height: 180px; background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); position: relative; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=300&fit=crop" alt="Book and schedule" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;" loading="lazy" decoding="async" width="400" height="300">
                        <div style="position: absolute; top: 1rem; left: 1rem; width: 48px; height: 48px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; color: white; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);">2</div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 0.75rem; color: #0f172a;">
                            <span style="color: #f97316;">Book</span> & Schedule
                        </h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.7; margin: 0;">Choose your preferred schedule and book instantly. Payments are processed securely through our platform.</p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="fade-in how-it-works-card" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid rgba(16, 185, 129, 0.1); transition: all 0.3s ease;">
                    <div class="how-it-works-image" style="height: 180px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); position: relative; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?w=400&h=300&fit=crop" alt="Connect and care" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;" loading="lazy" decoding="async" width="400" height="300">
                        <div style="position: absolute; top: 1rem; left: 1rem; width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; color: white; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);">3</div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 0.75rem; color: #0f172a;">
                            <span style="color: #10b981;">Connect</span> & Care
                        </h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.7; margin: 0;">Partners and contractors receive bookings, connect with families, and deliver exceptional services while building their reputation.</p>
                    </div>
                </div>
                
                <!-- Step 4 -->
                <div class="fade-in how-it-works-card" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid rgba(139, 92, 246, 0.1); transition: all 0.3s ease;">
                    <div class="how-it-works-image" style="height: 180px; background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); position: relative; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=300&fit=crop" alt="Rate and review" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;" loading="lazy" decoding="async" width="400" height="300">
                        <div style="position: absolute; top: 1rem; left: 1rem; width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; color: white; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);">4</div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 0.75rem; color: #0f172a;">
                            <span style="color: #8b5cf6;">Rate</span> & Review
                        </h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.7; margin: 0;">Share your experience and help others make informed decisions. Build trust within the community.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Bar -->
    <div class="landing-trust-bar" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 2rem 2rem; border-top: 1px solid rgba(15, 23, 42, 0.06); border-bottom: 1px solid rgba(15, 23, 42, 0.06);">
        <div class="trust-bar-grid" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 2rem 3.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.20);">
                    <i class="bi bi-shield-check" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem;">Background Checked</div>
                    <div style="color: #64748b; font-size: 0.85rem;">All partners verified</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.20);">
                    <i class="bi bi-star-fill" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem;">5-Star Reviews</div>
                    <div style="color: #64748b; font-size: 0.85rem;">2,000+ verified reviews</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.20);">
                    <i class="bi bi-lock-fill" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem;">Secure Payments</div>
                    <div style="color: #64748b; font-size: 0.85rem;">Encrypted transactions</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.20);">
                    <i class="bi bi-geo-alt-fill" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem;">5 Borough Coverage</div>
                    <div style="color: #64748b; font-size: 0.85rem;">All of NYC served</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <!-- Reviews and Testimonials Section (Premium Redesign) -->
    <section class="section-light" style="padding: 6rem 2rem; position: relative; overflow: hidden;">
        <!-- Decorative Elements -->
        <div aria-hidden="true" style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #0B4FA2 0%, #f97316 50%, #10b981 100%);"></div>
        <div aria-hidden="true" style="position: absolute; top: 60px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(11, 79, 162, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
        <div aria-hidden="true" style="position: absolute; bottom: 100px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(249, 115, 22, 0.06) 0%, transparent 70%); border-radius: 50%;"></div>
        
        <div class="container" style="max-width: 1280px; margin: 0 auto; position: relative; z-index: 1;">
            <!-- Section Header -->
            <div class="section-header fade-in" style="text-align: center; margin-bottom: 3rem;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 999px; background: linear-gradient(135deg, rgba(11, 79, 162, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%); border: 1px solid rgba(11, 79, 162, 0.2); margin-bottom: 1.25rem;">
                    <i class="bi bi-patch-check-fill" style="color: #0B4FA2; font-size: 1rem;"></i>
                    <span style="font-weight: 800; font-size: 0.85rem; color: #0B4FA2; text-transform: uppercase; letter-spacing: 0.05em;">Verified Reviews</span>
                </div>
                <h2 style="font-size: clamp(2rem, 5vw, 3.25rem); font-weight: 900; margin: 0 0 1rem; letter-spacing: -0.03em; line-height: 1.15;">
                    <span style="color: #0f172a;">What </span>
                    <span style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">NYC Families</span>
                    <span style="color: #0f172a;"> Say</span>
                </h2>
                <p style="font-size: 1.15rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.7;">Real stories from families across all five boroughs who trust CAS Private Care</p>
            </div>

            <!-- Stats Bar -->
            <div class="fade-in" style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; margin-bottom: 3.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 2rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid rgba(16, 185, 129, 0.15);">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);">
                        <i class="bi bi-heart-pulse-fill" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1;">4.9<span style="font-size: 1rem; color: #fbbf24; margin-left: 0.25rem;">★</span></div>
                        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">1,200+ Caregiver Reviews</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 2rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid rgba(11, 79, 162, 0.15);">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(11, 79, 162, 0.3);">
                        <i class="bi bi-house-heart-fill" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1;">4.9<span style="font-size: 1rem; color: #fbbf24; margin-left: 0.25rem;">★</span></div>
                        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">850+ Housekeeper Reviews</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 2rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid rgba(249, 115, 22, 0.15);">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(249, 115, 22, 0.3);">
                        <i class="bi bi-geo-alt-fill" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1;">5</div>
                        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">NYC Boroughs Covered</div>
                    </div>
                </div>
            </div>

            <!-- Reviews Grid -->
            <div class="reviews-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.75rem;">
                
                <!-- Review Card 1 - Elena Martinez -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(16, 185, 129, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #10b981; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">EM</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Elena Martinez</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-heart-pulse"></i> Caregiver • Manhattan, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); color: #065f46; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #10b981;"></i> Verified Review
                            </span>
                            <span style="background: rgba(251, 191, 36, 0.1); color: #b45309; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(251, 191, 36, 0.2);">
                                <i class="bi bi-shield-check"></i> Trusted Partner
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #10b981;">
                            "Elena has been caring for my 82-year-old mother for 6 months. She's patient, compassionate, and incredibly professional. My mother looks forward to her visits every day. Best caregiver in Manhattan!"
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Margaret W.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Upper West Side
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">6 months ago</div>
                        </div>
                    </div>
                </div>

                <!-- Review Card 2 - Carmen Torres -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(11, 79, 162, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #0B4FA2; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">CT</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Carmen Torres</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-house-heart"></i> Housekeeper • Brooklyn, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(11, 79, 162, 0.1) 0%, rgba(11, 79, 162, 0.05) 100%); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(11, 79, 162, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #0B4FA2;"></i> Verified Review
                            </span>
                            <span style="background: rgba(11, 79, 162, 0.08); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(11, 79, 162, 0.15);">
                                <i class="bi bi-stars"></i> 5-Star Cleaning
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #0B4FA2;">
                            "Carmen is simply the best housekeeper in Brooklyn! Thorough, reliable, and always leaves our home spotless. She's been with us for over a year and we couldn't be happier."
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">James & Lisa P.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #0B4FA2;"></i> Park Slope
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">1 year ago</div>
                        </div>
                    </div>
                </div>

                <!-- Review Card 3 - Aisha Ahmed -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(16, 185, 129, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #10b981; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">AA</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Aisha Ahmed</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-heart-pulse"></i> Caregiver • Queens, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); color: #065f46; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #10b981;"></i> Verified Review
                            </span>
                            <span style="background: rgba(16, 185, 129, 0.08); color: #065f46; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.15);">
                                <i class="bi bi-heart-pulse"></i> Caring Support
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #10b981;">
                            "Aisha is punctual, warm, and incredibly attentive. She helped us set a steady routine and brought so much peace to our home. We felt supported from day one."
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Rachel K.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Long Island City
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">3 months ago</div>
                        </div>
                    </div>
                </div>

                <!-- Review Card 4 - Robert Johnson -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(16, 185, 129, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #10b981; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">RJ</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Robert Johnson</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-heart-pulse"></i> Caregiver • Bronx, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); color: #065f46; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #10b981;"></i> Verified Review
                            </span>
                            <span style="background: rgba(16, 185, 129, 0.08); color: #065f46; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.15);">
                                <i class="bi bi-shield-check"></i> Patient & Steady
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #10b981;">
                            "Robert has been wonderful with my elderly father who has dementia. He's patient, kind, and skilled at managing challenging situations. We're so grateful to have found such a caring caregiver in the Bronx."
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Thomas & Anna D.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Riverdale
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">4 months ago</div>
                        </div>
                    </div>
                </div>

                <!-- Review Card 5 - Maria Silva -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(11, 79, 162, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #0B4FA2; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">MS</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Maria Silva</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-house-heart"></i> Housekeeper • Staten Island, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(11, 79, 162, 0.1) 0%, rgba(11, 79, 162, 0.05) 100%); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(11, 79, 162, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #0B4FA2;"></i> Verified Review
                            </span>
                            <span style="background: rgba(11, 79, 162, 0.08); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(11, 79, 162, 0.15);">
                                <i class="bi bi-eye"></i> Detail-Focused
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #0B4FA2;">
                            "Maria is absolutely fantastic! She's been cleaning our home weekly for 8 months. Professional, trustworthy, and pays attention to every detail. Best housekeeper in Staten Island by far!"
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Kevin & Nicole B.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #0B4FA2;"></i> Great Kills
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">8 months ago</div>
                        </div>
                    </div>
                </div>

                <!-- Review Card 6 - Diana Lopez -->
                <div class="review-card fade-in" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(11, 79, 162, 0.12); transition: all 0.3s ease;">
                    <!-- Card Header with Gradient -->
                    <div style="background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%); padding: 1.5rem 1.75rem; position: relative;">
                        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);"></div>
                        <div style="display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 64px; min-width: 64px; height: 64px; flex-shrink: 0; aspect-ratio: 1; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.35rem; color: #0B4FA2; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.5);">DL</div>
                            <div style="flex: 1;">
                                <h4 style="color: white; font-size: 1.15rem; font-weight: 800; margin: 0 0 0.25rem;">Diana Lopez</h4>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-house-heart"></i> Housekeeper • Manhattan, NY
                                </p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.4rem 0.75rem; border-radius: 999px; backdrop-filter: blur(10px);">
                                <span style="color: #fbbf24; font-size: 0.9rem; letter-spacing: 1px;">★★★★★</span>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div style="padding: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="background: linear-gradient(135deg, rgba(11, 79, 162, 0.1) 0%, rgba(11, 79, 162, 0.05) 100%); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(11, 79, 162, 0.2);">
                                <i class="bi bi-patch-check-fill" style="color: #0B4FA2;"></i> Verified Review
                            </span>
                            <span style="background: rgba(11, 79, 162, 0.08); color: #1e3a8a; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(11, 79, 162, 0.15);">
                                <i class="bi bi-check2-circle"></i> Consistent Results
                            </span>
                        </div>
                        <blockquote style="font-size: 1rem; color: #334155; line-height: 1.75; margin: 0 0 1.25rem; position: relative; padding-left: 1rem; border-left: 3px solid #0B4FA2;">
                            "Diana is consistent and detail-oriented. She leaves our apartment spotless and always checks in about priorities. The best housekeeper we've worked with in Manhattan."
                        </blockquote>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Daniel M.</div>
                                <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="bi bi-geo-alt-fill" style="color: #0B4FA2;"></i> Midtown East
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">2 months ago</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust Banner -->
            <div class="fade-in trust-banner" style="margin-top: 3.5rem; background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%); border-radius: 24px; padding: 3rem; position: relative; overflow: hidden;">
                <div aria-hidden="true" style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(249, 115, 22, 0.3) 0%, transparent 70%); border-radius: 50%;"></div>
                <div aria-hidden="true" style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%); border-radius: 50%;"></div>
                
                <div class="trust-stats-container" style="position: relative; z-index: 1; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 3rem;">
                    <div class="trust-stat-item" style="text-align: center;">
                        <div class="trust-stat-number" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">2,500+</div>
                        <div class="trust-stat-label" style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">NYC Families Served</div>
                    </div>
                    <div class="trust-stat-divider" style="width: 1px; height: 50px; background: rgba(255,255,255,0.2);"></div>
                    <div class="trust-stat-item" style="text-align: center;">
                        <div class="trust-stat-number" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">4.9★</div>
                        <div class="trust-stat-label" style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">Average Rating</div>
                    </div>
                    <div class="trust-stat-divider" style="width: 1px; height: 50px; background: rgba(255,255,255,0.2);"></div>
                    <div class="trust-stat-item" style="text-align: center;">
                        <div class="trust-stat-number" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">100%</div>
                        <div class="trust-stat-label" style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">Background Checked</div>
                    </div>
                    <div class="trust-stat-divider" style="width: 1px; height: 50px; background: rgba(255,255,255,0.2);"></div>
                    <div class="trust-stat-item" style="text-align: center;">
                        <div class="trust-stat-number" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">5</div>
                        <div class="trust-stat-label" style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">Boroughs Covered</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-dark ready-get-started-section" style="padding: 7rem 2rem; position: relative; overflow: hidden; background-image: url('{{ asset('images/ready-get-started-bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(15, 23, 42, 0.45) 0%, rgba(15, 23, 42, 0.35) 100%); pointer-events: none;"></div>
        <div class="container" style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">
            <div class="section-header fade-in">
                <h2><span class="ready-orange">Ready</span> <span class="ready-white">to Get Started?</span></h2>
                <p style="color: rgba(255, 255, 255, 0.9);">Join CAS Private Care LLC today and experience the future of caregiving services</p>
            </div>
            <div class="hero-buttons" style="margin-top: 3rem; justify-content: center;">
                <a href="{{ url('/register') }}" class="btn-primary">Sign Up Now</a>
                <a href="#how-it-works" class="btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    </main>

    <!-- Desktop Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo" width="120" height="120" loading="lazy" decoding="async">
                </div>
                <p>Connection that cares. Your trusted marketplace for professional caregiving services connecting families with verified care professionals.</p>
                <p style="margin-top: 1rem;">We provide a safe, reliable platform where quality care meets convenience. From elderly care to childcare, our verified professionals are ready to support your family's unique needs with compassion and expertise.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/profile.php?id=61584831099232" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Facebook" onclick="window.open(this.href, '_blank'); return false;"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.linkedin.com/in/CASprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on LinkedIn" onclick="window.open(this.href, '_blank'); return false;"><i class="bi bi-linkedin"></i></a>
                    <a href="https://www.instagram.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>For Clients</h3>
                <ul>
                    <li><a href="{{ url('/') }}#services">Browse Services</a></li>
                    <li><a href="{{ url('/') }}#how-it-works">How It Works</a></li>
                    <li><a href="{{ url('/register') }}">Sign Up</a></li>
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="{{ url('/about') }}">About</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>For Partners</h3>
                <ul>
                    <li><a href="{{ url('/register') }}">Join as Caregiver</a></li>
                    <li><a href="{{ url('/register') }}">Marketing Partner</a></li>
                    <li><a href="{{ url('/register') }}">Training Center</a></li>
                    <li><a href="{{ url('/') }}#how-it-works">How It Works</a></li>
                </ul>
                <h3 style="margin-top: 2rem;">Company</h3>
                <ul>
                    <li><a href="{{ url('/about') }}">About Us</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ url('/register') }}">Sign Up</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <div class="footer-location">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>{{ config('app.address', 'New York, USA') }}</span>
                </div>
                <div class="footer-location">
                    <i class="bi bi-telephone-fill"></i>
                    <span><a href="tel:{{ config('app.phone', '+16462828282') }}" style="color: #94a3b8; text-decoration: none;">{{ config('app.phone', '+1 (646) 282-8282') }}</a></span>
                </div>
                <div class="footer-location">
                    <i class="bi bi-envelope-fill"></i>
                    <span><a href="mailto:{{ config('app.email', 'contact@casprivatecare.online') }}" style="color: #94a3b8; text-decoration: none;">{{ config('app.email', 'contact@casprivatecare.online') }}</a></span>
                </div>
                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Newsletter</h3>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 1rem;">Get updates and tips</p>
                <div class="newsletter-input">
                    <input type="email" placeholder="Your email">
                    <button class="newsletter-btn">Subscribe</button>
                </div>
                <div style="margin-top: 1.5rem;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387190.27991608967!2d-74.25987368715493!3d40.69767006377258!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sus!4v1234567890" width="100%" height="120" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-bottom">
            <p>&copy; 2026 CAS Private Care LLC. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="{{ url('/privacy') }}">Privacy Policy</a>
                <a href="{{ url('/terms') }}">Terms of Service</a>
                <a href="{{ url('/contact') }}">Contact</a>
            </div>
        </div>
    </footer>

    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')

    <script>

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        document.getElementById('navLinks').classList.remove('active');
                    }
                }
            });
        });

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.getElementById('loading-screen').classList.add('fade-out');
            }, 1000);

            // Enable scroll animations only after JS is ready
            // This ensures content is visible even if JS fails
            document.body.classList.add('scroll-animate');

            document.querySelectorAll('.fade-in, .scale-in').forEach(el => {
                observer.observe(el);
            });

            const counters = document.querySelectorAll('.counter');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.getAttribute('data-target'));
                        const duration = 3000;
                        const increment = target / (duration / 16);
                        let current = 0;

                        const updateCounter = () => {
                            current += increment;
                            if (current < target) {
                                counter.textContent = Math.floor(current).toLocaleString();
                                requestAnimationFrame(updateCounter);
                            } else {
                                counter.textContent = target.toLocaleString();
                            }
                        };

                        updateCounter();
                        counterObserver.unobserve(counter);
                    }
                });
            }, { threshold: 0.3 });

            counters.forEach(counter => counterObserver.observe(counter));

            const progressBars = document.querySelectorAll('.progress-bar');
            const progressObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const bar = entry.target;
                        const progress = bar.getAttribute('data-progress');
                        setTimeout(() => {
                            bar.style.width = progress + '%';
                        }, 300);
                        progressObserver.unobserve(bar);
                    }
                });
            }, { threshold: 0.3 });

            progressBars.forEach(bar => progressObserver.observe(bar));

            // About section video: unmute when in view, mute when scrolled past; ensure autoplay (works on hosting)
            const aboutVideo = document.getElementById('about-section-video');
            const aboutSection = document.getElementById('about-section');
            if (aboutVideo && aboutSection) {
                function tryPlayVideo() {
                    aboutVideo.muted = true;
                    aboutVideo.play().catch(() => {});
                }
                // On hosting the video often isn't ready at DOMContentLoaded — wait for it to load
                aboutVideo.addEventListener('loadeddata', tryPlayVideo, { once: true });
                aboutVideo.addEventListener('canplay', tryPlayVideo, { once: true });
                if (aboutVideo.readyState >= 2) tryPlayVideo();
                else tryPlayVideo();
                const videoSectionObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            aboutVideo.muted = false;
                            aboutVideo.play().catch(() => {});
                        } else {
                            aboutVideo.muted = true;
                        }
                    });
                }, { threshold: 0.25, rootMargin: '0px' });
                videoSectionObserver.observe(aboutSection);
            }
        });
        
        let currentService = 0;
    const services = ['caregiver', 'housekeeping'];
        
        function switchService(type) {
            const description = document.getElementById('hero-description');
            const findBtn = document.getElementById('find-btn');
            const sliderBg = document.getElementById('slider-bg');
            const buttons = ['btn-caregiver', 'btn-housekeeping'];
            
            // Fade out
            description.style.transition = 'opacity 0.3s ease';
            findBtn.style.transition = 'opacity 0.3s ease';
            description.style.opacity = '0';
            findBtn.style.opacity = '0';
            
            buttons.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.color = '#64748b';
                    el.setAttribute('aria-selected', 'false');
                }
            });
            
            if (type === 'caregiver') {
                sliderBg.style.transform = 'translateX(0%)';
                const btnCaregiver = document.getElementById('btn-caregiver');
                if (btnCaregiver) { btnCaregiver.style.color = '#1e40af'; btnCaregiver.setAttribute('aria-selected', 'true'); }
                currentService = 0;
            } else if (type === 'housekeeping') {
                sliderBg.style.transform = 'translateX(100%)';
                const btnHousekeeping = document.getElementById('btn-housekeeping');
                if (btnHousekeeping) { btnHousekeeping.style.color = '#1e40af'; btnHousekeeping.setAttribute('aria-selected', 'true'); }
                currentService = 1;
            }
            
            setTimeout(() => {
                if (type === 'caregiver') {
                    description.textContent = 'A modern and trustworthy caregiving marketplace where families effortlessly connect with verified caregivers and companions for exceptional care services.';
                    findBtn.textContent = 'Find a Caregiver';
                    findBtn.href = '{{ url("/register") }}?service=caregiver';
                } else if (type === 'housekeeping') {
                    description.textContent = 'Professional housekeeping services marketplace where families effortlessly connect with reliable and trusted house helpers for all your home maintenance.';
                    findBtn.textContent = 'Find a Housekeeper';
                    findBtn.href = '{{ url("/register") }}?service=housekeeping';
                }
                
                // Fade in
                description.style.opacity = '1';
                findBtn.style.opacity = '1';
            }, 300);
        }
        
        // Auto-rotate services every 6 seconds
        setInterval(() => {
            currentService = (currentService + 1) % services.length;
            switchService(services[currentService]);
        }, 6000);
    </script>

    <!-- FAQ Chatbot -->
    <div id="chatbot-container">
        <!-- Floating Chat Button with Logo -->
        <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Open FAQ Chat" title="Chat with CAS Assistant">
            <img src="/logo flower.png" alt="CAS" class="chatbot-toggle-logo">
            <span class="chatbot-notification">1</span>
            <span class="chatbot-pulse-ring"></span>
        </button>

        <!-- Chat Window -->
        <div id="chatbot-window" class="chatbot-window hidden">
            <!-- Enhanced Header with Logo -->
            <div class="chatbot-header">
                <div class="chatbot-header-content">
                    <div class="chatbot-logo-container">
                        <img src="/logo flower.png" alt="CAS Private Care" class="chatbot-header-logo">
                        <span class="chatbot-status-dot"></span>
                    </div>
                    <div class="chatbot-header-text">
                        <h3>CAS Private Care</h3>
                        <p><i class="bi bi-circle-fill online-indicator"></i> Online • Ready to help</p>
                    </div>
                </div>
                <div class="chatbot-header-actions">
                    <button id="chatbot-minimize" class="chatbot-action-btn" aria-label="Minimize chat" title="Minimize">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button id="chatbot-close" class="chatbot-close" aria-label="Close chat" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Branded Subheader -->
            <div class="chatbot-subheader">
                <div class="chatbot-brand-strip">
                    <span class="brand-tag"><i class="bi bi-shield-check"></i> Verified Support</span>
                    <span class="brand-tag"><i class="bi bi-clock"></i> Quick Response</span>
                </div>
            </div>
            
            <div class="chatbot-body" id="chatbot-body">
                <!-- Welcome Card -->
                <div class="chat-welcome-card">
                    <img src="/logo flower.png" alt="CAS Private Care" class="welcome-logo">
                    <h4>Welcome to CAS Private Care!</h4>
                    <p>Your trusted partner for quality home care services in NYC.</p>
                </div>

                <!-- Initial Bot Message with Timestamp -->
                <div class="chat-message bot-message" data-time="">
                    <div class="message-avatar">
                        <img src="/logo flower.png" alt="CAS">
                    </div>
                    <div class="message-wrapper">
                        <div class="message-sender">CAS Assistant</div>
                        <div class="message-content">
                            <p>Hello! 👋 I'm here to help answer your questions about CAS Private Care. What would you like to know?</p>
                        </div>
                        <div class="message-meta">
                            <span class="message-time" id="initial-message-time"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick FAQ Options with Icons -->
                <div class="quick-questions">
                    <p class="quick-questions-label">Popular Questions</p>
                    <button class="quick-question-btn" data-question="How do I become a partner?">
                        <i class="bi bi-person-plus"></i>
                        <span>How do I become a partner?</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <button class="quick-question-btn" data-question="What types of partners do you have?">
                        <i class="bi bi-people"></i>
                        <span>What types of partners do you have?</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <button class="quick-question-btn" data-question="How does the platform work?">
                        <i class="bi bi-gear"></i>
                        <span>How does the platform work?</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <button class="quick-question-btn" data-question="What are your rates?">
                        <i class="bi bi-currency-dollar"></i>
                        <span>What are your rates?</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Enhanced Footer -->
            <div class="chatbot-footer">
                <div class="chatbot-input-container">
                    <button class="chatbot-attach-btn" aria-label="Attach file" title="Attach file (coming soon)" disabled>
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <input type="text" id="chatbot-input" placeholder="Type your message..." autocomplete="off">
                    <button id="chatbot-send" class="chatbot-send-btn" aria-label="Send message">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                <div class="chatbot-footer-branding">
                    <span>Powered by:</span>
                    <span style="font-weight: 600; color: #0B4FA2; font-size: 0.7rem;">CAS Private Care</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ===== Enhanced Chatbot Styles ===== */
        #chatbot-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Floating Toggle Button with Logo */
        .chatbot-toggle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%);
            border: 3px solid rgba(255, 255, 255, 0.3);
            color: white;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(11, 79, 162, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: visible;
        }

        .chatbot-toggle-logo {
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .chatbot-toggle-icon {
            font-size: 1.6rem;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 10px 36px rgba(11, 79, 162, 0.6);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .chatbot-toggle:active {
            transform: scale(0.95);
        }

        .chatbot-pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #0B4FA2;
            animation: chatbot-pulse-ring 2s ease-out infinite;
            pointer-events: none;
        }

        @keyframes chatbot-pulse-ring {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .chatbot-notification {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.5);
            animation: notification-bounce 2s infinite;
        }

        @keyframes notification-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        /* Chat Window */
        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 400px;
            height: 620px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(11, 79, 162, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: chatbot-slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes chatbot-slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .chatbot-window.hidden {
            display: none;
        }

        /* Enhanced Header */
        .chatbot-header {
            background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%);
            color: white;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .chatbot-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f97316, #fbbf24, #f97316);
        }

        .chatbot-header-content {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .chatbot-logo-container {
            position: relative;
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .chatbot-header-logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .chatbot-avatar-fallback {
            font-size: 1.5rem;
        }

        .chatbot-status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
        }

        .chatbot-header-text h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .chatbot-header-text p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .online-indicator {
            font-size: 0.5rem;
            color: #22c55e;
            animation: online-pulse 2s infinite;
        }

        @keyframes online-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .chatbot-header-actions {
            display: flex;
            gap: 0.5rem;
        }

        .chatbot-action-btn,
        .chatbot-close {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .chatbot-action-btn:hover,
        .chatbot-close:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }

        /* Branded Subheader */
        .chatbot-subheader {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            padding: 0.625rem 1rem;
            border-bottom: 1px solid #bfdbfe;
        }

        .chatbot-brand-strip {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .brand-tag {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            color: #0B4FA2;
            font-weight: 600;
            background: white;
            padding: 0.35rem 0.625rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(11, 79, 162, 0.1);
        }

        .brand-tag i {
            font-size: 0.75rem;
        }

        /* Chat Body */
        .chatbot-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .chatbot-body::-webkit-scrollbar {
            width: 6px;
        }

        .chatbot-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .chatbot-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .chatbot-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Welcome Card */
        .chat-welcome-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 0.5rem;
        }

        .chat-welcome-card .welcome-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 0.75rem;
        }

        .chat-welcome-card h4 {
            margin: 0 0 0.35rem 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0B4FA2;
        }

        .chat-welcome-card p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Chat Messages */
        .chat-message {
            display: flex;
            gap: 0.625rem;
            animation: message-fadeIn 0.4s ease-out;
        }

        @keyframes message-fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bot-message {
            align-items: flex-start;
        }

        .user-message {
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            overflow: hidden;
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .bot-message .message-avatar {
            background: white;
            color: white;
            border: 2px solid #dbeafe;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .user-message .message-avatar {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: 2px solid #fed7aa;
        }

        .message-wrapper {
            max-width: 78%;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .message-sender {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            margin-left: 0.5rem;
        }

        .user-message .message-sender {
            text-align: right;
            margin-right: 0.5rem;
            margin-left: 0;
        }

        .message-content {
            padding: 0.875rem 1rem;
            border-radius: 18px;
            line-height: 1.5;
            font-size: 0.875rem;
        }

        .bot-message .message-content {
            background: white;
            color: #1e293b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-bottom-left-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .user-message .message-content {
            background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%);
            color: white;
            border-bottom-right-radius: 6px;
            box-shadow: 0 2px 8px rgba(11, 79, 162, 0.2);
        }

        .message-content p {
            margin: 0;
        }

        /* Message Meta (Timestamp & Read Receipt) */
        .message-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 0.5rem;
            margin-top: 0.125rem;
        }

        .user-message .message-meta {
            justify-content: flex-end;
            margin-right: 0.5rem;
            margin-left: 0;
        }

        .message-time {
            font-size: 0.65rem;
            color: #94a3b8;
        }

        .message-status {
            font-size: 0.75rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
        }

        .message-status.sent {
            color: #94a3b8;
        }

        .message-status.delivered {
            color: #64748b;
        }

        .message-status.read {
            color: #0B4FA2;
        }

        .message-status i {
            font-size: 0.85rem;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 0.875rem 1rem;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.7;
            }
            30% {
                transform: translateY(-8px);
                opacity: 1;
            }
        }

        /* Quick Questions */
        .quick-questions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .quick-questions-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.25rem 0.5rem;
        }

        .quick-question-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.25s ease;
            font-size: 0.8rem;
            color: #1e293b;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .quick-question-btn i:first-child {
            color: #0B4FA2;
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        .quick-question-btn span {
            flex: 1;
            text-align: left;
        }

        .quick-question-btn i:last-child {
            color: #cbd5e1;
            font-size: 0.75rem;
            transition: transform 0.2s, color 0.2s;
        }

        .quick-question-btn:hover {
            border-color: #0B4FA2;
            background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(11, 79, 162, 0.15);
        }

        .quick-question-btn:hover i:first-child {
            color: #f97316;
        }

        .quick-question-btn:hover i:last-child {
            transform: translateX(3px);
            color: #0B4FA2;
        }

        /* Chat Footer */
        .chatbot-footer {
            border-top: 1px solid #e2e8f0;
            padding: 0.875rem 1rem;
            background: white;
        }

        .chatbot-input-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 28px;
            padding: 0.25rem 0.35rem 0.25rem 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .chatbot-input-container:focus-within {
            border-color: #0B4FA2;
            box-shadow: 0 0 0 3px rgba(11, 79, 162, 0.1);
        }

        .chatbot-attach-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: not-allowed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            opacity: 0.5;
        }

        #chatbot-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.625rem 0;
            font-size: 0.875rem;
            outline: none;
            color: #1e293b;
        }

        #chatbot-input::placeholder {
            color: #94a3b8;
        }

        .chatbot-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0B4FA2 0%, #1e3a8a 100%);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .chatbot-send-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(11, 79, 162, 0.4);
        }

        .chatbot-send-btn:active {
            transform: scale(0.95);
        }

        /* Footer Branding */
        .chatbot-footer-branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            margin-top: 0.625rem;
            padding-top: 0.625rem;
            border-top: 1px solid #f1f5f9;
            width: 100%;
            text-align: center;
        }

        .chatbot-footer-branding span {
            font-size: 0.65rem;
            color: #94a3b8;
            flex-shrink: 0;
            line-height: 14px;
        }

        .chatbot-footer-branding .footer-logo {
            height: 14px;
            width: 14px;
            object-fit: contain;
            opacity: 0.85;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .chatbot-footer-branding strong {
            font-size: 0.7rem;
            color: #0B4FA2;
            font-weight: 600;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            #chatbot-container {
                bottom: 16px;
                right: 16px;
            }

            .chatbot-window {
                width: calc(100vw - 32px);
                height: calc(100vh - 120px);
                max-height: 620px;
                bottom: 78px;
                right: 0;
                border-radius: 20px;
            }

            .chatbot-toggle {
                width: 58px;
                height: 58px;
            }

            .chatbot-toggle-logo {
                width: 30px;
                height: 30px;
            }

            .chatbot-subheader {
                padding: 0.5rem;
            }

            .brand-tag {
                font-size: 0.6rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>

    <script>
        // FAQ Data
        const faqData = {
            'how do i become a partner': {
                answer: 'To become a partner with CAS Private Care, click "Become a Partner" on our homepage or visit the registration page. Select your partner type (Caregiver, Housekeeping, Marketing Partner, or Training Center), complete the registration form with your credentials and information, and our team will review your application. Once approved, you\'ll be able to create your profile and start connecting with clients.'
            },
            'what types of partners do you have': {
                answer: 'We have four types of partners: 1) Caregivers - providing companionship, reminders, light meal prep, and day-to-day support. 2) Housekeeping - offering cleaning, laundry, and home organization services. 3) Marketing Partners - promoting our platform and connecting us with potential clients and partners. 4) Training Centers - providing education and certification programs for our partners.'
            },
            'how does the platform work': {
                answer: 'Our platform connects families with verified partners in 4 simple steps: 1) Browse & Select - Families search and review partner profiles, credentials, and ratings. 2) Book & Schedule - Choose preferred dates/times and book instantly with secure payments. 3) Connect & Care - Partners receive bookings and deliver exceptional services. 4) Rate & Review - Share experiences and build trust within the community.'
            },
            'what are your rates': {
                answer: 'Rates vary by service type and partner experience. All pricing details are transparently displayed on each partner\'s profile inside the platform. You can browse profiles, compare rates, and book directly. Contact us if you need help finding the right match for your budget.'
            },
            'how do i verify a partner': {
                answer: 'All partners on CAS Private Care are thoroughly verified before joining. We conduct criminal background checks, verify licenses and certifications, check professional references, and confirm work history. You can view verification badges on each partner\'s profile showing their verified status, licenses, and background check completion.'
            },
            'are partners licensed': {
                answer: 'Partners providing medical services or home health aide services must be licensed by the New York State Department of Health. All partners undergo background checks and certification verification regardless of service type. We ensure all partners meet or exceed New York State requirements for their respective services.'
            },
            'what services do partners provide': {
                answer: 'Our partners provide various services: Caregivers offer companionship, light meal prep, medication reminders, and day-to-day support. Housekeeping partners handle cleaning, laundry, home organization, and errands. Each partner specializes in their area of expertise to provide quality services.'
            },
            'how quickly can i get a partner': {
                answer: 'For emergency situations, we can typically arrange a partner within 4-6 hours. For scheduled services, we recommend booking 24-48 hours in advance to ensure the best match. Our online platform allows instant browsing and booking of available partners. We maintain a large network across all NYC boroughs to ensure availability.'
            },
            'do you serve all nyc boroughs': {
                answer: 'Yes! CAS Private Care provides verified partners throughout all five NYC boroughs: Manhattan, Brooklyn, Queens, Bronx, and Staten Island. We also serve surrounding areas in New York State. Our extensive network ensures we can match you with a qualified partner in your area.'
            },
            'what payment methods do you accept': {
                answer: 'We accept multiple payment methods including credit cards, debit cards, and secure online payments through our platform. All payments are processed securely with encryption. We also offer payment plans and can coordinate with insurance for qualifying services. Pricing is transparent with no hidden fees.'
            },
            'can i become a partner if i am a family member': {
                answer: 'Yes, in some cases. Through programs like CDPAP (Consumer Directed Personal Assistance Program), family members can become paid partners for eligible Medicaid recipients. Family members must complete required training and pass background checks. Contact us to learn more about eligibility and requirements for family member partnerships.'
            }
        };

        // Chatbot Functionality
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbotWindow = document.getElementById('chatbot-window');
        const chatbotClose = document.getElementById('chatbot-close');
        const chatbotMinimize = document.getElementById('chatbot-minimize');
        const chatbotBody = document.getElementById('chatbot-body');
        const chatbotInput = document.getElementById('chatbot-input');
        const chatbotSend = document.getElementById('chatbot-send');
        const notificationBadge = document.querySelector('.chatbot-notification');
        const pulseRing = document.querySelector('.chatbot-pulse-ring');

        let isChatOpen = false;

        // Format time helper
        function formatTime(date) {
            return date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        }

        // Set initial message time on page load
        document.addEventListener('DOMContentLoaded', function() {
            const initialTimeEl = document.getElementById('initial-message-time');
            if (initialTimeEl) {
                initialTimeEl.textContent = formatTime(new Date());
            }
        });

        // Toggle chatbot
        chatbotToggle.addEventListener('click', () => {
            if (isChatOpen) {
                closeChatbot();
            } else {
                openChatbot();
            }
        });

        chatbotClose.addEventListener('click', closeChatbot);
        if (chatbotMinimize) {
            chatbotMinimize.addEventListener('click', closeChatbot);
        }

        function openChatbot() {
            chatbotWindow.classList.remove('hidden');
            isChatOpen = true;
            chatbotInput.focus();
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
            if (pulseRing) {
                pulseRing.style.display = 'none';
            }
        }

        function closeChatbot() {
            chatbotWindow.classList.add('hidden');
            isChatOpen = false;
        }

        // Send message function
        function sendMessage(message) {
            if (!message.trim()) return;

            // Add user message with timestamp and status
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Show typing indicator
            const typingId = showTypingIndicator();

            // Process after delay (simulate thinking)
            setTimeout(() => {
                removeTypingIndicator(typingId);
                const answer = findAnswer(message);
                addMessage(answer, 'bot', true);
                
                // Update user message status to "read" after bot responds
                updateLastUserMessageStatus('read');
            }, 800 + Math.random() * 500);
        }

        function addMessage(text, sender, withTyping = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}-message`;

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            if (sender === 'bot') {
                avatar.innerHTML = '<img src="/logo flower.png" alt="CAS">';
            } else {
                avatar.innerHTML = '<i class="bi bi-person-fill"></i>';
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper';

            const senderLabel = document.createElement('div');
            senderLabel.className = 'message-sender';
            senderLabel.textContent = sender === 'bot' ? 'CAS Assistant' : 'You';

            const content = document.createElement('div');
            content.className = 'message-content';
            const p = document.createElement('p');
            p.textContent = text;
            content.appendChild(p);

            const meta = document.createElement('div');
            meta.className = 'message-meta';
            
            const timeSpan = document.createElement('span');
            timeSpan.className = 'message-time';
            timeSpan.textContent = formatTime(new Date());
            meta.appendChild(timeSpan);

            // Add status indicator for user messages
            if (sender === 'user') {
                const statusSpan = document.createElement('span');
                statusSpan.className = 'message-status sent';
                statusSpan.innerHTML = '<i class="bi bi-check2"></i>';
                meta.appendChild(statusSpan);
                
                // Simulate "delivered" after a moment
                setTimeout(() => {
                    statusSpan.className = 'message-status delivered';
                    statusSpan.innerHTML = '<i class="bi bi-check2-all"></i>';
                }, 300);
            }

            wrapper.appendChild(senderLabel);
            wrapper.appendChild(content);
            wrapper.appendChild(meta);

            messageDiv.appendChild(avatar);
            messageDiv.appendChild(wrapper);

            chatbotBody.appendChild(messageDiv);
            scrollToBottom();

            // Type out message with animation
            if (withTyping && sender === 'bot') {
                p.textContent = '';
                typeMessage(p, text);
            }
        }

        function updateLastUserMessageStatus(status) {
            const userMessages = chatbotBody.querySelectorAll('.user-message');
            if (userMessages.length > 0) {
                const lastUserMessage = userMessages[userMessages.length - 1];
                const statusEl = lastUserMessage.querySelector('.message-status');
                if (statusEl) {
                    statusEl.className = `message-status ${status}`;
                    if (status === 'read') {
                        statusEl.innerHTML = '<i class="bi bi-check2-all"></i>';
                    }
                }
            }
        }

        function typeMessage(element, text) {
            let i = 0;
            const typingInterval = setInterval(() => {
                if (i < text.length) {
                    element.textContent += text[i];
                    i++;
                    scrollToBottom();
                } else {
                    clearInterval(typingInterval);
                }
            }, 15 + Math.random() * 8);
        }

        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chat-message bot-message typing-indicator-message';
            typingDiv.id = 'typing-indicator';

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = '<img src="/logo flower.png" alt="CAS">';

            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper';

            const senderLabel = document.createElement('div');
            senderLabel.className = 'message-sender';
            senderLabel.textContent = 'CAS Assistant';

            const content = document.createElement('div');
            content.className = 'message-content';
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('div');
                dot.className = 'typing-dot';
                typingIndicator.appendChild(dot);
            }
            content.appendChild(typingIndicator);

            wrapper.appendChild(senderLabel);
            wrapper.appendChild(content);

            typingDiv.appendChild(avatar);
            typingDiv.appendChild(wrapper);
            chatbotBody.appendChild(typingDiv);
            scrollToBottom();

            return 'typing-indicator';
        }

        function removeTypingIndicator(id) {
            const typingElement = document.getElementById(id);
            if (typingElement) {
                typingElement.remove();
            }
        }

        function findAnswer(question) {
            const normalizedQuestion = question.toLowerCase().trim();
            
            // Direct match
            if (faqData[normalizedQuestion]) {
                return faqData[normalizedQuestion].answer;
            }

            // Keyword matching
            for (const [key, data] of Object.entries(faqData)) {
                const keywords = key.split(' ');
                const questionWords = normalizedQuestion.split(' ');
                const matchCount = keywords.filter(kw => questionWords.some(qw => qw.includes(kw) || kw.includes(qw))).length;
                
                if (matchCount >= 2) {
                    return data.answer;
                }
            }

            // Default response
            return 'I\'m sorry, I didn\'t quite understand that. Could you try rephrasing your question? You can also ask about: becoming a partner, partner types, how the platform works, rates, verification, or services.';
        }

        function scrollToBottom() {
            chatbotBody.scrollTop = chatbotBody.scrollHeight;
        }

        // Send button click
        chatbotSend.addEventListener('click', () => {
            sendMessage(chatbotInput.value);
        });

        // Enter key press
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage(chatbotInput.value);
            }
        });

        // Quick question buttons
        document.querySelectorAll('.quick-question-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const question = btn.getAttribute('data-question');
                sendMessage(question);
            });
        });

        // Close on outside click (mobile)
        document.addEventListener('click', (e) => {
            if (isChatOpen && !chatbotWindow.contains(e.target) && !chatbotToggle.contains(e.target)) {
                // Allow clicks inside chatbot window
                if (!chatbotWindow.contains(e.target)) {
                    closeChatbot();
                }
            }
        });

        // ===== PARTICLE SYSTEM =====
        function createParticles() {
            const particlesContainer = document.getElementById('particles-container');
            if (!particlesContainer) {
                return;
            }

            // Create 50 particles with variety
            const particleCount = 50;
            const particleTypes = ['', 'sparkle', 'blue-sparkle', 'glow'];
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Add random particle type (70% regular, 30% special)
                if (Math.random() > 0.7) {
                    const randomType = particleTypes[Math.floor(Math.random() * particleTypes.length)];
                    if (randomType) {
                        particle.classList.add(randomType);
                    }
                }
                
                // Random size between 2-8px (larger for special particles)
                const isSpecial = particle.classList.length > 1;
                const size = isSpecial ? Math.random() * 4 + 4 : Math.random() * 4 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random horizontal position
                particle.style.left = `${Math.random() * 100}%`;
                
                // Random animation duration between 6-18 seconds
                const duration = Math.random() * 12 + 6;
                particle.style.animationDuration = `${duration}s`;
                
                // Random delay for staggered start
                particle.style.animationDelay = `${Math.random() * 8}s`;
                
                // Random horizontal drift (-50px to 50px)
                const drift = (Math.random() - 0.5) * 100;
                particle.style.setProperty('--drift', `${drift}px`);
                
                // Random opacity
                particle.style.opacity = Math.random() * 0.6 + 0.2;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Add floating particles to sections
        function addSectionParticles() {
            const sections = document.querySelectorAll('section[id]');
            
            sections.forEach(section => {
                // Skip if section already has particles
                if (section.querySelector('.section-particles')) return;
                
                const particleContainer = document.createElement('div');
                particleContainer.className = 'section-particles';
                particleContainer.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    pointer-events: none;
                    overflow: hidden;
                    z-index: 0;
                `;
                
                // Add 5-8 subtle particles per section
                const count = Math.floor(Math.random() * 4) + 5;
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    
                    // Smaller, more subtle particles for sections
                    const size = Math.random() * 3 + 1;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.animationDuration = `${Math.random() * 15 + 10}s`;
                    particle.style.animationDelay = `${Math.random() * 10}s`;
                    particle.style.setProperty('--drift', `${(Math.random() - 0.5) * 80}px`);
                    particle.style.opacity = Math.random() * 0.3 + 0.1;
                    
                    particleContainer.appendChild(particle);
                }
                
                // Make sure section is positioned relative
                if (getComputedStyle(section).position === 'static') {
                    section.style.position = 'relative';
                }
                
                section.insertBefore(particleContainer, section.firstChild);
            });
        }

        // Initialize particles on page load
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            // Delay section particles for better performance
            setTimeout(() => {
                addSectionParticles();
            }, 1000);
        });

        // ===== SCROLL ANIMATIONS =====
        // Initialize Intersection Observer for scroll animations
        const scrollObserverOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        // Create observer for fade-in animations
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    fadeInObserver.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        // Create observer for slide-up animations
        const slideUpObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                    slideUpObserver.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        // Create observer for slide-in-left animations
        const slideLeftObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-left');
                    slideLeftObserver.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        // Create observer for slide-in-right animations
        const slideRightObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-right');
                    slideRightObserver.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        // Create observer for scale animations
        const scaleObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-scale');
                    scaleObserver.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        // Apply animations to elements - DISABLED: Using CSS-based progressive enhancement instead
        // The .fade-in and .scale-in classes now handle animations with the .visible class
        // This prevents content from being invisible if JS fails
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#!') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Parallax effect for hero section
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    const heroSection = document.querySelector('.hero');
                    
                    if (heroSection && scrolled < window.innerHeight) {
                        // Parallax effect on hero content
                        const heroContent = document.querySelector('.hero-left');
                        if (heroContent) {
                            heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
                            heroContent.style.opacity = 1 - (scrolled / 600);
                        }

                        // Parallax effect on hero image
                        const heroImage = document.querySelector('.hero-right');
                        if (heroImage) {
                            heroImage.style.transform = `translateY(${scrolled * 0.2}px)`;
                        }
                    }
                    
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Add scroll progress indicator
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #f97316, #3b82f6);
            width: 0%;
            z-index: 9999;
            transition: width 0.1s ease-out;
        `;
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (window.pageYOffset / windowHeight) * 100;
            progressBar.style.width = scrolled + '%';
        });
    </script>

    @include('partials.mobile-action-bar')
    @include('partials.cookie-consent')
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
                    .then(function(registration) {
                        // Check for updates periodically
                        setInterval(() => {
                            registration.update();
                        }, 60 * 60 * 1000); // Check every hour
                    })
.catch(function() {});
            });
        }
    </script>
    
    <!-- Booking Maintenance Modal -->
    <div id="maintenanceModal" class="maintenance-modal-overlay" style="display: none;">
        <div class="maintenance-modal">
            <div class="maintenance-modal-header">
                <i class="bi bi-wrench"></i>
                <span>System Maintenance</span>
            </div>
            <div class="maintenance-modal-body">
                <div class="maintenance-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3>Booking Currently Unavailable</h3>
                <p id="maintenanceMessage">Our booking system is currently under maintenance. Please try again later.</p>
                <div class="maintenance-info">
                    <i class="bi bi-info-circle"></i>
                    <span>Existing bookings are not affected by this maintenance.</span>
                </div>
                <p class="maintenance-apology">We apologize for any inconvenience. Please check back soon.</p>
            </div>
            <div class="maintenance-modal-footer">
                <button type="button" class="maintenance-close-btn" onclick="closeMaintenanceModal()">
                    <i class="bi bi-x-lg"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <style>
        /* Maintenance Modal Styles */
        .maintenance-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .maintenance-modal {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .maintenance-modal-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .maintenance-modal-header i {
            font-size: 1.5rem;
        }
        
        .maintenance-modal-body {
            padding: 32px;
            text-align: center;
        }
        
        .maintenance-icon {
            margin-bottom: 20px;
        }
        
        .maintenance-icon i {
            font-size: 64px;
            color: #f59e0b;
        }
        
        .maintenance-modal-body h3 {
            color: #1e293b;
            font-size: 1.5rem;
            margin-bottom: 12px;
        }
        
        .maintenance-modal-body > p {
            color: #64748b;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .maintenance-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1d4ed8;
            font-size: 0.9rem;
            margin-bottom: 16px;
            text-align: left;
        }
        
        .maintenance-apology {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
        .maintenance-modal-footer {
            padding: 16px 32px 32px;
            display: flex;
            justify-content: center;
        }
        
        .maintenance-close-btn {
            background: #0B4FA2;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .maintenance-close-btn:hover {
            background: #083d7a;
            transform: translateY(-2px);
        }
        
        /* Red maintenance indicator dot */
        .booking-btn-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .maintenance-dot {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 12px;
            height: 12px;
            background-color: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
            animation: pulse 2s ease-in-out infinite;
            display: none;
        }
        
        .maintenance-dot.active {
            display: block;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.6);
            }
        }
    </style>
    
    <script>
        // Booking Maintenance Mode
        let bookingMaintenanceEnabled = false;
        let bookingMaintenanceMessage = 'Our booking system is currently under maintenance. Please try again later.';
        
        // Check maintenance status on page load
        async function checkBookingMaintenanceStatus() {
            try {
                const response = await fetch('/api/booking-maintenance-status');
                if (response.ok) {
                    const data = await response.json();
                    bookingMaintenanceEnabled = data.maintenance_enabled || false;
                    bookingMaintenanceMessage = data.maintenance_message || bookingMaintenanceMessage;
                    
                    // Update indicator dots
                    document.querySelectorAll('.maintenance-dot').forEach(dot => {
                        if (bookingMaintenanceEnabled) {
                            dot.classList.add('active');
                        } else {
                            dot.classList.remove('active');
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to check maintenance status:', error);
            }
        }
        
        // Handle booking button click
        function handleBookingClick(event, targetUrl) {
            if (bookingMaintenanceEnabled) {
                event.preventDefault();
                document.getElementById('maintenanceMessage').textContent = bookingMaintenanceMessage;
                document.getElementById('maintenanceModal').style.display = 'flex';
                return false;
            }
            return true;
        }
        
        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').style.display = 'none';
        }
        
        // Close modal on overlay click
        document.getElementById('maintenanceModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMaintenanceModal();
            }
        });
        
        // Check status on page load
        document.addEventListener('DOMContentLoaded', checkBookingMaintenanceStatus);
    </script>
</body>
</html>
