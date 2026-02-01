<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    
    <!-- Primary Meta Tags -->
    <title>Housekeeping & Personal Assistant New York | CAS Private Care</title>
    <meta name="title" content="Housekeeping & Personal Assistant New York | CAS Private Care">
    <meta name="description" content="Find verified housekeepers and personal assistants in New York. Background-checked professionals for home cleaning, organization, errands & personal support. Available 24/7.">
    <meta name="keywords" content="housekeeper new york, personal assistant nyc, home cleaning service, house cleaner manhattan, personal helper brooklyn">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/housekeeping-personal-assistant') }}">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
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
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.1;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero .tagline {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #fbbf24;
        }

        .hero p {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: white;
            color: #059669;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
        }

        .trust-badges {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 3rem;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .trust-badge i {
            font-size: 1.5rem;
        }

        /* Section Styles */
        section {
            padding: 6rem 2rem;
        }

        .section-light {
            background: #ffffff;
        }

        .section-dark {
            background: #f8fafc;
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
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Location Grid */
        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .location-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .location-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .location-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .location-content {
            padding: 2rem;
        }

        .location-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #059669;
        }

        .location-content .subtitle {
            color: #64748b;
            margin-bottom: 1rem;
        }

        .location-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .location-link {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .location-link:hover {
            color: #059669;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .service-card:nth-child(2) .service-icon {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);
        }

        .service-card:nth-child(3) .service-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
        }

        .service-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #059669;
        }

        .service-card p {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .service-features {
            list-style: none;
            text-align: left;
            margin-top: 1.5rem;
        }

        .service-features li {
            padding: 0.5rem 0;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .service-features li i {
            color: #10b981;
            font-size: 1rem;
        }

        /* Why Choose Us */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-icon i {
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #059669;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.7;
        }

        /* Pricing Cards */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .pricing-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }

        .pricing-card.popular {
            border: 3px solid #10b981;
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'POPULAR';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .pricing-card.popular:hover {
            transform: scale(1.05) translateY(-8px);
        }

        .pricing-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #059669;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 0.5rem;
        }

        .price span {
            font-size: 1rem;
            color: #64748b;
        }

        .pricing-features {
            list-style: none;
            margin: 2rem 0;
            text-align: left;
        }

        .pricing-features li {
            padding: 0.75rem 0;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .pricing-features li i {
            color: #10b981;
            font-size: 1.2rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-align: center;
            padding: 6rem 2rem;
        }

        .cta-section h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: white;
        }

        .cta-section p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.95);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .tagline {
                font-size: 1.3rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .trust-badges {
                flex-direction: column;
                align-items: center;
            }

            .pricing-card.popular {
                transform: scale(1);
            }

            .pricing-card.popular:hover {
                transform: translateY(-8px);
            }
        }
    </style>
</head>
<body>
    @include('partials.navigation')

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Housekeeping & Personal Assistants in <span style="color: #fbbf24;">New York</span></h1>
            <p class="tagline">Professional Home Care & Personal Support Services</p>
            <p>Find experienced, background-checked housekeepers and personal assistants in New York for home cleaning, organization, errands, and personal support. Available 24/7 across all NYC boroughs.</p>
            
            <div class="hero-buttons">
                <a href="{{ url('/register') }}" class="btn-primary">Find Your Helper in New York</a>
                <a href="#services" class="btn-secondary">View Our Services</a>
            </div>

            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>100% Verified & Background Checked</span>
                </div>
                <div class="trust-badge">
                    <i class="bi bi-award"></i>
                    <span>Licensed & Insured</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Banner -->
    <section class="section-dark" style="padding: 3rem 2rem; text-align: center; background: #dbeafe;">
        <div class="container">
            <h3 style="font-size: 2rem; font-weight: 700; color: #059669; margin-bottom: 0.5rem;">
                TRUSTED BY <span style="color: #f97316;">1,000+ FAMILIES</span> IN NEW YORK
            </h3>
        </div>
    </section>

    <!-- Locations Section -->
    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Professional Housekeepers Throughout <span style="color: #f97316;">New York</span></h2>
                <p>Find verified housekeepers and personal assistants across all NYC boroughs.</p>
            </div>

            <div class="location-grid">
                <div class="location-card">
                    <x-responsive-image 
                        src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600" 
                        alt="Manhattan Housekeeping Services" 
                        class="location-image"
                        :widths="[400, 600, 800]"
                        sizes="(max-width: 768px) 100vw, 33vw"
                    />
                    <div class="location-content">
                        <h3>Manhattan</h3>
                        <p class="subtitle">Professional Home Services</p>
                        <span class="location-badge"><i class="bi bi-house-check"></i> Verified Professionals</span>
                        <p>Expert housekeepers and personal assistants serving Manhattan. Detailed cleaning and organization services.</p>
                    </div>
                </div>

                <div class="location-card">
                    <x-responsive-image 
                        src="https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=600" 
                        alt="Brooklyn Housekeeping Services" 
                        class="location-image"
                        :widths="[400, 600, 800]"
                        sizes="(max-width: 768px) 100vw, 33vw"
                    />
                    <div class="location-content">
                        <h3>Brooklyn</h3>
                        <p class="subtitle">Home Care Excellence</p>
                        <span class="location-badge"><i class="bi bi-star-fill"></i> Top Rated</span>
                        <p>Trusted housekeeping professionals in Brooklyn. Comprehensive home care and personal assistance.</p>
                    </div>
                </div>

                <div class="location-card">
                    <x-responsive-image 
                        src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600" 
                        alt="Queens Housekeeping Services" 
                        class="location-image"
                        :widths="[400, 600, 800]"
                        sizes="(max-width: 768px) 100vw, 33vw"
                    />
                    <div class="location-content">
                        <h3>Queens</h3>
                        <p class="subtitle">Reliable Home Services</p>
                        <span class="location-badge"><i class="bi bi-clock-history"></i> 24/7 Available</span>
                        <p>Dependable housekeepers across Queens. Complete home management and personal support services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2>Our Housekeeping & Personal Assistant <span style="color: #f97316;">Services</span></h2>
                <p>Comprehensive home care and personal support services tailored to your needs.</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <h3>Home Cleaning Services</h3>
                    <p>Professional housekeeping to keep your home spotless, organized, and comfortable.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Deep cleaning & sanitization</li>
                        <li><i class="bi bi-check-circle-fill"></i> Regular maintenance cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Kitchen & bathroom cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Floor care & vacuuming</li>
                        <li><i class="bi bi-check-circle-fill"></i> Window cleaning</li>
                    </ul>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-basket3"></i>
                    </div>
                    <h3>Laundry & Organization</h3>
                    <p>Complete laundry services and home organization to simplify your daily life.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Washing & drying</li>
                        <li><i class="bi bi-check-circle-fill"></i> Ironing & folding</li>
                        <li><i class="bi bi-check-circle-fill"></i> Closet organization</li>
                        <li><i class="bi bi-check-circle-fill"></i> Decluttering services</li>
                        <li><i class="bi bi-check-circle-fill"></i> Storage solutions</li>
                    </ul>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <h3>Personal Assistant Services</h3>
                    <p>Professional support for errands, appointments, and daily task management.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Grocery shopping</li>
                        <li><i class="bi bi-check-circle-fill"></i> Errand running</li>
                        <li><i class="bi bi-check-circle-fill"></i> Meal preparation</li>
                        <li><i class="bi bi-check-circle-fill"></i> Appointment coordination</li>
                        <li><i class="bi bi-check-circle-fill"></i> Pet care support</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose <span style="color: #f97316;">CAS Private Care</span></h2>
                <p>The most trusted platform for housekeeping and personal assistant services in New York.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Fully Verified</h3>
                    <p>Every professional undergoes comprehensive background checks, reference verification, and identity confirmation for your peace of mind.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h3>Highly Rated</h3>
                    <p>Browse verified reviews from real families. Our professionals maintain high ratings and consistently deliver exceptional service.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3>Flexible Scheduling</h3>
                    <p>Book services on your schedule. One-time cleaning, regular visits, or ongoing support - we adapt to your needs.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h3>Transparent Pricing</h3>
                    <p>Clear, competitive rates with no hidden fees. Know exactly what you're paying for before booking.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Professionals Section -->
    <section class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2>Top-Rated <span style="color: #f97316;">Professionals</span> in Your Borough</h2>
                <p>Meet experienced housekeepers and personal assistants trusted by families throughout NYC.</p>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Manhattan Housekeeper</span>
                    </div>
                    <h3>Lisa Anderson</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 5.0/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">10+ Years Experience • Detail-Oriented</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Deep cleaning specialist</li>
                        <li><i class="bi bi-check-circle-fill"></i> Home organization expert</li>
                        <li><i class="bi bi-check-circle-fill"></i> Eco-friendly products</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #10b981;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "Lisa is amazing! My apartment has never looked better."
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- Jennifer K., Manhattan</p>
                    </div>
                </div>

                <div class="pricing-card popular">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #f97316, #ea580c); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Brooklyn Personal Assistant</span>
                    </div>
                    <h3>Michael Torres</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 4.9/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">7+ Years Experience • Multitasker</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Errand & shopping services</li>
                        <li><i class="bi bi-check-circle-fill"></i> Appointment coordination</li>
                        <li><i class="bi bi-check-circle-fill"></i> Pet care assistance</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #f97316;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "Michael handles everything so efficiently. He's a lifesaver!"
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- Rachel M., Brooklyn</p>
                    </div>
                </div>

                <div class="pricing-card">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Queens Housekeeper</span>
                    </div>
                    <h3>Elena Vasquez</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 4.8/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">9+ Years Experience • Reliable</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Thorough cleaning services</li>
                        <li><i class="bi bi-check-circle-fill"></i> Laundry & ironing</li>
                        <li><i class="bi bi-check-circle-fill"></i> Meal preparation</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #8b5cf6;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "Elena is wonderful. Always on time and does excellent work!"
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- Tom H., Queens</p>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 3rem; padding: 2.5rem; background: white; border-radius: 20px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);">
                <h3 style="font-size: 1.8rem; color: #059669; margin-bottom: 1rem;">
                    <i class="bi bi-people-fill" style="color: #10b981;"></i> 
                    300+ Verified Professionals Across NYC
                </h3>
                <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 1.5rem;">
                    Browse housekeepers and personal assistants in Manhattan, Brooklyn, Queens, and more
                </p>
                <div style="display: flex; gap: 1.5rem; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 1.5rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">100%</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Background Checked</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #f97316;">4.9/5</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Average Rating</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #8b5cf6;">24/7</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Available Support</div>
                    </div>
                </div>
                <a href="{{ url('/register') }}" class="btn-secondary" style="margin-top: 2rem; display: inline-block;">Browse All Professionals</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Find Your Housekeeper or Personal Assistant?</h2>
            <p>Join 1,000+ families who trust CAS Private Care for their home care needs</p>
            <div class="hero-buttons">
                <a href="{{ url('/register') }}" class="btn-primary">Get Started Today</a>
                <a href="tel:+16462828282" class="btn-secondary"><i class="bi bi-telephone"></i> Call: (646) 282-8282</a>
            </div>
        </div>
    </section>

    @include('partials.footer')
    @include('partials.mobile-footer')
    @include('partials.cookie-consent')
</body>
</html>
