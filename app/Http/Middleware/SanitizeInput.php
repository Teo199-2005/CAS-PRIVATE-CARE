<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Input Sanitization Middleware
 * 
 * Provides additional security by sanitizing user inputs
 * to prevent XSS and injection attacks.
 * 
 * @package App\Http\Middleware
 */
class SanitizeInput
{
    /**
     * Fields that should never be sanitized (passwords, API keys, etc.)
     * 
     * @var array<string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'api_key',
        'secret_key',
        'token',
        '_token',
        'stripe_token',
        'payment_method_id',
    ];

    /**
     * Fields that may contain HTML content (rich text)
     * 
     * @var array<string>
     */
    protected array $allowHtml = [
        'bio',
        'description',
        'content',
        'body',
        'message',
        'notes',
        'admin_notes',
        'special_requests',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        
        $sanitized = $this->sanitizeArray($input);
        
        $request->merge($sanitized);
        
        return $next($request);
    }

    /**
     * Recursively sanitize an array of inputs
     */
    protected function sanitizeArray(array $input, string $prefix = ''): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (in_array($key, $this->except, true)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value, $fullKey);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($key, $value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize a string value
     */
    protected function sanitizeString(string $key, string $value): string
    {
        // Allow HTML for specific fields but clean dangerous tags
        if (in_array($key, $this->allowHtml, true)) {
            return $this->cleanHtml($value);
        }
        
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
        
        // Remove any script patterns that might have been encoded
        $value = preg_replace('/javascript\s*:/i', '', $value);
        $value = preg_replace('/on\w+\s*=/i', '', $value);
        $value = preg_replace('/data\s*:/i', '', $value);
        
        // Normalize line breaks
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }

    /**
     * Clean HTML content, removing dangerous tags and attributes
     */
    protected function cleanHtml(string $value): string
    {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove script tags and their content
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        
        // Remove style tags and their content
        $value = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $value);
        
        // Remove event handlers
        $value = preg_replace('/\s*on\w+\s*=\s*(["\']).*?\1/i', '', $value);
        $value = preg_replace('/\s*on\w+\s*=\s*[^\s>]+/i', '', $value);
        
        // Remove javascript: hrefs
        $value = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href=""', $value);
        
        // Remove data: src
        $value = preg_replace('/src\s*=\s*(["\'])\s*data:.*?\1/i', 'src=""', $value);
        
        // Remove dangerous tags
        $dangerousTags = ['iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select'];
        foreach ($dangerousTags as $tag) {
            $value = preg_replace("/<{$tag}\b[^>]*>(.*?)<\/{$tag}>/is", '', $value);
            $value = preg_replace("/<{$tag}\b[^>]*\/?>/i", '', $value);
        }
        
        // Limit allowed tags
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote>';
        $value = strip_tags($value, $allowedTags);
        
        return trim($value);
    }
}
