<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Frequently Asked Questions | CAS Private Care LLC</title>
    <meta name="title" content="Frequently Asked Questions | CAS Private Care LLC">
    <meta name="description" content="Find answers to common questions for clients booking care services and 1099 contractors. Learn about booking, payments, scheduling, and more.">
    <meta name="keywords" content="caregiver FAQ, care services FAQ, 1099 contractor FAQ, caregiver contractor questions, booking care services">
    <meta name="author" content="CAS Private Care LLC">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/faq') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/faq') }}">
    <meta property="og:title" content="Frequently Asked Questions | CAS Private Care LLC">
    <meta property="og:description" content="Find answers to common questions for clients booking care services and 1099 contractors.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/faq') }}">
    <meta property="twitter:title" content="Frequently Asked Questions | CAS Private Care LLC">
    <meta property="twitter:description" content="Find answers to common questions for clients booking care services and 1099 contractors.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">
    
    <!-- FAQ Schema for SEO -->
    <script type="application/ld+json">
    @php
    echo json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => [
        [
          '@type' => 'Question',
          'name' => 'How quickly can I get a caregiver?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'For emergency situations, we can typically arrange a caregiver within 4-6 hours. For scheduled services, we recommend booking 24-48 hours in advance to ensure the best match. Our online platform allows instant browsing and booking of available caregivers.'
          ]
        ],
        [
          '@type' => 'Question',
          'name' => 'What payment methods do you accept?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'We accept multiple payment methods including credit cards, debit cards, and secure online payments through our platform. All payments are processed securely with encryption. We also offer payment plans and can coordinate with insurance for qualifying services.'
          ]
        ],
        [
          '@type' => 'Question',
          'name' => 'Do you serve all NYC boroughs?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Yes! CAS Private Care provides verified caregivers throughout all five NYC boroughs: Manhattan, Brooklyn, Queens, Bronx, and Staten Island. We also serve surrounding areas in New York State.'
          ]
        ],
        [
          '@type' => 'Question',
          'name' => 'Is this a W-2 job or 1099 contractor position?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'This is a 1099 independent contractor position. You are self-employed and have flexibility over your schedule and which bookings to accept.'
          ]
        ],
        [
          '@type' => 'Question',
          'name' => 'How do payments work for contractors?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Payments are issued on a regular schedule through our platform once a booking is successfully completed. You can view your completed jobs and payout history in your contractor dashboard. Specific rates are shown inside the platform for each booking before you accept it.'
          ]
        ]
      ]
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    
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
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: #f9fafb;
        }

        main {
            margin-top: 88px;
            padding: 4rem 2rem;
        }

        :root {
            --faq-bg: #f8fafc;
            --faq-card: #ffffff;
            --faq-text: #0f172a;
            --faq-muted: #64748b;
            --faq-border: rgba(15, 23, 42, 0.08);
            --faq-blue: #1e40af;
            --faq-blue-2: #3b82f6;
            --faq-orange: #f97316;
            --faq-orange-2: #ea580c;
            --faq-ring: rgba(59, 130, 246, 0.18);
        }

        .faq-hero {
            text-align: center;
            margin-bottom: 4rem;
        }

        .faq-hero {
            padding: 3rem 1.25rem;
            background: radial-gradient(1200px 600px at 50% -10%, rgba(59, 130, 246, 0.18), transparent 60%),
                        radial-gradient(900px 500px at 90% 0%, rgba(249, 115, 22, 0.14), transparent 60%);
            border: 1px solid var(--faq-border);
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
        }

        .faq-hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .faq-hero p {
            font-size: 1.2rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
        }

        .faq-meta {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .faq-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid var(--faq-border);
            color: var(--faq-muted);
            font-weight: 600;
            font-size: 0.95rem;
            backdrop-filter: blur(8px);
        }

        .faq-pill i {
            color: var(--faq-blue-2);
        }

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .faq-section {
            margin-bottom: 4rem;
        }

        .faq-section {
            background: var(--faq-card);
            border: 1px solid var(--faq-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 14px 45px rgba(15, 23, 42, 0.06);
        }

        .faq-section-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
            border-radius: 0;
            margin-bottom: 0;
        }

        .faq-section-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .faq-section-header i {
            font-size: 2rem;
        }

        .faq-section-content {
            background: white;
            border-radius: 0;
            padding: 1.25rem;
            box-shadow: none;
        }

        /* Accordion */
        .faq-accordion {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        details.faq-item {
            border: 1px solid var(--faq-border);
            border-radius: 16px;
            background: #ffffff;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        details.faq-item[open] {
            border-color: rgba(59, 130, 246, 0.25);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
        }

        details.faq-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        details.faq-item summary {
            list-style: none;
            cursor: pointer;
            padding: 1.1rem 1.15rem;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 1rem;
            color: var(--faq-text);
            font-weight: 800;
            font-size: 1.05rem;
        }

        details.faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-q {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .faq-q i {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.12);
            color: var(--faq-blue-2);
            flex: 0 0 auto;
        }

        .faq-chevron {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid var(--faq-border);
            color: var(--faq-muted);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        details.faq-item[open] .faq-chevron {
            transform: rotate(180deg);
            background: rgba(59, 130, 246, 0.08);
        }

        .faq-a {
            padding: 0 1.15rem 1.15rem;
            color: var(--faq-muted);
            line-height: 1.8;
            font-size: 1rem;
        }

        .faq-a p {
            margin: 0;
        }

        .faq-a ul {
            margin: 0.75rem 0 0;
            padding-left: 1.25rem;
        }

        .faq-a li {
            margin: 0.35rem 0;
        }

        footer {
            background: #0f172a;
            color: white;
            padding: 5rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand p {
            color: #94a3b8;
            line-height: 1.7;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
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

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 0.9rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .faq-hero h1 {
                font-size: 2rem;
            }

            .faq-hero p {
                font-size: 1rem;
            }

            .faq-section-header h2 {
                font-size: 1.5rem;
            }

            .faq-item {
                padding: 1.5rem;
            }

            .faq-item:hover {
                padding-left: 1.75rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* Scroll Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        [data-animate] {
            opacity: 0;
        }

        [data-animate].visible {
            animation: fadeInUp 0.6s ease forwards;
        }

    /* Remove old pseudo icon rules; accordion uses explicit icons */
    </style>
</head>
<body>
    @include('partials.navigation')

    <main>
        <div class="faq-hero">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to your questions about booking, safety, and working with CAS Private Care.</p>
            <div class="faq-meta">
                <span class="faq-pill"><i class="bi bi-shield-check"></i> Verified contractors</span>
                <span class="faq-pill"><i class="bi bi-credit-card"></i> Secure payments</span>
                <span class="faq-pill"><i class="bi bi-clock"></i> Fast booking</span>
            </div>
        </div>

        <div class="faq-container">
            <!-- FAQs for Clients -->
            <div class="faq-section" data-animate>
                <div class="faq-section-header">
                    <h2>
                        <i class="bi bi-house-heart-fill"></i>
                        For Clients
                    </h2>
                </div>
                <div class="faq-section-content">
                    <div class="faq-accordion">
                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-grid"></i> What services do you provide?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>We connect families with verified independent contractors for:</p>
                                <ul>
                                    <li><strong>Caregiving</strong> (elderly & companion support, medication reminders, meal prep, mobility support, supervision)</li>
                                    <li><strong>Special needs support</strong> (personalized routines and assistance based on the client’s needs)</li>
                                    <li><strong>Housekeeping</strong> (house helpers, deep cleaning, and home organization depending on the booking)</li>
                                </ul>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-lightning-charge"></i> How quickly can I book a caregiver or housekeeper?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Availability depends on your location, schedule, and the type of service. You can browse available profiles and request a booking anytime. For urgent needs, choose the soonest available time and we’ll help you connect quickly.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-geo-alt"></i> Do you serve all NYC boroughs?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Yes. We connect clients with verified contractors across Manhattan, Brooklyn, Queens, the Bronx, and Staten Island (and nearby areas when available).</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-credit-card"></i> How do payments work?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Payments are processed securely through our platform. You’ll see the total cost for your booking before confirming. After service is completed, you can leave a rating and review.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-shield-check"></i> Are contractors verified and background-checked?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Yes. We use a verification process that may include identity verification, background checks, and credential validation where applicable. Individual profiles may show verified badges and documentation status.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-calendar-check"></i> Can I schedule recurring services?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Yes. You can request one-time or recurring bookings. Many clients schedule weekly or daily support depending on availability and the contractor’s schedule.</p>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- FAQs for 1099 Contractors -->
            <div class="faq-section" data-animate>
                <div class="faq-section-header" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                    <h2>
                        <i class="bi bi-briefcase-fill"></i>
                        For 1099 Contractors
                    </h2>
                </div>
                <div class="faq-section-content">
                    <div class="faq-accordion">
                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-person-badge"></i> Is this W-2 employment or 1099 contracting?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Contractors on CAS Private Care are independent 1099 contractors (self-employed). You control your availability and which bookings you accept. Contractors are responsible for their own taxes and business compliance.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-cash-stack"></i> How do payouts work?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Payouts are sent on a regular schedule through the platform after services are completed. You can view job history and payout status in your dashboard. Each booking shows the compensation details inside the platform before you accept.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-check2-square"></i> Do I choose my clients and bookings?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Yes. You can review booking details (service type, schedule, location, and requirements) and decide whether to accept. No minimum hours are required.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-map"></i> Where can I work?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>Bookings are available across NYC boroughs based on demand and your availability. You can filter requests by distance/location to find jobs that fit your route.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-award"></i> Do you offer referral or partner programs?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>We may offer partner programs (marketing partners and training center partnerships). Eligibility and terms are shown inside the platform or shared by our team—program amounts and compensation details are not displayed publicly.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                <span class="faq-q"><i class="bi bi-shield-lock"></i> What do I need to join?</span>
                                <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="faq-a">
                                <p>To join, complete registration and verification. Requirements may include identity verification, background checks, and confirming any applicable certifications (HHA/CNA/CPR) depending on the services you offer.</p>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>

        <!-- Still Need Help CTA -->
        <div style="max-width: 1000px; margin: 3rem auto 0; background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%); border-radius: 20px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; right: 0; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
            <div style="position: relative; z-index: 1;">
                <div style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                    <i class="bi bi-headset" style="font-size: 1.75rem; color: white;"></i>
                </div>
                <h3 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 700; color: white; margin-bottom: 0.75rem;">Still Have Questions?</h3>
                <p style="color: rgba(255, 255, 255, 0.85); font-size: 1rem; margin-bottom: 1.5rem; max-width: 500px; margin-left: auto; margin-right: auto;">Our team is here to help. Reach out and we'll get back to you within 24 hours.</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="tel:+13479947331" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.5rem; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: background 0.2s;">
                        <i class="bi bi-telephone-fill"></i>
                        Call Us
                    </a>
                    <a href="{{ url('/contact') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.5rem; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;">
                        <i class="bi bi-envelope-fill"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </main>

    @include('partials.footer')
    
    <!-- Mobile-Only Footer -->
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
                    }, index * 150);
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
