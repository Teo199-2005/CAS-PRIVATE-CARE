<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Accredited Training Center | CAS Private Care - Professional Caregiver Certification NYC</title>
    <meta name="title" content="Accredited Training Center | CAS Private Care - Professional Caregiver Certification NYC">
    <meta name="description" content="Get certified at our accredited training centers in NYC. Professional caregiver training, HHA certification, and specialized care programs in Manhattan, Brooklyn, Queens.">
    <meta name="keywords" content="caregiver training nyc, HHA certification, home health aide training, caregiver certification manhattan, brooklyn training center">
    <link rel="canonical" href="{{ url('/training-center') }}">
    
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
            margin-top: 88px;
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%);
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
            color: #0B4FA2;
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

        .accreditation-badges {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 3rem;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .badge i {
            font-size: 1.5rem;
            color: #fbbf24;
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
            background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Training Locations Grid */
        .locations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
        }

        .location-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .location-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .location-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .location-content {
            padding: 2.5rem;
        }

        .location-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0B4FA2, #1e40af);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .location-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #0B4FA2;
        }

        .location-info {
            margin: 1.5rem 0;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .info-item i {
            color: #0B4FA2;
            font-size: 1.3rem;
            margin-top: 0.2rem;
        }

        .info-item strong {
            color: #0B4FA2;
            display: block;
            margin-bottom: 0.25rem;
        }

        .info-item span {
            color: #64748b;
            font-size: 0.95rem;
        }

        .location-features {
            list-style: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e2e8f0;
        }

        .location-features li {
            padding: 0.5rem 0;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .location-features li i {
            color: #10b981;
            font-size: 1.1rem;
        }

        .location-cta {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-location {
            flex: 1;
            min-width: 140px;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .btn-directions {
            background: linear-gradient(135deg, #0B4FA2, #1e40af);
            color: white;
        }

        .btn-call {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        /* Programs Section */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .program-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s;
        }

        .program-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .program-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #0B4FA2, #1e40af);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(11, 79, 162, 0.3);
        }

        .program-card:nth-child(2) .program-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .program-card:nth-child(3) .program-icon {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3);
        }

        .program-card:nth-child(4) .program-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
        }

        .program-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .program-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #0B4FA2;
        }

        .program-duration {
            display: inline-block;
            background: #e0f2fe;
            color: #0B4FA2;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #0B4FA2, #1e40af);
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

            .locations-grid {
                grid-template-columns: 1fr;
            }

            .location-cta {
                flex-direction: column;
            }

            .btn-location {
                min-width: 100%;
            }

            /* Mobile Training Center Layout */
            .container > div[style*="grid-template-columns"] {
                display: flex !important;
                flex-direction: column !important;
                gap: 2rem !important;
            }

            /* Mobile Image Styling */
            .container img[alt*="Training Center"] {
                height: 250px !important;
                border-radius: 16px !important;
                margin-bottom: 1.5rem !important;
            }

            /* Mobile Info Card */
            .container > div > div > div[style*="background: white"] {
                padding: 2rem !important;
                border-radius: 16px !important;
            }

            /* Mobile Location Badge */
            div[style*="position: absolute"][style*="top: 20px"] {
                top: 15px !important;
                left: 15px !important;
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }

            /* Mobile Section Headers */
            .container h3[style*="font-size: 1.8rem"] {
                font-size: 1.4rem !important;
                margin-bottom: 1.5rem !important;
            }

            .container h4[style*="font-size: 1.2rem"] {
                font-size: 1.1rem !important;
            }

            /* Mobile Info Items */
            div[style*="padding: 1.25rem"][style*="border-radius: 16px"] {
                padding: 1rem !important;
            }

            /* Mobile Call Button */
            a[href^="tel"][style*="display: block"] {
                padding: 1rem !important;
                font-size: 1rem !important;
            }

            /* Mobile Map */
            div[style*="border-radius: 24px"] iframe {
                height: 400px !important;
            }

            /* Remove sticky on mobile */
            div[style*="position: sticky"] {
                position: static !important;
            }

            .hero {
                padding: 4rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .accreditation-badges {
                flex-direction: column;
            }

            .cta-section {
                padding: 4rem 1.5rem;
            }

            .cta-section h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 1.8rem;
            }

            .container img[alt*="Training Center"] {
                height: 200px !important;
            }

            .container h3 {
                font-size: 1.2rem !important;
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
    </style>
</head>
<body>
    @include('partials.navigation')

    {{-- Trust strip removed from training center - not relevant to training content --}}

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Accredited <span style="color: #fbbf24;">Training Center</span></h1>
            <p class="tagline">Professional Caregiver Certification Programs in NYC</p>
            <p>Get certified with New York State approved training programs. Launch your career in caregiving with hands-on training, expert instructors, and job placement assistance.</p>

            <div class="accreditation-badges">
                <div class="badge">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>NYS DOH Approved</span>
                </div>
                <div class="badge">
                    <i class="bi bi-award-fill"></i>
                    <span>Accredited Programs</span>
                </div>
                <div class="badge">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Certified Instructors</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Training Programs Overview -->
    <section class="section-light" style="border-bottom: 1px solid #e2e8f0;">
        <div class="container">
            <div class="section-header">
                <h2>Training <span style="color: #f97316;">Programs</span></h2>
                <p>Comprehensive certification programs to launch or advance your caregiving career</p>
            </div>
            
            <div class="training-programs-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <!-- HHA Program -->
                <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border-top: 4px solid #3b82f6; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <i class="bi bi-heart-pulse-fill" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; font-size: 1.25rem;">Home Health Aide (HHA)</h3>
                    <p style="color: #64748b; line-height: 1.7; margin-bottom: 1.5rem;">NYS-approved 76-hour program. Learn personal care, nutrition, and safety procedures.</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #dbeafe; border-radius: 50px; font-size: 0.8rem; color: #1e40af; font-weight: 600;">
                            <i class="bi bi-clock"></i> 2-3 Weeks
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #dcfce7; border-radius: 50px; font-size: 0.8rem; color: #166534; font-weight: 600;">
                            <i class="bi bi-patch-check-fill"></i> State Certified
                        </span>
                    </div>
                </div>
                
                <!-- PCA Program -->
                <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border-top: 4px solid #10b981; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <i class="bi bi-person-heart" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; font-size: 1.25rem;">Personal Care Aide (PCA)</h3>
                    <p style="color: #64748b; line-height: 1.7; margin-bottom: 1.5rem;">40-hour foundational program covering daily living assistance and communication skills.</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #dcfce7; border-radius: 50px; font-size: 0.8rem; color: #166534; font-weight: 600;">
                            <i class="bi bi-clock"></i> 1-2 Weeks
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #fef3c7; border-radius: 50px; font-size: 0.8rem; color: #92400e; font-weight: 600;">
                            <i class="bi bi-star-fill"></i> Entry Level
                        </span>
                    </div>
                </div>
                
                <!-- CPR/First Aid -->
                <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border-top: 4px solid #f97316; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <i class="bi bi-plus-circle-fill" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; font-size: 1.25rem;">CPR & First Aid</h3>
                    <p style="color: #64748b; line-height: 1.7; margin-bottom: 1.5rem;">American Heart Association certified training. Essential emergency response skills.</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #ffedd5; border-radius: 50px; font-size: 0.8rem; color: #9a3412; font-weight: 600;">
                            <i class="bi bi-clock"></i> 1 Day
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #dbeafe; border-radius: 50px; font-size: 0.8rem; color: #1e40af; font-weight: 600;">
                            <i class="bi bi-award-fill"></i> AHA Certified
                        </span>
                    </div>
                </div>
                
                
            </div>
        </div>
    </section>

    <!-- Certificates You'll Earn -->
    <section class="section-dark" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
        <div class="container">
            <div class="section-header">
                <h2>Certificates <span style="color: #10b981;">You'll Earn</span></h2>
                <p>Industry-recognized credentials to boost your career</p>
            </div>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; margin-top: 3rem;">
                <div style="background: white; border-radius: 16px; padding: 1.5rem 2.5rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1rem; min-width: 280px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-file-earmark-medical" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; color: #1e293b; margin: 0;">HHA Certificate</h4>
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">NYS DOH Approved</p>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 16px; padding: 1.5rem 2.5rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1rem; min-width: 280px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-heart-pulse" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; color: #1e293b; margin: 0;">CPR Certification</h4>
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">American Heart Assoc.</p>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 16px; padding: 1.5rem 2.5rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1rem; min-width: 280px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-bandaid" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; color: #1e293b; margin: 0;">First Aid</h4>
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Emergency Response</p>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 16px; padding: 1.5rem 2.5rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1rem; min-width: 280px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; color: #1e293b; margin: 0;">Infection Control</h4>
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Safety Protocols</p>
                    </div>
                </div>
            </div>
            
            <p style="text-align: center; margin-top: 2.5rem; color: #64748b; font-size: 1rem;">
                <i class="bi bi-info-circle" style="color: #10b981;"></i> All certificates are recognized by healthcare employers throughout New York State
            </p>
        </div>
    </section>

    <!-- Manhattan Training Center -->
    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2><i class="bi bi-building" style="color: #0B4FA2;"></i> Manhattan Training Center</h2>
                <p style="color: #f97316; font-weight: 600; font-size: 1.1rem;">Premier training facility in the heart of Manhattan</p>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: start; max-width: 1400px; margin: 0 auto;">
                <!-- Left Side - Image & Details -->
                <div>
                    <div style="position: relative; margin-bottom: 2.5rem;">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800" alt="Manhattan Training Center Building" style="width: 100%; height: 400px; object-fit: cover; border-radius: 24px; box-shadow: 0 20px 60px rgba(11, 79, 162, 0.15);">
                        <div style="position: absolute; top: 20px; left: 20px; background: linear-gradient(135deg, #0B4FA2, #1e40af); color: white; padding: 0.75rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 700; box-shadow: 0 8px 20px rgba(11, 79, 162, 0.3);">
                            <i class="bi bi-geo-alt-fill"></i> MANHATTAN
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 3rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;">
                        <h3 style="color: #0B4FA2; font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem;">Location Details</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 16px; border-left: 4px solid #0B4FA2;">
                                <i class="bi bi-geo-alt-fill" style="color: #0B4FA2; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #0B4FA2; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Address</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">350 5th Avenue, Suite 4820<br>New York, NY 10118</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 16px; border-left: 4px solid #0B4FA2;">
                                <i class="bi bi-telephone-fill" style="color: #0B4FA2; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #0B4FA2; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Phone</strong>
                                    <span style="color: #475569; font-size: 1rem;">(646) 282-8282</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 16px; border-left: 4px solid #0B4FA2;">
                                <i class="bi bi-clock-fill" style="color: #0B4FA2; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #0B4FA2; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Hours</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">Mon-Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 4:00 PM</span>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2.5rem; padding-top: 2rem; border-top: 2px solid #e2e8f0;">
                            <h4 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem;">Facilities & Features</h4>
                            <ul style="list-style: none; display: grid; gap: 1rem;">
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Modern simulation labs
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Fully equipped training rooms
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Near subway stations (34th St)
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Weekend classes available
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Job placement assistance
                                </li>
                            </ul>
                        </div>

                        <a href="tel:+16462828282" style="display: block; margin-top: 2.5rem; padding: 1.25rem; border-radius: 16px; font-weight: 700; font-size: 1.1rem; text-decoration: none; text-align: center; background: linear-gradient(135deg, #f97316, #ea580c); color: white; box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3); transition: all 0.3s;">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>

                <!-- Right Side - Map -->
                <div style="position: sticky; top: 100px;">
                    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.6174228824946!2d-73.98823492346697!3d40.74844097138757!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1735671000000!5m2!1sen!2sus"
                            width="100%" 
                            height="700" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brooklyn Training Center -->
    <section class="section-dark">
        <div class="container">
            <div class="section-header">
                <h2><i class="bi bi-building" style="color: #10b981;"></i> Brooklyn Training Center</h2>
                <p style="color: #f97316; font-weight: 600; font-size: 1.1rem;">Modern facility in the heart of downtown Brooklyn</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 4rem; align-items: start; max-width: 1400px; margin: 0 auto;">
                <!-- Left Side - Map -->
                <div style="position: sticky; top: 100px;">
                    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3025.441943665393!2d-73.98350632346964!3d40.69266927139149!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a4b6e6a3b2d%3A0x8c1c3c5c2c3c3c3c!2s445%20Albee%20Square%20W%2C%20Brooklyn%2C%20NY%2011201!5e0!3m2!1sen!2sus!4v1735671000000!5m2!1sen!2sus"
                            width="100%" 
                            height="700" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <!-- Right Side - Image & Details -->
                <div>
                    <div style="position: relative; margin-bottom: 2.5rem;">
                        <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800" alt="Brooklyn Training Center Building" style="width: 100%; height: 400px; object-fit: cover; border-radius: 24px; box-shadow: 0 20px 60px rgba(16, 185, 129, 0.15);">
                        <div style="position: absolute; top: 20px; left: 20px; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.75rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 700; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-geo-alt-fill"></i> BROOKLYN
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 3rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;">
                        <h3 style="color: #10b981; font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem;">Location Details</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 16px; border-left: 4px solid #10b981;">
                                <i class="bi bi-geo-alt-fill" style="color: #10b981; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #10b981; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Address</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">445 Albee Square West<br>Brooklyn, NY 11201</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 16px; border-left: 4px solid #10b981;">
                                <i class="bi bi-telephone-fill" style="color: #10b981; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #10b981; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Phone</strong>
                                    <span style="color: #475569; font-size: 1rem;">(718) 555-0142</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 16px; border-left: 4px solid #10b981;">
                                <i class="bi bi-clock-fill" style="color: #10b981; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #10b981; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Hours</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">Mon-Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 3:00 PM</span>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2.5rem; padding-top: 2rem; border-top: 2px solid #e2e8f0;">
                            <h4 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem;">Facilities & Features</h4>
                            <ul style="list-style: none; display: grid; gap: 1rem;">
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Spacious training facilities
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Clinical practice areas
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Near downtown Brooklyn
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Flexible class schedules
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Career counseling services
                                </li>
                            </ul>
                        </div>

                        <a href="tel:+17185550142" style="display: block; margin-top: 2.5rem; padding: 1.25rem; border-radius: 16px; font-weight: 700; font-size: 1.1rem; text-decoration: none; text-align: center; background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3); transition: all 0.3s;">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Queens Training Center -->
    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2><i class="bi bi-building" style="color: #f97316;"></i> Queens Training Center</h2>
                <p style="color: #f97316; font-weight: 600; font-size: 1.1rem;">Brand new state-of-the-art facility in Long Island City</p>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: start; max-width: 1400px; margin: 0 auto;">
                <!-- Left Side - Image & Details -->
                <div>
                    <div style="position: relative; margin-bottom: 2.5rem;">
                        <img src="https://images.unsplash.com/photo-1560440021-33f9b867899d?w=800" alt="Queens Training Center Building" style="width: 100%; height: 400px; object-fit: cover; border-radius: 24px; box-shadow: 0 20px 60px rgba(249, 115, 22, 0.15);">
                        <div style="position: absolute; top: 20px; left: 20px; background: linear-gradient(135deg, #f97316, #ea580c); color: white; padding: 0.75rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 700; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);">
                            <i class="bi bi-geo-alt-fill"></i> QUEENS
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 3rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;">
                        <h3 style="color: #f97316; font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem;">Location Details</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 16px; border-left: 4px solid #f97316;">
                                <i class="bi bi-geo-alt-fill" style="color: #f97316; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #f97316; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Address</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">37-11 35th Avenue<br>Long Island City, NY 11101</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 16px; border-left: 4px solid #f97316;">
                                <i class="bi bi-telephone-fill" style="color: #f97316; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #f97316; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Phone</strong>
                                    <span style="color: #475569; font-size: 1rem;">(718) 555-0198</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: start; gap: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 16px; border-left: 4px solid #f97316;">
                                <i class="bi bi-clock-fill" style="color: #f97316; font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #f97316; display: block; margin-bottom: 0.5rem; font-size: 1.05rem;">Hours</strong>
                                    <span style="color: #475569; font-size: 1rem; line-height: 1.6;">Mon-Fri: 8:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM</span>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2.5rem; padding-top: 2rem; border-top: 2px solid #e2e8f0;">
                            <h4 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem;">Facilities & Features</h4>
                            <ul style="list-style: none; display: grid; gap: 1rem;">
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Brand new facilities (2024)
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Advanced medical equipment
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Easy access from all boroughs
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Evening classes offered
                                </li>
                                <li style="color: #475569; display: flex; align-items: center; gap: 1rem; font-size: 1rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i> Free parking available
                                </li>
                            </ul>
                        </div>

                        <a href="tel:+17185550198" style="display: block; margin-top: 2.5rem; padding: 1.25rem; border-radius: 16px; font-weight: 700; font-size: 1.1rem; text-decoration: none; text-align: center; background: linear-gradient(135deg, #f97316, #ea580c); color: white; box-shadow: 0 8px 25px rgba(249, 115, 22, 0.3); transition: all 0.3s;">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>

                <!-- Right Side - Map -->
                <div style="position: sticky; top: 100px;">
                    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.2686753967693!2d-73.93926842346666!3d40.75377867138649!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2592f3e3e3e3e%3A0x1e3e3e3e3e3e3e3e!2s37-11%2035th%20Ave%2C%20Long%20Island%20City%2C%20NY%2011101!5e0!3m2!1sen!2sus!4v1735671000000!5m2!1sen!2sus"
                            width="100%" 
                            height="700" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Caregiving Career?</h2>
            <p>Enroll today and get certified in as little as 2 weeks</p>
            <div class="hero-buttons">
                <a href="{{ url('/register') }}" class="btn-primary">Enroll Now</a>
                <a href="tel:+16462828282" class="btn-secondary"><i class="bi bi-telephone"></i> Call: (646) 282-8282</a>
            </div>
            <p style="margin-top: 2rem; font-size: 1rem; opacity: 0.9;">
                <i class="bi bi-info-circle"></i> Financial aid and payment plans available
            </p>
        </div>
    </section>

    @include('partials.footer')
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
