{{--
    Responsive Image Blade Component v2.0
    
    Features:
    - Automatic srcset generation for responsive images
    - WebP/AVIF format support with fallbacks
    - Lazy loading with native browser support
    - Priority loading for above-the-fold images
    - LQIP (Low Quality Image Placeholder) support
    - Accessibility compliant (required alt text)
    
    Usage:
    <x-responsive-image 
        src="images/hero.jpg" 
        alt="Hero image description" 
        :widths="[400, 800, 1200]"
        sizes="(max-width: 768px) 100vw, 50vw"
        class="custom-class"
        priority
    />
    
    @props:
    - src: Image source (relative to public folder or absolute URL)
    - alt: Alt text (REQUIRED for accessibility - empty string for decorative images)
    - widths: Array of widths for srcset (default: [320, 640, 960, 1280, 1920])
    - sizes: Sizes attribute for responsive images
    - class: Additional CSS classes
    - lazy: Whether to use lazy loading (default: true, false if priority)
    - width: Explicit width attribute (recommended for CLS)
    - height: Explicit height attribute (recommended for CLS)
    - priority: High priority image (above fold) - disables lazy loading
    - aspectRatio: CSS aspect-ratio for placeholder (e.g., "16/9")
    - placeholder: Placeholder color or blur URL
    
    @version 2.0
    @date January 28, 2026
--}}

@props([
    'src',
    'alt',
    'widths' => [320, 640, 960, 1280, 1920],
    'sizes' => '100vw',
    'class' => '',
    'lazy' => true,
    'width' => null,
    'height' => null,
    'priority' => false,
    'decoding' => 'async',
    'aspectRatio' => null,
    'placeholder' => '#f1f5f9'
])

@php
    // Determine if this is an external URL or local asset
    $isExternal = str_starts_with($src, 'http://') || str_starts_with($src, 'https://');
    
    // For local assets, build the full URL
    $baseSrc = $isExternal ? $src : asset($src);
    
    // Get file extension for format detection
    $extension = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    $isRaster = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif']);
    
    // Build srcset for images
    $srcset = '';
    $webpSrcset = '';
    $avifSrcset = '';
    
    if ($isRaster && !$isExternal) {
        // For local images, check if WebP/AVIF versions exist
        $srcsetParts = [];
        $webpParts = [];
        $avifParts = [];
        
        $basePath = public_path($src);
        $baseDir = dirname($basePath);
        $baseName = pathinfo($src, PATHINFO_FILENAME);
        
        foreach ($widths as $w) {
            // Standard format
            $srcsetParts[] = "{$baseSrc} {$w}w";
            
            // Check for WebP version
            $webpPath = $baseDir . '/' . $baseName . '-' . $w . '.webp';
            if (file_exists($webpPath)) {
                $webpUrl = asset(dirname($src) . '/' . $baseName . '-' . $w . '.webp');
                $webpParts[] = "{$webpUrl} {$w}w";
            }
            
            // Check for AVIF version
            $avifPath = $baseDir . '/' . $baseName . '-' . $w . '.avif';
            if (file_exists($avifPath)) {
                $avifUrl = asset(dirname($src) . '/' . $baseName . '-' . $w . '.avif');
                $avifParts[] = "{$avifUrl} {$w}w";
            }
        }
        
        $srcset = implode(', ', $srcsetParts);
        if (!empty($webpParts)) {
            $webpSrcset = implode(', ', $webpParts);
        }
        if (!empty($avifParts)) {
            $avifSrcset = implode(', ', $avifParts);
        }
    } elseif ($isExternal && str_contains($src, 'unsplash.com')) {
        // Unsplash supports ?w= parameter
        $srcsetParts = [];
        foreach ($widths as $w) {
            $url = preg_replace('/[?&]w=\d+/', '', $src);
            $separator = str_contains($url, '?') ? '&' : '?';
            $srcsetParts[] = "{$url}{$separator}w={$w}&auto=format {$w}w";
        }
        $srcset = implode(', ', $srcsetParts);
    }
    
    // Loading and priority attributes
    $loading = $priority ? 'eager' : ($lazy ? 'lazy' : 'eager');
    $fetchpriority = $priority ? 'high' : 'auto';
    
    // Build inline style for aspect ratio placeholder
    $placeholderStyle = '';
    if ($aspectRatio) {
        $placeholderStyle = "aspect-ratio: {$aspectRatio}; background-color: {$placeholder};";
    }
    
    // Combine classes
    $imgClass = trim("responsive-img {$class}");
@endphp

{{-- Use picture element for format negotiation when we have WebP/AVIF --}}
@if($avifSrcset || $webpSrcset)
    <picture>
        @if($avifSrcset)
            <source 
                type="image/avif" 
                srcset="{{ $avifSrcset }}"
                sizes="{{ $sizes }}"
            >
        @endif
        @if($webpSrcset)
            <source 
                type="image/webp" 
                srcset="{{ $webpSrcset }}"
                sizes="{{ $sizes }}"
            >
        @endif
        <img 
            src="{{ $baseSrc }}"
            @if($srcset) srcset="{{ $srcset }}" @endif
            sizes="{{ $sizes }}"
            alt="{{ $alt }}"
            @if($width) width="{{ $width }}" @endif
            @if($height) height="{{ $height }}" @endif
            loading="{{ $loading }}"
            decoding="{{ $decoding }}"
            @if($priority) fetchpriority="{{ $fetchpriority }}" @endif
            @if($placeholderStyle) style="{{ $placeholderStyle }}" @endif
            class="{{ $imgClass }}"
            {{ $attributes }}
        >
    </picture>
@elseif($srcset)
    <img 
        src="{{ $baseSrc }}"
        srcset="{{ $srcset }}"
        sizes="{{ $sizes }}"
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if($priority) fetchpriority="{{ $fetchpriority }}" @endif
        @if($placeholderStyle) style="{{ $placeholderStyle }}" @endif
        class="{{ $imgClass }}"
        {{ $attributes }}
    >
@else
    <img 
        src="{{ $baseSrc }}"
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if($priority) fetchpriority="{{ $fetchpriority }}" @endif
        @if($placeholderStyle) style="{{ $placeholderStyle }}" @endif
        class="{{ $imgClass }}"
        {{ $attributes }}
    >
@endif
