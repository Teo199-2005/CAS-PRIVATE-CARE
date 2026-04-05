<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    
    <!-- Primary Meta Tags -->
    <title>W-2 Caregiver Careers | CAS Private Care LLC - Join Our Team</title>
    <meta name="title" content="W-2 Caregiver Careers | CAS Private Care LLC - Join Our Team">
    <meta name="description" content="Join CAS Private Care as a W-2 caregiver employee. Competitive pay, payroll and tax withholding, direct deposit. Caregiver jobs across NYC.">
    <meta name="keywords" content="W-2 caregiver jobs NYC, employed caregiver, home care employment, CAS Private Care careers">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/contractors') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/contractors') }}">
    <meta property="og:title" content="W-2 Caregiver Careers | CAS Private Care LLC">
    <meta property="og:description" content="Join our team as a W-2 caregiver. Payroll, direct deposit, and support across NYC.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/contractors') }}">
    <meta property="twitter:title" content="W-2 Caregiver Careers | CAS Private Care LLC">
    <meta property="twitter:description" content="Join our team as a W-2 caregiver with payroll and direct deposit.">
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
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
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

        /* Hero Section - padding clears fixed nav; no margin so no white gap */
        .contractor-hero {
            margin-top: 0;
            padding-top: 80px;
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
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
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
            background-color: #dbeafe;
            background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
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

        .cta-section .cta-btn {
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

        .cta-section .cta-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        /* =============================================================
           MOBILE RESPONSIVE DESIGN - 1099 Contractors Page
           ============================================================= */
        
        /* Tablets (769px - 1024px) */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 3rem 1.5rem;
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

        /* Standard Mobile (768px) */
        @media (max-width: 768px) {
            /* Hero Section */
            .contractor-hero {
                margin-top: 0;
                padding-top: 70px;
                min-height: auto;
            }
            
            .hero-container {
                padding: 2.5rem 1.25rem;
                gap: 2rem;
            }
            
            .contractor-hero h1 {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .contractor-hero p {
                font-size: 1rem;
                line-height: 1.6;
                margin-bottom: 1.5rem;
            }
            
            .hero-badge {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
                margin-bottom: 1rem;
            }
            
            .hero-features {
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }
            
            .hero-feature {
                padding: 0.5rem 0.875rem;
                font-size: 0.8rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }
            
            .hero-buttons .cta-btn {
                width: 100%;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
                text-align: center;
                justify-content: center;
            }

            /* Section Styles */
            section {
                padding: 3rem 1.25rem;
            }
            
            .section-header h2 {
                font-size: 1.5rem;
                line-height: 1.3;
            }
            
            .section-header p {
                font-size: 0.9rem;
            }

            /* Benefits Grid - 2x2 */
            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .benefit-card {
                padding: 1.25rem 1rem;
                border-radius: 12px;
            }
            
            .benefit-card h3 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .benefit-card p {
                font-size: 0.8rem;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            .benefit-icon {
                width: 44px;
                height: 44px;
                margin-bottom: 0.75rem;
            }
            
            .benefit-icon i {
                font-size: 1.1rem;
            }

            /* Info Cards */
            .info-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .info-card {
                padding: 1.5rem;
            }
            
            .info-card h3 {
                font-size: 1.1rem;
            }
            
            .info-card p,
            .info-card li {
                font-size: 0.9rem;
            }

            /* Join Steps - 2x2 Grid */
            .join-steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .join-step {
                padding: 1.25rem 1rem;
                border-radius: 12px;
                text-align: center;
            }
            
            .step-number {
                width: 55px;
                height: 55px;
                font-size: 1.5rem;
                margin: 0 auto 0.75rem;
            }
            
            .join-step h3 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .join-step p {
                font-size: 0.75rem;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            /* CTA Section */
            .cta-section {
                padding: 3rem 1.25rem;
            }
            
            .cta-section h2 {
                font-size: 1.75rem;
            }
            
            .cta-section p {
                font-size: 1rem;
            }
            
            .cta-section .cta-btn {
                width: 100%;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
            
            /* Requirements Section */
            .requirements-section {
                padding: 3rem 1.25rem;
            }
            
            .requirements-grid {
                gap: 1rem;
            }
            
            .requirement-category {
                padding: 1.5rem;
                border-radius: 16px;
            }
            
            .requirement-category h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }
            
            .requirement-list li {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
            
            /* Info Section */
            .info-section {
                padding: 3rem 1.25rem;
            }
            
            .info-grid {
                gap: 2rem;
            }
            
            .info-content h2 {
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .info-content > p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .info-list li {
                font-size: 0.9rem;
                margin-bottom: 0.75rem;
            }
            
            .info-card h4 {
                font-size: 0.95rem;
            }
            
            .info-card p {
                font-size: 0.85rem;
            }
            
            .info-card i {
                font-size: 2rem;
            }

            .requirement-item {
                padding: 1rem;
                font-size: 0.9rem;
            }
        }
        
        /* Small Phones (480px) */
        @media (max-width: 480px) {
            .contractor-hero h1 {
                font-size: 1.75rem;
            }
            
            .contractor-hero p {
                font-size: 0.9rem;
            }
            
            .hero-features {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-feature {
                width: 100%;
                justify-content: center;
            }
            
            section {
                padding: 2.5rem 1rem;
            }
            
            .section-header h2 {
                font-size: 1.35rem;
            }
            
            .benefits-grid {
                gap: 0.5rem;
            }
            
            .benefit-card {
                padding: 1rem 0.75rem;
            }
            
            .benefit-card h3 {
                font-size: 0.85rem;
            }
            
            .benefit-card p {
                font-size: 0.75rem;
                -webkit-line-clamp: 2;
            }
            
            .benefit-icon {
                width: 38px;
                height: 38px;
            }
            
            .benefit-icon i {
                font-size: 1rem;
            }
            
            .join-steps {
                gap: 0.5rem;
            }
            
            .step-number {
                width: 45px;
                height: 45px;
                font-size: 1.25rem;
            }
            
            .join-step h3 {
                font-size: 0.8rem;
            }
            
            .join-step p {
                font-size: 0.7rem;
            }
            
            /* Requirements compact */
            .requirements-section {
                padding: 2.5rem 1rem;
            }
            
            .requirement-category {
                padding: 1.25rem;
            }
            
            .requirement-category h3 {
                font-size: 1rem;
            }
            
            .requirement-list li {
                padding: 0.625rem;
                font-size: 0.85rem;
            }
            
            /* Info section compact */
            .info-section {
                padding: 2.5rem 1rem;
            }
            
            .info-content h2 {
                font-size: 1.5rem;
            }
            
            .info-cards {
                gap: 0.75rem;
            }
            
            .info-card {
                padding: 1.25rem;
            }
        }
        
        /* Very Small Phones (360px) */
        @media (max-width: 360px) {
            .contractor-hero h1 {
                font-size: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
            
            .benefit-card h3 {
                font-size: 0.8rem;
            }
            
            .join-step h3 {
                font-size: 0.75rem;
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
                        W-2 Caregiver Careers
                    </div>
                    <h1>Join Our Team as a <span>W-2 Caregiver</span></h1>
                    <p>Work with CAS Private Care as an employee: payroll with tax withholding, direct deposit, and a supportive team matching you with families across NYC.</p>
                    
                    <div class="hero-features">
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            W-2 Employment
                        </span>
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            Direct Deposit
                        </span>
                        <span class="hero-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            Support &amp; Training
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
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=600&q=80" alt="Professional caregiver" loading="lazy" decoding="async">
                    <div class="hero-stats-card">
                        <h4>500+</h4>
                        <p>Team Members</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <div class="container">
                <div class="section-header">
                    <h2>Why Work With <span>CAS Private Care?</span></h2>
                    <p>Stable W-2 employment focused on quality home care</p>
                </div>
                
                <div class="benefits-grid">
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <h3>Consistent Scheduling</h3>
                        <p>Coordinate assignments that fit your availability while meeting family needs. Your manager and the platform help keep schedules organized.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Meaningful Work</h3>
                        <p>Support clients in their homes with companion care, daily living assistance, and respectful, professional service.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3>Grow Your Skills</h3>
                        <p>Build experience with diverse cases, feedback from families, and optional training aligned with your role.</p>
                    </div>
                    
                    <div class="benefit-card" data-animate>
                        <div class="benefit-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h3>Payroll &amp; Direct Deposit</h3>
                        <p>Get paid on a regular payroll schedule with taxes withheld. Complete secure payroll onboarding for banking details.</p>
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

        <!-- W-2 Employment Section -->
        <section class="info-section">
            <div class="container">
                <div class="info-grid">
                    <div class="info-content">
                        <h2>What Does <span>W-2 Employment</span> Mean?</h2>
                        <p>You are hired as an employee of CAS Private Care LLC. We withhold payroll taxes, pay employer-side taxes where applicable, and pay you through payroll direct deposit after onboarding.</p>
                        
                        <ul class="info-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Employee status</strong> — covered by our payroll and employment policies</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Tax withholding</strong> — federal and state withholding handled through payroll</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Payroll onboarding</strong> — legal name, SSN, ID address, bank details, emergency contact (for Gusto/payroll)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Platform tools</strong> — scheduling, time tracking, and communication in one place</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>NYC coverage</strong> — assignments across boroughs based on need and your availability</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="info-cards">
                        <div class="info-card" data-animate>
                            <i class="bi bi-clock-history"></i>
                            <h4>Steady Opportunities</h4>
                            <p>Part-time and full-time paths depending on openings</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-phone"></i>
                            <h4>Digital Workflow</h4>
                            <p>View assignments through our caregiver dashboard</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-cash-stack"></i>
                            <h4>Payroll Paydays</h4>
                            <p>Direct deposit on the company payroll schedule</p>
                        </div>
                        <div class="info-card" data-animate>
                            <i class="bi bi-star-fill"></i>
                            <h4>Recognition</h4>
                            <p>Build trust with families through quality care</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Requirements Section -->
        <section class="requirements-section">
            <div class="container">
                <div class="section-header">
                    <h2>Caregiver <span>Requirements</span></h2>
                    <p>What you need to join our W-2 caregiver team</p>
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
                    
                </div>
            </div>
        </section>

        <!-- How to Join Section -->
        <section class="join-section">
            <div class="container">
                <div class="section-header">
                    <h2>How to <span>Get Started</span></h2>
                    <p>Join our W-2 team in a few clear steps</p>
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
                        <h3>Payroll Onboarding</h3>
                        <p>Complete your caregiver profile and secure payroll information (tax ID, bank details) for direct deposit.</p>
                    </div>
                    
                    <div class="join-step" data-animate>
                        <div class="step-number">4</div>
                        <h3>Start Working</h3>
                        <p>Once cleared, receive assignments and track time through your caregiver dashboard.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Payouts Explained Section -->
        <section class="section-light" style="padding: 5rem 2rem;">
            <div class="container">
                <div class="section-header">
                    <h2>How <span>Pay</span> Works</h2>
                    <p>W-2 payroll with direct deposit—no contractor payout accounts</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-credit-card-fill" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Clients &amp; Billing</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">Families pay for services through our platform. Your wages are paid separately on payroll, not as instant transfers to a contractor account.</p>
                    </div>
                    
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-percent" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Payroll Taxes</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">Standard payroll withholding applies (federal/state as applicable). You receive pay stubs and year-end W-2 forms from payroll.</p>
                    </div>
                    
                    <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                            <i class="bi bi-calendar-week" style="color: white; font-size: 1.75rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Direct Deposit</h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7;">After payroll onboarding, net pay is deposited to the bank account you provide. We do not use Stripe Connect for caregiver wages.</p>
                    </div>
                    
                    
                </div>
            </div>
        </section>

        <!-- Contractor FAQ Section -->
        <section style="padding: 5rem 2rem; background: #f1f5f9;">
            <div class="container">
                <div class="section-header">
                    <h2>Caregiver <span>FAQ</span></h2>
                    <p>Common questions from applicants and team members</p>
                </div>
                
                <div style="max-width: 800px; margin: 3rem auto 0;">
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                Am I an employee of CAS Private Care?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">W-2 caregivers are employees of CAS Private Care LLC. Schedules and assignments are coordinated with management; pay is through payroll with applicable tax withholding.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                How do taxes work as a W-2 employee?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">We withhold income and employment taxes as required and remit them through payroll. You will receive a W-2 after year-end. For personal tax questions, consult a tax professional.</p>
                        </details>
                    </div>
                    
                    <div style="background: white; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <details style="padding: 1.5rem;">
                            <summary style="font-weight: 700; color: #1e293b; cursor: pointer; font-size: 1.05rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                                Can I work for other employers too?
                                <i class="bi bi-chevron-down" style="color: #f97316;"></i>
                            </summary>
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">Outside employment may be allowed depending on scheduling, non-compete or confidentiality terms in your offer, and applicable law. Discuss any second job with your manager during onboarding.</p>
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
                            <p style="margin-top: 1rem; color: #64748b; line-height: 1.8;">Clients' homes usually have what you need for companion and daily living support. Any employer-provided supplies or dress code will be explained during onboarding. Reliable transportation to assignments is required.</p>
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
