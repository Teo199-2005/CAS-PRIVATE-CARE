<?php

namespace App\Helpers;

/**
 * String Helper
 * 
 * Provides common string manipulation utilities.
 * 
 * @package App\Helpers
 */
class StringHelper
{
    /**
     * Generate a URL-friendly slug from a string
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function slug(string $string, string $separator = '-'): string
    {
        // Convert to lowercase
        $string = mb_strtolower($string, 'UTF-8');

        // Replace special characters
        $string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);

        // Replace whitespace with separator
        $string = preg_replace('/\s+/', $separator, trim($string));

        // Remove consecutive separators
        $string = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $string);

        return trim($string, $separator);
    }

    /**
     * Truncate a string to a maximum length with ellipsis
     *
     * @param string $string
     * @param int $maxLength
     * @param string $suffix
     * @return string
     */
    public static function truncate(string $string, int $maxLength, string $suffix = '...'): string
    {
        if (mb_strlen($string, 'UTF-8') <= $maxLength) {
            return $string;
        }

        return mb_substr($string, 0, $maxLength - mb_strlen($suffix, 'UTF-8'), 'UTF-8') . $suffix;
    }

    /**
     * Truncate to word boundary
     *
     * @param string $string
     * @param int $maxLength
     * @param string $suffix
     * @return string
     */
    public static function truncateWords(string $string, int $maxLength, string $suffix = '...'): string
    {
        if (mb_strlen($string, 'UTF-8') <= $maxLength) {
            return $string;
        }

        $truncated = mb_substr($string, 0, $maxLength, 'UTF-8');
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        }

        return trim($truncated) . $suffix;
    }

    /**
     * Convert string to title case
     *
     * @param string $string
     * @return string
     */
    public static function titleCase(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Sanitize a filename
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = basename($filename);

        // Replace dangerous characters
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);

        // Remove consecutive underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Limit length
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        if (strlen($name) > 200) {
            $name = substr($name, 0, 200);
        }

        return $ext ? "{$name}.{$ext}" : $name;
    }

    /**
     * Extract initials from a name
     *
     * @param string $name
     * @param int $maxInitials
     * @return string
     */
    public static function initials(string $name, int $maxInitials = 2): string
    {
        $words = preg_split('/\s+/', trim($name));
        $initials = '';

        foreach ($words as $index => $word) {
            if ($index >= $maxInitials) break;
            if (mb_strlen($word, 'UTF-8') > 0) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
            }
        }

        return $initials;
    }

    /**
     * Mask sensitive data (email, phone, etc.)
     *
     * @param string $string
     * @param int $visibleStart
     * @param int $visibleEnd
     * @param string $mask
     * @return string
     */
    public static function mask(
        string $string,
        int $visibleStart = 2,
        int $visibleEnd = 2,
        string $mask = '*'
    ): string {
        $length = mb_strlen($string, 'UTF-8');

        if ($length <= ($visibleStart + $visibleEnd)) {
            return str_repeat($mask, $length);
        }

        $start = mb_substr($string, 0, $visibleStart, 'UTF-8');
        $end = mb_substr($string, -$visibleEnd, $visibleEnd, 'UTF-8');
        $maskLength = $length - $visibleStart - $visibleEnd;

        return $start . str_repeat($mask, $maskLength) . $end;
    }

    /**
     * Mask an email address
     *
     * @param string $email
     * @return string
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return self::mask($email);
        }

        $local = self::mask($parts[0], 2, 1);
        $domain = $parts[1];

        return "{$local}@{$domain}";
    }

    /**
     * Mask a phone number (show last 4 digits)
     *
     * @param string $phone
     * @return string
     */
    public static function maskPhone(string $phone): string
    {
        // Remove non-digits
        $digits = preg_replace('/\D/', '', $phone);
        
        if (strlen($digits) < 4) {
            return str_repeat('*', strlen($digits));
        }

        $visible = substr($digits, -4);
        $masked = str_repeat('*', strlen($digits) - 4);

        return $masked . $visible;
    }

    /**
     * Format phone number to (XXX) XXX-XXXX
     *
     * @param string $phone
     * @return string
     */
    public static function formatPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Handle country code
        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 4)
            );
        }

        return $phone; // Return original if can't format
    }

    /**
     * Generate excerpt from HTML content
     *
     * @param string $html
     * @param int $maxLength
     * @return string
     */
    public static function excerpt(string $html, int $maxLength = 160): string
    {
        // Strip HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        return self::truncateWords($text, $maxLength);
    }

    /**
     * Check if string contains any of the given needles
     *
     * @param string $haystack
     * @param array $needles
     * @param bool $caseSensitive
     * @return bool
     */
    public static function containsAny(string $haystack, array $needles, bool $caseSensitive = false): bool
    {
        if (!$caseSensitive) {
            $haystack = mb_strtolower($haystack, 'UTF-8');
        }

        foreach ($needles as $needle) {
            $searchNeedle = $caseSensitive ? $needle : mb_strtolower($needle, 'UTF-8');
            if (mb_strpos($haystack, $searchNeedle, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }
}
