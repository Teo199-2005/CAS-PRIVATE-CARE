<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">

    <!-- Primary Meta Tags -->
    <title>Blog | CAS Private Care LLC | Caregiving Tips & Resources</title>
    <meta name="title" content="Blog | CAS Private Care LLC | Caregiving Tips & Resources">
    <meta name="description" content="Read our blog for helpful caregiving tips, resources, and insights about home care services, elderly care, and connecting with quality caregivers in New York.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/blog') }}">
    <meta property="og:title" content="Blog | CAS Private Care LLC | Caregiving Tips & Resources">
    <meta property="og:description" content="Helpful tips and resources about caregiving, home care services, and connecting with quality caregivers in New York.">
    <meta property="og:image" content="{{ asset('logo.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/blog') }}">
    <meta property="twitter:title" content="Blog | CAS Private Care LLC | Caregiving Tips & Resources">
    <meta property="twitter:description" content="Helpful tips and resources about caregiving, home care services, and connecting with quality caregivers in New York.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @include('partials.nav-footer-styles')

    <style>
        /* Blog styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0f172a;
            overflow-x: hidden;
            background: #f8fafc;
        }

        main {
            margin-top: 88px;
        }

        /* NOTE: The rest of the original blog styles continue below in this file. */
    </style>
</head>

<body>
    @include('partials.navigation')

    @include('partials.trust-strip')

    <main>
        <!-- Hero Section -->
        <section class="blog-hero">
            <div class="container">
                <h1><i class="bi bi-book-half"></i> Our <span style="color: #fbbf24;">Blog</span></h1>
                <p>Helpful tips, resources, and insights about caregiving, home care services, and connecting with quality caregivers</p>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <input type="text" placeholder="Search blogs...">
                    <i class="bi bi-search"></i>
                </div>
            </div>
        </section>

        <!-- Blog Content Section -->
        <section class="section-light">
            <div class="container">
                <div class="blog-layout">
                    <!-- Main Content -->
                    <div>
                        <!-- Featured Article -->
                        <div class="blog-card" style="grid-column: 1 / -1;">
                            <div class="blog-card-image" style="height: 250px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                                <span class="featured-badge">
                                    <i class="bi bi-star-fill"></i>
                                    FEATURED
                                </span>
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div class="blog-card-content">
                                <span class="blog-category category-tips">
                                    <i class="bi bi-lightbulb-fill"></i>
                                    Tips & Guides
                                </span>
                                <h3>Essential Guide to Choosing the Right Caregiver</h3>
                                <p>Learn the key factors to consider when selecting a caregiver for your loved ones, including qualifications, experience, and compatibility. This comprehensive guide covers everything from initial screening to building a lasting relationship.</p>
                                <div class="blog-meta">
                                    <div class="blog-author">
                                        <i class="bi bi-person-circle"></i>
                                        CAS Care Team
                                        <div class="blog-read-time">
                                            <i class="bi bi-clock"></i>
                                            8 min read
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Article 4 -->
                            <div class="blog-card">
                                <div class="blog-card-image" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                                    <i class="bi bi-battery-charging"></i>
                                </div>
                                <div class="blog-card-content">
                                    <span class="blog-category category-resources">
                                        <i class="bi bi-book-fill"></i>
                                        Caregiver Resources
                                    </span>
                                    <h3>Managing Caregiver Burnout: Self-Care Tips for Caregivers</h3>
                                    <p>Caregiving can be demanding. Learn how to recognize burnout signs and implement self-care strategies to maintain your wellbeing.</p>
                                    <div class="blog-meta">
                                        <div class="blog-author">
                                            <i class="bi bi-person-circle"></i>
                                            CAS Care Team
                                        </div>
                                        <div class="blog-read-time">
                                            <i class="bi bi-clock"></i>
                                            5 min read
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blog Grid -->
                        <div class="blog-grid">
                            <!-- Article 1 -->
                            <div class="blog-card">
                                <div class="blog-card-image" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="bi bi-house-heart"></i>
                                </div>
                                <div class="blog-card-content">
                                    <span class="blog-category category-stories">
                                        <i class="bi bi-heart-fill"></i>
                                        Success Stories
                                    </span>
                                    <h3>Creating a Safe Home Environment for Seniors</h3>
                                    <p>Practical tips and modifications to make your home safer and more comfortable for elderly family members.</p>
                                    <div class="blog-meta">
                                        <div class="blog-author">
                                            <i class="bi bi-person-circle"></i>
                                            CAS Care Team
                                        </div>
                                        <div class="blog-read-time">
                                            <i class="bi bi-clock"></i>
                                            6 min read
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Article 2 -->
                            <div class="blog-card">
                                <div class="blog-card-image" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="blog-card-content">
                                    <span class="blog-category category-tips">
                                        <i class="bi bi-lightbulb-fill"></i>
                                        Tips & Guides
                                    </span>
                                    <h3>Building Trust Between Caregivers and Families</h3>
                                    <p>Learn effective communication strategies to build strong relationships between caregivers and family members.</p>
                                    <div class="blog-meta">
                                        <div class="blog-author">
                                            <i class="bi bi-person-circle"></i>
                                            CAS Care Team
                                        </div>
                                        <div class="blog-read-time">
                                            <i class="bi bi-clock"></i>
                                            4 min read
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Article 3 -->
                            <div class="blog-card">
                                <div class="blog-card-image" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                                    <i class="bi bi-clipboard2-pulse"></i>
                                </div>
                                <div class="blog-card-content">
                                    <span class="blog-category category-resources">
                                        <i class="bi bi-book-fill"></i>
                                        Caregiver Resources
                                    </span>
                                    <h3>Understanding Medicare and Home Care Coverage</h3>
                                    <p>A comprehensive guide to understanding what Medicare covers for home care services and how to maximize benefits.</p>
                                    <div class="blog-meta">
                                        <div class="blog-author">
                                            <i class="bi bi-person-circle"></i>
                                            CAS Care Team
                                        </div>
                                        <div class="blog-read-time">
                                            <i class="bi bi-clock"></i>
                                            7 min read
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="sidebar">
                        <h3><i class="bi bi-fire"></i> Popular Posts</h3>
                        <div class="popular-posts">
                            <div class="popular-post">
                                <div class="popular-post-image">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                                <div class="popular-post-content">
                                    <h4>Signs Your Loved One May Need Care</h4>
                                    <span class="popular-post-date">Dec 15, 2025</span>
                                </div>
                            </div>
                            <div class="popular-post">
                                <div class="popular-post-image">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="popular-post-content">
                                    <h4>Creating a Care Schedule That Works</h4>
                                    <span class="popular-post-date">Dec 10, 2025</span>
                                </div>
                            </div>
                            <div class="popular-post">
                                <div class="popular-post-image">
                                    <i class="bi bi-chat-heart"></i>
                                </div>
                                <div class="popular-post-content">
                                    <h4>Communication Tips for Dementia Care</h4>
                                    <span class="popular-post-date">Dec 5, 2025</span>
                                </div>
                            </div>
                        </div>

                        <!-- Newsletter Box -->
                        @include('partials.newsletter-signup')
                    </aside>
                </div>

                <!-- CTA Banner -->
                <div class="cta-banner">
                    <h2>Ready to Find Quality Care?</h2>
                    <p>Connect with verified, background-checked caregivers in your area. Get started in minutes.</p>
                    <div class="cta-buttons">
                        <a href="{{ url('/register') }}" class="btn-white">
                            <i class="bi bi-search"></i>
                            Find a Caregiver
                        </a>
                        <a href="{{ url('/contact') }}" class="btn-outline">
                            <i class="bi bi-telephone"></i>
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('partials.footer')
    @include('partials.mobile-footer')

    <style>
            color: #f97316;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .btn-outline {
            padding: 1rem 2rem;
            background: transparent;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid white;
            transition: background 0.2s;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Tablet Responsive Styles */
        @media (min-width: 481px) and (max-width: 1024px) {
            .blog-hero {
                margin-top: 72px;
                padding: 4rem 2rem !important;
            }

            .blog-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1.75rem;
            }

            .blog-card-image {
                height: 180px;
            }

            .blog-card-image i {
                font-size: 3.5rem;
            }

            .blog-layout {
                gap: 2.5rem;
            }

            .sidebar {
                padding: 1.75rem;
            }
        }

        /* Mobile Responsive Styles */
        @media (max-width: 480px) {
            .blog-hero {
                margin-top: 70px;
                padding: 3rem 1.5rem !important;
            }

            .blog-hero h1 {
                font-size: 2rem !important;
                line-height: 1.2 !important;
                margin-bottom: 1rem !important;
            }

            .blog-hero p {
                font-size: 0.95rem !important;
                line-height: 1.6 !important;
                max-width: 100% !important;
            }

            .search-bar {
                margin: 1.5rem auto;
            }

            .search-bar input {
                padding: 0.85rem 2.75rem 0.85rem 1.25rem;
                font-size: 0.95rem;
            }

            .section-light {
                padding: 3rem 1rem !important;
            }

            .container {
                padding: 0 1rem !important;
            }

            .blog-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                margin-top: 2rem;
            }

            .blog-card-image {
                height: 160px !important;
            }

            .blog-card-image i {
                font-size: 2.5rem !important;
            }

            .blog-card h3 {
                font-size: 1.15rem;
            }

            .blog-card p {
                font-size: 0.9rem;
            }

            .blog-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .sidebar {
                padding: 1.5rem;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .newsletter-form button {
                width: 100%;
            }

            .cta-banner {
                padding: 2rem 1.5rem;
                margin-top: 2rem;
            }

            .cta-banner h2 {
                font-size: 1.75rem;
            }

            .cta-banner p {
                font-size: 1rem;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .btn-white, .btn-outline {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .blog-hero {
                margin-top: 72px;
                padding: 4rem 2rem;
            }

            .blog-hero h1 {
                font-size: 2.75rem;
            }

            .blog-hero p {
                font-size: 1.15rem;
            }

            .blog-layout {
                grid-template-columns: 1fr;
            }

            .blog-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .blog-hero h1 {
                font-size: 2.5rem;
            }

            .blog-hero p {
                font-size: 1.125rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .blog-layout {
                grid-template-columns: 1fr 280px;
                gap: 2rem;
            }

            .blog-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
    </style>

    @include('partials.mobile-action-bar')
</body>
</html>

