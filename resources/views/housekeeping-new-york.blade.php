<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <title>Housekeeping Services New York | Professional House Cleaners NYC</title>
    <meta name="description" content="Find verified housekeepers in New York. Background-checked cleaning professionals for homes across Manhattan, Brooklyn, Queens. Available 24/7.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    @include('partials.nav-footer-styles')
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
        }

        .hero {
            margin-top: 80px;
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-align: center;
        }

        .hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .hero .tagline {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #fbbf24;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: white;
            color: #059669;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        section {
            padding: 6rem 2rem;
        }

        .section-light { background: #ffffff; }
        .section-dark { background: #f8fafc; }

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
            color: #059669;
        }

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

        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-icon i {
            font-size: 2.5rem;
            color: white;
        }

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
        }

        .pricing-card.popular {
            border: 3px solid #10b981;
            transform: scale(1.05);
        }

        .cta-section {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-align: center;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .pricing-card.popular { transform: scale(1); }
        }
    </style>
</head>
<body>
    @include('partials.navigation')

    <section class="hero">
        <div class="container">
            <h1>Housekeeping Services in <span style="color: #fbbf24;">New York</span></h1>
            <p class="tagline">Professional House Cleaning & Home Care</p>
            <p style="font-size: 1.2rem;">Find experienced, background-checked housekeepers in New York for deep cleaning, organization, and home maintenance. Available 24/7 across all NYC boroughs.</p>
            
            <div class="hero-buttons">
                <a href="{{ url('/register') }}" class="btn-primary">Find a Housekeeper</a>
                <a href="#services" class="btn-secondary">View Services</a>
            </div>
        </div>
    </section>

    <section class="section-dark" style="padding: 3rem 2rem; text-align: center; background: #dbeafe;">
        <h3 style="font-size: 2rem; font-weight: 700; color: #059669;">
            TRUSTED BY <span style="color: #f97316;">1,000+ FAMILIES</span> IN NEW YORK
        </h3>
    </section>

    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Housekeepers Across <span style="color: #f97316;">NYC</span></h2>
                <p style="font-size: 1.2rem; color: #64748b;">Professional cleaning services in Manhattan, Brooklyn, Queens</p>
            </div>

            <div class="location-grid">
                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600" alt="Manhattan" class="location-image">
                    <div class="location-content">
                        <h3>Manhattan Housekeepers</h3>
                        <p style="color: #64748b;">Expert cleaning professionals serving all Manhattan neighborhoods</p>
                    </div>
                </div>
                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=600" alt="Brooklyn" class="location-image">
                    <div class="location-content">
                        <h3>Brooklyn Housekeepers</h3>
                        <p style="color: #64748b;">Trusted home cleaning services throughout Brooklyn</p>
                    </div>
                </div>
                <div class="location-card">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600" alt="Queens" class="location-image">
                    <div class="location-content">
                        <h3>Queens Housekeepers</h3>
                        <p style="color: #64748b;">Reliable housekeeping across all Queens areas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2>Our Housekeeping <span style="color: #f97316;">Services</span></h2>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <h3>Deep Cleaning</h3>
                    <p style="color: #64748b;">Thorough cleaning of all rooms, surfaces, and hard-to-reach areas</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3>Regular Maintenance</h3>
                    <p style="color: #64748b;">Weekly or bi-weekly cleaning to keep your home spotless</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-basket3"></i>
                    </div>
                    <h3>Laundry & Organization</h3>
                    <p style="color: #64748b;">Complete laundry services and home organization solutions</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Top-Rated <span style="color: #f97316;">Housekeepers</span></h2>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card">
                    <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                    </div>
                    <h3>Lisa Anderson</h3>
                    <div style="color: #fbbf24; font-size: 1.5rem; margin: 1rem 0;">
                        <i class="bi bi-star-fill"></i> 5.0/5
                    </div>
                    <p style="color: #64748b;">Manhattan • 10+ Years Experience</p>
                    <p style="font-style: italic; color: #64748b; margin-top: 1rem;">"Lisa is amazing! My apartment has never looked better."</p>
                </div>

                <div class="pricing-card popular">
                    <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                    </div>
                    <h3>Maria Santos</h3>
                    <div style="color: #fbbf24; font-size: 1.5rem; margin: 1rem 0;">
                        <i class="bi bi-star-fill"></i> 4.9/5
                    </div>
                    <p style="color: #64748b;">Brooklyn • 8+ Years Experience</p>
                    <p style="font-style: italic; color: #64748b; margin-top: 1rem;">"Maria is thorough and efficient. Highly recommend!"</p>
                </div>

                <div class="pricing-card">
                    <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                    </div>
                    <h3>Elena Vasquez</h3>
                    <div style="color: #fbbf24; font-size: 1.5rem; margin: 1rem 0;">
                        <i class="bi bi-star-fill"></i> 4.8/5
                    </div>
                    <p style="color: #64748b;">Queens • 9+ Years Experience</p>
                    <p style="font-style: italic; color: #64748b; margin-top: 1rem;">"Elena is wonderful. Always on time and does excellent work!"</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 style="font-size: 3rem; color: white;">Ready to Find Your Housekeeper?</h2>
            <p style="font-size: 1.3rem; margin: 1.5rem 0;">Join 1,000+ families who trust CAS Private Care</p>
            <div class="hero-buttons">
                <a href="{{ url('/register') }}" class="btn-primary">Get Started Today</a>
                <a href="tel:+16462828282" class="btn-secondary"><i class="bi bi-telephone"></i> (646) 282-8282</a>
            </div>
        </div>
    </section>

    @include('partials.footer')
    @include('partials.mobile-footer')
    @include('partials.cookie-consent')
</body>
</html>
