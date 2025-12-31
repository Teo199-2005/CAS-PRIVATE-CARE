<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>{{ $post->title }} - CAS Private Care LLC Blog</title>
    <meta name="description" content="{{ $post->excerpt }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Sora:wght@600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    
    @include('partials.nav-footer-styles')
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
            overflow-x: hidden;
        }

        .post-hero {
            margin-top: 88px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            padding: 100px 20px 60px;
            text-align: center;
            color: white;
        }

        .post-category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .post-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .post-meta {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
            font-size: 1rem;
            opacity: 0.95;
        }

        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .post-featured-image {
            width: 100%;
            max-height: none;
            height: auto;
            object-fit: contain;
            object-position: center;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            display: block;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #1e293b;
        }

        .post-content img {
            width: 100%;
            max-width: 600px;
            max-height: 400px;
            height: auto;
            object-fit: contain;
            object-position: center;
            border-radius: 15px;
            margin: 30px auto;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            display: block;
        }

        .post-content p {
            margin-bottom: 20px;
        }

        .post-content h2 {
            font-size: 2rem;
            margin: 40px 0 20px;
            color: #1e293b;
            font-weight: 700;
        }

        .post-content h3 {
            font-size: 1.5rem;
            margin: 30px 0 15px;
            color: #1e293b;
            font-weight: 600;
        }

        .post-content ul,
        .post-content ol {
            margin: 20px 0 20px 30px;
            line-height: 1.8;
        }

        .post-content li {
            margin-bottom: 12px;
        }

        .post-content li strong {
            color: #1e40af;
            font-weight: 600;
        }

        .post-content img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 50px 0;
            padding: 30px 0;
            border-top: 2px solid #f0f0f0;
            border-bottom: 2px solid #f0f0f0;
        }

        .back-to-blog {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-to-blog:hover {
            background: #2563eb;
            transform: translateX(-5px);
        }

        .share-buttons {
            display: flex;
            gap: 10px;
        }

        .share-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            transform: translateY(-3px);
        }

        .share-btn.facebook { background: #3b5998; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.linkedin { background: #0077b5; }
        .share-btn.email { background: #666; }

        .related-posts {
            margin-top: 60px;
        }

        .related-posts h2 {
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            color: #2c3e50;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .related-card-image {
            width: 100%;
            height: auto;
            min-height: 180px;
            max-height: 250px;
            object-fit: contain;
            object-position: center;
            background: #f9fafb;
        }

        .related-card-content {
            padding: 20px;
        }

        .related-card-category {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .related-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.3;
        }

        @media (max-width: 768px) {
            .post-hero {
                margin-top: 70px;
                padding: 60px 20px 40px;
            }

            .post-title {
                font-size: 2rem;
            }

            .post-meta {
                flex-direction: column;
                gap: 10px;
            }

            .post-featured-image,
            .post-content img {
                max-width: 100%;
                max-height: 280px;
                border-radius: 12px;
                margin: 20px auto;
            }

            .related-card-image {
                min-height: 150px;
                max-height: 200px;
            }

            .post-actions {
                flex-direction: column;
                gap: 20px;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    @include('partials.navigation')

    <!-- Hero Section -->
    <section class="post-hero">
        <span class="post-category">{{ $post->category }}</span>
        <h1 class="post-title">{{ $post->title }}</h1>
        <div class="post-meta">
            <div class="post-meta-item">
                <i class="bi bi-person-circle"></i>
                <span>{{ $post->author }}</span>
            </div>
            <div class="post-meta-item">
                <i class="bi bi-calendar"></i>
                <span>{{ date('F j, Y', strtotime($post->published_at)) }}</span>
            </div>
            <div class="post-meta-item">
                <i class="bi bi-clock"></i>
                <span>{{ $post->reading_time }}</span>
            </div>
        </div>
    </section>

    <!-- Post Content -->
    <article class="post-container">
        <div class="post-content">
            {!! $post->content !!}
        </div>

        <div class="post-actions">
            <a href="{{ route('blog.index') }}" class="back-to-blog">
                <i class="bi bi-arrow-left"></i>
                Back to Blog
            </a>
            
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="share-btn facebook" title="Share on Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="share-btn twitter" title="Share on Twitter">
                    <i class="bi bi-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->url()) }}&title={{ urlencode($post->title) }}" target="_blank" class="share-btn linkedin" title="Share on LinkedIn">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(request()->url()) }}" class="share-btn email" title="Share via Email">
                    <i class="bi bi-envelope"></i>
                </a>
            </div>
        </div>

        <!-- Related Posts -->
        @if($relatedPosts->count() > 0)
        <section class="related-posts">
            <h2>Related Articles</h2>
            <div class="related-grid">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related->slug) }}" class="related-card">
                    <img src="{{ $related->image }}" alt="{{ $related->title }}" class="related-card-image">
                    <div class="related-card-content">
                        <span class="related-card-category">{{ $related->category }}</span>
                        <h3 class="related-card-title">{{ $related->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </article>

    <!-- Footer -->
    @include('partials.footer')

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-links');
            
            if (navToggle) {
                navToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    navToggle.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
