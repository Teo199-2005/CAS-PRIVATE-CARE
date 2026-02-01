<style>
    /* Section Background Styles */
    .section-light {
        background-color: #ffffff;
        background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
    }

    .section-dark {
        background-color: #dbeafe;
        background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
    }

    /* Navigation Styles */
    nav {
        position: fixed;
        top: 0;
        width: 100%;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        padding: 0;
        height: 72px;
        border-bottom: 1px solid rgba(59, 130, 246, 0.2);
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
        flex-shrink: 0;
    }

    .logo-section img {
        height: 55px;
        width: auto;
        object-fit: contain;
        transition: transform 0.3s ease;
        background-color: #ffffff;
        padding: 6px 10px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .logo-section:hover img {
        transform: scale(1.05);
    }

    .logo-section a {
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .nav-links {
        display: flex;
        gap: 0.25rem;
        list-style: none;
        align-items: center;
        margin: 0;
        padding: 0;
        flex-wrap: nowrap;
    }

    .nav-links li {
        display: flex;
        align-items: center;
        white-space: nowrap;
        position: relative;
    }

    /* Remove dividers - cleaner look */
    .nav-links li:not(:last-child)::after {
        display: none;
    }

    /* Extra spacing before CTA button (Register) */
    .nav-links li:last-child {
        margin-left: 0.75rem;
    }

    .nav-links a {
        text-decoration: none;
        color: #e2e8f0;
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.5rem 0.875rem;
        border-radius: 8px;
        position: relative;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
    }

    .nav-links a:not(.cta-btn):hover {
        color: #ffffff;
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .nav-links a:not(.cta-btn):active {
        background: rgba(59, 130, 246, 0.3);
    }

    .dropdown {
        position: relative;
    }

    .dropdown > a {
        display: inline-flex;
        align-items: center;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        padding: 0.5rem;
        min-width: 220px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    /* Ensure dropdown works on desktop */
    @media (min-width: 769px) {
        .dropdown:hover .dropdown-menu {
            display: block !important;
        }
    }

    .dropdown-menu a {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #e2e8f0 !important;
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .dropdown-menu a:hover {
        background: rgba(59, 130, 246, 0.25) !important;
        color: #ffffff !important;
        transform: translateX(4px);
    }

    .nav-links .cta-btn {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        color: white !important;
        padding: 0.625rem 1.5rem !important;
        border-radius: 50px !important;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .nav-links .cta-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .nav-links .cta-btn:hover::before {
        left: 100%;
    }

    .nav-links .cta-btn:hover {
        background: linear-gradient(135deg, #fb923c 0%, #f97316 100%) !important;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 25px rgba(249, 115, 22, 0.5);
    }

    .nav-links .cta-btn:active {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
        transform: translateY(0);
    }

    /* Login Button - glass effect */
    .nav-links a[href*="login"] {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.625rem 1.25rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .nav-links a[href*="login"]:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.15);
    }

    .mobile-menu-btn {
        display: none;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 1.5rem;
        color: #ffffff;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        transition: all 0.2s ease;
        z-index: 1001;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    .mobile-menu-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .mobile-menu-btn:active {
        background: rgba(59, 130, 246, 0.12);
    }

    /* Footer Styles */
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

    /* Mobile Responsive Styles */
    @media (max-width: 1024px) {
        .nav-links {
            gap: 0.375rem;
        }

        .nav-links a {
            font-size: 0.95rem;
            padding: 0.5625rem 0.9375rem;
        }

        .cta-btn {
            padding: 0.6875rem 1.375rem !important;
            font-size: 0.95rem;
        }
    }

    /* ============================================
       MOBILE-FIRST RESPONSIVE DESIGN
       ============================================ */
    
    /* Mobile devices (phones, 320px - 768px) */
    @media (max-width: 768px) {
        /* Enhanced Mobile Navigation */
        nav {
            height: 70px;
            padding: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            padding: 0 1rem;
            height: 100%;
        }

        .logo-section img {
            height: 55px;
            transition: transform 0.3s ease;
        }

        .mobile-menu-btn {
            display: flex;
            width: 48px;
            height: 48px;
            font-size: 1.75rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .mobile-menu-btn:active {
            transform: scale(0.95);
            background: #f8fafc;
        }

        /* Mobile Navigation Menu - Clean Modern Styling */
        .nav-links {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #1e3a5f 0%, #0d1b2a 100%);
            padding: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border-radius: 0 0 20px 20px;
            z-index: 1000;
            max-height: 85vh;
            overflow-y: auto;
        }

        .nav-links.active {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.625rem !important;
            width: 100% !important;
        }

        .nav-links li {
            margin: 0 !important;
            width: 100% !important;
            display: block !important;
            list-style: none !important;
        }

        /* Remove dividers on mobile */
        .nav-links li:not(:last-child)::after {
            display: none !important;
        }

        /* Make Login span full width */
        .nav-links li:nth-last-child(2) {
            grid-column: 1 / -1 !important;
        }

        /* Make last item (Register) span full width */
        .nav-links li:last-child {
            grid-column: 1 / -1 !important;
            margin-top: 0.375rem !important;
        }

        .nav-links a {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.875rem 0.5rem !important;
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-radius: 10px !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            min-height: 48px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            line-height: 1.3 !important;
            word-wrap: break-word !important;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            background: rgba(59, 130, 246, 0.3);
            color: #ffffff !important;
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.5);
        }

        .nav-links a.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff !important;
            border-color: transparent;
        }

        /* Hide the chevron icon on mobile for cleaner look */
        .nav-links .dropdown-toggle i {
            display: none;
        }

        .dropdown-menu {
            position: relative;
            top: auto;
            left: 0;
            right: 0;
            opacity: 1;
            visibility: visible;
            transform: none;
            box-shadow: none;
            padding: 0;
            margin-top: 0.5rem;
            margin-left: 0;
            background: transparent;
            border-radius: 0;
            border: none;
            display: none;
            z-index: 1000;
        }

        /* Show dropdown when parent has 'open' class */
        .dropdown.open .dropdown-menu {
            display: flex !important;
            flex-direction: column;
            gap: 0.5rem;
            padding-left: 1rem;
            border-left: 3px solid #3b82f6;
            margin-left: 0.5rem;
            grid-column: 1 / -1;
        }

        .dropdown-menu a {
            width: 100%;
            padding: 0.75rem 1rem;
            min-height: 44px;
            text-align: center;
            justify-content: center;
            background: rgba(59, 130, 246, 0.15) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .dropdown-menu a:hover {
            background: rgba(59, 130, 246, 0.35) !important;
            color: #ffffff !important;
        }

        .dropdown-menu a::before,
        .dropdown-menu a::after {
            display: none !important;
        }

        /* Login button styling */
        .nav-links a[href*="login"] {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
        }

        .nav-links a[href*="login"]:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        /* Register CTA Button - Eye-catching */
        .nav-links .cta-btn {
            margin-left: 0 !important;
            width: 100%;
            justify-content: center;
            font-size: 1rem !important;
            padding: 1rem 1.25rem !important;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            letter-spacing: 0.025em;
            text-transform: none;
        }

        .nav-links .cta-btn::before,
        .nav-links .cta-btn::after {
            display: none !important;
        }

        .nav-links .cta-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.45) !important;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%) !important;
        }

        /* ============================================
           MOBILE NAV - Individual Section Styling
           Each nav item gets unique color & icon
           ============================================ */
        
        /* Home - Blue gradient with house icon */
        .nav-links li:first-child a {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25) 0%, rgba(37, 99, 235, 0.35) 100%) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
            position: relative;
        }
        .nav-links li:first-child a::before {
            content: "🏠";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links li:first-child a:hover,
        .nav-links li:first-child a:active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.45) 0%, rgba(37, 99, 235, 0.55) 100%) !important;
            border-color: rgba(59, 130, 246, 0.6) !important;
        }
        
        /* Services dropdown - Purple gradient with grid icon */
        .nav-links li.dropdown > a {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.25) 0%, rgba(124, 58, 237, 0.35) 100%) !important;
            border-color: rgba(139, 92, 246, 0.4) !important;
        }
        .nav-links li.dropdown > a::before {
            content: "📋";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links li.dropdown > a:hover,
        .nav-links li.dropdown > a:active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.45) 0%, rgba(124, 58, 237, 0.55) 100%) !important;
            border-color: rgba(139, 92, 246, 0.6) !important;
        }
        
        /* Caregiver dropdown item - Teal with heart icon */
        .dropdown-menu a[href*="caregiver"] {
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.2) 0%, rgba(13, 148, 136, 0.3) 100%) !important;
            border-color: rgba(20, 184, 166, 0.35) !important;
        }
        .dropdown-menu a[href*="caregiver"]::before {
            content: "💚";
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        .dropdown-menu a[href*="caregiver"]:hover,
        .dropdown-menu a[href*="caregiver"]:active {
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.4) 0%, rgba(13, 148, 136, 0.5) 100%) !important;
            border-color: rgba(20, 184, 166, 0.5) !important;
        }
        
        /* Housekeeper dropdown item - Amber with sparkle icon */
        .dropdown-menu a[href*="housekeeper"] {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.3) 100%) !important;
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        .dropdown-menu a[href*="housekeeper"]::before {
            content: "✨";
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        .dropdown-menu a[href*="housekeeper"]:hover,
        .dropdown-menu a[href*="housekeeper"]:active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.4) 0%, rgba(217, 119, 6, 0.5) 100%) !important;
            border-color: rgba(245, 158, 11, 0.5) !important;
        }
        
        /* 1099 Contractors - Orange gradient with briefcase icon */
        .nav-links a[href*="contractors"] {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.25) 0%, rgba(234, 88, 12, 0.35) 100%) !important;
            border-color: rgba(249, 115, 22, 0.4) !important;
        }
        .nav-links a[href*="contractors"]::before {
            content: "💼";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="contractors"]:hover,
        .nav-links a[href*="contractors"]:active {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.45) 0%, rgba(234, 88, 12, 0.55) 100%) !important;
            border-color: rgba(249, 115, 22, 0.6) !important;
        }
        
        /* Accredited Training Center - Emerald with graduation cap icon */
        .nav-links a[href*="training"] {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(5, 150, 105, 0.35) 100%) !important;
            border-color: rgba(16, 185, 129, 0.4) !important;
        }
        .nav-links a[href*="training"]::before {
            content: "🎓";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="training"]:hover,
        .nav-links a[href*="training"]:active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.45) 0%, rgba(5, 150, 105, 0.55) 100%) !important;
            border-color: rgba(16, 185, 129, 0.6) !important;
        }
        
        /* About - Sky blue with info icon */
        .nav-links a[href*="about"]:not([href*="caregiver"]):not([href*="housekeeper"]) {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(2, 132, 199, 0.35) 100%) !important;
            border-color: rgba(14, 165, 233, 0.4) !important;
        }
        .nav-links a[href*="about"]:not([href*="caregiver"]):not([href*="housekeeper"])::before {
            content: "ℹ️";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="about"]:not([href*="caregiver"]):not([href*="housekeeper"]):hover,
        .nav-links a[href*="about"]:not([href*="caregiver"]):not([href*="housekeeper"]):active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.45) 0%, rgba(2, 132, 199, 0.55) 100%) !important;
            border-color: rgba(14, 165, 233, 0.6) !important;
        }
        
        /* Blog - Rose/Pink with pencil icon */
        .nav-links a[href*="blog"] {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.25) 0%, rgba(225, 29, 72, 0.35) 100%) !important;
            border-color: rgba(244, 63, 94, 0.4) !important;
        }
        .nav-links a[href*="blog"]::before {
            content: "📝";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="blog"]:hover,
        .nav-links a[href*="blog"]:active {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.45) 0%, rgba(225, 29, 72, 0.55) 100%) !important;
            border-color: rgba(244, 63, 94, 0.6) !important;
        }
        
        /* Contact Us - Indigo with envelope icon */
        .nav-links a[href*="contact"] {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25) 0%, rgba(79, 70, 229, 0.35) 100%) !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
        }
        .nav-links a[href*="contact"]::before {
            content: "📧";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="contact"]:hover,
        .nav-links a[href*="contact"]:active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.45) 0%, rgba(79, 70, 229, 0.55) 100%) !important;
            border-color: rgba(99, 102, 241, 0.6) !important;
        }
        
        /* FAQ - Yellow/Amber with question icon */
        .nav-links a[href*="faq"] {
            background: linear-gradient(135deg, rgba(234, 179, 8, 0.25) 0%, rgba(202, 138, 4, 0.35) 100%) !important;
            border-color: rgba(234, 179, 8, 0.4) !important;
        }
        .nav-links a[href*="faq"]::before {
            content: "❓";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="faq"]:hover,
        .nav-links a[href*="faq"]:active {
            background: linear-gradient(135deg, rgba(234, 179, 8, 0.45) 0%, rgba(202, 138, 4, 0.55) 100%) !important;
            border-color: rgba(234, 179, 8, 0.6) !important;
        }
        
        /* Login - Glass effect with key icon */
        .nav-links a[href*="login"] {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.25) 100%) !important;
            border: 2px solid rgba(255, 255, 255, 0.35) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .nav-links a[href*="login"]::before {
            content: "🔐";
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .nav-links a[href*="login"]:hover,
        .nav-links a[href*="login"]:active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.35) 100%) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        
        /* Register CTA - Premium gradient with star icon */
        .nav-links .cta-btn::before {
            content: "⭐" !important;
            margin-right: 0.5rem;
            font-size: 1rem;
            display: inline !important;
        }

        /* Footer Mobile Styles */
        footer {
            padding: 3rem 1.5rem 2rem;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 2.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .footer-brand {
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 2rem;
        }

        .footer-logo {
            margin-bottom: 1.25rem;
            display: flex;
            justify-content: center;
        }

        .footer-logo img {
            height: 80px !important;
            width: auto;
            background-color: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .footer-brand p {
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
            text-align: left;
            color: #cbd5e1;
        }

        .footer-brand p:last-of-type {
            margin-bottom: 1.5rem;
        }

        .footer-social {
            justify-content: center;
            gap: 0.75rem;
        }

        .social-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .social-icon:active {
            background: #f97316;
            transform: scale(0.95);
        }

        .footer-section {
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 2rem;
        }

        .footer-section:last-of-type {
            border-bottom: none;
        }

        .footer-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1.25rem;
            font-weight: 700;
            color: white;
            letter-spacing: 0.01em;
        }

        .footer-section ul {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.875rem;
        }

        .footer-section ul li {
            margin: 0;
        }

        .footer-section a {
            color: #cbd5e1;
            font-size: 0.95rem;
            padding: 0.5rem 0;
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        .footer-section a:active {
            color: #f97316;
            transform: translateX(4px);
        }

        .footer-location {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            margin-bottom: 1.25rem;
            color: #cbd5e1;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .footer-location i {
            color: #f97316;
            margin-top: 0.25rem;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .footer-location a {
            color: #cbd5e1;
        }

        .footer-location a:active {
            color: #f97316;
        }

        .footer-bottom {
            flex-direction: column;
            gap: 1.25rem;
            text-align: center;
            padding-top: 1rem;
        }

        .footer-bottom p {
            font-size: 0.875rem;
            color: #94a3b8;
            margin: 0;
        }

        .footer-bottom-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .footer-bottom-links a {
            color: #94a3b8;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-bottom-links a:active {
            color: #f97316;
        }

        .newsletter-input {
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .newsletter-input input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
            min-height: 48px;
        }

        .newsletter-input input:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(255, 255, 255, 0.12);
        }

        .newsletter-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            min-height: 48px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .newsletter-btn:active {
            transform: scale(0.98);
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);
        }

        iframe {
            height: 200px !important;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .footer-divider {
            margin: 2rem auto 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            height: 1px;
        }
    }

    /* ============================================
       ADDITIONAL MOBILE ENHANCEMENTS
       ============================================ */

    /* Extra small devices (320px - 480px) */
    @media (max-width: 480px) {
        /* Ultra-compact mobile navigation */
        nav {
            height: 64px;
        }

        .nav-container {
            padding: 0 0.75rem;
        }

        .logo-section img {
            height: 50px;
        }

        .mobile-menu-btn {
            width: 44px;
            height: 44px;
            font-size: 1.5rem;
        }

        /* 2x2 Grid - more compact on smaller screens */
        .nav-links {
            top: 64px;
            max-height: calc(100vh - 64px);
            padding: 0.5rem;
            gap: 0.375rem;
        }

        .nav-links li {
            width: calc(50% - 0.1875rem);
        }

        .nav-links li:last-child {
            width: 100%;
            margin-top: 0.125rem;
        }

        .nav-links a {
            padding: 0.625rem 0.375rem;
            font-size: 0.75rem;
            min-height: 40px;
            border-radius: 6px;
        }
        
        /* Smaller icons on very small screens */
        .nav-links a::before,
        .dropdown-menu a::before {
            font-size: 0.85rem !important;
            margin-right: 0.35rem !important;
        }
        
        /* Hide icons completely on tiny screens for space */
        @media (max-width: 360px) {
            .nav-links a::before,
            .dropdown-menu a::before {
                display: none !important;
            }
        }

        .dropdown-menu {
            left: 0.5rem;
            right: 0.5rem;
            padding: 0.375rem;
            gap: 0.375rem;
        }

        .dropdown-menu a {
            padding: 0.625rem 0.375rem;
            font-size: 0.75rem;
            min-height: 40px;
        }

        .cta-btn {
            font-size: 0.875rem !important;
            padding: 0.75rem 1rem !important;
        }

        /* Compact footer on very small screens */
        footer {
            padding: 2.5rem 1rem 1.5rem;
        }

        .footer-content {
            gap: 2rem;
        }

        .footer-logo img {
            height: 70px !important;
        }

        .footer-brand p {
            font-size: 0.875rem;
        }

        .footer-section h3 {
            font-size: 1.05rem;
        }

        .footer-section a {
            font-size: 0.9rem;
            padding: 0.625rem 0;
        }

        .social-icon {
            width: 42px;
            height: 42px;
            font-size: 1.1rem;
        }

        .footer-location {
            font-size: 0.9rem;
        }

        .newsletter-input input,
        .newsletter-btn {
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
            min-height: 44px;
        }

        .footer-bottom p,
        .footer-bottom-links a {
            font-size: 0.825rem;
        }
    }

    /* Medium phones (481px - 600px) */
    @media (min-width: 481px) and (max-width: 600px) {
        nav {
            height: 68px;
        }

        .logo-section img {
            height: 52px;
        }

        .mobile-menu-btn {
            width: 46px;
            height: 46px;
        }

        .nav-links {
            top: 68px;
            max-height: calc(100vh - 68px);
        }
    }

    /* Large phones / Small tablets (601px - 768px) */
    @media (min-width: 601px) and (max-width: 768px) {
        nav {
            height: 72px;
        }

        .logo-section img {
            height: 58px;
        }

        .nav-links {
            top: 72px;
            max-height: calc(100vh - 72px);
            padding: 1.25rem;
        }

        .nav-links a {
            font-size: 1.05rem;
        }

        .cta-btn {
            font-size: 1.1rem !important;
        }

        .footer-content {
            gap: 2.25rem;
        }
    }

    /* Touch-friendly improvements for all mobile devices */
    @media (max-width: 768px) and (hover: none) and (pointer: coarse) {
        /* Ensure all interactive elements are at least 44x44px */
        .nav-links a,
        .dropdown-menu a,
        .footer-section a,
        .footer-bottom-links a,
        .social-icon,
        .mobile-menu-btn,
        button,
        input[type="submit"],
        input[type="button"] {
            min-height: 44px;
            min-width: 44px;
        }

        /* Larger tap targets for better UX */
        .nav-links li {
            padding: 0.125rem 0;
        }

        /* Prevent text selection on touch */
        .nav-links,
        .footer-section,
        .social-icon {
            -webkit-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Improve button feedback */
        button:active,
        .nav-links a:active,
        .cta-btn:active,
        .social-icon:active {
            opacity: 0.8;
        }
    }

    /* Landscape orientation on mobile devices */
    @media (max-width: 900px) and (max-height: 500px) and (orientation: landscape) {
        nav {
            height: 56px;
        }

        .logo-section img {
            height: 44px;
        }

        .mobile-menu-btn {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }

        .nav-links {
            top: 56px;
            max-height: calc(100vh - 56px);
            padding: 0.75rem;
        }

        .nav-links a {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            min-height: 42px;
        }

        footer {
            padding: 2rem 1rem 1rem;
        }

        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
    }

    /* Tablet devices (769px - 1024px) */
    @media (min-width: 769px) and (max-width: 1024px) {
        .nav-container {
            padding: 0 1.5rem;
        }

        .nav-links {
            gap: 0.25rem;
        }

        .nav-links a {
            font-size: 0.95rem;
            padding: 0.625rem 0.875rem;
        }

        .cta-btn {
            padding: 0.75rem 1.25rem !important;
            font-size: 0.95rem !important;
        }

        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
        }

        .footer-brand {
            grid-column: span 2;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 2rem;
            margin-bottom: 1rem;
        }

        .footer-logo {
            display: flex;
            justify-content: center;
        }

        .footer-social {
            justify-content: center;
        }
    }

    /* Desktop-first for larger screens */
    @media (min-width: 1025px) {
        .mobile-menu-btn {
            display: none !important;
        }

        .nav-links {
            display: flex !important;
        }
    }

    /* High-resolution displays (Retina, etc.) */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .logo-section img,
        .footer-logo img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
    }

    /* Reduced motion preferences */
    @media (prefers-reduced-motion: reduce) {
        nav,
        .nav-links,
        .nav-links a,
        .dropdown-menu,
        .cta-btn,
        .mobile-menu-btn,
        .social-icon,
        .footer-section a {
            transition: none !important;
            animation: none !important;
        }
    }

    /* ============================================
       SAFE AREA INSETS - Notched Device Support
       ============================================ */
    
    @supports (padding: env(safe-area-inset-top)) {
        /* Navigation bar - notch aware */
        @media (max-width: 768px) {
            nav {
                padding-top: env(safe-area-inset-top) !important;
            }
            
            /* Adjust nav height with safe area */
            .nav-container {
                min-height: 70px;
            }
        }
        
        /* Footer - home indicator aware */
        footer {
            padding-bottom: calc(2rem + env(safe-area-inset-bottom)) !important;
        }
        
        /* Side edges for landscape mode */
        @media (orientation: landscape) and (max-width: 900px) {
            nav .nav-container {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
            }
            
            footer {
                padding-left: max(1.5rem, env(safe-area-inset-left)) !important;
                padding-right: max(1.5rem, env(safe-area-inset-right)) !important;
            }
        }
    }

    /* Dark mode support for mobile */
    @media (prefers-color-scheme: dark) and (max-width: 768px) {
        nav {
            background: #1e293b;
            border-bottom-color: #334155;
        }

        .nav-links {
            background: #1e293b;
        }

        .nav-links a {
            color: #e2e8f0;
        }

        .dropdown-menu {
            background: #334155;
            border-color: #475569;
        }

        .mobile-menu-btn {
            background: #334155;
            color: #e2e8f0;
            border-color: #475569;
        }
    }

    /* ============================================
       MOBILE REVIEWS SECTION - 2x2 Grid
       ============================================ */
    
    /* Reviews/Testimonials Mobile Optimization */
    @media (max-width: 768px) {
        /* Reviews Section */
        section[style*="padding: 6rem 2rem"] {
            padding: 3rem 1rem !important;
        }

        /* Section Header */
        section .section-header h2 {
            font-size: 2rem !important;
            line-height: 1.3 !important;
        }

        section .section-header p {
            font-size: 1rem !important;
            padding: 0 0.5rem !important;
        }

        /* Service Type Highlights - Stack vertically on mobile */
        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit"] {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
            margin-bottom: 2.5rem !important;
        }

        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit"] > div {
            padding: 1.5rem !important;
        }

        /* Reviews Grid - 2x2 Layout on Mobile */
        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1rem !important;
        }

        /* Individual Review Cards - Mobile Optimized */
        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px"] > div {
            padding: 1.25rem !important;
            border-radius: 12px !important;
        }

        /* Review Header (Avatar + Name) */
        section > div > div[style*="display: grid"] > div > div[style*="display: flex; align-items: center; gap: 1rem"] {
            gap: 0.75rem !important;
            margin-bottom: 0.75rem !important;
        }

        /* Avatar Circle */
        section > div > div[style*="display: grid"] > div > div > div[style*="width: 56px; height: 56px"] {
            width: 42px !important;
            height: 42px !important;
            font-size: 1rem !important;
            flex-shrink: 0 !important;
        }

        /* Name and Title */
        section > div > div[style*="display: grid"] > div > div > div[style*="flex: 1"] h4 {
            font-size: 0.95rem !important;
            line-height: 1.3 !important;
        }

        section > div > div[style*="display: grid"] > div > div > div[style*="flex: 1"] p {
            font-size: 0.75rem !important;
        }

        /* Star Rating */
        section > div > div[style*="display: grid"] > div > div[style*="color: #fbbf24"] {
            font-size: 0.95rem !important;
            margin-bottom: 0.5rem !important;
        }

        /* Review Text */
        section > div > div[style*="display: grid"] > div > p[style*="color: #374151"] {
            font-size: 0.85rem !important;
            line-height: 1.5 !important;
            margin-bottom: 0.75rem !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 4 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }

        /* Review Footer (Reviewer name + location) */
        section > div > div[style*="display: grid"] > div > div[style*="display: flex; justify-content: space-between"] {
            flex-direction: column !important;
            gap: 0.375rem !important;
            padding-top: 0.5rem !important;
            font-size: 0.75rem !important;
        }

        section > div > div[style*="display: grid"] > div > div[style*="display: flex; justify-content: space-between"] span {
            font-size: 0.75rem !important;
        }

        /* SEO Location Summary Box */
        section > div > div[style*="background: linear-gradient(135deg, #f9fafb"] {
            padding: 1.5rem !important;
            margin-top: 2rem !important;
        }

        section > div > div[style*="background: linear-gradient(135deg, #f9fafb"] h3 {
            font-size: 1.3rem !important;
            margin-bottom: 1rem !important;
            line-height: 1.4 !important;
        }

        section > div > div[style*="background: linear-gradient(135deg, #f9fafb"] p {
            font-size: 0.875rem !important;
            line-height: 1.6 !important;
            text-align: left !important;
        }
    }

    /* Extra Small Phones (320px - 480px) */
    @media (max-width: 480px) {
        /* Reviews Grid - Still 2x2 but more compact */
        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px"] {
            gap: 0.75rem !important;
        }

        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px"] > div {
            padding: 1rem !important;
        }

        /* Even smaller text for tiny screens */
        section > div > div[style*="display: grid"] > div > div > div[style*="flex: 1"] h4 {
            font-size: 0.875rem !important;
        }

        section > div > div[style*="display: grid"] > div > p[style*="color: #374151"] {
            font-size: 0.8rem !important;
            -webkit-line-clamp: 3 !important;
        }

        section > div > div[style*="display: grid"] > div > div > div[style*="width: 56px; height: 56px"] {
            width: 38px !important;
            height: 38px !important;
            font-size: 0.95rem !important;
        }
    }

    /* Tablets (769px - 1024px) - 2 columns */
    @media (min-width: 769px) and (max-width: 1024px) {
        section > div > div[style*="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1.5rem !important;
        }
    }
</style>

