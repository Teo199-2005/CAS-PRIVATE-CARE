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
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
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

        /* =============================================================
           MOBILE RESPONSIVE DESIGN - Training Center Page
           ============================================================= */
        
        /* Tablets (769px - 1024px) */
        @media (max-width: 1024px) {
            .hero {
                padding: 4rem 1.5rem;
            }
            
            .locations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .programs-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .training-programs-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .certificates-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .location-layout {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
            }
            
            .location-map {
                position: static !important;
                order: 2;
            }
            
            .location-map iframe {
                height: 400px !important;
            }
        }
        
        /* Standard Mobile (768px) */
        @media (max-width: 768px) {
            /* Hero Section */
            .hero {
                margin-top: 70px;
                padding: 3rem 1rem;
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
                font-size: 0.95rem;
                line-height: 1.6;
                margin-bottom: 1.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }
            
            .hero-buttons .btn-primary,
            .hero-buttons .btn-secondary {
                width: 100%;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
                text-align: center;
            }
            
            .accreditation-badges {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
                margin-top: 2rem;
            }

            /* Section full width on mobile */
            section {
                padding: 2.5rem 1rem !important;
            }

            .section-light,
            .section-dark {
                padding: 2.5rem 1rem !important;
            }

            .container {
                padding: 0 0.5rem !important;
                max-width: 100% !important;
                margin: 0 !important;
            }

            .section-header {
                margin-bottom: 2rem !important;
                padding: 0 !important;
            }
            
            .section-header h2 {
                font-size: 1.75rem !important;
                line-height: 1.3 !important;
            }
            
            .section-header p {
                font-size: 1rem !important;
            }
            
            /* Training Programs Grid */
            .training-programs-grid {
                grid-template-columns: 1fr !important;
                gap: 1.25rem !important;
            }
            
            .training-programs-grid > div {
                padding: 0 !important;
            }
            
            .training-programs-grid > div > div:last-child {
                padding: 1.5rem !important;
            }
            
            .program-card-new {
                border-radius: 20px !important;
            }
            
            .program-card-new h3 {
                font-size: 1.2rem !important;
            }
            
            /* Certificates Grid */
            .certificates-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .certificates-grid > div {
                padding: 1.25rem 1.5rem !important;
                border-radius: 16px !important;
            }
            
            /* Combined Training & Certificates Section - Stack on mobile */
            .training-certificates-layout {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
            
            .training-certificates-layout > div:last-child {
                border-radius: 16px !important;
                padding: 1.25rem !important;
            }
            
            /* Locations Grid - Stack on mobile */
            .locations-grid {
                grid-template-columns: 1fr !important;
                gap: 1.25rem !important;
            }
            
            .location-card {
                max-width: 100% !important;
            }

            /* Location Layout - Force full width single column */
            .location-layout {
                display: flex !important;
                flex-direction: column !important;
                gap: 1.5rem !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .location-main {
                width: 100% !important;
            }

            .location-map {
                position: static !important;
                width: 100% !important;
                order: 2;
            }

            .location-map iframe {
                height: 280px !important;
            }
            
            .location-map > div:first-child {
                border-radius: 16px !important;
            }

            .location-details-card {
                padding: 1.5rem !important;
                border-radius: 16px !important;
            }

            .location-details-card h3 {
                font-size: 1.25rem !important;
                margin-bottom: 1rem !important;
            }

            .training-center-image {
                height: 220px !important;
                border-radius: 16px !important;
            }
            
            /* Image containers in location sections */
            .location-main > div:first-child {
                border-radius: 20px !important;
                margin-bottom: 1.5rem !important;
            }
            
            .location-main > div:first-child img {
                height: 220px !important;
            }
            
            /* Details items in location cards */
            .location-details-card > div > div {
                padding: 1rem !important;
                border-radius: 12px !important;
            }
            
            /* Facilities grid */
            .location-details-card div[style*="grid-template-columns: repeat(2"] {
                grid-template-columns: 1fr !important;
            }
            
            .location-details-card div[style*="grid-column: span 2"] {
                grid-column: span 1 !important;
            }

            /* Force 2-column grid to single column */
            div[style*="grid-template-columns: 1.2fr 1fr"],
            div[style*="grid-template-columns: 1fr 1.2fr"],
            div[style*="grid-template-columns: 1.2fr"],
            div[style*="grid-template-columns"] {
                display: flex !important;
                flex-direction: column !important;
                gap: 1.25rem !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Location Details Card */
            div[style*="background: white"][style*="padding: 3rem"] {
                padding: 1.25rem !important;
                border-radius: 14px !important;
                margin: 0 !important;
            }

            /* Location Details Header */
            div[style*="background: white"] h3[style*="font-size: 1.8rem"] {
                font-size: 1.25rem !important;
                margin-bottom: 1rem !important;
            }

            /* Info Items with left border */
            div[style*="border-left: 4px solid"] {
                padding: 0.875rem !important;
                gap: 0.875rem !important;
            }

            div[style*="border-left: 4px solid"] i {
                font-size: 1.1rem !important;
            }

            div[style*="border-left: 4px solid"] strong {
                font-size: 0.9rem !important;
            }

            div[style*="border-left: 4px solid"] span {
                font-size: 0.85rem !important;
            }

            /* Facilities section */
            div[style*="border-top: 2px solid"] h4 {
                font-size: 1rem !important;
            }

            div[style*="border-top: 2px solid"] li {
                font-size: 0.85rem !important;
                gap: 0.625rem !important;
            }

            /* Map container */
            div[style*="position: sticky"][style*="top: 100px"] {
                position: static !important;
            }

            div[style*="border-radius: 24px"][style*="overflow: hidden"] iframe {
                height: 250px !important;
            }

            /* Image container */
            div[style*="position: relative"][style*="margin-bottom"] {
                margin-bottom: 1rem !important;
            }

            /* Image styling */
            img[style*="height: 400px"],
            .container img {
                height: 180px !important;
                border-radius: 14px !important;
                width: 100% !important;
            }

            /* Position absolute badge */
            div[style*="position: absolute"][style*="top: 20px"][style*="left: 20px"] {
                top: 12px !important;
                left: 12px !important;
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }
            
            .badge {
                width: 100%;
                justify-content: center;
                padding: 0.625rem 1rem;
                font-size: 0.85rem;
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
            
            /* Locations Grid - Single column on mobile */
            .locations-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            
            .location-card {
                border-radius: 16px;
            }
            
            .location-image {
                height: 180px;
            }
            
            .location-content {
                padding: 1.5rem;
            }
            
            .location-content h3 {
                font-size: 1.25rem;
            }
            
            .location-cta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-location {
                min-width: 100%;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            /* Programs Grid - 2 columns */
            .programs-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .program-card {
                padding: 1.25rem;
                border-radius: 12px;
            }
            
            .program-card h3 {
                font-size: 1rem;
            }
            
            .program-card p {
                font-size: 0.8rem;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            /* Mobile Training Center Layout */
            .container > div[style*="grid-template-columns"] {
                display: flex !important;
                flex-direction: column !important;
                gap: 1.5rem !important;
            }

            /* Mobile Image Styling */
            .container img[alt*="Training Center"] {
                height: 220px !important;
                border-radius: 14px !important;
                margin-bottom: 1rem !important;
            }

            /* Mobile Info Card */
            .container > div > div > div[style*="background: white"] {
                padding: 1.5rem !important;
                border-radius: 14px !important;
            }

            /* Mobile Location Badge */
            div[style*="position: absolute"][style*="top: 20px"] {
                top: 12px !important;
                left: 12px !important;
                padding: 0.4rem 0.75rem !important;
                font-size: 0.75rem !important;
            }

            /* Mobile Section Headers */
            .container h3[style*="font-size: 1.8rem"] {
                font-size: 1.3rem !important;
                margin-bottom: 1.25rem !important;
            }

            .container h4[style*="font-size: 1.2rem"] {
                font-size: 1rem !important;
            }

            /* Mobile Info Items */
            div[style*="padding: 1.25rem"][style*="border-radius: 16px"] {
                padding: 0.875rem !important;
            }

            /* Mobile Call Button */
            a[href^="tel"][style*="display: block"] {
                padding: 0.875rem !important;
                font-size: 0.95rem !important;
            }

            /* Mobile Map */
            div[style*="border-radius: 24px"] iframe {
                height: 300px !important;
            }

            /* Remove sticky on mobile */
            div[style*="position: sticky"] {
                position: static !important;
            }

            /* CTA Section */
            .cta-section {
                padding: 3rem 1.25rem !important;
            }
            
            .cta-section h2 {
                font-size: 1.75rem !important;
                line-height: 1.3 !important;
            }
            
            .cta-section h2 br {
                display: none;
            }
            
            .cta-section p {
                font-size: 1rem !important;
            }
            
            .cta-section .hero-buttons {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            
            .cta-section .hero-buttons a {
                width: 100% !important;
                justify-content: center !important;
                padding: 1rem 1.5rem !important;
            }
            
            .cta-section > div > div:last-child {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }
            }

            .cta-section h2 {
                font-size: 1.75rem;
            }
            
            .cta-section p {
                font-size: 1rem;
            }

            /* Training Programs Grid */
            .training-programs-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .training-programs-grid > div {
                padding: 1.5rem !important;
                border-radius: 16px !important;
            }
            
            .training-programs-grid h3 {
                font-size: 1.1rem !important;
            }
            
            .training-programs-grid p {
                font-size: 0.9rem !important;
            }

            /* Certificates Section */
            div[style*="display: flex"][style*="flex-wrap: wrap"][style*="justify-content: center"] {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            
            div[style*="display: flex"][style*="flex-wrap: wrap"][style*="justify-content: center"] > div {
                min-width: unset !important;
                width: 100% !important;
                padding: 1.25rem 1.5rem !important;
            }
            
            div[style*="display: flex"][style*="flex-wrap: wrap"][style*="justify-content: center"] h4 {
                font-size: 1rem !important;
            }
            
            div[style*="display: flex"][style*="flex-wrap: wrap"][style*="justify-content: center"] p {
                font-size: 0.85rem !important;
            }
        }

        /* Small Phones (480px) */
        @media (max-width: 480px) {
            .hero {
                padding: 2.5rem 1rem;
            }
            
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .hero .tagline {
                font-size: 1rem;
            }
            
            section {
                padding: 2.5rem 1rem;
            }
            
            .section-header h2 {
                font-size: 1.35rem;
            }

            .container img[alt*="Training Center"] {
                height: 180px !important;
            }

            .container h3 {
                font-size: 1.1rem !important;
            }
            
            .programs-grid {
                gap: 0.5rem;
            }
            
            .program-card {
                padding: 1rem;
            }
            
            .program-card h3 {
                font-size: 0.9rem;
            }
            
            .program-card p {
                font-size: 0.75rem;
                -webkit-line-clamp: 2;
            }
        }
        
        /* Very Small Phones (360px) */
        @media (max-width: 360px) {
            .hero h1 {
                font-size: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
            
            .program-card h3 {
                font-size: 0.85rem;
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

    <!-- Training Programs & Certificates Combined - Side by Side -->
    <section class="section-light" style="background: #ffffff; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 1;">
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 2.5rem; text-align: center;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 0.5rem 1.25rem; border-radius: 50px; margin-bottom: 1rem; border: 1px solid rgba(59, 130, 246, 0.15);">
                    <i class="bi bi-mortarboard-fill" style="color: #3b82f6;"></i>
                    <span style="color: #1e40af; font-weight: 600; font-size: 0.9rem;">Professional Training</span>
                </div>
                <h2 style="font-family: 'Sora', sans-serif; font-size: 2.5rem; font-weight: 700; margin-bottom: 0.75rem; color: #0B4FA2;">Training Programs & Certifications</h2>
                <p style="font-size: 1.1rem; color: #64748b; max-width: 600px; margin: 0 auto;">Comprehensive certification programs to launch or advance your caregiving career</p>
            </div>
            
            <!-- Two Column Layout -->
            <div class="training-certificates-layout" style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 2rem; align-items: start;">
                
                <!-- LEFT SIDE - Training Programs -->
                <div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-book-fill" style="color: white; font-size: 1rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin: 0; font-size: 1.15rem;">Training Programs</h3>
                    </div>
                    
                    <div class="training-programs-grid" style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- HHA Program -->
                        <div class="program-card-new" style="background: white; border-radius: 16px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(59, 130, 246, 0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.05)'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); flex-shrink: 0;">
                                    <i class="bi bi-heart-pulse-fill" style="color: white; font-size: 1.25rem;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <h4 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin: 0; font-size: 1rem;">Home Health Aide (HHA)</h4>
                                    </div>
                                    <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 0.5rem 0;">NYS-approved 76-hour program covering personal care & safety.</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #eff6ff; border-radius: 6px; font-size: 0.75rem; color: #1e40af; font-weight: 600;">
                                            <i class="bi bi-clock-fill"></i> 2-3 Weeks
                                        </span>
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #f0fdf4; border-radius: 6px; font-size: 0.75rem; color: #166534; font-weight: 600;">
                                            <i class="bi bi-patch-check-fill"></i> State Certified
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PCA Program -->
                        <div class="program-card-new" style="background: white; border-radius: 16px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(16, 185, 129, 0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.05)'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); flex-shrink: 0;">
                                    <i class="bi bi-person-heart" style="color: white; font-size: 1.25rem;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <h4 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin: 0; font-size: 1rem;">Personal Care Aide (PCA)</h4>
                                    </div>
                                    <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 0.5rem 0;">40-hour foundational program for daily living assistance.</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #f0fdf4; border-radius: 6px; font-size: 0.75rem; color: #166534; font-weight: 600;">
                                            <i class="bi bi-clock-fill"></i> 1-2 Weeks
                                        </span>
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #fefce8; border-radius: 6px; font-size: 0.75rem; color: #854d0e; font-weight: 600;">
                                            <i class="bi bi-star-fill"></i> Entry Level
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CPR/First Aid -->
                        <div class="program-card-new" style="background: white; border-radius: 16px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(249, 115, 22, 0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.05)'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25); flex-shrink: 0;">
                                    <i class="bi bi-plus-circle-fill" style="color: white; font-size: 1.25rem;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <h4 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin: 0; font-size: 1rem;">CPR & First Aid</h4>
                                    </div>
                                    <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 0.5rem 0;">AHA certified emergency response training.</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #fff7ed; border-radius: 6px; font-size: 0.75rem; color: #9a3412; font-weight: 600;">
                                            <i class="bi bi-clock-fill"></i> 1 Day
                                        </span>
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; background: #fef2f2; border-radius: 6px; font-size: 0.75rem; color: #dc2626; font-weight: 600;">
                                            <i class="bi bi-award-fill"></i> AHA Certified
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RIGHT SIDE - Certificates -->
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 20px; padding: 1.5rem; border: 1px solid #e2e8f0; height: fit-content;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-award-fill" style="color: white; font-size: 1rem;"></i>
                        </div>
                        <h3 style="font-family: 'Sora', sans-serif; font-weight: 700; color: #1e293b; margin: 0; font-size: 1.15rem;">Certificates You'll Earn</h3>
                    </div>
                    
                    <div class="certificates-grid" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- HHA Certificate -->
                        <div style="background: white; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.85rem; border: 1px solid #e2e8f0;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-file-earmark-medical" style="color: white; font-size: 1.1rem;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 0.9rem;">HHA Certificate</h4>
                                <p style="color: #64748b; font-size: 0.75rem; margin: 0; display: flex; align-items: center; gap: 0.2rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 0.7rem;"></i> NYS DOH Approved
                                </p>
                            </div>
                        </div>
                        
                        <!-- CPR Certification -->
                        <div style="background: white; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.85rem; border: 1px solid #e2e8f0;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-heart-pulse" style="color: white; font-size: 1.1rem;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 0.9rem;">CPR Certification</h4>
                                <p style="color: #64748b; font-size: 0.75rem; margin: 0; display: flex; align-items: center; gap: 0.2rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 0.7rem;"></i> American Heart Assoc.
                                </p>
                            </div>
                        </div>
                        
                        <!-- First Aid -->
                        <div style="background: white; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.85rem; border: 1px solid #e2e8f0;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-bandaid" style="color: white; font-size: 1.1rem;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 0.9rem;">First Aid</h4>
                                <p style="color: #64748b; font-size: 0.75rem; margin: 0; display: flex; align-items: center; gap: 0.2rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 0.7rem;"></i> Emergency Response
                                </p>
                            </div>
                        </div>
                        
                        <!-- Infection Control -->
                        <div style="background: white; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.85rem; border: 1px solid #e2e8f0;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-shield-check" style="color: white; font-size: 1.1rem;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 0.9rem;">Infection Control</h4>
                                <p style="color: #64748b; font-size: 0.75rem; margin: 0; display: flex; align-items: center; gap: 0.2rem;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 0.7rem;"></i> Safety Protocols
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trust note -->
                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                            <i class="bi bi-patch-check-fill" style="color: #10b981;"></i>
                            Recognized by NY State healthcare employers
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- All Training Center Locations -->
    <section class="section-light" style="background: #f8fafc; padding: 5rem 0;">
        <div class="container" style="max-width: 1400px;">
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 3rem; text-align: center;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 0.5rem 1.25rem; border-radius: 50px; margin-bottom: 1rem; font-size: 0.9rem;">
                    <i class="bi bi-geo-alt-fill" style="color: #3b82f6;"></i>
                    <span style="color: #1e40af; font-weight: 600;">Our Locations</span>
                </div>
                <h2 style="font-family: 'Sora', sans-serif; font-size: 2.5rem; font-weight: 700; color: #0B4FA2; margin-bottom: 0.75rem;">Training Center Locations</h2>
                <p style="color: #64748b; font-size: 1.15rem;">Three convenient locations across NYC</p>
            </div>

            <!-- Locations Grid - 3 Columns -->
            <div class="locations-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem;">
                
                <!-- Manhattan -->
                <div class="location-card" style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(11,79,162,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.06)'">
                    <!-- Image -->
                    <div style="position: relative; height: 280px; overflow: hidden;">
                        <x-responsive-image 
                            src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800" 
                            alt="Manhattan Training Center Classroom" 
                            :widths="[400, 600, 800]"
                            sizes="(max-width: 768px) 100vw, 33vw"
                            style="width: 100%; height: 100%; object-fit: cover;"
                        />
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(11,79,162,0.5) 0%, transparent 60%);"></div>
                        <div style="position: absolute; top: 15px; left: 15px; background: white; color: #0B4FA2; padding: 0.4rem 0.85rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="bi bi-geo-alt-fill"></i> MANHATTAN
                        </div>
                        <div style="position: absolute; bottom: 15px; left: 15px; display: flex; align-items: center; gap: 0.4rem; background: rgba(0,0,0,0.7); color: white; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-star-fill" style="color: #fbbf24;"></i> 4.9 Rating
                        </div>
                    </div>
                    <!-- Content -->
                    <div style="padding: 1.5rem;">
                        <h3 style="font-family: 'Sora', sans-serif; font-size: 1.35rem; font-weight: 700; color: #0B4FA2; margin-bottom: 1rem;">Manhattan Training Center</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.95rem; color: #475569; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i class="bi bi-geo-alt-fill" style="color: #0B4FA2; flex-shrink: 0; margin-top: 2px; font-size: 1rem;"></i>
                                <span>350 5th Avenue, Suite 4820<br>New York, NY 10118</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-telephone-fill" style="color: #0B4FA2; font-size: 1rem;"></i>
                                <span style="font-weight: 600;">(646) 282-8282</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-clock-fill" style="color: #0B4FA2; font-size: 1rem;"></i>
                                <span>Mon-Fri: 8AM-6PM | Sat: 9AM-4PM</span>
                            </div>
                        </div>

                        <!-- Features -->
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <span style="background: #eff6ff; color: #1e40af; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Simulation labs</span>
                            <span style="background: #eff6ff; color: #1e40af; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Near subway</span>
                            <span style="background: #eff6ff; color: #1e40af; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Job placement</span>
                        </div>

                        <a href="tel:+16462828282" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.5rem; background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%); color: white; border-radius: 12px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(11,79,162,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(11,79,162,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(11,79,162,0.3)'">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>

                <!-- Brooklyn -->
                <div class="location-card" style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(16,185,129,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.06)'">
                    <!-- Image -->
                    <div style="position: relative; height: 280px; overflow: hidden;">
                        <x-responsive-image 
                            src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800" 
                            alt="Brooklyn Training Center Medical Lab" 
                            :widths="[400, 600, 800]"
                            sizes="(max-width: 768px) 100vw, 33vw"
                            style="width: 100%; height: 100%; object-fit: cover;"
                        />
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(16,185,129,0.5) 0%, transparent 60%);"></div>
                        <div style="position: absolute; top: 15px; left: 15px; background: white; color: #10b981; padding: 0.4rem 0.85rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="bi bi-geo-alt-fill"></i> BROOKLYN
                        </div>
                        <div style="position: absolute; bottom: 15px; left: 15px; display: flex; align-items: center; gap: 0.4rem; background: rgba(0,0,0,0.7); color: white; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-star-fill" style="color: #fbbf24;"></i> 4.8 Rating
                        </div>
                    </div>
                    <!-- Content -->
                    <div style="padding: 1.5rem;">
                        <h3 style="font-family: 'Sora', sans-serif; font-size: 1.35rem; font-weight: 700; color: #10b981; margin-bottom: 1rem;">Brooklyn Training Center</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.95rem; color: #475569; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i class="bi bi-geo-alt-fill" style="color: #10b981; flex-shrink: 0; margin-top: 2px; font-size: 1rem;"></i>
                                <span>445 Albee Square West<br>Brooklyn, NY 11201</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-telephone-fill" style="color: #10b981; font-size: 1rem;"></i>
                                <span style="font-weight: 600;">(718) 555-0142</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-clock-fill" style="color: #10b981; font-size: 1rem;"></i>
                                <span>Mon-Fri: 8AM-6PM | Sat: 9AM-3PM</span>
                            </div>
                        </div>

                        <!-- Features -->
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <span style="background: #ecfdf5; color: #166534; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Clinical areas</span>
                            <span style="background: #ecfdf5; color: #166534; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Downtown</span>
                            <span style="background: #ecfdf5; color: #166534; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Career counseling</span>
                        </div>

                        <a href="tel:+17185550142" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.5rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(16,185,129,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(16,185,129,0.3)'">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>

                <!-- Queens -->
                <div class="location-card" style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s ease; position: relative;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(249,115,22,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.06)'">
                    <!-- Image -->
                    <div style="position: relative; height: 280px; overflow: hidden;">
                        <x-responsive-image 
                            src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=800" 
                            alt="Queens Training Center Modern Facility" 
                            :widths="[400, 600, 800]"
                            sizes="(max-width: 768px) 100vw, 33vw"
                            style="width: 100%; height: 100%; object-fit: cover;"
                        />
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(249,115,22,0.5) 0%, transparent 60%);"></div>
                        <div style="position: absolute; top: 15px; left: 15px; background: white; color: #f97316; padding: 0.4rem 0.85rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="bi bi-geo-alt-fill"></i> QUEENS
                        </div>
                        <div style="position: absolute; top: 15px; right: 15px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #1e293b; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; box-shadow: 0 4px 12px rgba(245,158,11,0.4);">
                            <i class="bi bi-stars"></i> NEW 2024
                        </div>
                        <div style="position: absolute; bottom: 15px; left: 15px; display: flex; align-items: center; gap: 0.4rem; background: rgba(0,0,0,0.7); color: white; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-star-fill" style="color: #fbbf24;"></i> 5.0 Rating
                        </div>
                    </div>
                    <!-- Content -->
                    <div style="padding: 1.5rem;">
                        <h3 style="font-family: 'Sora', sans-serif; font-size: 1.35rem; font-weight: 700; color: #f97316; margin-bottom: 1rem;">Queens Training Center</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.95rem; color: #475569; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i class="bi bi-geo-alt-fill" style="color: #f97316; flex-shrink: 0; margin-top: 2px; font-size: 1rem;"></i>
                                <span>37-11 35th Avenue<br>Long Island City, NY 11101</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-telephone-fill" style="color: #f97316; font-size: 1rem;"></i>
                                <span style="font-weight: 600;">(718) 555-0198</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-clock-fill" style="color: #f97316; font-size: 1rem;"></i>
                                <span>Mon-Fri: 8AM-6PM | Sat: 10AM-4PM</span>
                            </div>
                        </div>

                        <!-- Features -->
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <span style="background: #fff7ed; color: #9a3412; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #f97316;"></i> Brand new</span>
                            <span style="background: #fff7ed; color: #9a3412; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #f97316;"></i> Free parking</span>
                            <span style="background: #fff7ed; color: #9a3412; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill" style="color: #f97316;"></i> Evening classes</span>
                        </div>

                        <a href="tel:+17185550198" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.5rem; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; border-radius: 12px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(249,115,22,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(249,115,22,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(249,115,22,0.3)'">
                            <i class="bi bi-telephone-fill"></i> Call Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - Compact & Minimalist -->
    <section class="cta-section" style="background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%); padding: 3rem 0;">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
                
                <!-- Left - Text Content -->
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(255, 255, 255, 0.15); padding: 0.35rem 0.85rem; border-radius: 50px; margin-bottom: 0.75rem; font-size: 0.8rem;">
                        <i class="bi bi-rocket-takeoff-fill" style="color: #fbbf24; font-size: 0.75rem;"></i>
                        <span style="color: white; font-weight: 600;">Start Your Journey</span>
                    </div>
                    <h2 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; color: white; line-height: 1.3;">Ready to Start Your <span style="color: #fbbf24;">Caregiving Career?</span></h2>
                    <p style="font-size: 1rem; color: rgba(255, 255, 255, 0.85); margin: 0;">Get certified in as little as 2 weeks. Join thousands of successful caregivers in NYC.</p>
                </div>
                
                <!-- Right - Buttons & Info -->
                <div style="display: flex; flex-direction: column; gap: 1rem; align-items: flex-end;">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <a href="{{ url('/register') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: white; color: #0B4FA2; border-radius: 10px; font-weight: 600; font-size: 0.95rem; text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="bi bi-person-plus-fill"></i> Enroll Now
                        </a>
                        <a href="tel:+16462828282" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: rgba(255, 255, 255, 0.15); color: white; border-radius: 10px; font-weight: 600; font-size: 0.95rem; text-decoration: none; border: 1px solid rgba(255, 255, 255, 0.3); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                            <i class="bi bi-telephone-fill"></i> (646) 282-8282
                        </a>
                    </div>
                    <div style="display: flex; gap: 1.25rem; font-size: 0.8rem; color: rgba(255, 255, 255, 0.8);">
                        <span><i class="bi bi-check-circle-fill" style="color: #10b981; margin-right: 0.3rem;"></i>Financial aid</span>
                        <span><i class="bi bi-check-circle-fill" style="color: #10b981; margin-right: 0.3rem;"></i>Payment plans</span>
                        <span><i class="bi bi-check-circle-fill" style="color: #10b981; margin-right: 0.3rem;"></i>Job placement</span>
                    </div>
                </div>
            </div>
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
