<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Become a 1099 Contractor | CAS Private Care LLC - Join Our Network</title>
    <meta name="title" content="Become a 1099 Contractor | CAS Private Care LLC - Join Our Network">
    <meta name="description" content="Join CAS Private Care as an independent 1099 contractor. Flexible scheduling, choose your own clients, competitive rates. Caregivers and housekeepers wanted in NYC.">
    <meta name="keywords" content="1099 contractor caregiver, independent contractor housekeeping, caregiver jobs NYC, housekeeper contractor, flexible caregiver work, self-employed caregiver">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/contractors') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/contractors') }}">
    <meta property="og:title" content="Become a 1099 Contractor | CAS Private Care LLC">
    <meta property="og:description" content="Join our network of independent contractors. Flexible work, choose your clients, competitive pay.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/contractors') }}">
    <meta property="twitter:title" content="Become a 1099 Contractor | CAS Private Care LLC">
    <meta property="twitter:description" content="Join our network of independent contractors. Flexible work, choose your clients.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">
    
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

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease forwards;
        }

        /* Hero Section */
        .contractor-hero {
            margin-top: 80px;
            min-height: 90vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            position: relative;
            overflow: hidden;
        }

        .contractor-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="0,100 100,0 100,100" fill="rgba(255,255,255,0.03)"/></svg>');
            background-size: 100px 100px;
        }

        .contractor-hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content {
            color: white;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease;
        }

        .hero-badge i {
            color: #fef3c7;
        }

        .contractor-hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease 0.1s both;
        }

        .contractor-hero h1 span {
            color: #fef3c7;
        }

        .contractor-hero p {
            font-size: 1.25rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.8s ease 0.3s both;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .hero-feature i {
            color: #fef3c7;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .btn-hero-primary {
            background: white;
            color: #000;
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

        .btn-hero-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            color: #000;
        }

        .btn-hero-secondary {
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

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        .hero-image {
            position: relative;
            animation: fadeInRight 1s ease 0.3s both;
        }

        .hero-image img {
            width: 100%;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }

        .hero-stats-card {
            position: absolute;
            bottom: -30px;
            left: -30px;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            animation: float 3s ease-in-out infinite;
        }

        .hero-stats-card h4 {
            font-family: 'Sora', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: #ea580c;
        }

        .hero-stats-card p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Benefits Section */
        .benefits-section {
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

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .benefit-card {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border-radius: 24px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s;
            opacity: 0;
            border: 2px solid transparent;
        }

        .benefit-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            border-color: #f97316;
            box-shadow: 0 20px 50px rgba(249, 115, 22, 0.15);
        }

        .benefit-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .benefit-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }

        .benefit-card:hover .benefit-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .benefit-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .benefit-card p {
            color: #64748b;
            line-height: 1.8;
        }

        /* 1099 Info Section */
        .info-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }

        .info-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="80" cy="20" r="60" fill="rgba(255,255,255,0.03)"/></svg>');
            background-size: 400px 400px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .info-content {
            color: white;
        }

        .info-content h2 {
            font-family: 'Sora', sans-serif;
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .info-content h2 span {
            color: #fbbf24;
        }

        .info-content > p {
            font-size: 1.15rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .info-list {
            list-style: none;
        }

        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .info-list li i {
            color: #fbbf24;
            font-size: 1.25rem;
            margin-top: 0.2rem;
        }

        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s;
            opacity: 0;
        }

        .info-card.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .info-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .info-card i {
            font-size: 2.5rem;
            color: #fbbf24;
            margin-bottom: 1rem;
            display: block;
        }

        .info-card h4 {
            font-family: 'Sora', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .info-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        /* Requirements Section */
        .requirements-section {
            padding: 6rem 2rem;
            background: #f8fafc;
        }

        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3rem;
            margin-top: 3rem;
        }

        .requirement-category {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            opacity: 0;
        }

        .requirement-category.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .requirement-category h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .requirement-category h3 i {
            color: #f97316;
        }

        .requirement-list {
            list-style: none;
        }

        .requirement-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .requirement-list li:hover {
            background: #e0f2fe;
            transform: translateX(5px);
        }

        .requirement-list li i {
            color: #10b981;
            font-size: 1.25rem;
            margin-top: 0.1rem;
        }

        .requirement-list li span {
            color: #475569;
            line-height: 1.6;
        }

        /* How to Join Section */
        .join-section {
            padding: 6rem 2rem;
            background: white;
        }

        .join-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .join-step {
            text-align: center;
            position: relative;
            opacity: 0;
        }

        .join-step.visible {
            animation: fadeInUp 0.6s ease forwards;
        }

        .join-step::after {
            content: '';
            position: absolute;
            top: 50px;
            right: -50%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #f97316 0%, #3b82f6 100%);
        }

        .join-step:last-child::after {
            display: none;
        }

        .step-number {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-family: 'Sora', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
            position: relative;
            z-index: 2;
            transition: transform 0.3s;
        }

        .join-step:hover .step-number {
            transform: scale(1.1);
        }

        .join-step h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .join-step p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
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
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2.5rem;
        }

        .cta-btn {
            background: white;
            color: #ea580c;
            padding: 1.25rem 3rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .cta-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-image {
                display: none;
            }

            .hero-features {
                justify-content: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }

            .join-steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .join-step::after {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .contractor-hero h1 {
                font-size: 2.5rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .benefit-card {
                padding: 1.25rem;
            }
            
            .benefit-card h3 {
                font-size: 1rem;
            }
            
            .benefit-card p {
                font-size: 0.85rem;
            }
            
            .benefit-icon {
                width: 50px;
                height: 50px;
            }
            
            .benefit-icon i {
                font-size: 1.25rem;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }

            .join-steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .step-number {
                width: 70px;
                height: 70px;
                font-size: 1.75rem;
            }
            
            .join-step h3 {
                font-size: 1rem;
            }
            
            .join-step p {
                font-size: 0.8rem;
            }

            .cta-section h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .benefit-card {
                padding: 1rem;
            }
            
            .benefit-card h3 {
                font-size: 0.9rem;
            }
            
            .benefit-card p {
                font-size: 0.8rem;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            .benefit-icon {
                width: 40px;
                height: 40px;
            }
            
            .benefit-icon i {
                font-size: 1rem;
            }
            
            .join-steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .step-number {
                width: 55px;
                height: 55px;
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
            }
            
            .join-step h3 {
                font-size: 0.85rem;
            }
            
            .join-step p {
                font-size: 0.75rem;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    @include('partials.navigation')
    
    <main>
        <!-- Hero Section -->
        <section class="contractor-hero">
            <div class="hero-container">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-briefcase-fill"></i>
                        Independent 1099 Contractors
                    </div>
                    <h1>Be Your Own Boss as a <span>1099 Contractor</span></h1>
                    <p>Join CAS Private Care's network of independent contractors. Set your own schedule, choose your clients, and grow your caregiving or housekeeping business on your terms.</p>
                    
                    <div class="hero-features">
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            Flexible Schedule
                        </span>
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            Choose Your Clients
                        </span>
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            Weekly Payments
                        </span>
                    </div>
                    
                    <div class="hero-buttons">
                        <a href="{{ url('/register') }}" class="btn-hero-primary">
                            <i class="bi bi-person-plus-fill"></i>
                            Apply Now
                        </a>
                        <a href="{{ url('/faq') }}" class="btn-hero-secondary">
                            <i class="bi bi-question-circle"></i>
                            Learn More
                        </a>
                    </div>
                </div>
                
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=600&q=80" alt="Professional caregiver">
                    <div class="hero-stats-card">
                        <h4>500+</h4>
                        <p>Active Contractors</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <div class="container">
                <div class="section-header">
                    <h2>Why Work as a <span>1099 Contractor?</span></h2>
                    <p>Enjoy the freedom and flexibility of independent contracting</p>
                </div>
                
                <div class="benefits-grid">
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <h3>Set Your Own Schedule</h3>
                        <p>Work when you want. Accept bookings that fit your lifestyle and availability. You're in complete control of your time.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Choose Your Clients</h3>
                        <p>Review booking requests and choose the clients that are the right fit for you. Build lasting relationships on your terms.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3>Grow Your Business</h3>
                        <p>Build your reputation with reviews, expand your client base, and increase your earnings as you gain experience.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h3>Weekly Payments</h3>
                        <p>Get paid weekly via direct deposit. Track your earnings in real-time through your contractor dashboard.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Platform Support</h3>
                        <p>Access our booking system, client matching, and 24/7 support team. We handle the admin so you can focus on care.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h3>Work Near You</h3>
                        <p>Find clients in your area across all NYC boroughs. Filter opportunities by location and travel time.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 1099 Information Section -->
        <section class="info-section">
            <div class="container">
                <div class="info-grid">
                    <div class="info-content">
                        <h2>What Does <span>1099 Contractor</span> Mean?</h2>
                        <p>As a 1099 independent contractor, you're self-employed and run your own business. This gives you freedom and flexibility that traditional employment doesn't offer.</p>
                        
                        <ul class="info-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>You're self-employed</strong> — not an employee of CAS Private Care</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Set your own rates</strong> — negotiate directly with clients for your services</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Tax responsibility</strong> — you handle your own taxes as a business owner</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Business deductions</strong> — deduct eligible expenses like mileage and supplies</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Multiple clients</strong> — work with multiple families and grow your business</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="info-cards">
                        <div class="info-card" data-animate>
                            <i class="bi bi-clock-history"></i>
                            <h4>Flexible Hours</h4>
                            <p>Work part-time or full-time on your schedule</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-phone"></i>
                            <h4>Easy Booking</h4>
                            <p>Accept jobs through our mobile-friendly platform</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-cash-stack"></i>
                            <h4>Direct Payments</h4>
                            <p>Receive weekly payments via Stripe</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-star-fill"></i>
                            <h4>Build Your Brand</h4>
                            <p>Grow your reputation with client reviews</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Requirements Section -->
        <section class="requirements-section">
            <div class="container">
                <div class="section-header">
                    <h2>Contractor <span>Requirements</span></h2>
                    <p>What you need to join our network of independent contractors</p>
                </div>
                
                <div class="requirements-grid">
                    <div class="requirement-category" data-animate>
                        <h3><i class="bi bi-file-earmark-check"></i> Basic Requirements</h3>
                        <ul class="requirement-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Must be 18 years or older</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Legal authorization to work in the United States</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Valid government-issued photo ID</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Reliable transportation to client locations</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Smartphone with internet access for booking management</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="requirement-category" data-animate>
                        <h3><i class="bi bi-shield-check"></i> Verification Process</h3>
                        <ul class="requirement-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Complete background check (we cover the cost)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Identity verification</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Professional reference check</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Skills assessment (if applicable)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Certifications verification (HHA, CPR, etc.)</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="requirement-category" data-animate>
                        <h3><i class="bi bi-heart-pulse"></i> For Caregivers</h3>
                        <ul class="requirement-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Prior caregiving experience (1+ years preferred)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>HHA or CNA certification (preferred but not required)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>CPR/First Aid certification (or willingness to obtain)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Compassionate and patient demeanor</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="requirement-category" data-animate>
                        <h3><i class="bi bi-house-heart"></i> For Housekeepers</h3>
                        <ul class="requirement-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Prior housekeeping experience (1+ years preferred)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Knowledge of cleaning products and techniques</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Attention to detail and organization skills</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Physical ability to perform cleaning tasks</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How to Join Section -->
        <section class="join-section">
            <div class="container">
                <div class="section-header">
                    <h2>How to <span>Get Started</span></h2>
                    <p>Join our network in just a few simple steps</p>
                </div>
                
                <div class="join-steps">
                    <div class="join-step" data-animate>
                        <div class="step-number">1</div>
                        <h3>Apply Online</h3>
                        <p>Complete our simple application form with your information and experience.</p>
                    </div>
                    
                    <div class="join-step" data-animate>
                        <div class="step-number">2</div>
                        <h3>Get Verified</h3>
                        <p>Complete background check and submit required documents for verification.</p>
                    </div>
                    
                    <div class="join-step" data-animate>
                        <div class="step-number">3</div>
                        <h3>Set Up Profile</h3>
                        <p>Create your professional profile, set your availability, and connect your payment.</p>
                    </div>
                    
                    <div class="join-step" data-animate>
                        <div class="step-number">4</div>
                        <h3>Start Earning</h3>
                        <p>Browse available jobs, accept bookings, and start building your client base!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Payouts Explained Section -->
        <section style="padding: 5rem 2rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-top: 1px solid rgba(251, 191, 36, 0.3);">
            <div class="container">
                <div class="section-header">
                    <h2>How <span>Payouts</span> Work</h2>
                    <p>Transparent payment processing so you always know what to expect</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-credit-card-fill" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Client Pays</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">Clients pay through our secure platform at time of booking. All payments are processed via Stripe.</p>
                    </div>
                    
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-percent" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Platform Fee</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">A small platform fee is deducted to cover payment processing, support, and booking management.</p>
                    </div>
                    
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-calendar-week" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Weekly Payouts</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">Your earnings are deposited directly to your bank account every week via Stripe Connect.</p>
                    </div>
                    
                    
                </div>
            </div>
        </section>

        <!-- Contractor FAQ Section -->
        <section style="padding: 5rem 2rem; background: #f1f5f9;">
            <div class="container">
                <div class="section-header">
                    <h2>Contractor <span>FAQ</span></h2>
                    <p>Common questions from our independent contractors</p>
                </div>
                
                <div style="max-width: 800px; margin: 3rem auto 0;">
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                Am I an employee of CAS Private Care?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">No. As a 1099 independent contractor, you are self-employed. You control your own schedule, rates, and choose which jobs to accept. CAS Private Care provides the platform to connect you with clients.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                How do I handle taxes as a 1099 contractor?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">As an independent contractor, you're responsible for your own taxes. We recommend setting aside 25-30% of your earnings for taxes and consulting with a tax professional. You may be able to deduct business expenses like mileage, supplies, and professional development.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                Can I work for other platforms or clients?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">Absolutely! As an independent contractor, you're free to work with other platforms, agencies, or private clients. Many of our contractors diversify their income sources.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                What happens if I need to cancel a booking?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">We understand that life happens. You can cancel bookings through your dashboard with as much notice as possible. Frequent last-minute cancellations may affect your profile rating and visibility on the platform.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                Do I need my own equipment or supplies?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">For housekeepers, clients typically provide cleaning supplies, but you may bring your preferred products. For caregivers, the client's home has what you need. You'll want reliable transportation to get to client locations.</p>
                        </details>
                    </div>
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
    @include('partials.cookie-consent')
</body>
</html>
