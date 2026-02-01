<?php

namespace App\Helpers;

/**
 * Image Helper
 * 
 * Provides utilities for responsive image handling, srcset generation,
 * and image optimization for performance.
 * 
 * @package App\Helpers
 */
class ImageHelper
{
    /**
     * Common responsive image widths for srcset
     */
    public const SRCSET_WIDTHS = [320, 480, 640, 768, 1024, 1280, 1536, 1920];

    /**
     * Generate srcset attribute for responsive images
     *
     * @param string $basePath Base image path (without extension)
     * @param string $extension Image extension (jpg, png, webp)
     * @param array $widths Array of widths to include
     * @return string srcset attribute value
     */
    public static function generateSrcset(
        string $basePath,
        string $extension = 'jpg',
        array $widths = []
    ): string {
        $widths = $widths ?: self::SRCSET_WIDTHS;
        $srcset = [];

        foreach ($widths as $width) {
            $srcset[] = "{$basePath}-{$width}w.{$extension} {$width}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate sizes attribute for responsive images
     *
     * @param array $breakpoints Array of breakpoint => size mappings
     * @param string $defaultSize Default size for largest screens
     * @return string sizes attribute value
     */
    public static function generateSizes(
        array $breakpoints = [],
        string $defaultSize = '100vw'
    ): string {
        $default = [
            '(max-width: 640px)' => '100vw',
            '(max-width: 1024px)' => '50vw',
            '(max-width: 1536px)' => '33vw',
        ];

        $breakpoints = $breakpoints ?: $default;
        $sizes = [];

        foreach ($breakpoints as $query => $size) {
            $sizes[] = "{$query} {$size}";
        }

        $sizes[] = $defaultSize;

        return implode(', ', $sizes);
    }

    /**
     * Generate a complete responsive image tag
     *
     * @param string $src Primary image source
     * @param string $alt Alt text for accessibility
     * @param array $options Additional options
     * @return string HTML img tag
     */
    public static function responsiveImage(
        string $src,
        string $alt,
        array $options = []
    ): string {
        $defaults = [
            'loading' => 'lazy',
            'decoding' => 'async',
            'width' => null,
            'height' => null,
            'class' => '',
            'srcset' => null,
            'sizes' => null,
            'fetchpriority' => null,
        ];

        $options = array_merge($defaults, $options);

        $attrs = [
            'src' => e($src),
            'alt' => e($alt),
            'loading' => $options['loading'],
            'decoding' => $options['decoding'],
        ];

        if ($options['width']) {
            $attrs['width'] = $options['width'];
        }

        if ($options['height']) {
            $attrs['height'] = $options['height'];
        }

        if ($options['class']) {
            $attrs['class'] = e($options['class']);
        }

        if ($options['srcset']) {
            $attrs['srcset'] = e($options['srcset']);
        }

        if ($options['sizes']) {
            $attrs['sizes'] = e($options['sizes']);
        }

        if ($options['fetchpriority']) {
            $attrs['fetchpriority'] = $options['fetchpriority'];
        }

        $attrString = '';
        foreach ($attrs as $key => $value) {
            if ($value !== null) {
                $attrString .= " {$key}=\"{$value}\"";
            }
        }

        return "<img{$attrString}>";
    }

    /**
     * Generate picture element with WebP fallback
     *
     * @param string $src Primary image source
     * @param string $alt Alt text
     * @param array $options Options
     * @return string HTML picture element
     */
    public static function pictureElement(
        string $src,
        string $alt,
        array $options = []
    ): string {
        $pathInfo = pathinfo($src);
        $webpSrc = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

        $defaults = [
            'loading' => 'lazy',
            'decoding' => 'async',
            'width' => null,
            'height' => null,
            'class' => '',
            'webp' => true,
            'avif' => false,
        ];

        $options = array_merge($defaults, $options);

        $html = '<picture>';

        // AVIF source (if enabled)
        if ($options['avif']) {
            $avifSrc = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.avif';
            $html .= '<source srcset="' . e($avifSrc) . '" type="image/avif">';
        }

        // WebP source (if enabled)
        if ($options['webp']) {
            $html .= '<source srcset="' . e($webpSrc) . '" type="image/webp">';
        }

        // Fallback img
        $html .= self::responsiveImage($src, $alt, $options);

        $html .= '</picture>';

        return $html;
    }

    /**
     * Get placeholder image data URL for lazy loading
     *
     * @param int $width Placeholder width
     * @param int $height Placeholder height
     * @param string $color Background color (hex without #)
     * @return string Data URL for placeholder
     */
    public static function placeholderDataUrl(
        int $width = 1,
        int $height = 1,
        string $color = 'f1f5f9'
    ): string {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">'
             . '<rect width="100%" height="100%" fill="#' . $color . '"/>'
             . '</svg>';

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    /**
     * Add lazy loading attributes to existing img tag
     *
     * @param string $html HTML containing img tags
     * @param bool $excludeAboveFold Exclude images with fetchpriority="high"
     * @return string Modified HTML
     */
    public static function addLazyLoading(string $html, bool $excludeAboveFold = true): string
    {
        // Match img tags
        $pattern = '/<img([^>]*)>/i';

        return preg_replace_callback($pattern, function ($matches) use ($excludeAboveFold) {
            $attrs = $matches[1];

            // Skip if already has loading attribute
            if (stripos($attrs, 'loading=') !== false) {
                return $matches[0];
            }

            // Skip if above-fold (fetchpriority="high")
            if ($excludeAboveFold && stripos($attrs, 'fetchpriority="high"') !== false) {
                return $matches[0];
            }

            // Add lazy loading
            return '<img loading="lazy" decoding="async"' . $attrs . '>';
        }, $html);
    }
}
