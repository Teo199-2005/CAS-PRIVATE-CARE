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
        body {
            font-family: 'Sora', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #1e293b;
            background-color: #ffffff;
        }

        .section-light {
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            padding: 6rem 2rem;
        }

        .section-dark {
            background-color: #dbeafe;
            background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
            padding: 6rem 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .blog-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 8rem 2rem;
            text-align: center;
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

    <main style="padding-top: 88px;">
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
                    <a href="{{ url('/') }}" style="display: inline-block; padding: 1rem 2rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 1rem;">Return to Home</a>
                </div>
            </div>
        </section>
    </main>

    @include('partials.footer')
</body>
</html>

