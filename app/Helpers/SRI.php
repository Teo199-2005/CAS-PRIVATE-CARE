<?php

namespace App\Helpers;

/**
 * Subresource Integrity (SRI) Helper
 * 
 * Provides integrity hashes for external resources to prevent
 * supply chain attacks and ensure resource authenticity.
 * 
 * Usage in Blade:
 *   {!! SRI::script('https://cdn.example.com/lib.js') !!}
 *   {!! SRI::style('https://cdn.example.com/lib.css') !!}
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
 */
class SRI
{
    /**
     * Known integrity hashes for common CDN resources
     * Format: 'url' => 'sha384-hash'
     * 
     * Generate new hashes using:
     * curl -s URL | openssl dgst -sha384 -binary | openssl base64 -A
     */
    private static array $knownHashes = [
        // Bootstrap Icons 1.11.3
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' 
            => 'sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+',
        
        // Google Fonts are exempt from SRI as they serve different content based on user agent
        // Stripe.js - Stripe manages their own integrity
        'https://js.stripe.com/v3/' => null, // Stripe handles their own integrity
        
        // Chart.js 4.4.1
        'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'
            => 'sha384-C7hLboSJNLKcdWTaDpHSfI3R1eK7L9LMpgKSBvxVH1r3LCnFN0E+UQNvZL8N0JIo',
    ];

    /**
     * Cache for dynamically fetched hashes
     */
    private static array $hashCache = [];

    /**
     * Generate a script tag with SRI
     *
     * @param string $url The script URL
     * @param array $attributes Additional attributes (defer, async, etc.)
     * @return string The complete script tag
     */
    public static function script(string $url, array $attributes = []): string
    {
        $integrity = self::getIntegrity($url);
        
        $attrs = [
            'src' => $url,
            'crossorigin' => 'anonymous',
        ];

        if ($integrity) {
            $attrs['integrity'] = $integrity;
        }

        // Merge additional attributes
        $attrs = array_merge($attrs, $attributes);

        return self::buildTag('script', $attrs, '');
    }

    /**
     * Generate a link tag (stylesheet) with SRI
     *
     * @param string $url The stylesheet URL
     * @param array $attributes Additional attributes
     * @return string The complete link tag
     */
    public static function style(string $url, array $attributes = []): string
    {
        $integrity = self::getIntegrity($url);

        $attrs = [
            'rel' => 'stylesheet',
            'href' => $url,
            'crossorigin' => 'anonymous',
        ];

        if ($integrity) {
            $attrs['integrity'] = $integrity;
        }

        // Merge additional attributes
        $attrs = array_merge($attrs, $attributes);

        return self::buildTag('link', $attrs);
    }

    /**
     * Get the integrity hash for a URL
     *
     * @param string $url
     * @return string|null
     */
    public static function getIntegrity(string $url): ?string
    {
        // Check known hashes first
        if (isset(self::$knownHashes[$url])) {
            return self::$knownHashes[$url];
        }

        // Check cache
        if (isset(self::$hashCache[$url])) {
            return self::$hashCache[$url];
        }

        // For production, you could fetch and compute the hash
        // For now, return null (no integrity check)
        return null;
    }

    /**
     * Register a known hash for a URL
     *
     * @param string $url
     * @param string $hash
     */
    public static function registerHash(string $url, string $hash): void
    {
        self::$knownHashes[$url] = $hash;
    }

    /**
     * Build an HTML tag with attributes
     *
     * @param string $tag
     * @param array $attributes
     * @param string|null $content
     * @return string
     */
    private static function buildTag(string $tag, array $attributes, ?string $content = null): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            if ($value === null) continue;
            if ($value === true) {
                $attrString .= " {$key}";
            } else {
                $attrString .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "\"";
            }
        }

        if ($content !== null) {
            return "<{$tag}{$attrString}>{$content}</{$tag}>";
        }

        // Self-closing tags
        if (in_array($tag, ['link', 'meta', 'img', 'br', 'hr', 'input'])) {
            return "<{$tag}{$attrString}>";
        }

        return "<{$tag}{$attrString}></{$tag}>";
    }

    /**
     * Compute SHA-384 hash for content
     *
     * @param string $content
     * @return string
     */
    public static function computeHash(string $content): string
    {
        $hash = hash('sha384', $content, true);
        return 'sha384-' . base64_encode($hash);
    }

    /**
     * Fetch a URL and compute its hash (for development/setup)
     *
     * @param string $url
     * @return string|null
     */
    public static function fetchAndComputeHash(string $url): ?string
    {
        try {
            $content = file_get_contents($url);
            if ($content === false) {
                return null;
            }
            return self::computeHash($content);
        } catch (\Exception $e) {
            return null;
        }
    }
}
