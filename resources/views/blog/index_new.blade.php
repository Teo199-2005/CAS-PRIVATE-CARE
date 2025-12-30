<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Blog - CAS Private Care LLC</title>
    <meta name="description" content="Helpful tips, resources, and insights about caregiving, home care services, and connecting with quality caregivers">
    
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
            background: #f8fafc;
        }

        .blog-hero {
            margin-top: 88px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            padding: 60px 20px 40px;
            text-align: center;
            color: white;
        }

        .blog-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .blog-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.95;
        }

        .blog-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 20px;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
        }

        .blog-main {
            width: 100%;
        }

        .blog-sidebar {
            position: sticky;
            top: 108px;
            height: fit-content;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .search-box {
            position: relative;
            max-width: 100%;
            width: 100%;
            margin-bottom: 40px;
        }

        .search-box input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .search-box button {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #3b82f6;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-box button:hover {
            background: #2563eb;
        }

        /* Featured Post */
        .featured-post {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 0;
            margin-bottom: 40px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .featured-post:hover {
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .featured-post-image {
            width: 100%;
            height: 100%;
            min-height: 400px;
            object-fit: cover;
        }

        .featured-post-content {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .featured-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 15px;
            width: fit-content;
        }

        .featured-post-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
            line-height: 1.2;
        }

        .featured-post-excerpt {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.05rem;
        }

        .featured-post-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .featured-post-meta i {
            margin-right: 5px;
        }

        .featured-read-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .featured-read-more:hover {
            background: #2563eb;
            transform: translateX(5px);
        }

        /* Blog List */
        .blog-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .blog-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
            height: 180px;
            border: 1px solid #e5e7eb;
        }

        .blog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .blog-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .blog-card-content {
            padding: 20px 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .blog-card-category {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 8px;
            align-self: flex-start;
        }

        .blog-card-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1e293b;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-card-excerpt {
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 12px;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .blog-card-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .blog-card-meta i {
            margin-right: 4px;
        }

        .read-more-btn {
            padding: 8px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .read-more-btn:hover {
            background: #2563eb;
            transform: translateX(5px);
        }

        /* Sidebar Styles */
        .sidebar-widget {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }

        .widget-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1e293b;
            padding-bottom: 10px;
            border-bottom: 2px solid #3b82f6;
        }

        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-list li {
            margin-bottom: 10px;
        }

        .category-list a {
            text-decoration: none;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .category-list a:hover,
        .category-list a.active {
            background: #eff6ff;
            color: #3b82f6;
        }

        .category-count {
            background: #e5e7eb;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .category-list a:hover .category-count,
        .category-list a.active .category-count {
            background: #3b82f6;
            color: white;
        }

        .popular-posts {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .popular-post-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .popular-post-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .popular-post-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .popular-post-content {
            flex: 1;
        }

        .popular-post-category {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .popular-post-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .popular-post-title a {
            text-decoration: none;
            color: inherit;
        }

        .popular-post-title a:hover {
            color: #3b82f6;
        }

        .popular-post-date {
            font-size: 0.8rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #e0e0e0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }

        @media (max-width: 1024px) {
            .blog-container {
                grid-template-columns: 1fr;
            }

            .blog-sidebar {
                position: static;
                margin-top: 40px;
            }

            .featured-post {
                grid-template-columns: 1fr;
            }

            .featured-post-image {
                min-height: 300px;
            }

            .blog-card {
                grid-template-columns: 1fr;
                height: auto;
            }

            .blog-card-image {
                height: 200px;
            }
        }

        @media (max-width: 768px) {
            .blog-hero h1 {
                font-size: 2rem;
            }

            .blog-hero p {
                font-size: 1rem;
            }

            .featured-post-content {
                padding: 25px;
            }

            .featured-post-title {
                font-size: 1.5rem;
            }

            .blog-card-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .read-more-btn {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    @include('partials.navigation')

    <!-- Hero Section -->
    <section class="blog-hero">
        <h1>Our Blog</h1>
        <p>Helpful tips, resources, and insights about caregiving, home care services, and connecting with quality caregivers</p>
    </section>

    <!-- Blog Content -->
    <div class="blog-container">
        <!-- Main Content -->
        <div class="blog-main">
            <!-- Search -->
            <form action="{{ route('blog.index') }}" method="GET" class="search-box">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <input type="text" name="search" placeholder="Search articles..." value="{{ request('search') }}">
                <button type="submit"><i class="bi bi-search"></i></button>
            </form>

            @if($posts->count() > 0)
                <!-- Featured Post (First Post) -->
                @php $featured = $posts->first(); @endphp
                <a href="{{ route('blog.show', $featured['slug']) }}" class="featured-post">
                    <img src="{{ $featured['image'] }}" alt="{{ $featured['title'] }}" class="featured-post-image">
                    <div class="featured-post-content">
                        <span class="featured-badge">FEATURED</span>
                        <h2 class="featured-post-title">{{ $featured['title'] }}</h2>
                        <p class="featured-post-excerpt">{{ $featured['excerpt'] }}</p>
                        <div class="featured-post-meta">
                            <span><i class="bi bi-person-circle"></i>{{ $featured['author'] }}</span>
                            <span><i class="bi bi-clock"></i>{{ $featured['reading_time'] }}</span>
                        </div>
                        <span class="featured-read-more">
                            Read More <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Recent Posts Title -->
                <h2 class="section-title">Recent Articles</h2>

                <!-- Blog List (Remaining Posts) -->
                <div class="blog-list">
                    @foreach($posts->skip(1) as $post)
                        <div class="blog-card">
                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="blog-card-image">
                            <div class="blog-card-content">
                                <span class="blog-card-category">{{ $post['category'] }}</span>
                                <h3 class="blog-card-title">{{ $post['title'] }}</h3>
                                <p class="blog-card-excerpt">{{ $post['excerpt'] }}</p>
                                <div class="blog-card-footer">
                                    <div class="blog-card-meta">
                                        <span><i class="bi bi-person-circle"></i>{{ $post['author'] }}</span>
                                        <span><i class="bi bi-clock"></i>{{ $post['reading_time'] }}</span>
                                    </div>
                                    <a href="{{ route('blog.show', $post['slug']) }}" class="read-more-btn">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h3>No articles found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="category-list">
                    <li>
                        <a href="{{ route('blog.index') }}" class="{{ !request('category') ? 'active' : '' }}">
                            <span>All Posts</span>
                            <span class="category-count">{{ $posts->count() }}</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        @php
                            $count = collect($posts)->where('category', $cat)->count();
                        @endphp
                        <li>
                            <a href="{{ route('blog.index', ['category' => $cat]) }}" 
                               class="{{ request('category') == $cat ? 'active' : '' }}">
                                <span>{{ $cat }}</span>
                                <span class="category-count">{{ $count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Popular Posts Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Popular Posts</h3>
                <ul class="popular-posts">
                    @foreach($posts->take(4) as $popularPost)
                        <li class="popular-post-item">
                            <img src="{{ $popularPost['image'] }}" alt="{{ $popularPost['title'] }}" class="popular-post-image">
                            <div class="popular-post-content">
                                <span class="popular-post-category">{{ $popularPost['category'] }}</span>
                                <div class="popular-post-title">
                                    <a href="{{ route('blog.show', $popularPost['slug']) }}">{{ $popularPost['title'] }}</a>
                                </div>
                                <div class="popular-post-date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ date('M j, Y', strtotime($popularPost['published_at'])) }}
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>

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
