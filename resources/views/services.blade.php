<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Our Services | CAS Private Care LLC - Caregiving & Housekeeping Services NYC</title>
    <meta name="title" content="Our Services | CAS Private Care LLC - Caregiving & Housekeeping Services NYC">
    <meta name="description" content="Professional caregiving and housekeeping services in New York. Elderly care, companion care, deep cleaning, and special needs support. Book verified 1099 contractors today.">
    <meta name="keywords" content="caregiver services NYC, housekeeping services New York, elderly care Manhattan, companion care Brooklyn, home care Queens, deep cleaning services">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/services') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/services') }}">
    <meta property="og:title" content="Our Services | CAS Private Care LLC">
    <meta property="og:description" content="Professional caregiving and housekeeping services in New York. Book verified 1099 contractors today.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/services') }}">
    <meta property="twitter:title" content="Our Services | CAS Private Care LLC">
    <meta property="twitter:description" content="Professional caregiving and housekeeping services in New York.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">
    
    <!-- Service Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "serviceType": "Home Care Services",
        "provider": {
            "@type": "Organization",
            "name": "CAS Private Care LLC",
            "url": "{{ url('/') }}"
        },
        "areaServed": {
            "@type": "State",
            "name": "New York"
        },
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "Care Services",
            "itemListElement": [
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Elderly Care"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Companion Care"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Housekeeping"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Special Needs Care"}}
            ]
        }
    }
    </script>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
            color: #1e293b;
            overflow-x: hidden;
            background: #f8fafc;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease forwards;
        }

        .animate-fade-left {
            animation: fadeInLeft 0.8s ease forwards;
        }

        .animate-fade-right {
            animation: fadeInRight 0.8s ease forwards;
        }

        .animate-scale {
            animation: scaleIn 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* Hero Section */
        .services-hero {
            margin-top: 88px;
            padding: 8rem 2rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .services-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/><circle cx="50" cy="50" r="30" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/><circle cx="50" cy="50" r="20" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/></svg>');
            background-size: 200px 200px;
            animation: float 20s ease-in-out infinite;
        }

        .services-hero::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 120%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease;
        }

        .hero-badge i {
            color: #fbbf24;
        }

        .services-hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            animation: fadeInUp 0.8s ease 0.1s both;
        }

        .services-hero h1 span {
            color: #fbbf24;
        }

        .services-hero p {
            font-size: 1.35rem;
            color: rgba(255, 255, 255, 0.95);
            max-width: 700px;
            margin: 0 auto 2.5rem;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.3s both;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-value {
            font-family: 'Sora', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: #fbbf24;
            display: block;
        }

        .hero-stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        /* Services Section */
        .services-section {
            padding: 6rem 2rem;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .section-header h2 span {
            color: #f97316;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Service Cards */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
        }

        .service-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            opacity: 0;
        }

        .service-card.visible {
            opacity: 1;
            animation: fadeInUp 0.8s ease forwards;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
        }

        .service-card-image {
            height: 220px;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .service-card-image::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 40%, rgba(0,0,0,0.6) 100%);
        }

        .service-card-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #3b82f6;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s;
        }

        .service-card:hover .service-card-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .service-card-content {
            padding: 2rem;
        }

        .service-card-category {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .service-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .service-card p {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .service-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .service-feature {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #475569;
        }

        .service-feature i {
            color: #10b981;
        }

        .service-card-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .service-card-btn:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        /* Why Choose Us Section */
        .why-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .why-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s;
            opacity: 0;
        }

        .why-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .why-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        .why-card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            transition: transform 0.3s;
        }

        .why-card:hover .why-card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .why-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .why-card p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* Process Section */
        .process-section {
            padding: 6rem 2rem;
            background: white;
        }

        .process-timeline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 4rem;
            position: relative;
        }

        .process-timeline::before {
            content: '';
            position: absolute;
            top: 50px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #f97316 100%);
            border-radius: 2px;
        }

        .process-step {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 0 1rem;
            opacity: 0;
        }

        .process-step.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .process-number {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-family: 'Sora', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            position: relative;
            z-index: 2;
            transition: transform 0.3s;
        }

        .process-step:hover .process-number {
            transform: scale(1.1);
        }

        .process-step h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .process-step p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-section h2 {
            font-family: 'Sora', sans-serif;
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            background: white;
            color: #1e40af;
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-cta-secondary {
            background: transparent;
            color: white;
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }

        .btn-cta-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .why-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .process-timeline {
                flex-direction: column;
                gap: 3rem;
            }
            
            .process-timeline::before {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .services-hero {
                padding: 5rem 1.5rem;
            }
            
            .services-hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                gap: 2rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .why-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-section h2 {
                font-size: 2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @include('partials.navigation')
    
    @include('partials.trust-strip')

    <main>
        <!-- Hero Section -->
        <section class="services-hero">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i>
                    Verified 1099 Contractors
                </div>
                <h1>Professional Care <span>Services</span> for Every Family</h1>
                <p>Connect with verified independent contractors for caregiving and housekeeping services throughout New York. Quality care, flexible scheduling, trusted professionals.</p>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-value">2,500+</span>
                        <span class="hero-stat-label">Families Served</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-value">500+</span>
                        <span class="hero-stat-label">Verified Contractors</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-value">4.9★</span>
                        <span class="hero-stat-label">Average Rating</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-value">24/7</span>
                        <span class="hero-stat-label">Support Available</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Services Section -->
        <section class="services-section">
            <div class="container">
                <div class="section-header">
                    <h2><span>Our</span> Services</h2>
                    <p>Comprehensive care solutions tailored to your family's unique needs</p>
                </div>
                
                <div class="services-grid">
                    <!-- Elderly Care -->
                    <div class="service-card" data-animate>
                        <div class="service-card-image" style="background-image: url('https://images.unsplash.com/photo-1581579438747-1dc8d17bbce4?w=800');">
                            <div class="service-card-icon">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                        </div>
                        <div class="service-card-content">
                            <span class="service-card-category">
                                <i class="bi bi-star-fill"></i>
                                Most Popular
                            </span>
                            <h3>Elderly Care</h3>
                            <p>Compassionate and professional care for your loved ones. Our verified caregivers provide personalized support with dignity and respect.</p>
                            <div class="service-features">
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Daily Activities</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Meal Prep</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Medication Reminders</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Companionship</span>
                            </div>
                            <a href="{{ url('/register') }}" class="service-card-btn">
                                Book Now <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Companion Care -->
                    <!-- Companion Care removed -->

                    <!-- House Helpers -->
                    <div class="service-card" data-animate>
                        <div class="service-card-image" style="background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800');">
                            <div class="service-card-icon">
                                <i class="bi bi-house-heart"></i>
                            </div>
                        </div>
                        <div class="service-card-content">
                            <span class="service-card-category">
                                <i class="bi bi-house-check"></i>
                                Housekeeping
                            </span>
                            <h3>House Helpers</h3>
                            <p>Reliable household assistance for a cleaner, more organized home. Professional helpers who manage all your home maintenance needs.</p>
                            <div class="service-features">
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Cleaning</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Laundry</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Organization</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Errands</span>
                            </div>
                            <a href="{{ url('/register') }}" class="service-card-btn">
                                Book Now <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Deep Cleaning -->
                    <div class="service-card" data-animate>
                        <div class="service-card-image" style="background-image: url('https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=800');">
                            <div class="service-card-icon">
                                <i class="bi bi-droplet-half"></i>
                            </div>
                        </div>
                        <div class="service-card-content">
                            <span class="service-card-category">
                                <i class="bi bi-sparkle"></i>
                                Housekeeping
                            </span>
                            <h3>Deep Cleaning</h3>
                            <p>Thorough housekeeping for kitchens, bathrooms, and high-touch areas. Perfect for move-in/move-out and seasonal refreshes.</p>
                            <div class="service-features">
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Kitchen Deep Clean</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Bathroom Sanitization</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Floor Care</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Window Cleaning</span>
                            </div>
                            <a href="{{ url('/register') }}" class="service-card-btn">
                                Book Now <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Special Needs Care -->
                    <div class="service-card" data-animate>
                        <div class="service-card-image" style="background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800');">
                            <div class="service-card-icon">
                                <i class="bi bi-hand-thumbs-up"></i>
                            </div>
                        </div>
                        <div class="service-card-content">
                            <span class="service-card-category">
                                <i class="bi bi-award"></i>
                                Specialized
                            </span>
                            <h3>Special Needs Care</h3>
                            <p>Specialized care for individuals with unique requirements. Trained professionals who provide personalized support with patience.</p>
                            <div class="service-features">
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Personalized Plans</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Trained Specialists</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Patience & Care</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Family Support</span>
                            </div>
                            <a href="{{ url('/register') }}" class="service-card-btn">
                                Book Now <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- 24/7 Care -->
                    <div class="service-card" data-animate>
                        <div class="service-card-image" style="background-image: url('https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?w=800');">
                            <div class="service-card-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="service-card-content">
                            <span class="service-card-category">
                                <i class="bi bi-moon-stars"></i>
                                Round-the-Clock
                            </span>
                            <h3>24/7 Live-In Care</h3>
                            <p>Continuous care and support around the clock. Peace of mind knowing your loved ones are never alone with trusted live-in caregivers.</p>
                            <div class="service-features">
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Overnight Care</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Emergency Response</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Meal Preparation</span>
                                <span class="service-feature"><i class="bi bi-check-circle-fill"></i> Daily Assistance</span>
                            </div>
                            <a href="{{ url('/register') }}" class="service-card-btn">
                                Book Now <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Service Comparison Section -->
        <section style="padding: 5rem 2rem; background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border-top: 1px solid rgba(251, 191, 36, 0.2); border-bottom: 1px solid rgba(251, 191, 36, 0.2);">
            <div class="container">
                <div class="section-header">
                    <h2><span>Caregiving</span> vs <span style="color: #10b981;">Housekeeping</span></h2>
                    <p>Choose the right service for your family's needs</p>
                </div>
                
                <div class="compare-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <!-- Caregiving Column -->
                    <div style="background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 2px solid #3b82f6;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-heart-pulse-fill" style="color: white; font-size: 1.75rem;"></i>
                            </div>
                            <div>
                                <h3 style="font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 700; color: #1e40af; margin: 0;">Caregiving</h3>
                                <span style="font-size: 0.9rem; color: #64748b;">Personal care & support</span>
                            </div>
                        </div>
                        <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0;">
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Elderly & senior care assistance
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Medication reminders
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Mobility assistance
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Meal preparation
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Companionship & emotional support
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                24/7 live-in care options
                            </li>
                        </ul>
                        <a href="{{ url('/register?role=client&service=caregiver') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none;">
                            Find a Caregiver <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <!-- Housekeeping Column -->
                    <div style="background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 2px solid #10b981;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-house-heart-fill" style="color: white; font-size: 1.75rem;"></i>
                            </div>
                            <div>
                                <h3 style="font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 700; color: #059669; margin: 0;">Housekeeping</h3>
                                <span style="font-size: 0.9rem; color: #64748b;">Home maintenance & cleaning</span>
                            </div>
                        </div>
                        <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0;">
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Regular & deep cleaning
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Laundry & ironing
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Kitchen & bathroom sanitization
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Home organization
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Grocery shopping & errands
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 0.75rem; color: #475569;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; margin-top: 2px;"></i>
                                Move-in/move-out cleaning
                            </li>
                        </ul>
                        <a href="{{ url('/register?role=client&service=housekeeping') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none;">
                            Find a Housekeeper <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Safety & Trust Block -->
        <section style="padding: 5rem 2rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
            <div class="container">
                <div class="safety-trust-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; align-items: center;">
                    <div>
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.15); padding: 0.5rem 1rem; border-radius: 50px; color: #065f46; font-weight: 600; font-size: 0.9rem; margin-bottom: 1.5rem;">
                            <i class="bi bi-shield-check"></i>
                            Your Safety, Our Priority
                        </div>
                        <h2 style="font-family: 'Sora', sans-serif; font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; line-height: 1.2;">
                            Trusted & Verified <span style="color: #10b981;">Professionals</span>
                        </h2>
                        <p style="font-size: 1.1rem; color: #475569; line-height: 1.8; margin-bottom: 2rem;">
                            Every contractor on our platform goes through a comprehensive verification process. We prioritize your family's safety and peace of mind.
                        </p>
                        <a href="{{ url('/about') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #10b981; font-weight: 700; text-decoration: none; font-size: 1.05rem;">
                            Learn about our vetting process <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="bi bi-person-badge" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">ID Verification</h4>
                            <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Government ID check for all contractors</p>
                        </div>
                        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="bi bi-search" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Background Check</h4>
                            <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Comprehensive criminal background screening</p>
                        </div>
                        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="bi bi-star-fill" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Verified Reviews</h4>
                            <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Real feedback from real families</p>
                        </div>
                        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="bi bi-lock-fill" style="color: white; font-size: 1.25rem;"></i>
                            </div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Secure Payments</h4>
                            <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Encrypted transactions via Stripe</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="why-section">
            <div class="container">
                <div class="section-header">
                    <h2>Why Choose <span>CAS Private Care</span></h2>
                    <p>We make finding quality care simple, safe, and reliable</p>
                </div>
                
                <div class="why-grid">
                    <div class="why-card" data-animate>
                        <div class="why-card-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Verified Contractors</h3>
                        <p>All contractors undergo thorough background checks and credential verification before joining our platform.</p>
                    </div>
                    
                    <div class="why-card" data-animate>
                        <div class="why-card-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3>Flexible Scheduling</h3>
                        <p>Book on-demand or schedule recurring appointments. Our platform adapts to your family's unique needs.</p>
                    </div>
                    
                    <div class="why-card" data-animate>
                        <div class="why-card-icon">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <h3>Secure Payments</h3>
                        <p>Safe, encrypted payment processing powered by Stripe. Multiple payment options for your convenience.</p>
                    </div>
                    
                    <div class="why-card" data-animate>
                        <div class="why-card-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Our dedicated support team is available around the clock to assist you with any questions or concerns.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Process Section -->
        <section class="process-section">
            <div class="container">
                <div class="section-header">
                    <h2>How It <span>Works</span></h2>
                    <p>Get started with quality care in just a few simple steps</p>
                </div>
                
                <div class="process-timeline">
                    <div class="process-step" data-animate>
                        <div class="process-number">1</div>
                        <h3>Browse & Select</h3>
                        <p>Search for contractors, review profiles, credentials, and ratings to find the perfect match.</p>
                    </div>
                    
                    <div class="process-step" data-animate>
                        <div class="process-number">2</div>
                        <h3>Book & Schedule</h3>
                        <p>Choose your preferred schedule and book instantly with secure online payments.</p>
                    </div>
                    
                    <div class="process-step" data-animate>
                        <div class="process-number">3</div>
                        <h3>Connect & Care</h3>
                        <p>Your contractor arrives on time and provides exceptional care services for your family.</p>
                    </div>
                    
                    <div class="process-step" data-animate>
                        <div class="process-number">4</div>
                        <h3>Rate & Review</h3>
                        <p>Share your experience to help others and build trust within the community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>Ready to Get Started?</h2>
                <p>Join thousands of New York families who trust CAS Private Care for quality caregiving and housekeeping services.</p>
                <div class="cta-buttons">
                    <a href="{{ url('/register') }}" class="btn-cta-primary">
                        <i class="bi bi-person-plus"></i>
                        Sign Up Now
                    </a>
                    <a href="{{ url('/contact') }}" class="btn-cta-secondary">
                        <i class="bi bi-telephone"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </section>
    </main>
    
    @include('partials.footer')
    
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
</body>
</html>
