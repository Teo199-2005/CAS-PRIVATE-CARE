<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Blog | CAS Private Care LLC | Caregiving Tips & Resources</title>
    <meta name="title" content="Blog | CAS Private Care LLC | Caregiving Tips & Resources">
    <meta name="description" content="Read our blog for helpful caregiving tips, resources, and insights about home care services, elderly care, and connecting with quality caregivers in New York.">
    <meta name="keywords" content="caregiving blog, elderly care tips, home care resources, caregiver advice, caregiving articles">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/blog') }}">
    <meta property="og:title" content="Blog | CAS Private Care LLC">
    <meta property="og:description" content="Read our blog for helpful caregiving tips, resources, and insights about home care services.">
    <meta property="og:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/blog') }}">
    <meta property="twitter:title" content="Blog | CAS Private Care LLC">
    <meta property="twitter:description" content="Read our blog for helpful caregiving tips, resources, and insights about home care services.">
    <meta property="twitter:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @include('partials.nav-footer-styles')
    
    <style>
        /* CRITICAL: Universal viewport lock */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box !important;
        }

        html {
            overflow-x: hidden !important;
            width: 100vw !important;
            max-width: 100vw !important;
            position: relative;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* CRITICAL: Force ALL block elements to respect viewport */
        div, section, main, header, footer, article, aside {
            max-width: 100vw !important;
            overflow-x: hidden !important;
        }

        main {
            width: 100%;
            overflow-x: hidden;
        }

        .section-light {
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            padding: 6rem 2rem;
            width: 100%;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .blog-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 8rem 2rem;
            text-align: center;
            width: 100%;
            overflow-x: hidden;
            margin-top: 88px;
        }

        .blog-hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
        }

        .blog-hero p {
            font-size: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
        }

        .coming-soon {
            text-align: center;
            padding: 4rem 2rem;
        }

        .coming-soon h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .coming-soon p {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .coming-soon a {
            display: inline-block;
            padding: 1rem 2rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .coming-soon a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
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

            .section-light {
                padding: 3rem 1rem !important;
            }

            .container {
                padding: 0 1rem !important;
            }

            .coming-soon {
                padding: 2rem 1rem !important;
            }

            .coming-soon h2 {
                font-size: 1.75rem !important;
                line-height: 1.3 !important;
            }

            .coming-soon p {
                font-size: 1rem !important;
                line-height: 1.6 !important;
            }

            .coming-soon a {
                width: 100%;
                text-align: center;
                padding: 0.95rem 1.5rem !important;
                font-size: 0.95rem !important;
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

            .coming-soon h2 {
                font-size: 2.25rem;
            }

            .coming-soon p {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .blog-hero h1 {
                font-size: 2.5rem;
            }

            .blog-hero p {
                font-size: 1.125rem;
            }

            .coming-soon h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    @include('partials.navigation')

    <main>
        <!-- Hero Section -->
        <section class="blog-hero">
            <div class="container">
                <h1>Our <span style="color: #fbbf24;">Blog</span></h1>
                <p>Helpful tips, resources, and insights about caregiving, home care services, and connecting with quality caregivers</p>
            </div>
        </section>

        <!-- Coming Soon Section -->
        <section class="section-light">
            <div class="container">
                <div class="coming-soon">
                    <h2>Coming Soon</h2>
                    <p>We're working on bringing you valuable caregiving tips, resources, and insights. Check back soon for helpful articles about home care services, elderly care, and connecting with quality caregivers.</p>
                    <a href="{{ url('/') }}">Return to Home</a>
                </div>
            </div>
        </section>
    </main>

    @include('partials.footer')
    
    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')

    <script>
        // CRITICAL: Force viewport reset and prevent horizontal scroll
        (function() {
            // Reset scroll position to left on load
            window.scrollTo(0, 0);
            document.documentElement.scrollLeft = 0;
            document.body.scrollLeft = 0;
            
            // Force all elements to respect viewport
            function constrainViewport() {
                const html = document.documentElement;
                const body = document.body;
                
                html.style.overflowX = 'hidden';
                html.style.maxWidth = '100vw';
                html.style.width = '100%';
                
                body.style.overflowX = 'hidden';
                body.style.maxWidth = '100vw';
                body.style.width = '100%';
                
                // Reset horizontal scroll
                window.scrollTo(0, window.scrollY);
                html.scrollLeft = 0;
                body.scrollLeft = 0;
            }
            
            // Run on load
            constrainViewport();
            
            // Run after DOM is fully loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', constrainViewport);
            }
            
            // Run on window load
            window.addEventListener('load', constrainViewport);
            
            // Prevent horizontal scrolling
            window.addEventListener('scroll', function() {
                if (window.scrollX !== 0) {
                    window.scrollTo(0, window.scrollY);
                }
            });
            
            // Reset on resize
            window.addEventListener('resize', constrainViewport);
        })();
    </script>
</body>
</html>

