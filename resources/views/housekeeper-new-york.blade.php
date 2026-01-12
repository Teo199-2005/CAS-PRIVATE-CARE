<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Housekeeper New York | Verified & Trusted | CAS Private Care</title>
    <meta name="title" content="Housekeeper New York | Verified & Trusted | CAS Private Care">
    <meta name="description" content="Find verified housekeepers in New York. Background-checked professionals for home cleaning, organizing, and housekeeping services. Book online. Available 24/7.">
    <meta name="keywords" content="housekeeper new york, professional cleaner new york, nyc housekeeper, hire housekeeper new york, home cleaning services, housekeeping nyc">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/housekeeper-new-york') }}">
    
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
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
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
            color: #1e40af;
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
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
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
            color: #1e40af;
        }

        .location-content .subtitle {
            color: #64748b;
            margin-bottom: 1rem;
        }

        .location-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .location-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .location-link:hover {
            color: #1e40af;
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
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .service-card:nth-child(2) .service-icon {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);
        }

        .service-card:nth-child(3) .service-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .service-card:nth-child(4) .service-icon {
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
            color: #1e40af;
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
            background: linear-gradient(135deg, #3b82f6, #1e40af);
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
            color: #1e40af;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.7;
        }

        /* Pricing Section */
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
            border: 3px solid #3b82f6;
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'POPULAR';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #3b82f6, #1e40af);
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
            color: #1e40af;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            color: #3b82f6;
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
            background: linear-gradient(135deg, #3b82f6, #1e40af);
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

        /* FAQ Section */
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .faq-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .faq-item:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .faq-question {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .faq-question i {
            color: #3b82f6;
        }

        .faq-answer {
            color: #64748b;
            line-height: 1.8;
            font-size: 1.05rem;
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
            <h1>Verified Housekeepers in <span style="color: #fbbf24;">New York</span></h1>
            <p class="tagline">Trusted Housekeeping Services</p>
            <p>Find experienced, background-checked housekeepers in New York for home cleaning, organizing, laundry, and housekeeping services. Available 24/7 across all NYC boroughs including Manhattan, Brooklyn, Queens, Bronx, and Staten Island.</p>
            
            <div class="hero-buttons">
                <a href="{{ url('/register?partner=housekeeper') }}" class="btn-primary">Find Your Housekeeper in New York</a>
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
            <h3 style="font-size: 2rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem;">
                TRUSTED BY <span style="color: #f97316;">1,000+ FAMILIES</span> IN NEW YORK
            </h3>
        </div>
    </section>

    <!-- Locations Section -->
    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Professional Housekeepers Available Throughout <span style="color: #f97316;">New York</span></h2>
                <p>CAS Private Care provides verified, professional Housekeeper services across all five boroughs of New York City.</p>
            </div>

            <div class="location-grid">
                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1546436836-07a91091f160?w=600" alt="Manhattan" class="location-image">
                    <div class="location-content">
                        <h3>Manhattan</h3>
                        <p class="subtitle">Upper East Side to Lower Manhattan</p>
                        <span class="location-badge"><i class="bi bi-clock"></i> 24/7 Available</span>
                        <p>Professional Housekeeper services throughout Manhattan. Available 24/7 for immediate care needs.</p>
                        <a href="{{ url('/Housekeeper-manhattan') }}" class="location-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1490644658840-3f2e3f8c5625?w=600" alt="Brooklyn" class="location-image">
                    <div class="location-content">
                        <h3>Brooklyn</h3>
                        <p class="subtitle">Park Slope to Brighton Beach</p>
                        <span class="location-badge"><i class="bi bi-geo-alt"></i> All Neighborhoods</span>
                        <p>Trusted Housekeepers serving all Brooklyn neighborhoods. We're here for your care needs.</p>
                        <a href="{{ url('/Housekeeper-brooklyn') }}" class="location-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600" alt="Queens" class="location-image">
                    <div class="location-content">
                        <h3>Queens</h3>
                        <p class="subtitle">Astoria, Flushing, Jamaica & More</p>
                        <span class="location-badge"><i class="bi bi-house-heart"></i> In-Home Care</span>
                        <p>Reliable Housekeeper services across Queens. Comprehensive in-home care support.</p>
                        <a href="{{ url('/Housekeeper-queens') }}" class="location-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2>Our Housekeeping <span style="color: #f97316;">Services</span> in New York</h2>
                <p>CAS Private Care offers comprehensive housekeeping services throughout New York, providing professional cleaning and home maintenance support for all your needs.</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <h3>Regular Home Cleaning</h3>
                    <p>Comprehensive home cleaning services to keep your space spotless and organized.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Dusting and vacuuming</li>
                        <li><i class="bi bi-check-circle-fill"></i> Kitchen and bathroom cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Floor mopping and care</li>
                        <li><i class="bi bi-check-circle-fill"></i> Surface sanitization</li>
                        <li><i class="bi bi-check-circle-fill"></i> Trash removal</li>
                    </ul>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <h3>Deep Cleaning Services</h3>
                    <p>Thorough deep cleaning for a fresh, spotless home from top to bottom.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Carpet and upholstery cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Window washing</li>
                        <li><i class="bi bi-check-circle-fill"></i> Appliance cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Baseboard and ceiling cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Closet organization</li>
                    </ul>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-basket"></i>
                    </div>
                    <h3>Laundry & Organization</h3>
                    <p>Professional laundry services and home organization to simplify your daily routine.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check-circle-fill"></i> Washing and folding</li>
                        <li><i class="bi bi-check-circle-fill"></i> Ironing and pressing</li>
                        <li><i class="bi bi-check-circle-fill"></i> Linen changes</li>
                        <li><i class="bi bi-check-circle-fill"></i> Closet organizing</li>
                        <li><i class="bi bi-check-circle-fill"></i> Decluttering services</li>
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
                <p>When searching for a Housekeeper in New York, you want assurance that you're choosing a trusted, reliable service.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Fully Verified</h3>
                    <p>Every Housekeeper undergoes comprehensive verification including criminal background checks, license verification, and professional reference checks.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h3>Licensed & Insured</h3>
                    <p>All Housekeepers providing medical services are licensed by New York State Department of Health with full insurance coverage.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3>Available 24/7</h3>
                    <p>Emergency Housekeepers available within 4-6 hours. Our extensive network ensures we can match you with a qualified professional anytime.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h3>Transparent Pricing</h3>
                    <p>Competitive rates with no hidden fees. Flexible hourly, daily, and monthly packages available. Get a personalized quote based on your specific care needs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Housekeepers by Borough Section -->
    <section class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2>Top-Rated <span style="color: #f97316;">Housekeepers</span> in Your Borough</h2>
                <p>Meet experienced, verified housekeepers trusted by families throughout New York City.</p>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #1e40af); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Manhattan Housekeeper</span>
                    </div>
                    <h3>Maria Rodriguez</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 4.9/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">8+ Years Experience • Background Checked</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Deep cleaning specialist</li>
                        <li><i class="bi bi-check-circle-fill"></i> Eco-friendly products</li>
                        <li><i class="bi bi-check-circle-fill"></i> Bilingual (English/Spanish)</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #3b82f6;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "Maria is wonderful! My apartment has never looked better and she's so detail-oriented."
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- Robert L., Manhattan</p>
                    </div>
                </div>

                <div class="pricing-card popular">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #f97316, #ea580c); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Brooklyn Housekeeper</span>
                    </div>
                    <h3>James Chen</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 5.0/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">6+ Years Experience • Professional Cleaner</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Move-in/move-out specialist</li>
                        <li><i class="bi bi-check-circle-fill"></i> Carpet & upholstery cleaning</li>
                        <li><i class="bi bi-check-circle-fill"></i> Window washing expert</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #f97316;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "James did an amazing job with our move-out cleaning. Spotless results!"
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- Amanda M., Brooklyn</p>
                    </div>
                </div>

                <div class="pricing-card">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                        </div>
                        <span style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Queens Housekeeper</span>
                    </div>
                    <h3>Sarah Johnson</h3>
                    <div class="price" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 4.8/5</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1rem;">5+ Years Experience • Organizing Specialist</p>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Home organization expert</li>
                        <li><i class="bi bi-check-circle-fill"></i> Laundry & ironing services</li>
                        <li><i class="bi bi-check-circle-fill"></i> Reliable & detail-oriented</li>
                    </ul>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin: 1rem 0; border-left: 4px solid #10b981;">
                        <p style="font-size: 0.9rem; color: #475569; font-style: italic; margin: 0;">
                            "Sarah transformed our home! Everything is organized and always clean."
                        </p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.5rem 0 0 0;">- David W., Queens</p>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 3rem; padding: 2.5rem; background: white; border-radius: 20px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);">
                <h3 style="font-size: 1.8rem; color: #1e40af; margin-bottom: 1rem;">
                    <i class="bi bi-people-fill" style="color: #3b82f6;"></i> 
                    500+ Verified Housekeepers Across NYC
                </h3>
                <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 1.5rem;">
                    Browse Housekeepers in Manhattan, Brooklyn, Queens, Bronx, and Staten Island
                </p>
                <div style="display: flex; gap: 1.5rem; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 1.5rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #3b82f6;">100%</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Background Checked</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #f97316;">4.9/5</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Average Rating</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">24/7</div>
                        <div style="color: #64748b; font-size: 0.95rem;">Available Support</div>
                    </div>
                </div>
                <a href="{{ url('/register') }}" class="btn-secondary" style="margin-top: 2rem; display: inline-block;">Browse All Housekeepers</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Find Your Housekeeper in New York?</h2>
            <p>Join 1,000+ families who trust CAS Private Care for their Housekeeper needs</p>
            <div class="hero-buttons">
                <a href="{{ url('/register?partner=housekeeper') }}" class="btn-primary">Get Started Today</a>
                <a href="tel:+16462828282" class="btn-secondary"><i class="bi bi-telephone"></i> Call: (646) 282-8282</a>
            </div>
        </div>
    </section>

    @include('partials.footer')
    @include('partials.mobile-footer')
</body>
</html>

