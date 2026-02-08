<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect HTTP to HTTPS in production when APP_URL is https.
 * Only active when APP_ENV=production and APP_URL starts with https://
 * so local development is not affected.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = config('app.url');
        $isProduction = config('app.env') === 'production';
        $wantsHttps = $appUrl && str_starts_with($appUrl, 'https://');

        if ($isProduction && $wantsHttps && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
