<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">

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

    <!-- Google Fonts for the landing page -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Preload critical images for LCP -->
    <link rel="preload" as="image" href="{{ asset('cover.jpg') }}">
    <link rel="preload" as="image" href="{{ asset('logo flower.png') }}">
    
    @include('partials.nav-footer-styles')
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .hero {
            margin-top: 88px;
            padding: 8rem 2rem 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent !important;
            background-color: transparent !important;
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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 30px;
            padding: 4rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .hero-left {
            text-align: left;
        }

        .hero-right {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .hero-image-container {
            position: relative;
            height: 350px;
            width:610px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hero-image-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .hero-cover-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            transition: transform 0.5s ease;
        }

        .hero-image-container:hover .hero-cover-image {
            transform: scale(1.05);
        }

        .hero-social-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: nowrap;
            justify-content: center;
        }

        .hero-social-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            margin: 0;
            font-weight: 500;
            letter-spacing: 1px;
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
            background: rgba(15, 23, 42, 0.55);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.1rem;
            border: 1px solid rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
        }

        .hero-social-icon:hover {
            background: rgba(15, 23, 42, 0.75);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.28);
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
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        .about-feature-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            padding: 3rem 2rem;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
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
            transform: translateY(-12px) scale(1.03);
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
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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

        .about-feature-card:hover .about-feature-icon {
            transform: scale(1.15) rotate(5deg);
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
            font-size: 2rem;
        }

        .about-feature-content {
            position: relative;
            z-index: 1;
        }

        .about-feature-content h3 {
            font-size: 1.5rem;
            margin: 0 0 1rem 0;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .about-feature-content p {
            color: #64748b;
            margin: 0;
            line-height: 1.7;
            font-size: 1rem;
            font-weight: 400;
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== SCROLL ANIMATION CLASSES ===== */
        /* Fade In Animation */
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Slide Up Animation */
        .animate-slide-up {
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Slide In From Left Animation */
        .animate-slide-left {
            animation: slideLeft 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Slide In From Right Animation */
        .animate-slide-right {
            animation: slideRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Scale Animation */
        .animate-scale {
            animation: scaleIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Bounce Animation for hover effects */
        .animate-bounce:hover {
            animation: bounce 0.6s ease-in-out;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Pulse Animation */
        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
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

        /* Smooth transitions for all hero elements */
        .hero * {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .trademark {
            font-size: 0.6em;
            vertical-align: super;
        }

        .hero .tagline {
            font-size: 2.2rem;
            color: #1e40af;
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.9), -1px -1px 0 rgba(255, 255, 255, 0.8);
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
        }

        .hero p {
            font-size: 1.25rem;
            color: #334155;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            font-weight: 500;
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.9);
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
        }

        .hero-trust-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.8s both;
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

        .btn-primary, .btn-secondary {
            padding: 1.1rem 3rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.15rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .btn-primary::before,
        .btn-secondary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:hover::before,
        .btn-secondary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: white;
            color: #1e40af;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            background: #f0f9ff;
        }

        .btn-primary:active {
            transform: translateY(-2px) scale(0.98);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid #f97316;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            transform: translateY(-4px) scale(1.02);
            border-color: #ea580c;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.4);
        }

        .btn-secondary:active {
            transform: translateY(-2px) scale(0.98);
        }

        section {
            padding: 5rem 2rem;
        }

        /* Unique Section Styling */
        #features {
            padding: 7rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
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
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        #about-section {
            padding: 8rem 2rem;
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
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Unique Container Widths */
        #features .container {
            max-width: 1400px;
        }

        #how-it-works .container {
            max-width: 1300px;
            position: relative;
            z-index: 1;
        }

        #services .container {
            max-width: 1400px;
        }

        #locations .container {
            max-width: 1600px;
        }

        #requirements .container {
            max-width: 1300px;
            position: relative;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
        }

        /* Unique Section Headers */
        #how-it-works .section-header {
            margin-bottom: 5rem;
        }

        #how-it-works .section-header h2 {
            color: #1e40af;
        }

        #how-it-works .section-header p {
            color: #64748b;
        }

        #services .section-header {
            margin-bottom: 5rem;
        }

        #locations .section-header {
            margin-bottom: 4.5rem;
        }

        #requirements .section-header {
            margin-bottom: 5rem;
        }

        #requirements .section-header h2 {
            color: #1e40af;
        }

        #requirements .section-header p {
            color: #64748b;
        }

        .section-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            position: relative;
            display: inline-block;
        }
        


        .section-header p {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto 2rem;
            font-weight: 400;
        }

        .section-header::after {
            content: '';
            display: block;
            width: 600px;
            max-width: 90%;
            height: 4px;
            background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #1e40af 50%, #3b82f6 80%, transparent 100%);
            margin: 0 auto;
            border-radius: 2px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            position: relative;
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            min-height: 380px;
            border: 2px solid transparent;
        }

        #features .feature-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        #features .feature-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 25px 60px rgba(59, 130, 246, 0.25);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.2);
        }

        .feature-card:hover .feature-bg {
            transform: scale(1.1);
            opacity: 0.35;
        }

        .feature-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0.25;
            transition: all 0.4s ease;
            z-index: 0;
        }

        .feature-content {
            position: relative;
            z-index: 1;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #f97316;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            color: #1e40af;
            margin-bottom: 1rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .feature-card p {
            color: #64748b;
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
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
        }

        #how-it-works .step:hover {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-color: #3b82f6;
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.25);
        }

        .step {
            text-align: center;
            position: relative;
            padding: 2rem 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(30, 58, 138, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(249, 115, 22, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.5s ease;
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
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.2);
        }

        .step::after {
            content: '→';
            position: absolute;
            right: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: #f97316;
            font-weight: 700;
        }

        .step:last-child::after {
            display: none;
        }

        .step-number {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        .step-number::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            transform: translate(-50%, -50%) scale(0);
            opacity: 0.5;
            transition: transform 0.5s ease;
        }

        .step:hover .step-number::before {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }

        .step:hover .step-number {
            transform: scale(1.15) rotate(360deg);
            box-shadow: 0 15px 45px rgba(249, 115, 22, 0.5);
        }

        .step h3 {
            font-size: 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            font-weight: 600;
            letter-spacing: -0.01em;
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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

        #locations .location-card {
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
        }

        .location-card {
            background: white;
            padding: 2.5rem;
            border-radius: 0;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.12);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
            transition: all 0.5s ease;
        }

        .location-card:hover::after {
            opacity: 0.85;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.75) 100%);
        }

        .location-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #3b82f6 0%, #f97316 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
        }

        .location-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 30px 70px rgba(59, 130, 246, 0.3);
            border-color: #3b82f6;
        }

        .location-card:hover::before {
            transform: scaleX(1);
        }

        .location-card > * {
            position: relative;
            z-index: 1;
        }

        .location-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            color: white;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            transition: all 0.4s ease;
        }

        .location-card:hover .location-card-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.4);
        }

        .location-card h4 {
            font-size: 1.65rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .location-card p {
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.8;
            margin-bottom: auto;
            font-size: 1.05rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        }

        .location-card-link {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            position: relative;
            z-index: 2;
            margin-top: auto;
        }

        .location-card-link:hover {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            transform: translateX(5px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
            gap: 1rem;
        }

        .location-card-link i {
            transition: transform 0.3s ease;
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
            transform: scale(1.1) rotate(5deg);
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

        .service-item {
            position: relative;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.3);
        }

        .service-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 0.4s ease;
        }

        .service-item:hover .service-bg {
            transform: scale(1.1);
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.7));
            transition: background 0.4s ease;
        }

        .service-item:hover .service-overlay {
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.8));
        }

        .service-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2rem;
            color: white;
        }

        .service-item h4 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: white;
            letter-spacing: -0.01em;
        }

        .service-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            opacity: 0.7;
            transform: translateY(0);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
            padding: 0.75rem 2rem;
            background: white;
            color: #1e40af;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translateY(20px);
            align-self: flex-start;
        }

        .service-item:hover .book-now-btn {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.1s;
        }

        .book-now-btn:hover {
            background: linear-gradient(135deg, #f97316, #ea580c);
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

        footer {
            background: #0f172a;
            color: white;
            padding: 5rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
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
            border-radius: 10px;
        }

        .footer-brand p {
            color: #94a3b8;
            line-height: 1.7;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .footer-social {
            display: flex;
            gap: 1rem;
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
            transition: all 0.3s;
        }

        .social-icon:hover {
            background: #f97316;
            transform: translateY(-3px);
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
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.95rem;
        }

        .footer-section a:hover {
            color: #f97316;
        }

        .footer-location {
            display: flex;
            align-items: start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .footer-location i {
            color: #f97316;
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
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
            min-width: 200px;
        }

        .newsletter-input input::placeholder {
            color: #64748b;
        }

        .newsletter-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .newsletter-btn:hover {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            transform: translateY(-2px);
        }

        .footer-divider {
            max-width: 1400px;
            margin: 0 auto 2rem;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .footer-bottom {
            max-width: 1400px;
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
            transition: color 0.3s;
        }

        .footer-bottom-links a:hover {
            color: #f97316;
        }

        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .scale-in {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .scale-in.visible {
            opacity: 1;
            transform: scale(1);
        }

        /* Mobile First - Base styles for mobile devices */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }

            .hero {
                margin-top: 70px;
                padding: 2rem 1rem;
                min-height: auto;
                position: relative;
            }

            /* Show background images on mobile - single image behind content */
            .hero-bg-images {
                display: block !important;
            }
            
            .hero-bg-slice {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                transform: none;
                margin: 0;
            }
            
            .hero-bg-slice:nth-child(1) {
                display: block;
            }
            
            .hero-bg-slice:nth-child(2),
            .hero-bg-slice:nth-child(3) {
                display: none;
            }

            .hero::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(30, 58, 138, 0.7) 0%, rgba(59, 130, 246, 0.5) 100%);
                z-index: 1;
            }

            .hero::after {
                display: none;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem 1.5rem;
                border-radius: 24px;
                position: relative;
                z-index: 2;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
                margin-top: 1rem;
            }

            .hero-left {
                text-align: center;
            }

            .hero h1 {
                font-size: 2rem;
                line-height: 1.2;
                margin-bottom: 0.75rem;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            }

            .hero .tagline {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .hero p {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
                line-height: 1.6;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                padding: 1rem 2rem;
                font-size: 0.95rem;
                text-align: center;
            }

            .hero-trust-badges {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .trust-badge {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            /* Hide hero image on very small screens */
            .hero-right {
                display: none !important;
            }

            .hero-image-container {
                height: 220px !important;
                border-radius: 16px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            }

            .hero-cover-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            .hero-social-container {
                margin-top: 0 !important;
                flex-direction: column;
                gap: 0.75rem;
                align-items: center;
            }

            .hero-social-text {
                font-size: 0.7rem !important;
            }

            .hero-social-icon {
                width: 38px !important;
                height: 38px !important;
                font-size: 0.95rem !important;
            }

            section {
                padding: 2.5rem 1rem;
            }

            .container {
                padding: 0 1rem;
            }

            .section-header {
                margin-bottom: 2.5rem;
            }

            .section-header h2 {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            .section-header p {
                font-size: 0.95rem;
                margin-bottom: 1.5rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .feature-card {
                padding: 1.5rem;
                min-height: auto;
            }

            .feature-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }

            .feature-card h3 {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }

            .feature-card p {
                font-size: 0.9rem;
                line-height: 1.6;
            }

            /* Steps Section - Mobile Layout (2x2 grid) */
            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
                margin-top: 2.5rem;
                position: relative;
            }

            .steps-container::before {
                display: none;
            }

            .step {
                padding: 2rem 1.25rem;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border-radius: 20px;
                border: 2px solid #e2e8f0;
                box-shadow: 0 8px 24px rgba(30, 58, 138, 0.1), 
                            0 2px 8px rgba(0, 0, 0, 0.05);
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
                text-align: center;
                z-index: 1;
                transition: all 0.3s ease;
            }

            .step:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 32px rgba(59, 130, 246, 0.15),
                            0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #3b82f6;
            }

            .step::after {
                display: none;
            }

            .step-number {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin: 0 auto 0.5rem;
                flex-shrink: 0;
                position: relative;
                z-index: 2;
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4),
                            0 4px 12px rgba(249, 115, 22, 0.2);
            }

            .step-content {
                flex: 1;
                width: 100%;
            }

            .step h3 {
                font-size: 1.15rem;
                margin-bottom: 0.75rem;
                line-height: 1.4;
                font-weight: 700;
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .step p {
                font-size: 0.875rem;
                line-height: 1.6;
                margin: 0;
                color: #475569;
                font-weight: 400;
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
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stat-item {
                padding: 1.5rem;
            }

            .stat-item h3 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
            }

            .stat-item p {
                font-size: 0.95rem;
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
            }

font-size: 1.1rem !important;
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

        }

        /* Small tablets and large phones */
        @media (min-width: 481px) and (max-width: 768px) {
            .hero {
                margin-top: 72px;
                padding: 3rem 1.5rem;
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


            /* About section tablet */
            #about-section > div {
                grid-template-columns: 1fr !important;
                gap: 3rem !important;
            }
        }

        /* Standard mobile breakpoint (kept for backward compatibility) */
        @media (max-width: 768px) {
            .hero {
                padding: 3rem 1.5rem;
            }
            
            /* Show single background image on tablet/mobile */
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
            
            /* Add overlay for readability */
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

            .hero-content {
                grid-template-columns: 1fr;
                gap: 2.5rem;
                padding: 2rem;
                background: rgba(255, 255, 255, 0.95);
                position: relative;
                z-index: 2;
            }

            .hero-left {
                text-align: center;
            }

            /* Hide hero image on mobile phones */
            .hero-right {
                display: none !important;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-trust-badges {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .tagline {
                font-size: 1.25rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
                padding: 0.875rem 2rem;
                font-size: 1rem;
            }

            section {
                padding: 3rem 1.5rem;
            }

            .section-header h2 {
                font-size: 2rem;
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
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }

            .step::after {
                display: none;
            }

            .step {
                padding: 2rem 1.25rem;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border: 2px solid #e2e8f0;
                box-shadow: 0 8px 24px rgba(30, 58, 138, 0.1);
            }

            .step:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 32px rgba(59, 130, 246, 0.15);
                border-color: #3b82f6;
            }

            .step-number {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin: 0 auto 0.5rem;
                box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
            }

            .step h3 {
                font-size: 1.15rem;
                margin-bottom: 0.75rem;
                font-weight: 700;
            }

            .step p {
                font-size: 0.875rem;
                line-height: 1.6;
                color: #475569;
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
                grid-template-columns: repeat(3, 1fr);
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
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        #loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loading-logo {
            animation: pulse 1.5s ease-in-out infinite;
        }

        .loading-logo img {
            height: 300px;
            width: auto;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

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
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div id="loading-screen">
        <div class="loading-logo">
            <img src="{{ asset('logo.png') }}" alt="CAS Private Care LLC Logo" width="300" height="300">
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
                
                <div style="position: relative; display: flex; justify-content: center; margin: 2rem 0; background: rgba(30, 64, 175, 0.15); padding: 0.5rem; border-radius: 50px; backdrop-filter: blur(10px); border: 1px solid rgba(30, 64, 175, 0.3); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div id="slider-bg" style="position: absolute; top: 0.5rem; left: 0.5rem; width: calc(50% - 0.5rem); height: calc(100% - 1rem); background: white; border-radius: 25px; transition: transform 0.3s ease; z-index: 1; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"></div>
                    <button onclick="switchService('caregiver')" id="btn-caregiver" style="position: relative; z-index: 2; padding: 0.75rem 1.5rem; border: none; border-radius: 25px; background: transparent; color: #1e40af; font-weight: 600; cursor: pointer; transition: color 0.3s; flex: 1;">Caregiver</button>
                    <button onclick="switchService('housekeeping')" id="btn-housekeeping" style="position: relative; z-index: 2; padding: 0.75rem 1.5rem; border: none; border-radius: 25px; background: transparent; color: #1e40af; font-weight: 600; cursor: pointer; transition: color 0.3s; flex: 1;">Housekeeping</button>
                </div>
                
                <p id="hero-description" style="transition: opacity 0.5s ease;">A modern and trustworthy platform connecting families with verified caregivers and housekeepers. Background-checked professionals ready to support your family across all NYC boroughs.</p>
                
                <div class="hero-buttons">
                    <a href="{{ url('/register') }}" class="btn-primary" id="find-btn" style="transition: opacity 0.5s ease;">Find a Caregiver</a>
                    <a href="{{ url('/register') }}" class="btn-secondary">Become a Partner</a>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-image-container">
                    <img src="{{ asset('cover.jpg') }}" alt="CAS Private Care LLC Cover" class="hero-cover-image">
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
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div class="fade-in" style="position: relative;">
                <video autoplay loop muted playsinline preload="auto" style="width: 100%; height: 500px; object-fit: cover; border-radius: 20px; box-shadow: 0 20px 60px rgba(59, 130, 246, 0.2);">
                    <source src="{{ asset('what.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div style="position: absolute; bottom: -20px; right: -20px; background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <i class="bi bi-shield-check-fill" style="font-size: 3rem; color: #3b82f6;" aria-hidden="true"></i>
                        <div>
                            <h4 style="font-size: 1.5rem; color: #1e40af; margin: 0;">100%</h4>
                            <p style="color: #64748b; margin: 0; font-size: 0.9rem;">Verified</p>
                        </div>
                    </div>
                </div>
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
                <h2 style="font-size: 3rem; font-weight: 700; margin-bottom: 1rem;">
                    <span style="color: #f97316;">Meet</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Our Founder</span>
                </h2>
            </div>
            <div class="ceo-content" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 4rem; align-items: center; background: white; padding: 3rem; border-radius: 0; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1); border: 3px solid #3b82f6;">
                <div class="fade-in" style="text-align: center;">
                    <div style="position: relative; display: inline-block;">
                        <img src="{{ asset('CEO.jpg') }}" alt="Charles Andrew Santiago - CEO and Founder of CAS Private Care LLC" style="width: 100%; max-width: 700px; height: auto; border-radius: 20px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);" loading="lazy" decoding="async">
                        <div style="position: absolute; bottom: -15px; right: -15px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); padding: 1rem 1.5rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);">
                            <i class="bi bi-award-fill" style="font-size: 2rem; color: white;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
                <div class="fade-in">
                    <div style="margin-bottom: 2rem;">
                        <h3 style="font-size: 1.5rem; color: #1e293b; margin: 0 0 0.5rem 0; font-weight: 600;">CAS PRIVATE CARE LLC</h3>
                        <p style="color: #64748b; margin: 0; font-size: 0.9rem;">Professional Caregiving Services</p>
                    </div>
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b;">
                        <span style="color: #f97316;">Charles Andrew</span> <span style="color: #1e40af;">Santiago</span>
                    </h2>
                    <p style="font-size: 1.3rem; color: #3b82f6; font-weight: 600; margin-bottom: 1.5rem;">CEO / Founder</p>
                    <p style="font-size: 1.1rem; color: #64748b; line-height: 1.8; margin-bottom: 0;">
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
                        <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url">Book Now</a>
                    </div>
                </article>


                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">House Helpers</h4>
                        <p class="service-description" itemprop="description">Reliable household assistance for a cleaner home. Professional helpers who manage cleaning, laundry, and household organization efficiently.</p>
                        <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url">Book Now</a>
                    </div>
                </article>
                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Special Needs Care</h4>
                        <p class="service-description" itemprop="description">Specialized care for individuals with unique requirements. Trained professionals who provide personalized support with patience and expertise.</p>
                        <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url">Book Now</a>
                    </div>
                </article>

                <article class="service-item scale-in" itemscope itemtype="https://schema.org/Service">
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Deep Cleaning</h4>
                        <p class="service-description" itemprop="description">Thorough housekeeping for kitchens, bathrooms, and high-touch areas. Perfect for move-in/move-out and seasonal refreshes.</p>
                        <a href="{{ url('/register') }}" class="book-now-btn" itemprop="url">Book Now</a>
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
                    <a href="{{ url('/register?role=client&borough=manhattan') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.95); color: #1e40af; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease;">
                        Browse Manhattan <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1505843513577-22bb7d21e455?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h4>Brooklyn Partners</h4>
                    <p>Trusted caregivers and housekeepers serving all Brooklyn neighborhoods. From Park Slope to Brighton Beach.</p>
                    <a href="{{ url('/register?role=client&borough=brooklyn') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.95); color: #1e40af; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease;">
                        Browse Brooklyn <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1500916434205-0c77489c6cf7?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <h4>Queens Partners</h4>
                    <p>Reliable caregivers and housekeepers across Queens, including Astoria, Flushing, and Jamaica.</p>
                    <a href="{{ url('/register?role=client&borough=queens') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.95); color: #1e40af; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease;">
                        Browse Queens <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://images.unsplash.com/photo-1514565131-fce0801e5785?w=800&q=80');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <h4>Bronx Partners</h4>
                    <p>Professional caregivers and housekeepers serving the Bronx communities. Specialized elderly care services available.</p>
                    <a href="{{ url('/register?role=client&borough=bronx') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.95); color: #1e40af; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease;">
                        Browse Bronx <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="location-card fade-in" style="background-image: url('https://www.nyhabitat.com/blog/wp-content/uploads/2014/09/New-york-nyc-borough-staten-island-ferry-manhattan-skyline.jpg');">
                    <div class="location-card-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h4>Staten Island Partners</h4>
                    <p>Dedicated caregivers and housekeepers for Staten Island residents. Personalized connections for your family.</p>
                    <a href="{{ url('/register?role=client&borough=staten-island') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.95); color: #1e40af; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease;">
                        Browse Staten Island <i class="bi bi-arrow-right"></i>
                    </a>
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

    <section class="section-light" id="how-it-works" itemscope itemtype="https://schema.org/HowTo">
        <div class="container">
            <div class="section-header fade-in">
                <h2 itemprop="name"><span style="color: #f97316;">How</span> CAS Private Care LLC Works</h2>
                <p>Simple, fast, and secure — connecting care in four easy steps</p>
            </div>
            <div class="steps-container">
                <div class="step fade-in">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3><span style="color: #f97316;">Browse</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">& Select</span></h3>
                        <p>Clients search for caregivers or nannies, review their profiles, credentials, and ratings to find the perfect match.</p>
                    </div>
                </div>
                <div class="step fade-in">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3><span style="color: #f97316;">Book</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">& Schedule</span></h3>
                        <p>Choose your preferred schedule and book instantly. Payments are processed securely through our platform.</p>
                    </div>
                </div>
                <div class="step fade-in">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3><span style="color: #f97316;">Connect</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">& Care</span></h3>
                        <p>Partners and contractors receive bookings, connect with families, and deliver exceptional services while building their reputation.</p>
                    </div>
                </div>
                <div class="step fade-in">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3><span style="color: #f97316;">Rate</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">& Review</span></h3>
                        <p>Share your experience and help others make informed decisions. Build trust within the community.</p>
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

    <!-- Reviews and Testimonials Section (Restyled) -->
    <section class="section-light" style="padding: 6.5rem 2rem; position: relative; overflow: hidden;">
        <div aria-hidden="true" style="position: absolute; top: -180px; right: -220px; width: 520px; height: 520px; background: radial-gradient(circle, rgba(59, 130, 246, 0.14) 0%, transparent 62%); border-radius: 50%;"></div>
        <div aria-hidden="true" style="position: absolute; bottom: -220px; left: -240px; width: 560px; height: 560px; background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 62%); border-radius: 50%;"></div>
        <div class="container" style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">
            <div class="section-header fade-in" style="text-align: center; margin-bottom: 3.25rem;">
                <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border-radius: 999px; border: 1px solid rgba(59, 130, 246, 0.25); background: rgba(59, 130, 246, 0.10); color: #1e3a8a; font-weight: 900; font-size: 0.9rem;">
                        <i class="bi bi-patch-check-fill" style="color: #3b82f6;"></i>
                        Verified reviews
                    </span>
                </div>
                <h2 style="font-size: 3rem; font-weight: 900; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                    <span style="color: #f97316;">Reviews</span>
                    <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">From New York Families</span>
                </h2>
                <p style="font-size: 1.12rem; color: #64748b; max-width: 900px; margin: 0 auto; line-height: 1.7;">Trusted caregivers and housekeepers serving NYC with 5-star ratings</p>
            </div>

            <!-- Rating Highlights (No Personal Assistants) -->
            <div class="rating-highlights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.25rem; margin-bottom: 3.5rem;">
                <div class="fade-in" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.06) 100%); padding: 2rem; border-radius: 18px; text-align: left; border: 1px solid rgba(16, 185, 129, 0.22); box-shadow: 0 14px 40px rgba(16, 185, 129, 0.10);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #065f46;">
                            <i class="bi bi-heart-pulse-fill" style="color: #10b981; font-size: 1.1rem;"></i>
                            Caregivers
                        </div>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.6rem; border-radius: 999px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.22); color: #065f46; font-weight: 900; font-size: 0.85rem;">
                            <span style="color: #fbbf24;">★</span> 4.9/5.0
                        </span>
                    </div>
                    <div style="color: #0f172a; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.15rem;">Caregivers in NYC</div>
                    <div style="color: #64748b; font-size: 0.95rem;">1,200+ verified reviews</div>
                </div>

                <div class="fade-in" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0.06) 100%); padding: 2rem; border-radius: 18px; text-align: left; border: 1px solid rgba(59, 130, 246, 0.22); box-shadow: 0 14px 40px rgba(59, 130, 246, 0.11);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #1e3a8a;">
                            <i class="bi bi-house-heart-fill" style="color: #3b82f6; font-size: 1.1rem;"></i>
                            Housekeepers
                        </div>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.6rem; border-radius: 999px; background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.22); color: #1e3a8a; font-weight: 900; font-size: 0.85rem;">
                            <span style="color: #fbbf24;">★</span> 4.9/5.0
                        </span>
                    </div>
                    <div style="color: #0f172a; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.15rem;">Housekeepers in NY</div>
                    <div style="color: #64748b; font-size: 0.95rem;">850+ verified reviews</div>
                </div>

                <div class="fade-in" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.10) 0%, rgba(59, 130, 246, 0.06) 100%); padding: 2rem; border-radius: 18px; text-align: left; border: 1px solid rgba(15, 23, 42, 0.10); box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #0f172a;">
                            <i class="bi bi-star-fill" style="color: #fbbf24; font-size: 1.05rem;"></i>
                            Top Rated
                        </div>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.6rem; border-radius: 999px; background: rgba(15, 23, 42, 0.06); border: 1px solid rgba(15, 23, 42, 0.10); color: #0f172a; font-weight: 900; font-size: 0.85rem;">
                            <span style="color: #fbbf24;">★</span> 5-star average
                        </span>
                    </div>
                    <div style="color: #0f172a; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.15rem;">Verified & Reliable</div>
                    <div style="color: #64748b; font-size: 0.95rem;">Trusted across all five boroughs</div>
                </div>
            </div>

            <!-- Detailed Reviews Grid -->
            <div class="reviews-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                <!-- Caregiver Review 1 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(16, 185, 129, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <!-- Verified Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(16, 185, 129, 0.12); color: #065f46; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #10b981;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(16, 185, 129, 0.25); outline: 3px solid rgba(16, 185, 129, 0.10);">EM</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Elena Martinez</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Caregiver • Manhattan, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-shield-check" style="color: #10b981;"></i>
                            Trusted partner
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Elena has been caring for my 82-year-old mother for 6 months. She's patient, compassionate, and incredibly professional. My mother looks forward to her visits every day. Best caregiver in Manhattan!"</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- Margaret W.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Upper West Side</span>
                    </div>
                </div>

                <!-- Housekeeper Review 1 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(59, 130, 246, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <!-- Verified Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(59, 130, 246, 0.12); color: #1e3a8a; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(59, 130, 246, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #3b82f6;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(59, 130, 246, 0.22); outline: 3px solid rgba(59, 130, 246, 0.10);">CT</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Carmen Torres</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Housekeeper • Brooklyn, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-stars" style="color: #3b82f6;"></i>
                            5-star cleaning
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Carmen is simply the best housekeeper in Brooklyn! Thorough, reliable, and always leaves our home spotless. She's been with us for over a year and we couldn't be happier."</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- James & Lisa P.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #3b82f6;"></i> Park Slope</span>
                    </div>
                </div>

                <!-- Caregiver Review 3 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(16, 185, 129, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <!-- Verified Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(16, 185, 129, 0.12); color: #065f46; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #10b981;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(16, 185, 129, 0.25); outline: 3px solid rgba(16, 185, 129, 0.10);">AA</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Aisha Ahmed</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Caregiver • Queens, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-heart-pulse" style="color: #10b981;"></i>
                            Caring support
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Aisha is punctual, warm, and incredibly attentive. She helped us set a steady routine and brought so much peace to our home. We felt supported from day one."</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- Rachel K.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Long Island City</span>
                    </div>
                </div>

                <!-- Caregiver Review 2 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(16, 185, 129, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <!-- Verified Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(16, 185, 129, 0.12); color: #065f46; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(16, 185, 129, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #10b981;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(16, 185, 129, 0.25); outline: 3px solid rgba(16, 185, 129, 0.10);">RJ</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Robert Johnson</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Caregiver • Bronx, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-shield-check" style="color: #10b981;"></i>
                            Patient & steady
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Robert has been wonderful with my elderly father who has dementia. He's patient, kind, and skilled at managing challenging situations. We're so grateful to have found such a caring caregiver in the Bronx."</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- Thomas & Anna D.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #10b981;"></i> Riverdale</span>
                    </div>
                </div>

                <!-- Housekeeper Review 2 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(59, 130, 246, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <!-- Verified Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(59, 130, 246, 0.12); color: #1e3a8a; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(59, 130, 246, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #3b82f6;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(59, 130, 246, 0.22); outline: 3px solid rgba(59, 130, 246, 0.10);">MS</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Maria Silva</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Housekeeper • Staten Island, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-stars" style="color: #3b82f6;"></i>
                            Detail-focused
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Maria is absolutely fantastic! She's been cleaning our home weekly for 8 months. Professional, trustworthy, and pays attention to every detail. Best housekeeper in Staten Island by far!"</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- Kevin & Nicole B.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #3b82f6;"></i> Great Kills</span>
                    </div>
                </div>

                <!-- Housekeeper Review 3 -->
                <div class="fade-in" style="background: rgba(255, 255, 255, 0.92); padding: 2.1rem; border-radius: 18px; box-shadow: 0 18px 55px rgba(15, 23, 42, 0.08); border: 1px solid rgba(59, 130, 246, 0.18); position: relative; transition: transform .25s ease, box-shadow .25s ease;">
                    <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(59, 130, 246, 0.12); color: #1e3a8a; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(59, 130, 246, 0.22);">
                        <i class="bi bi-patch-check-fill" style="color: #3b82f6;"></i>
                        Verified
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 58px; height: 58px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.25rem; box-shadow: 0 12px 30px rgba(59, 130, 246, 0.22); outline: 3px solid rgba(59, 130, 246, 0.10);">DL</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.12rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Diana Lopez</h4>
                            <p style="color: #64748b; margin: 0.2rem 0 0; font-size: 0.88rem;">Housekeeper • Manhattan, NY</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem;">
                        <div style="color: #fbbf24; font-size: 1.05rem; letter-spacing: 1px;">★★★★★</div>
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="bi bi-stars" style="color: #3b82f6;"></i>
                            Consistent results
                        </div>
                    </div>
                    <p style="color: #334155; line-height: 1.75; font-size: 0.98rem; margin-bottom: 1.05rem;">"Diana is consistent and detail-oriented. She leaves our apartment spotless and always checks in about priorities. The best housekeeper we've worked with in Manhattan."</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; color: #64748b; padding-top: 0.9rem; border-top: 1px solid rgba(15, 23, 42, 0.08);">
                        <span style="font-weight: 900; color: #0f172a;">- Daniel M.</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><i class="bi bi-geo-alt-fill" style="color: #3b82f6;"></i> Midtown East</span>
                    </div>
                </div>
            </div>

            <!-- SEO-Optimized Location Summary (Restyled) -->
            <div class="fade-in" style="position: relative; background: radial-gradient(1200px 500px at 50% 0%, rgba(59, 130, 246, 0.12) 0%, rgba(16, 185, 129, 0.08) 30%, rgba(249, 115, 22, 0.06) 55%, transparent 75%), linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); padding: 4rem 3rem; border-radius: 28px; margin-top: 4rem; border: 1px solid rgba(17, 24, 39, 0.08); box-shadow: 0 24px 80px rgba(0, 0, 0, 0.10); overflow: hidden;">
                <!-- subtle highlights -->
                <div aria-hidden="true" style="position: absolute; top: -120px; right: -140px; width: 420px; height: 420px; background: radial-gradient(circle, rgba(59, 130, 246, 0.16) 0%, transparent 60%); border-radius: 50%; filter: blur(0px);"></div>
                <div aria-hidden="true" style="position: absolute; bottom: -160px; left: -160px; width: 460px; height: 460px; background: radial-gradient(circle, rgba(16, 185, 129, 0.14) 0%, transparent 62%); border-radius: 50%;"></div>

                <!-- Service Type Pills -->
                <div style="position: relative; z-index: 1; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1.75rem;">
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 0.9rem; border-radius: 999px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #065f46; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.2px;">
                        <span style="width: 34px; height: 34px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);">
                            <i class="bi bi-heart-pulse-fill" style="color: #ffffff; font-size: 1.05rem;"></i>
                        </span>
                        Caregivers
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 0.9rem; border-radius: 999px; background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.25); color: #1e3a8a; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.2px;">
                        <span style="width: 34px; height: 34px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.22);">
                            <i class="bi bi-house-heart-fill" style="color: #ffffff; font-size: 1.05rem;"></i>
                        </span>
                        Housekeepers
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 0.9rem; border-radius: 999px; background: rgba(15, 23, 42, 0.06); border: 1px solid rgba(15, 23, 42, 0.12); color: #0f172a; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.2px;">
                        <span style="width: 34px; height: 34px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #334155 100%); box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);">
                            <i class="bi bi-stars" style="color: #ffffff; font-size: 1.05rem;"></i>
                        </span>
                        Top Rated
                    </span>
                </div>

                <h3 style="position: relative; z-index: 1; font-size: 2.2rem; font-weight: 900; color: #0f172a; margin: 0 0 0.6rem; text-align: center; line-height: 1.2; letter-spacing: -0.02em;">
                    Across NYC
                </h3>

                <p style="position: relative; z-index: 1; max-width: 850px; margin: 0 auto 2.5rem; text-align: center; font-size: 1.05rem; line-height: 1.7; color: #475569;">
                    Why New York Families Choose <span style="font-weight: 900; color: #111827;">CAS Private Care</span>
                </p>

                <!-- Content + Quick Benefits -->
                <div style="position: relative; z-index: 1; max-width: 980px; margin: 0 auto; background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 18px; border: 1px solid rgba(17, 24, 39, 0.08); box-shadow: 0 16px 45px rgba(0, 0, 0, 0.08);">
                    <div class="across-nyc-grid" style="display: grid; grid-template-columns: 1.35fr 0.65fr; gap: 1.5rem; align-items: start;">
                        <div>
                            <p style="color: #0f172a; line-height: 1.9; font-size: 1.05rem; margin: 0 0 1.25rem;">
                                CAS Private Care connects New York families with <strong style="color: #10b981;">verified caregivers in Manhattan, Brooklyn, Queens, Bronx, and Staten Island</strong>. Our platform features <strong style="color: #3b82f6;">professional housekeepers in New York</strong> providing deep cleaning and household management.
                            </p>

                            <div style="height: 1px; background: linear-gradient(90deg, transparent 0%, rgba(148, 163, 184, 0.55) 50%, transparent 100%); margin: 1.25rem 0;"></div>

                            <p style="color: #0f172a; line-height: 1.9; font-size: 1.05rem; margin: 0;">
                                All contractors are background-checked, highly rated with verified reviews, and trusted by over <strong style="color: #3b82f6;">2,500 New York families</strong>. Whether you need a <strong style="color: #10b981;">caregiver in Manhattan</strong>, a <strong style="color: #3b82f6;">housekeeper in Brooklyn</strong>, or trusted help in any borough, we connect you with the best professionals in New York.
                            </p>
                        </div>

                        <div style="display: grid; gap: 0.85rem;">
                            <div style="padding: 1rem 1rem; border-radius: 14px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.10) 0%, rgba(59, 130, 246, 0.04) 60%, rgba(255, 255, 255, 0.6) 100%); border: 1px solid rgba(59, 130, 246, 0.18);">
                                <div style="display: flex; align-items: center; gap: 0.65rem; font-weight: 900; color: #0f172a; font-size: 0.98rem; margin-bottom: 0.35rem;">
                                    <i class="bi bi-shield-check" style="color: #3b82f6;"></i>
                                    Verified & Background-Checked
                                </div>
                                <div style="color: #475569; font-size: 0.92rem; line-height: 1.55;">Choose partners with verified profiles and reviews.</div>
                            </div>

                            <div style="padding: 1rem 1rem; border-radius: 14px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.10) 0%, rgba(16, 185, 129, 0.04) 60%, rgba(255, 255, 255, 0.6) 100%); border: 1px solid rgba(16, 185, 129, 0.18);">
                                <div style="display: flex; align-items: center; gap: 0.65rem; font-weight: 900; color: #0f172a; font-size: 0.98rem; margin-bottom: 0.35rem;">
                                    <i class="bi bi-star-fill" style="color: #10b981;"></i>
                                    Highly Rated Partners
                                </div>
                                <div style="color: #475569; font-size: 0.92rem; line-height: 1.55;">Find top-rated help across all five boroughs.</div>
                            </div>

                            <div style="padding: 1rem 1rem; border-radius: 14px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.10) 0%, rgba(249, 115, 22, 0.04) 60%, rgba(255, 255, 255, 0.6) 100%); border: 1px solid rgba(249, 115, 22, 0.18);">
                                <div style="display: flex; align-items: center; gap: 0.65rem; font-weight: 900; color: #0f172a; font-size: 0.98rem; margin-bottom: 0.35rem;">
                                    <i class="bi bi-geo-alt" style="color: #f97316;"></i>
                                    Borough Coverage
                                </div>
                                <div style="color: #475569; font-size: 0.92rem; line-height: 1.55;">Manhattan, Brooklyn, Queens, Bronx, Staten Island.</div>
                            </div>
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

    <section class="section-dark" style="padding: 7rem 2rem; position: relative; overflow: hidden;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">
            <div class="section-header fade-in">
                <h2><span style="color: #f97316;">Ready</span> to Get Started?</h2>
                <p style="color: #64748b;">Join CAS Private Care LLC today and experience the future of caregiving services</p>
            </div>
            <div class="hero-buttons" style="margin-top: 3rem; justify-content: center;">
                <a href="{{ url('/register') }}" class="btn-primary">Sign Up Now</a>
                <a href="#how-it-works" class="btn-secondary">Learn More</a>
            </div>
        </div>
        <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        <div style="position: absolute; bottom: -100px; left: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    </section>

    </main>

    <!-- Desktop Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo" width="120" height="120">
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
        });
        
        let currentService = 0;
    const services = ['caregiver', 'housekeeping'];
        
        function switchService(type) {
            const description = document.getElementById('hero-description');
            const findBtn = document.getElementById('find-btn');
            const sliderBg = document.getElementById('slider-bg');
            const buttons = ['btn-caregiver', 'btn-housekeeping', 'btn-personal'];
            
            // Fade out
            description.style.transition = 'opacity 0.3s ease';
            findBtn.style.transition = 'opacity 0.3s ease';
            description.style.opacity = '0';
            findBtn.style.opacity = '0';
            
            buttons.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.color = 'white';
            });
            
            if (type === 'caregiver') {
                sliderBg.style.transform = 'translateX(0%)';
                document.getElementById('btn-caregiver').style.color = '#1e40af';
                currentService = 0;
            } else if (type === 'housekeeping') {
                sliderBg.style.transform = 'translateX(100%)';
                document.getElementById('btn-housekeeping').style.color = '#1e40af';
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
        <!-- Floating Chat Button -->
        <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Open FAQ Chat" title="Ask us anything">
            <i class="bi bi-chat-dots-fill"></i>
            <span class="chatbot-notification">1</span>
        </button>

        <!-- Chat Window -->
        <div id="chatbot-window" class="chatbot-window hidden">
            <div class="chatbot-header">
                <div class="chatbot-header-content">
                    <div class="chatbot-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="chatbot-header-text">
                        <h3>CAS Private Care Assistant</h3>
                        <p>We're here to help!</p>
                    </div>
                </div>
                <button id="chatbot-close" class="chatbot-close" aria-label="Close chat">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="chatbot-body" id="chatbot-body">
                <div class="chat-message bot-message">
                    <div class="message-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Hello! I'm here to help answer your questions about CAS Private Care. What would you like to know?</p>
                    </div>
                </div>

                <!-- Quick FAQ Options -->
                <div class="quick-questions">
                    <button class="quick-question-btn" data-question="How do I become a partner?">
                        How do I become a partner?
                    </button>
                    <button class="quick-question-btn" data-question="What types of partners do you have?">
                        What types of partners do you have?
                    </button>
                    <button class="quick-question-btn" data-question="How does the platform work?">
                        How does the platform work?
                    </button>
                    <button class="quick-question-btn" data-question="What are your rates?">
                        What are your rates?
                    </button>
                </div>
            </div>

            <div class="chatbot-footer">
                <div class="chatbot-input-container">
                    <input type="text" id="chatbot-input" placeholder="Type your question..." autocomplete="off">
                    <button id="chatbot-send" class="chatbot-send-btn" aria-label="Send message">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Chatbot Styles */
        #chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .chatbot-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(59, 130, 246, 0.6);
        }

        .chatbot-toggle:active {
            transform: scale(0.95);
        }

        .chatbot-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f97316;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: pulse-notification 2s infinite;
        }

        @keyframes pulse-notification {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chatbot-window.hidden {
            display: none;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chatbot-header-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chatbot-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .chatbot-header-text h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .chatbot-header-text p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .chatbot-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            font-size: 1rem;
        }

        .chatbot-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chatbot-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #f8fafc;
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

        .chat-message {
            display: flex;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
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
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .bot-message .message-avatar {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
        }

        .user-message .message-avatar {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .message-content {
            max-width: 75%;
            padding: 0.875rem 1rem;
            border-radius: 16px;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .bot-message .message-content {
            background: white;
            color: #1e293b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-bottom-left-radius: 4px;
        }

        .user-message .message-content {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-content p {
            margin: 0;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
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
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        .quick-questions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .quick-question-btn {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            color: #1e293b;
            font-weight: 500;
        }

        .quick-question-btn:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            color: #3b82f6;
            transform: translateX(4px);
        }

        .chatbot-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1rem;
            background: white;
        }

        .chatbot-input-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        #chatbot-input {
            flex: 1;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        #chatbot-input:focus {
            border-color: #3b82f6;
        }

        .chatbot-send-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1.1rem;
        }

        .chatbot-send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .chatbot-send-btn:active {
            transform: scale(0.95);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            #chatbot-container {
                bottom: 15px;
                right: 15px;
            }

            .chatbot-window {
                width: calc(100vw - 30px);
                height: calc(100vh - 100px);
                max-height: 600px;
                bottom: 75px;
                right: 0;
            }

            .chatbot-toggle {
                width: 56px;
                height: 56px;
                font-size: 1.35rem;
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
        const chatbotBody = document.getElementById('chatbot-body');
        const chatbotInput = document.getElementById('chatbot-input');
        const chatbotSend = document.getElementById('chatbot-send');
        const notificationBadge = document.querySelector('.chatbot-notification');

        let isChatOpen = false;

        // Toggle chatbot
        chatbotToggle.addEventListener('click', () => {
            if (isChatOpen) {
                closeChatbot();
            } else {
                openChatbot();
            }
        });

        chatbotClose.addEventListener('click', closeChatbot);

        function openChatbot() {
            chatbotWindow.classList.remove('hidden');
            isChatOpen = true;
            chatbotInput.focus();
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
        }

        function closeChatbot() {
            chatbotWindow.classList.add('hidden');
            isChatOpen = false;
        }

        // Send message function
        function sendMessage(message) {
            if (!message.trim()) return;

            // Add user message
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Show typing indicator
            const typingId = showTypingIndicator();

            // Process after delay (simulate thinking)
            setTimeout(() => {
                removeTypingIndicator(typingId);
                const answer = findAnswer(message);
                addMessage(answer, 'bot', true);
            }, 800 + Math.random() * 500);
        }

        function addMessage(text, sender, withTyping = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}-message`;

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = sender === 'bot' ? '<i class="bi bi-robot"></i>' : '<i class="bi bi-person-fill"></i>';

            const content = document.createElement('div');
            content.className = 'message-content';
            const p = document.createElement('p');
            p.textContent = text;
            content.appendChild(p);

            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);

            chatbotBody.appendChild(messageDiv);
            scrollToBottom();

            // Type out message with animation
            if (withTyping && sender === 'bot') {
                p.textContent = '';
                typeMessage(p, text);
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
            }, 20 + Math.random() * 10);
        }

        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chat-message bot-message typing-indicator-message';
            typingDiv.id = 'typing-indicator';

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = '<i class="bi bi-robot"></i>';

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

            typingDiv.appendChild(avatar);
            typingDiv.appendChild(content);
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
            console.log('Particles container:', particlesContainer);
            if (!particlesContainer) {
                console.error('Particles container not found!');
                return;
            }

            // Create 50 particles with variety
            const particleCount = 50;
            const particleTypes = ['', 'sparkle', 'blue-sparkle', 'glow'];
            
            console.log('Creating', particleCount, 'particles...');
            
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
            
            console.log('Created', particleCount, 'particles successfully!');
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
        const observerOptions = {
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
        }, observerOptions);

        // Create observer for slide-up animations
        const slideUpObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                    slideUpObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Create observer for slide-in-left animations
        const slideLeftObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-left');
                    slideLeftObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Create observer for slide-in-right animations
        const slideRightObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-right');
                    slideRightObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Create observer for scale animations
        const scaleObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-scale');
                    scaleObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Apply animations to elements
        document.addEventListener('DOMContentLoaded', () => {
            // Fade in sections
            document.querySelectorAll('section[id="about-section"], section[id="services"], section[id="how-it-works"], section[id="locations"], .ceo-section, .section-light, .section-dark').forEach(el => {
                el.style.opacity = '0';
                fadeInObserver.observe(el);
            });

            // Slide up cards with stagger
            document.querySelectorAll('.location-card, .step, .about-feature-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(50px)';
                el.style.transitionDelay = `${index * 0.15}s`;
                slideUpObserver.observe(el);
            });

            // Slide up service items
            document.querySelectorAll('.service-item').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(40px)';
                el.style.transitionDelay = `${index * 0.1}s`;
                slideUpObserver.observe(el);
            });

            // Slide in from left (founder/about content)
            document.querySelectorAll('.ceo-content').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(-50px)';
                slideLeftObserver.observe(el);
            });

            // Slide in from right (images)
            document.querySelectorAll('.ceo-image-container').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(50px)';
                slideRightObserver.observe(el);
            });

            // Scale animations for step numbers and icons
            document.querySelectorAll('.step-number, .location-card-icon, .service-icon').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.8)';
                scaleObserver.observe(el);
            });

            // Animate section headers
            document.querySelectorAll('.section-header').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                slideUpObserver.observe(el);
            });

            // Animate review cards
            document.querySelectorAll('.review-stat, .review-testimonial-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(40px)';
                el.style.transitionDelay = `${index * 0.12}s`;
                slideUpObserver.observe(el);
            });
        });

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
</body>
</html>
