<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>CAS Private Care LLC - Verified Caregivers & Home Care Services New York</title>
    <meta name="title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta name="description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services. 24/7 support. Book trusted care professionals today.">
    <meta name="keywords" content="caregivers New York, home care services, elderly care, nanny services, housekeeping, personal care, verified caregivers, Manila caregivers, professional care services">
    <meta name="author" content="CAS Private Care LLC">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="og:description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:site_name" content="CAS Private Care LLC">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="twitter:description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services.">
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
        'streetAddress' => '123 Care Street',
        'addressLocality' => 'Manila',
        'addressRegion' => 'Metro Manila',
        'postalCode' => '1000',
        'addressCountry' => 'PH'
      ],
      'geo' => [
        '@type' => 'GeoCoordinates',
        'latitude' => 14.5995,
        'longitude' => 120.9842
      ],
      'openingHoursSpecification' => [
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'opens' => '00:00',
        'closes' => '23:59'
      ],
      'sameAs' => [
        'https://www.facebook.com/casprivatecare',
        'https://www.twitter.com/casprivatecare',
        'https://www.instagram.com/casprivatecare',
        'https://www.linkedin.com/company/casprivatecare'
      ],
      'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => '4.9',
        'bestRating' => '5',
        'worstRating' => '1',
        'ratingCount' => '5000'
      ]
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>
    
    <script type="application/ld+json">
    @php
    echo json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Service',
      'serviceType' => 'Home Care Services',
      'provider' => [
        '@type' => 'Organization',
        'name' => 'CAS Private Care LLC'
      ],
      'areaServed' => [
        '@type' => 'Country',
        'name' => 'New York'
      ],
      'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Care Services',
        'itemListElement' => [
          [
            '@type' => 'Offer',
            'itemOffered' => [
              '@type' => 'Service',
              'name' => 'Elderly Care Services'
            ]
          ],
          [
            '@type' => 'Offer',
            'itemOffered' => [
              '@type' => 'Service',
              'name' => 'Housekeeping Services'
            ]
          ],
          [
            '@type' => 'Offer',
            'itemOffered' => [
              '@type' => 'Service',
              'name' => 'Personal Care Services'
            ]
          ],
          [
            '@type' => 'Offer',
            'itemOffered' => [
              '@type' => 'Service',
              'name' => 'Special Needs Care'
            ]
          ]
        ]
      ]
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Sora:wght@600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Preload critical images for LCP -->
    <link rel="preload" as="image" href="{{ asset('cover.jpg') }}">
    <link rel="preload" as="image" href="{{ asset('logo flower.png') }}">
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

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(30, 58, 138, 0.08);
            z-index: 1000;
            padding: 0.75rem 0;
            height: 80px;
            border-bottom: 3px solid #f97316;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-section img {
            height: 90px;
            width: auto;
            object-fit: contain;
        }

        .brand-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0B4FA2 0%, #0E63B6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #0B4FA2;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            position: relative;
        }

        .nav-links a:not(.cta-btn):hover {
            color: #2F86D6;
            background: rgba(47, 134, 214, 0.08);
            backdrop-filter: blur(10px);
            transform: translateY(-2px);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #0B4FA2;
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 0;
        }

        .dropdown-menu a:hover {
            background: rgba(47, 134, 214, 0.1);
            color: #2F86D6;
            transform: none;
        }

        .cta-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .cta-btn::before {
            display: none;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #1e40af;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.3s;
            z-index: 1001;
        }

        .mobile-menu-btn:hover {
            background: rgba(30, 64, 175, 0.1);
        }

        .mobile-menu-btn:active {
            background: rgba(30, 64, 175, 0.2);
        }

        .hero {
            margin-top: 80px;
            padding: 8rem 2rem 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-bg-images {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            z-index: 0;
        }

        .hero-bg-slice {
            flex: 1;
            background-size: cover;
            background-position: center;
            position: relative;
            transform: skewX(-5deg);
            margin: 0 -2%;
        }

        .hero-bg-slice:nth-child(1) {
            background-image: url('https://images.unsplash.com/photo-1609220136736-443140cffec6?w=800');
        }

        .hero-bg-slice:nth-child(2) {
            background-image: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800');
        }

        .hero-bg-slice:nth-child(3) {
            background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800');
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.65) 0%, rgba(59, 130, 246, 0.6) 50%, rgba(96, 165, 250, 0.55) 100%);
            z-index: 1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 4rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
            flex-wrap: wrap;
            justify-content: center;
        }

        .hero-social-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            margin: 0;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .hero-social-icons {
            display: flex;
            gap: 0.75rem;
        }

        .hero-social-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.1rem;
        }

        .hero-social-icon:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        /* About Section Feature Cards */
        .about-features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .about-feature-card {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .about-feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .about-feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .about-feature-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .about-feature-content h3 {
            font-size: 1.25rem;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .about-feature-content p {
            color: #64748b;
            margin: 0;
            line-height: 1.6;
            font-size: 0.95rem;
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
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .trademark {
            font-size: 0.6em;
            vertical-align: super;
        }

        .hero .tagline {
            font-size: 2.2rem;
            color: #ffffff;
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-shadow: 0 3px 15px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .hero p {
            font-size: 1.25rem;
            color: #f0f9ff;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            font-weight: 400;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .hero-trust-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 0.8s ease-out 0.8s both;
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
            animation: fadeInUp 0.8s ease-out 0.6s both;
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
        }

        .btn-primary {
            background: white;
            color: #1e40af;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            background: #f0f9ff;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid #f97316;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            transform: translateY(-4px);
            border-color: #ea580c;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.4);
        }

        section {
            padding: 5rem 2rem;
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

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
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
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            min-height: 350px;
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
            gap: 1rem;
            margin-top: 3rem;
            position: relative;
        }

        .step {
            text-align: center;
            position: relative;
            padding: 2rem 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(30, 58, 138, 0.08);
            transition: all 0.3s ease;
        }

        .step-content {
            flex: 1;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
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
            transition: all 0.3s ease;
        }

        .step:hover .step-number {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(249, 115, 22, 0.4);
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

        .step p {
            color: #64748b;
            line-height: 1.7;
            font-weight: 400;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
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
            background: linear-gradient(to bottom, rgba(30, 64, 175, 0.3), rgba(30, 64, 175, 0.85));
            transition: background 0.4s ease;
        }

        .service-item:hover .service-overlay {
            background: linear-gradient(to bottom, rgba(30, 64, 175, 0.5), rgba(30, 64, 175, 0.95));
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

        .footer-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
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

            nav {
                padding: 0.5rem 0;
                height: 70px;
            }

            .nav-container {
                padding: 0 1rem;
            }

            .logo-section img {
                height: 60px;
            }

            .brand-name {
                font-size: 1rem;
            }

            .mobile-menu-btn {
                display: block;
                font-size: 1.5rem;
                padding: 0.5rem;
                color: #1e40af;
            }

            .nav-links {
                display: none;
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                max-height: calc(100vh - 70px);
                overflow-y: auto;
                z-index: 999;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .nav-links a {
                display: block;
                padding: 1rem;
                width: 100%;
                text-align: center;
                border-radius: 8px;
            }

            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                padding: 0.5rem 0;
                margin-top: 0.5rem;
                background: #f8fafc;
                border-radius: 8px;
            }

            .hero {
                margin-top: 70px;
                padding: 2rem 1rem;
                min-height: auto;
            }

            .hero-bg-images {
                display: none;
            }

            .hero::before {
                background: linear-gradient(135deg, rgba(30, 64, 175, 0.85) 0%, rgba(59, 130, 246, 0.8) 100%);
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 1.5rem;
                border-radius: 20px;
            }

            .hero-left {
                text-align: center;
            }

            .hero h1 {
                font-size: 2rem;
                line-height: 1.2;
                margin-bottom: 0.75rem;
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

            .hero-right {
                display: flex;
                gap: 1.5rem;
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

            /* Steps Section - Mobile Layout */
            .steps-container {
                grid-template-columns: 1fr;
                gap: 1.25rem;
                margin-top: 2rem;
                position: relative;
            }

            .steps-container::before {
                content: '';
                position: absolute;
                left: 35px;
                top: 0;
                bottom: 0;
                width: 3px;
                background: linear-gradient(180deg, #f97316 0%, #ea580c 100%);
                z-index: 0;
            }

            .step {
                padding: 1.5rem;
                background: white;
                border-radius: 16px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 1.25rem;
                text-align: left;
                z-index: 1;
            }

            .step::after {
                display: none;
            }

            .step-number {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin: 0;
                flex-shrink: 0;
                position: relative;
                z-index: 2;
                box-shadow: 0 6px 20px rgba(249, 115, 22, 0.35);
            }

            .step-content {
                flex: 1;
            }

            .step h3 {
                font-size: 1.15rem;
                margin-bottom: 0.5rem;
                line-height: 1.3;
            }

            .step p {
                font-size: 0.875rem;
                line-height: 1.6;
                margin: 0;
                color: #64748b;
            }

            /* Services Section - Mobile Layout */
            .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .service-item {
                height: 280px;
                border-radius: 16px;
            }

            .service-content {
                padding: 1.5rem;
                justify-content: flex-end;
            }

            .service-item h4 {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
                line-height: 1.2;
            }

            .service-description {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
                line-height: 1.5;
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

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .footer-section {
                text-align: center;
            }

            .footer-section ul {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .footer-bottom-links {
                flex-direction: column;
                gap: 0.75rem;
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

            /* Feature cards grid layout on mobile - 2x2 grid */
            .about-features-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 1rem !important;
            }

            .about-feature-card {
                flex-direction: column !important;
                padding: 1.25rem !important;
                text-align: center;
            }

            .about-feature-icon {
                width: 45px !important;
                height: 45px !important;
                margin: 0 auto;
            }

            .about-feature-icon i {
                font-size: 1.25rem !important;
            }

            .about-feature-content h3 {
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

            /* Newsletter form mobile */
            .newsletter-input {
                flex-direction: column;
            }

            .newsletter-input input,
            .newsletter-btn {
                width: 100%;
            }

            /* Footer improvements */
            .footer-logo img {
                height: 80px !important;
            }

            iframe {
                height: 200px !important;
            }
        }

        /* Small tablets and large phones */
        @media (min-width: 481px) and (max-width: 768px) {
            nav {
                padding: 0.6rem 0;
                height: 75px;
            }

            .nav-container {
                padding: 0 1.5rem;
            }

            .logo-section img {
                height: 70px;
            }

            .brand-name {
                font-size: 1.15rem;
            }

            .mobile-menu-btn {
                display: block;
                font-size: 1.5rem;
            }

            .nav-links {
                display: none;
                position: fixed;
                top: 75px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 2rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                max-height: calc(100vh - 75px);
                overflow-y: auto;
                z-index: 999;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                width: 100%;
                margin-bottom: 0.75rem;
            }

            .nav-links a {
                display: block;
                padding: 1rem;
                width: 100%;
                text-align: center;
            }

            .hero {
                margin-top: 75px;
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

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            /* About section tablet */
            #about-section > div {
                grid-template-columns: 1fr !important;
                gap: 3rem !important;
            }
        }

        /* Standard mobile breakpoint (kept for backward compatibility) */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .nav-links.active {
                display: flex;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero {
                padding: 3rem 1.5rem;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 2.5rem;
                padding: 2rem;
            }

            .hero-left {
                text-align: center;
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
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .step::after {
                display: none;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .stat-item h3 {
                font-size: 2rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
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

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
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

            .footer-content {
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
    <nav>
        <div class="nav-container">
            <div class="logo-section">
                <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo - Professional Caregiving Services" width="150" height="150">
            </div>
            <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navLinks" id="mobileMenuBtn">
                <span aria-hidden="true">☰</span>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li class="dropdown">
                    <a href="#services">Services <i class="bi bi-chevron-down" style="font-size: 0.8rem; margin-left: 0.5rem;"></i></a>
                    <div class="dropdown-menu">
                        <a href="#services">Browse All Services</a>
                        <a href="{{ url('/register') }}">Get Started</a>
                        <a href="{{ url('/login') }}">Login</a>
                    </div>
                </li>
                <li><a href="#training">Training Center</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="{{ url('/login') }}">Login</a></li>
                <li><a href="{{ url('/register') }}" class="cta-btn">Register</a></li>
            </ul>
        </div>
    </nav>

    <main id="main-content">
    <header class="hero">
        <div class="hero-bg-images">
            <div class="hero-bg-slice"></div>
            <div class="hero-bg-slice"></div>
            <div class="hero-bg-slice"></div>
        </div>
        <div class="hero-content">
            <div class="hero-left">
                <h1>CAS Private Care LLC</h1>
                <p class="tagline">Comfort and Support</p>
                
                <div style="position: relative; display: flex; justify-content: center; margin: 2rem 0; background: rgba(255,255,255,0.1); padding: 0.5rem; border-radius: 50px; backdrop-filter: blur(10px);">
                    <div id="slider-bg" style="position: absolute; top: 0.5rem; left: 0.5rem; width: calc(33.33% - 0.33rem); height: calc(100% - 1rem); background: white; border-radius: 25px; transition: transform 0.3s ease; z-index: 1;"></div>
                    <button onclick="switchService('caregiver')" id="btn-caregiver" style="position: relative; z-index: 2; padding: 0.75rem 1.5rem; border: none; border-radius: 25px; background: transparent; color: #1e40af; font-weight: 600; cursor: pointer; transition: color 0.3s; flex: 1;">Caregiver</button>
                    <button onclick="switchService('housekeeping')" id="btn-housekeeping" style="position: relative; z-index: 2; padding: 0.75rem 1.5rem; border: none; border-radius: 25px; background: transparent; color: white; font-weight: 600; cursor: pointer; transition: color 0.3s; flex: 1;">Housekeeping</button>
                    <button onclick="switchService('personal')" id="btn-personal" style="position: relative; z-index: 2; padding: 0.75rem 1.5rem; border: none; border-radius: 25px; background: transparent; color: white; font-weight: 600; cursor: pointer; transition: color 0.3s; flex: 1;">Personal Care</button>
                </div>
                
                <p id="hero-description" style="transition: opacity 0.5s ease;">A modern and trustworthy caregiving marketplace where families effortlessly connect with verified caregivers, nannies, and home helpers.</p>
                
                <div class="hero-buttons">
                    <a href="#" class="btn-primary" id="find-btn" style="transition: opacity 0.5s ease;">Find a Caregiver</a>
                    <a href="{{ url('/register') }}" class="btn-secondary">Become a Partner</a>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-image-container">
                    <img src="{{ asset('cover.jpg') }}" alt="CAS Private Care LLC Cover" class="hero-cover-image">
                </div>
                <div class="hero-social-container">
                    <p class="hero-social-text">CONNECT WITH US</p>
                    <div class="hero-social-icons">
                        <a href="#" class="hero-social-icon" aria-label="Follow us on Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="hero-social-icon" aria-label="Follow us on Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="hero-social-icon" aria-label="Follow us on Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="hero-social-icon" aria-label="Follow us on LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>

    <section class="section-light" style="padding: 6rem 2rem;" id="about-section" itemscope itemtype="https://schema.org/AboutPage">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div class="fade-in" style="position: relative;">
                <img src="https://images.unsplash.com/photo-1609220136736-443140cffec6?w=800&q=75" alt="Verified caregiver providing professional elderly care services" style="width: 100%; height: 500px; object-fit: cover; border-radius: 20px; box-shadow: 0 20px 60px rgba(59, 130, 246, 0.2);" loading="lazy" width="800" height="500" decoding="async">
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
                <h2 style="font-size: 3rem; font-weight: 700; margin-bottom: 1.5rem;"><span style="color: #f97316;">What</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">is CAS Private Care LLC?</span></h2>
                <p style="font-size: 1.1rem; color: #64748b; line-height: 1.8; margin-bottom: 2rem;">Your trusted platform connecting families with verified caregivers for quality care services. We make finding professional care simple, safe, and reliable.</p>
                <div class="about-features-grid">
                    <div class="about-feature-card">
                        <div class="about-feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="about-feature-content">
                            <h3><span style="color: #f97316;">For</span> <span style="color: #1e40af;">Families</span></h3>
                            <p>Browse verified caregivers, nannies, and helpers. Book instantly with secure payments.</p>
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

    <section class="section-dark" id="how-it-works" itemscope itemtype="https://schema.org/HowTo">
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
                        <p>Contractors receive bookings, connect with families, and deliver exceptional care while building their reputation.</p>
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
                    <div class="service-bg" style="background-image: url('https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=800');"></div>
                    <div class="service-overlay"></div>
                    <div class="service-content">
                        <h4 itemprop="name">Personal Care</h4>
                        <p class="service-description" itemprop="description">Comprehensive personal care for all ages and needs. Skilled caregivers providing daily living assistance, personal hygiene support, and companionship.</p>
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


            </div>
        </div>
    </section>

    <div class="section-divider">
        <div class="divider-line-thick"></div>
        <div class="divider-line-thin"></div>
    </div>



    <section id="about" style="position: relative; padding: 0; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1600') center/cover;"></div>
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(59, 130, 246, 0.85));"></div>
        <div style="position: relative; z-index: 1; padding: 6rem 2rem;">
            <div class="container">
                <div class="section-header fade-in" style="margin-bottom: 4rem;">
                    <h2 style="color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.2); -webkit-text-fill-color: white;">Trusted by Thousands</h2>
                    <p style="color: white;">Building connections that matter</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div class="fade-in" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); text-align: center;">
                        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="bi bi-people-fill" style="font-size: 2.5rem; color: #f97316;"></i>
                        </div>
                        <h3 style="font-size: 3rem; color: white; font-weight: 700; margin-bottom: 0.5rem;"><span class="counter" data-target="{{ str_replace(',', '', $stats['total_caregivers']) }}">0</span>+</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">Active Caregivers</p>
                        <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.2); border-radius: 10px; margin-top: 1rem; overflow: hidden;">
                            <div class="progress-bar" data-progress="90" style="height: 100%; background: white; border-radius: 10px; width: 0; transition: width 2s ease;"></div>
                        </div>
                    </div>
                    <div class="fade-in" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); text-align: center;">
                        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="bi bi-heart-fill" style="font-size: 2.5rem; color: #f97316;"></i>
                        </div>
                        <h3 style="font-size: 3rem; color: white; font-weight: 700; margin-bottom: 0.5rem;"><span class="counter" data-target="{{ str_replace(',', '', $stats['total_clients']) }}">0</span>+</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">Happy Families</p>
                        <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.2); border-radius: 10px; margin-top: 1rem; overflow: hidden;">
                            <div class="progress-bar" data-progress="95" style="height: 100%; background: white; border-radius: 10px; width: 0; transition: width 2s ease;"></div>
                        </div>
                    </div>
                    <div class="fade-in" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); text-align: center;">
                        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="bi bi-star-fill" style="font-size: 2.5rem; color: #f97316;"></i>
                        </div>
                        <h3 style="font-size: 3rem; color: white; font-weight: 700; margin-bottom: 0.5rem;"><span class="counter" data-target="{{ $stats['satisfaction_rate'] }}">0</span>%</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">Satisfaction Rate</p>
                        <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.2); border-radius: 10px; margin-top: 1rem; overflow: hidden;">
                            <div class="progress-bar" data-progress="98" style="height: 100%; background: white; border-radius: 10px; width: 0; transition: width 2s ease;"></div>
                        </div>
                    </div>
                    <div class="fade-in" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); text-align: center;">
                        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="bi bi-headset" style="font-size: 2.5rem; color: #f97316;"></i>
                        </div>
                        <h3 style="font-size: 3rem; color: white; font-weight: 700; margin-bottom: 0.5rem;">24/7</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">Support Available</p>
                        <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.2); border-radius: 10px; margin-top: 1rem; overflow: hidden;">
                            <div class="progress-bar" data-progress="100" style="height: 100%; background: white; border-radius: 10px; width: 0; transition: width 2s ease;"></div>
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

    <section class="section-dark">
        <div class="container">
            <div class="section-header fade-in">
                <h2><span style="color: #f97316;">Ready</span> to Get Started?</h2>
                <p>Join CAS Private Care LLC today and experience the future of caregiving services</p>
            </div>
            <div class="hero-buttons" style="margin-top: 3rem; justify-content: center;">
                <a href="{{ url('/register') }}" class="btn-primary">Sign Up Now</a>
                <a href="#how-it-works" class="btn-secondary">Learn More</a>
            </div>
        </div>
    </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo" width="120" height="120">
                </div>
                <p>Connection that cares. Your trusted marketplace for professional caregiving services connecting families with verified care professionals.</p>
                <p style="margin-top: 1rem;">We provide a safe, reliable platform where quality care meets convenience. From elderly care to childcare, our verified professionals are ready to support your family's unique needs with compassion and expertise.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.twitter.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="https://www.instagram.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.linkedin.com/company/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>For Clients</h3>
                <ul>
                    <li><a href="#services">Browse Services</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="{{ url('/register') }}">Sign Up</a></li>
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="#about">About</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>For Caregivers</h3>
                <ul>
                    <li><a href="{{ url('/register') }}">Join as Caregiver</a></li>
                    <li><a href="#training">Training Center</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                </ul>
                <h3 style="margin-top: 2rem;">Company</h3>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
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
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.4076!2d120.9842!3d14.5995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDM1JzU4LjIiTiAxMjDCsDU5JzAzLjEiRQ!5e0!3m2!1sen!2sph!4v1234567890" width="100%" height="120" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-bottom">
            <p>&copy; 2024 CAS Private Care LLC. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="{{ url('/privacy') }}">Privacy Policy</a>
                <a href="{{ url('/terms') }}">Terms of Service</a>
                <a href="#contact">Contact</a>
            </div>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const menuBtn = document.getElementById('mobileMenuBtn');
            const isExpanded = navLinks.classList.toggle('active');
            menuBtn.setAttribute('aria-expanded', isExpanded);
        }

        document.addEventListener('click', function(event) {
            const nav = document.querySelector('nav');
            const navLinks = document.getElementById('navLinks');
            
            if (!nav.contains(event.target) && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
            }
        });

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
            
            setTimeout(() => {
                setInterval(() => {
                    currentService = (currentService + 1) % services.length;
                    switchService(services[currentService]);
                }, 5000);
            }, 5000);
        });
        
        let currentService = 0;
        const services = ['caregiver', 'housekeeping', 'personal'];
        
        function switchService(type) {
            const description = document.getElementById('hero-description');
            const findBtn = document.getElementById('find-btn');
            const sliderBg = document.getElementById('slider-bg');
            const buttons = ['btn-caregiver', 'btn-housekeeping', 'btn-personal'];
            
            description.style.opacity = '0';
            findBtn.style.opacity = '0';
            
            buttons.forEach(id => {
                document.getElementById(id).style.color = 'white';
            });
            
            if (type === 'caregiver') {
                sliderBg.style.transform = 'translateX(0%)';
                document.getElementById('btn-caregiver').style.color = '#1e40af';
                currentService = 0;
            } else if (type === 'housekeeping') {
                sliderBg.style.transform = 'translateX(100%)';
                document.getElementById('btn-housekeeping').style.color = '#1e40af';
                currentService = 1;
            } else if (type === 'personal') {
                sliderBg.style.transform = 'translateX(200%)';
                document.getElementById('btn-personal').style.color = '#1e40af';
                currentService = 2;
            }
            
            setTimeout(() => {
                if (type === 'caregiver') {
                    description.textContent = 'A modern and trustworthy caregiving marketplace where families effortlessly connect with verified caregivers and companions.';
                    findBtn.textContent = 'Find a Caregiver';
                } else if (type === 'housekeeping') {
                    description.textContent = 'Professional housekeeping services connecting families with reliable and trusted house helpers for all your home maintenance needs.';
                    findBtn.textContent = 'Find a Housekeeper';
                } else if (type === 'personal') {
                    description.textContent = 'Compassionate personal care services connecting families with qualified personal care assistants for daily living support.';
                    findBtn.textContent = 'Find Personal Care';
                }
                
                description.style.opacity = '1';
                findBtn.style.opacity = '1';
            }, 250);
        }
        
        setInterval(() => {
            currentService = (currentService + 1) % services.length;
            switchService(services[currentService]);
        }, 5000);
    </script>
</body>
</html>
