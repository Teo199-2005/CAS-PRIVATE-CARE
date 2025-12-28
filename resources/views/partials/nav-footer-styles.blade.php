<style>
    /* Navigation Styles */
    nav {
        position: fixed;
        top: 0;
        width: 100%;
        background: #ffffff;
        backdrop-filter: blur(20px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        padding: 0;
        height: 88px;
        border-bottom: 2px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
    }

    .nav-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
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
        height: 75px;
        width: auto;
        object-fit: contain;
        transition: transform 0.3s ease;
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

    /* Subtle dividers between navigation items */
    .nav-links li:not(:last-child)::after {
        content: '';
        position: absolute;
        right: -0.125rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(0, 0, 0, 0.1);
        pointer-events: none;
    }

    /* Extra spacing before CTA button (Register) */
    .nav-links li:last-child {
        margin-left: 0.75rem;
    }

    .nav-links a {
        text-decoration: none;
        color: #1e293b;
        font-weight: 500;
        font-size: 1rem;
        padding: 0.625rem 1rem;
        border-radius: 6px;
        position: relative;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid transparent;
    }

    .nav-links a:not(.cta-btn):hover {
        color: #3b82f6;
        background: rgba(59, 130, 246, 0.08);
        border-color: rgba(59, 130, 246, 0.2);
    }

    .nav-links a:not(.cta-btn):active {
        background: rgba(59, 130, 246, 0.12);
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
        left: 0;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 0.5rem;
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        border: 2px solid #e5e7eb;
    }

    .dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-menu a {
        display: block;
        padding: 0.75rem 1.25rem;
        color: #1e293b;
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 500;
    }

    .dropdown-menu a:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        transform: none;
    }

    .cta-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white !important;
        padding: 0.75rem 1.5rem !important;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(37, 99, 235, 0.3);
        position: relative;
        overflow: hidden;
    }

    .cta-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .cta-btn:hover::before {
        left: 100%;
    }

    .cta-btn:hover {
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        border-color: rgba(37, 99, 235, 0.5);
    }

    .cta-btn:active {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: 2px solid #e5e7eb;
        font-size: 1.5rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        z-index: 1001;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
    }

    .mobile-menu-btn:hover {
        background: rgba(59, 130, 246, 0.08);
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

    @media (max-width: 768px) {
        nav {
            height: 80px;
        }

        .nav-container {
            padding: 0 1rem;
        }

        .logo-section img {
            height: 60px;
        }

        .mobile-menu-btn {
            display: flex;
        }

        .nav-links {
            display: none;
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: white;
            flex-direction: column;
            padding: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 999;
            gap: 0.5rem;
            align-items: stretch;
        }

        .nav-links.active {
            display: flex;
        }

        .nav-links li {
            width: 100%;
        }

        .nav-links a {
            display: block;
            padding: 1rem 1.25rem;
            width: 100%;
            font-size: 1rem;
            justify-content: space-between;
        }

        .dropdown-menu {
            position: static;
            opacity: 1;
            visibility: visible;
            transform: none;
            box-shadow: none;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            margin-left: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .cta-btn {
            margin-left: 0;
            width: 100%;
            justify-content: center;
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
</style>

