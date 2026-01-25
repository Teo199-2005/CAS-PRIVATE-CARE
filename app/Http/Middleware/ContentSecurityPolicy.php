<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and add CSP headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique nonce for this request
        $nonce = base64_encode(random_bytes(16));
        
        // Store nonce in request for blade templates
        $request->attributes->set('csp_nonce', $nonce);
        
        // Share nonce with views globally
        view()->share('cspNonce', $nonce);
        
        $response = $next($request);
        
        // Build CSP policy
        $policy = $this->buildPolicy($nonce);
        
        // Add CSP headers
        $response->headers->set('Content-Security-Policy', $policy);
        
        // Add additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Add Permissions-Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', implode(', ', [
            'geolocation=(self)',
            'camera=()',
            'microphone=()',
            'payment=(self)',
        ]));
        
        return $response;
    }
    
    /**
     * Build the Content Security Policy
     */
    protected function buildPolicy(string $nonce): string
    {
        $directives = [
            // Default fallback
            "default-src 'self'",
            
            // Scripts: allow self, nonce, and specific CDNs (unsafe-eval REMOVED - Vue uses pre-compiled templates)
            "script-src 'self' 'nonce-{$nonce}' https://js.stripe.com https://www.google.com https://www.gstatic.com https://connect.facebook.net https://cdn.jsdelivr.net",
            
            // Styles: allow self, nonce, and style CDNs
            "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            
            // Images: allow self, data URIs, and external sources
            "img-src 'self' data: https: blob:",
            
            // Fonts: allow self and Google Fonts
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            
            // Connections: API calls and external services
            "connect-src 'self' https://api.stripe.com https://www.google-analytics.com https://www.facebook.com https://api.brevo.com wss:",
            
            // Frames: allow Stripe, Google, Facebook
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.google.com https://www.facebook.com",
            
            // Form submissions
            "form-action 'self'",
            
            // Base URI restriction
            "base-uri 'self'",
            
            // Object/embed sources
            "object-src 'none'",
            
            // Media sources
            "media-src 'self'",
            
            // Worker sources (for service workers)
            "worker-src 'self' blob:",
            
            // Manifest
            "manifest-src 'self'",
            
            // Upgrade insecure requests in production
            config('app.env') === 'production' ? 'upgrade-insecure-requests' : '',
        ];
        
        // Filter out empty directives
        $directives = array_filter($directives);
        
        return implode('; ', $directives);
    }
    
    /**
     * Get the CSP nonce for the current request
     */
    public static function getNonce(Request $request): ?string
    {
        return $request->attributes->get('csp_nonce');
    }
}
