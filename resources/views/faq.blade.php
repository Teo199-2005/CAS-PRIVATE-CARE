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
            'text' => 'You earn $28 per hour as a caregiver contractor. Payments are processed on a regular schedule, and you\'ll receive detailed earnings reports. All rates are transparent and agreed upon before you accept any booking.'
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
            margin-top: 80px;
            padding: 4rem 2rem;
        }

        .faq-hero {
            text-align: center;
            margin-bottom: 4rem;
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

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .faq-section {
            margin-bottom: 4rem;
        }

        .faq-section-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px 16px 0 0;
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
            border-radius: 0 0 16px 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .faq-item {
            padding: 2rem;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-item:hover {
            background: #f9fafb;
            padding-left: 2.5rem;
        }

        .faq-item h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
        }

        .faq-item p {
            color: #6b7280;
            line-height: 1.7;
            margin: 0;
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
    </style>
</head>
<body>
    @include('partials.navigation')

    <main>
        <div class="faq-hero">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to your questions about our services</p>
        </div>

        <div class="faq-container">
            <!-- FAQs for Clients -->
            <div class="faq-section">
                <div class="faq-section-header">
                    <h2>
                        <i class="bi bi-house-heart-fill"></i>
                        For Clients
                    </h2>
                </div>
                <div class="faq-section-content">
                    <div class="faq-item">
                        <h3>What services do you provide?</h3>
                        <p>We focus on in-home caregiving services, including: Elderly Care (personal care support, meal preparation, medication reminders, companionship), Companion Care (conversation, supervision, light daily support), Personal Care (daily living assistance and personal hygiene support), and Special Needs Care (personalized support for individuals with unique requirements). All caregivers are verified and background-checked.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>How quickly can I get a caregiver?</h3>
                        <p>For emergency situations, we can typically arrange a caregiver within 4-6 hours. For scheduled services, we recommend booking 24-48 hours in advance to ensure the best match. Our online platform allows instant browsing and booking of available caregivers. We maintain a large network across all NYC boroughs to ensure availability.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Do you serve all NYC boroughs?</h3>
                        <p>Yes! CAS Private Care provides verified caregivers throughout all five NYC boroughs: Manhattan, Brooklyn, Queens, Bronx, and Staten Island. We also serve surrounding areas in New York State. Our extensive network ensures we can match you with a qualified caregiver in your area.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>What payment methods do you accept?</h3>
                        <p>We accept multiple payment methods including credit cards, debit cards, and secure online payments through our platform. All payments are processed securely with encryption. We also offer payment plans and can coordinate with insurance for qualifying services. Pricing is transparent with no hidden fees.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Are your caregivers verified and licensed?</h3>
                        <p>Yes, all our caregivers are thoroughly verified and background-checked. Caregivers providing medical services or home health aide services are licensed by the New York State Department of Health. All caregivers undergo background checks and certification verification regardless of service type. We ensure all caregivers meet or exceed New York State requirements.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>How does the booking process work?</h3>
                        <p>Our booking process is simple: 1) Browse & Select - Search for caregivers and review their profiles, credentials, and ratings. 2) Book & Schedule - Choose your preferred schedule and book instantly. Payments are processed securely through our platform. 3) Connect & Care - Caregivers receive bookings and deliver exceptional services. 4) Rate & Review - Share your experience to help others make informed decisions.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Can I schedule recurring services?</h3>
                        <p>Yes, you can schedule both one-time and recurring services through our platform. Our intuitive booking system allows you to set up recurring appointments with your preferred caregiver, making it easy to maintain consistent care for yourself or your loved ones.</p>
                    </div>
                </div>
            </div>

            <!-- FAQs for 1099 Contractors -->
            <div class="faq-section">
                <div class="faq-section-header" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                    <h2>
                        <i class="bi bi-briefcase-fill"></i>
                        For 1099 Contractors
                    </h2>
                </div>
                <div class="faq-section-content">
                    <div class="faq-item">
                        <h3>Is this a W-2 job or 1099 contractor position?</h3>
                        <p>This is a 1099 independent contractor position. You are self-employed and have flexibility over your schedule and which bookings to accept. You are responsible for your own taxes, insurance, and compliance with applicable laws.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>How do payments work?</h3>
                        <p>You earn <span style="color: #10b981; font-weight: 700;">$28 per hour</span> as a caregiver contractor. Payments are processed on a regular schedule, and you'll receive detailed earnings reports. All rates are transparent and agreed upon before you accept any booking. Your caregiver rate of $28.00/hour remains consistent regardless of referral codes.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Do I choose my clients?</h3>
                        <p>Yes, as an independent contractor, you have full control over which bookings to accept. You can review client needs, schedule, location, and rate before accepting any booking. This gives you the flexibility to work with clients that match your preferences and availability.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>What areas of New York do you serve?</h3>
                        <p>We connect caregiver contractors with clients across all five NYC boroughs: Manhattan, Brooklyn, Queens, the Bronx, and Staten Island. You can choose jobs near you and work in the communities you know best. Our platform allows you to filter bookings by location to find opportunities in your preferred areas.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>How flexible is the schedule?</h3>
                        <p>You have complete flexibility over your schedule. Work when it suits you - choose your own hours and accept bookings that fit your availability. Build your income around your life. There are no minimum hours required, so you can work as much or as little as you prefer.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>What are the referral and training incentives?</h3>
                        <p>We offer additional earning opportunities through our partner network: <span style="color: #10b981; font-weight: 700;">Marketing Partner Program</span> - Earn $1.00/hour for every hour worked by caregivers you refer. <span style="color: #10b981; font-weight: 700;">Training Center Partnership</span> - Training centers earn $0.50/hour for caregivers they train and certify. These programs allow you to build passive income while supporting the caregiver community.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>What are the requirements to become a contractor?</h3>
                        <p>To become a contractor with CAS Private Care, you need to: Complete the registration process, submit required documents and credentials, pass our verification and background check process, and maintain any necessary licenses or certifications for your service type. Our team will guide you through each step of the application process.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.footer')
    
    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')
</body>
</html>
