<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * CSP nonce for inline scripts (generated per request)
     */
    protected static ?string $nonce = null;

    /**
     * Get the CSP nonce for the current request
     */
    public static function getNonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = Str::random(32);
        }
        return self::$nonce;
    }

    /**
     * Handle an incoming request and add security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate nonce for this request
        $nonce = self::getNonce();
        
        // Share nonce with views
        view()->share('cspNonce', $nonce);
        
        $response = $next($request);

        // Core security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=(self), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');
        
        // Cross-Origin policies for additional security
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        
        // Cache-Control for sensitive pages
        if ($this->isSensitivePage($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }
        
        // Only add HSTS if using HTTPS in production
        if ($request->secure() && config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        // Content Security Policy - enabled with comprehensive rules
        if (config('app.env') === 'production') {
            $csp = $this->buildContentSecurityPolicy($nonce);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
    
    /**
     * Check if the current page is sensitive (login, payment, etc.)
     */
    protected function isSensitivePage(Request $request): bool
    {
        $sensitivePaths = ['/login', '/register', '/payment', '/reset-password', '/admin'];
        foreach ($sensitivePaths as $path) {
            if (str_starts_with($request->getPathInfo(), $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build Content Security Policy header value with nonce support
     */
    protected function buildContentSecurityPolicy(string $nonce): string
    {
        $directives = [
            // Default fallback - restrict to self
            "default-src 'self'",
            
            // Scripts - use nonce for inline scripts, allow trusted CDNs
            // Note: 'unsafe-eval' REMOVED - Vue.js now uses pre-compiled templates via Vite
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' https://js.stripe.com https://www.google.com https://www.gstatic.com https://connect.facebook.net https://cdn.jsdelivr.net https://www.googletagmanager.com",
            
            // Styles - allow self, nonce for inline styles, and font CDNs
            "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            
            // Images - allow self, data URIs, https sources, and blob
            "img-src 'self' data: https: blob:",
            
            // Fonts - allow self, data URIs, Google Fonts, and CDN
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            
            // Connections - API calls and external services
            "connect-src 'self' https://api.stripe.com https://www.google-analytics.com https://www.facebook.com https://api.brevo.com wss: https:",
            
            // Frames - allow Stripe, Google, Facebook for OAuth/payments
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.google.com https://www.facebook.com https://www.youtube.com",
            
            // Frame ancestors - prevent clickjacking
            "frame-ancestors 'self'",
            
            // Form submissions - only to self
            "form-action 'self'",
            
            // Base URI restriction
            "base-uri 'self'",
            
            // Block object/embed (Flash, Java applets)
            "object-src 'none'",
            
            // Media sources
            "media-src 'self' https:",
            
            // Worker sources (for service workers)
            "worker-src 'self' blob:",
            
            // Manifest
            "manifest-src 'self'",
            
            // Upgrade insecure requests in production
            "upgrade-insecure-requests",
            
            // Report violations (optional - uncomment and set your endpoint)
            // "report-uri /api/csp-report",
        ];
        
        return implode('; ', $directives);
    }
    
    /**
     * Reset nonce for next request
     */
    public static function resetNonce(): void
    {
        self::$nonce = null;
    }
}