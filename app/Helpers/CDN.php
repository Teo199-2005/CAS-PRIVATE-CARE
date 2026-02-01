<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * CDN Helper for Asset Management
 * 
 * Provides utilities for CDN URL generation, cache-busting, and 
 * fallback handling for static assets.
 */
class CDN
{
    /**
     * CDN providers with their configurations
     */
    protected static array $providers = [
        'cloudflare' => [
            'supports_webp' => true,
            'supports_avif' => true,
            'image_optimization' => true,
        ],
        'cloudfront' => [
            'supports_webp' => true,
            'supports_avif' => true,
            'image_optimization' => false,
        ],
        'bunny' => [
            'supports_webp' => true,
            'supports_avif' => true,
            'image_optimization' => true,
        ],
        'default' => [
            'supports_webp' => false,
            'supports_avif' => false,
            'image_optimization' => false,
        ]
    ];

    /**
     * Get the CDN URL for an asset
     *
     * @param string $path Relative path to the asset
     * @param bool $versioned Whether to add cache-busting version
     * @return string
     */
    public static function asset(string $path, bool $versioned = true): string
    {
        $cdnUrl = config('app.cdn_url');
        
        // If no CDN configured, fall back to local
        if (!$cdnUrl) {
            return asset($path);
        }

        $path = ltrim($path, '/');
        $url = rtrim($cdnUrl, '/') . '/' . $path;

        if ($versioned) {
            $url = self::addVersion($url, $path);
        }

        return $url;
    }

    /**
     * Get optimized image URL with format and size parameters
     *
     * @param string $path Image path
     * @param array $options Optimization options
     * @return string
     */
    public static function image(string $path, array $options = []): string
    {
        $cdnUrl = config('app.cdn_url');
        $provider = config('app.cdn_provider', 'default');
        $providerConfig = self::$providers[$provider] ?? self::$providers['default'];

        // Default options
        $defaults = [
            'width' => null,
            'height' => null,
            'quality' => 80,
            'format' => 'auto',
            'fit' => 'cover',
        ];

        $options = array_merge($defaults, $options);

        // If no CDN or provider doesn't support optimization, return standard URL
        if (!$cdnUrl || !$providerConfig['image_optimization']) {
            return self::asset($path);
        }

        $path = ltrim($path, '/');
        $baseUrl = rtrim($cdnUrl, '/');

        // Build transformation parameters based on provider
        switch ($provider) {
            case 'cloudflare':
                return self::buildCloudflareUrl($baseUrl, $path, $options);
            
            case 'bunny':
                return self::buildBunnyUrl($baseUrl, $path, $options);
            
            default:
                return $baseUrl . '/' . $path;
        }
    }

    /**
     * Build Cloudflare Image Resizing URL
     */
    protected static function buildCloudflareUrl(string $baseUrl, string $path, array $options): string
    {
        $params = [];

        if ($options['width']) {
            $params[] = 'width=' . $options['width'];
        }
        if ($options['height']) {
            $params[] = 'height=' . $options['height'];
        }
        if ($options['quality']) {
            $params[] = 'quality=' . $options['quality'];
        }
        if ($options['format'] === 'auto') {
            $params[] = 'format=auto';
        }
        if ($options['fit']) {
            $params[] = 'fit=' . $options['fit'];
        }

        $transformations = implode(',', $params);
        
        return "{$baseUrl}/cdn-cgi/image/{$transformations}/{$path}";
    }

    /**
     * Build Bunny CDN Image Processing URL
     */
    protected static function buildBunnyUrl(string $baseUrl, string $path, array $options): string
    {
        $params = [];

        if ($options['width']) {
            $params['width'] = $options['width'];
        }
        if ($options['height']) {
            $params['height'] = $options['height'];
        }
        if ($options['quality']) {
            $params['quality'] = $options['quality'];
        }
        if ($options['format'] === 'auto') {
            $params['auto_optimize'] = 'true';
        }

        $queryString = http_build_query($params);
        $url = $baseUrl . '/' . $path;
        
        return $queryString ? "{$url}?{$queryString}" : $url;
    }

    /**
     * Generate srcset for responsive images
     *
     * @param string $path Image path
     * @param array $widths Array of widths to generate
     * @param array $options Additional options
     * @return string
     */
    public static function srcset(string $path, array $widths = [320, 640, 960, 1280, 1920], array $options = []): string
    {
        $srcset = [];

        foreach ($widths as $width) {
            $url = self::image($path, array_merge($options, ['width' => $width]));
            $srcset[] = "{$url} {$width}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Add version hash for cache-busting
     */
    protected static function addVersion(string $url, string $path): string
    {
        $version = self::getAssetVersion($path);
        
        if (!$version) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'v=' . $version;
    }

    /**
     * Get asset version based on file modification time or manifest
     */
    protected static function getAssetVersion(string $path): ?string
    {
        // Check Vite manifest first
        $manifestPath = public_path('build/.vite/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = Cache::remember('vite_manifest', 3600, function () use ($manifestPath) {
                return json_decode(file_get_contents($manifestPath), true);
            });

            if (isset($manifest[$path])) {
                // Extract hash from Vite filename
                return substr(md5($manifest[$path]['file']), 0, 8);
            }
        }

        // Fall back to file modification time
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return substr(md5(filemtime($fullPath)), 0, 8);
        }

        return null;
    }

    /**
     * Get CDN URL for a script with SRI hash
     *
     * @param string $path Script path
     * @return array{url: string, integrity: string|null}
     */
    public static function script(string $path): array
    {
        return [
            'url' => self::asset($path),
            'integrity' => SRI::hash(public_path($path))
        ];
    }

    /**
     * Preload critical assets
     *
     * @param array $assets List of critical assets to preload
     * @return string HTML link tags for preloading
     */
    public static function preloadTags(array $assets): string
    {
        $tags = [];

        foreach ($assets as $asset) {
            $url = self::asset($asset['path']);
            $as = $asset['as'] ?? 'fetch';
            $type = $asset['type'] ?? '';
            $crossorigin = $asset['crossorigin'] ?? false;

            $tag = sprintf('<link rel="preload" href="%s" as="%s"', $url, $as);
            
            if ($type) {
                $tag .= sprintf(' type="%s"', $type);
            }
            if ($crossorigin) {
                $tag .= ' crossorigin';
            }
            
            $tag .= '>';
            $tags[] = $tag;
        }

        return implode("\n    ", $tags);
    }

    /**
     * Check if CDN is configured and available
     */
    public static function isEnabled(): bool
    {
        return !empty(config('app.cdn_url'));
    }

    /**
     * Get current CDN provider name
     */
    public static function getProvider(): string
    {
        return config('app.cdn_provider', 'default');
    }

    /**
     * Check if current CDN provider supports a feature
     */
    public static function supports(string $feature): bool
    {
        $provider = config('app.cdn_provider', 'default');
        $config = self::$providers[$provider] ?? self::$providers['default'];
        
        return $config[$feature] ?? false;
    }
}
