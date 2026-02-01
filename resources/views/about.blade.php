<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>About Us | CAS Private Care LLC | Our Story, Mission & Team</title>
    <meta name="title" content="About Us | CAS Private Care LLC | Our Story, Mission & Team">
    <meta name="description" content="Learn about CAS Private Care LLC - a trusted caregiving platform connecting families with verified caregivers in New York. Discover our mission, values, and commitment to quality care.">
    <meta name="keywords" content="about cas private care, caregiver platform new york, home care services, caregiving company">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/about') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/about') }}">
    <meta property="og:title" content="About Us | CAS Private Care LLC">
    <meta property="og:description" content="Learn about CAS Private Care LLC - a trusted caregiving platform connecting families with verified caregivers in New York.">
    <meta property="og:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/about') }}">
    <meta property="twitter:title" content="About Us | CAS Private Care LLC">
    <meta property="twitter:description" content="Learn about CAS Private Care LLC - a trusted caregiving platform connecting families with verified caregivers in New York.">
    <meta property="twitter:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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

        .section-light {
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            padding: 6rem 2rem;
        }

        .section-dark {
            background-color: #dbeafe;
            background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
            padding: 6rem 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e40af;
        }

        .section-header p {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .about-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 8rem 2rem;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
        }

        .about-hero p {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.95);
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .mission-hero {
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1600&q=80');
            background-size: cover;
            background-position: center;
            /* Avoid background-attachment: fixed for cross-browser mobile performance */
            background-attachment: scroll;
            padding: 10rem 2rem;
            color: white;
            text-align: center;
            overflow: hidden;
        }

        /* Re-enable parallax-like effect only where it is reliably smooth */
        @media (min-width: 1025px) {
            .mission-hero {
                background-attachment: fixed;
            }
        }

        .mission-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.85) 0%, rgba(59, 130, 246, 0.75) 100%);
            z-index: 1;
        }

        .mission-hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
        }

        .mission-hero h2 {
            font-size: 5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.02em;
        }

        .mission-hero .subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 5rem;
            font-weight: 300;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
        }

        .mission-content {
            text-align: left;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 5rem 4rem;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1100px;
            margin: 0 auto;
        }

        .mission-content .mission-label {
            font-size: 1rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.5rem;
        }

        .mission-content .mission-text {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .mission-content .mission-text .first-letter {
            font-size: 8rem;
            font-weight: 900;
            color: #3b82f6;
            line-height: 0.85;
            margin-top: -0.1em;
            flex-shrink: 0;
        }

        .mission-content .mission-text .text-content {
            flex: 1;
            padding-top: 1rem;
        }

        .mission-content .mission-text .text-content .mission-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e40af;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .mission-content .mission-text .text-content p {
            font-size: 1.15rem;
            line-height: 1.9;
            color: #475569;
            font-weight: 400;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 4rem;
        }

        .value-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .value-card:nth-child(2) .value-icon {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);
        }

        .value-card:nth-child(3) .value-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .value-icon i {
            color: white;
            font-size: 2.5rem;
            line-height: 1;
            display: inline-block;
        }

        .value-card h4 {
            font-size: 1.5rem;
            color: #1e40af;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .value-card p {
            color: #64748b;
            line-height: 1.7;
        }

        .founder-section {
            background: white;
            border-radius: 24px;
            padding: 4rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-top: 4rem;
        }

        .founder-content {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 4rem;
            align-items: center;
        }

        .founder-image {
            position: relative;
        }

        .founder-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .founder-badge {
            position: absolute;
            bottom: -15px;
            right: -15px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            padding: 1rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .founder-badge i {
            font-size: 2rem;
            color: white;
        }

        .founder-info h3 {
            font-size: 1.5rem;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .founder-info .company-tag {
            color: #64748b;
            margin: 0 0 2rem 0;
            font-size: 0.9rem;
        }

        .founder-info h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .founder-info .title {
            font-size: 1.3rem;
            color: #3b82f6;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .founder-info p {
            font-size: 1.1rem;
            color: #64748b;
            line-height: 1.8;
            margin: 0;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 4rem;
        }

        .stat-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .section-divider {
            width: 100%;
            margin: 0;
        }

        .section-divider .divider-line-thick {
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 2px;
            margin-bottom: 4px;
        }

        .section-divider .divider-line-thin {
            height: 2px;
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
            border-radius: 1px;
        }

        /* =============================================================
           MOBILE RESPONSIVE DESIGN - About Page
           ============================================================= */
        
        /* Tablets (769px - 1024px) */
        @media (max-width: 1024px) {
            .about-hero {
                padding: 5rem 2rem;
            }
            
            .mission-hero {
                background-attachment: scroll;
                padding: 6rem 2rem;
            }

            .mission-hero h2 {
                font-size: 3.5rem;
            }

            .mission-content {
                padding: 3.5rem 2.5rem;
            }

            .mission-content .mission-text .first-letter {
                font-size: 6rem;
            }

            .mission-content .mission-text .text-content .mission-title {
                font-size: 2rem;
            }

            .mission-content .mission-text .text-content p {
                font-size: 1.1rem;
            }

            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }

            .founder-content {
                grid-template-columns: 1fr;
            }
        }

        /* Standard Mobile (768px) */
        @media (max-width: 768px) {
            /* About Hero */
            .about-hero {
                margin-top: 70px;
                padding: 3rem 1.25rem;
            }
            
            .about-hero h1 {
                font-size: 2rem;
                line-height: 1.2;
                margin-bottom: 1rem;
            }

            .about-hero p {
                font-size: 1rem;
                line-height: 1.6;
            }

            /* Mission Hero */
            .mission-hero {
                padding: 3rem 1.25rem;
            }

            .mission-hero h2 {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .mission-hero .subtitle {
                font-size: 1rem;
                margin-bottom: 2rem;
            }

            .mission-content {
                padding: 1.75rem 1.25rem;
                border-radius: 16px;
            }

            .mission-content .mission-text {
                flex-direction: column;
                gap: 0.75rem;
            }

            .mission-content .mission-text .first-letter {
                font-size: 4rem;
                margin-top: 0;
                align-self: flex-start;
            }

            .mission-content .mission-text .text-content {
                padding-top: 0;
            }

            .mission-content .mission-text .text-content .mission-title {
                font-size: 1.25rem;
                margin-bottom: 0.75rem;
            }

            .mission-content .mission-text .text-content p {
                font-size: 0.9rem;
                line-height: 1.6;
            }

            /* Section Styles */
            .section-light,
            .section-dark {
                padding: 3rem 1.25rem;
            }
            
            .section-header {
                margin-bottom: 2.5rem;
            }

            .section-header h2 {
                font-size: 1.5rem;
                line-height: 1.3;
            }
            
            .section-header p {
                font-size: 0.9rem;
            }

            /* Values Grid - 2x2 on mobile */
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .value-card {
                padding: 1.25rem 1rem;
                border-radius: 12px;
            }
            
            .value-card h3 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .value-card p {
                font-size: 0.8rem;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            .value-icon {
                width: 44px;
                height: 44px;
                margin-bottom: 0.75rem;
            }
            
            .value-icon i {
                font-size: 1.1rem;
            }

            /* Stats section 2x2 grid on mobile */
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1.25rem 1rem;
                border-radius: 12px;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }

            /* Founder Section */
            .founder-section {
                padding: 1.5rem;
                border-radius: 16px;
            }
            
            .founder-content {
                gap: 1.5rem;
            }
            
            .founder-image {
                height: 250px;
                border-radius: 12px;
            }
            
            .founder-info h3 {
                font-size: 1.25rem;
            }
            
            .founder-info p {
                font-size: 0.9rem;
            }
            
            /* Team Grid */
            .team-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .team-card {
                padding: 1rem;
                border-radius: 12px;
            }
            
            .team-card img {
                width: 60px;
                height: 60px;
            }
            
            .team-card h4 {
                font-size: 0.9rem;
            }
            
            .team-card p {
                font-size: 0.75rem;
            }
        }
        
        /* Small Phones (480px) */
        @media (max-width: 480px) {
            .about-hero {
                padding: 2.5rem 1rem;
            }
            
            .about-hero h1 {
                font-size: 1.75rem;
            }
            
            .mission-hero {
                padding: 2.5rem 1rem;
            }
            
            .mission-hero h2 {
                font-size: 1.75rem;
            }
            
            .mission-content {
                padding: 1.25rem 1rem;
            }
            
            .mission-content .mission-text .first-letter {
                font-size: 3rem;
            }
            
            .mission-content .mission-text .text-content .mission-title {
                font-size: 1.1rem;
            }
            
            .section-light,
            .section-dark {
                padding: 2.5rem 1rem;
            }
            
            .section-header h2 {
                font-size: 1.35rem;
            }
            
            .values-grid {
                gap: 0.5rem;
            }
            
            .value-card {
                padding: 1rem 0.75rem;
            }
            
            .value-card h3 {
                font-size: 0.85rem;
            }
            
            .value-card p {
                font-size: 0.75rem;
                -webkit-line-clamp: 2;
            }
            
            .value-icon {
                width: 38px;
                height: 38px;
            }
            
            .stats-section {
                gap: 0.5rem;
            }
            
            .stat-card {
                padding: 1rem 0.75rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.65rem;
            }
        }
        
        /* Very Small Phones (360px) */
        @media (max-width: 360px) {
            .about-hero h1 {
                font-size: 1.5rem;
            }
            
            .mission-hero h2 {
                font-size: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
            
            .value-card h3 {
                font-size: 0.8rem;
            }
            
            .stat-number {
                font-size: 1.35rem;
            }
        }

        /* Scroll Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        [data-animate] {
            opacity: 0;
        }

        [data-animate].visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        [data-animate="left"].visible {
            animation: fadeInLeft 0.6s ease forwards;
        }

        [data-animate="right"].visible {
            animation: fadeInRight 0.6s ease forwards;
        }

        [data-animate="scale"].visible {
            animation: scaleIn 0.6s ease forwards;
        }

        /* Skip Link for Accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #1e40af;
            color: white;
            padding: 8px 16px;
            z-index: 10000;
            text-decoration: none;
            font-weight: 600;
            border-radius: 0 0 4px 0;
            transition: top 0.3s ease;
        }
        .skip-link:focus {
            top: 0;
            outline: 2px solid #fbbf24;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    @include('partials.navigation')

    @include('partials.trust-strip')

    <main id="main-content" role="main" tabindex="-1">
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <h1><span style="color: #fbbf24;">About</span> CAS Private Care LLC</h1>
                <p>Connecting families with trusted, verified caregivers across New York. Building stronger communities through compassionate care, one connection at a time.</p>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- Mission Section -->
        <section class="mission-hero">
            <div class="mission-hero-content">
                <h2>OUR MISSION</h2>
                <p class="subtitle">Driving our commitment to transform the caregiving industry and make quality care accessible to all</p>
                
                <div class="mission-content">
                    <div class="mission-label">Our Mission</div>
                    <div class="mission-text">
                        <span class="first-letter">T</span>
                        <div class="text-content">
                            <div class="mission-title">o transform the caregiving industry</div>
                            <p>by creating a trusted platform that connects families with exceptional, verified contractors. We are dedicated to ensuring every family receives quality, compassionate care while creating meaningful opportunities for professional caregivers. Our mission is to make professional caregiving services accessible, reliable, and safe for everyone.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- What We Do Section -->
        <section class="section-light">
            <div class="container">
                <div class="section-header">
                    <h2><span style="color: #f97316;">What</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">We Do</span></h2>
                    <p>A modern marketplace connecting families with independent 1099 contractors</p>
                </div>
                
                <div class="what-we-do-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                    <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 20px; padding: 2.5rem; border-left: 4px solid #3b82f6;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-people-fill" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">We Connect</h3>
                        </div>
                        <p style="color: #475569; line-height: 1.8; margin: 0;">We bridge the gap between NYC families seeking quality care and verified independent contractors looking for flexible work opportunities.</p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 20px; padding: 2.5rem; border-left: 4px solid #10b981;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-shield-check" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">We Verify</h3>
                        </div>
                        <p style="color: #475569; line-height: 1.8; margin: 0;">Every contractor undergoes comprehensive background checks, ID verification, and credential validation before joining our trusted network.</p>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 20px; padding: 2.5rem; border-left: 4px solid #f97316;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-credit-card-2-front" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">We Facilitate</h3>
                        </div>
                        <p style="color: #475569; line-height: 1.8; margin: 0;">Our platform handles secure payments, scheduling, and communication so both families and contractors can focus on what matters most—quality care.</p>
                    </div>
                </div>
                
                <div style="margin-top: 3rem; background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;">
                    <p style="color: #64748b; font-size: 1.05rem; margin: 0; line-height: 1.8;">
                        <i class="bi bi-info-circle" style="color: #3b82f6;"></i> 
                        <strong>1099 Contractor Model:</strong> All service providers on our platform are independent contractors, not employees. They set their own rates, choose their clients, and manage their own schedules.
                    </p>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- Our Values Section -->
        <section class="section-dark">
            <div class="container">
                <div class="section-header">
                    <h2><span style="color: #f97316;">Our</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Core Values</span></h2>
                    <p>The principles that guide everything we do at CAS Private Care</p>
                </div>

                <div class="values-grid">
                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-shield-fill-check" aria-hidden="true"></i>
                        </div>
                        <h4>Trust & Safety</h4>
                        <p>We prioritize the safety and security of our clients and caregivers through comprehensive background checks, verification processes, and ongoing monitoring.</p>
                    </div>

                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                        <h4>Compassion</h4>
                        <p>Every interaction is guided by empathy and understanding. We believe in treating every individual with dignity, respect, and genuine care.</p>
                    </div>

                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h4>Excellence</h4>
                        <p>We maintain the highest standards in everything we do, from caregiver vetting to client service, continuously striving for improvement and innovation.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- Founder Section -->
        <section class="section-light">
            <div class="container">
                <div class="section-header">
                    <h2><span style="color: #f97316;">Meet</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Our Founder</span></h2>
                    <p>The visionary leader behind CAS Private Care LLC</p>
                </div>

                <div class="founder-section" data-animate="scale">
                    <div class="founder-content">
                        <div class="founder-image">
                            <img src="{{ asset('CEO.jpg') }}" alt="Charles Andrew Santiago - CEO and Founder of CAS Private Care LLC" loading="lazy">
                            <div class="founder-badge">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                        <div class="founder-info">
                            <h3>CAS PRIVATE CARE LLC</h3>
                            <p class="company-tag">Professional Caregiving Services</p>
                            <h2>
                                <span style="color: #f97316;">Charles Andrew</span> <span style="color: #1e40af;">Santiago</span>
                            </h2>
                            <p class="title">CEO / Founder</p>
                            <p>
                                With a vision to transform the caregiving industry, Charles Andrew Santiago is dedicated to building a trusted platform that connects families with exceptional contractors. His goal is to ensure every family receives quality, compassionate care while creating meaningful opportunities for professional contractors. Through CAS Private Care LLC, he strives to make professional caregiving services accessible, reliable, and safe for everyone, fostering stronger communities one connection at a time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- Why Choose Us Section -->
        <section class="section-dark">
            <div class="container">
                <div class="section-header">
                    <h2><span style="color: #f97316;">Why Choose</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">CAS Private Care</span></h2>
                    <p>What sets us apart in the caregiving industry</p>
                </div>

                <div class="values-grid">
                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h4>100% Verified</h4>
                        <p>All our caregivers undergo comprehensive background checks, license verification, and credential validation before joining our network.</p>
                    </div>

                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h4>24/7 Availability</h4>
                        <p>Care needs don't follow a schedule. We're available around the clock to connect you with qualified caregivers when you need them most.</p>
                    </div>

                    <div class="value-card" data-animate>
                        <div class="value-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h4>Transparent Pricing</h4>
                        <p>No hidden fees or surprise charges. We believe in clear, upfront pricing so you know exactly what you're paying for.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- Stats Section -->
        <section class="section-light">
            <div class="container">
                <div class="section-header">
                    <h2><span style="color: #f97316;">Our</span> <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Impact</span></h2>
                    <p>Trusted by families and caregivers throughout New York</p>
                </div>

                <div class="stats-section">
                    <div class="stat-card" data-animate>
                        <div class="stat-number">1,000+</div>
                        <div class="stat-label">Families Served</div>
                    </div>
                    <div class="stat-card" data-animate>
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Verified Caregivers</div>
                    </div>
                    <div class="stat-card" data-animate>
                        <div class="stat-number">4.9/5</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                    <div class="stat-card" data-animate>
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Available Support</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider">
            <div class="divider-line-thick"></div>
            <div class="divider-line-thin"></div>
        </div>

        <!-- CTA Section -->
        <section class="section-dark" style="padding: 7rem 2rem; background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; text-align: center;">
            <div class="container">
                <h2 style="color: white; font-size: 3rem; margin-bottom: 1rem;">Ready to Experience CAS Private Care?</h2>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.25rem; margin-bottom: 2rem;">
                    Join thousands of families who trust us for their caregiving needs
                </p>
                <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ url('/register') }}" style="display: inline-block; background: white; color: #3b82f6; padding: 1rem 2.5rem; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 1.1rem; transition: transform 0.3s ease;">
                        Get Started Today
                    </a>
                    <a href="{{ url('/caregiver-new-york') }}" style="display: inline-block; background: transparent; border: 2px solid white; color: white; padding: 1rem 2.5rem; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 1.1rem; transition: transform 0.3s ease;">
                        Learn More
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('partials.footer')
    
    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')
    
    <script>
        // Intersection Observer for scroll animations
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
        
        document.querySelectorAll('[data-animate]').forEach(el => {
            observer.observe(el);
        });
    </script>

    @include('partials.mobile-action-bar')
    @include('partials.cookie-consent')
</body>
</html>

