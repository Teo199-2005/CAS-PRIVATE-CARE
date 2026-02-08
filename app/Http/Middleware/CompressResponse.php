<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gzip compress response when client accepts it and server didn't already compress.
 * Use as fallback when mod_deflate is not available (e.g. some shared hosts).
 * Only compresses text-like responses above a minimum size.
 */
class CompressResponse
{
    protected int $minSize = 1024;

    protected array $compressibleTypes = [
        'text/plain',
        'text/html',
        'text/css',
        'text/javascript',
        'application/javascript',
        'application/json',
        'application/xml',
        'application/xhtml+xml',
        'image/svg+xml',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || strlen($content) < $this->minSize) {
            return $response;
        }

        $compressed = gzencode($content, 6, FORCE_GZIP);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', trim($response->headers->get('Vary', '') . ', Accept-Encoding', ', '));
        $response->headers->remove('Content-Length');

        return $response;
    }

    protected function shouldCompress(Request $request, Response $response): bool
    {
        if (!config('performance.compression.enabled', true)) {
            return false;
        }
        if (!str_contains(strtolower($request->header('Accept-Encoding', '')), 'gzip')) {
            return false;
        }
        if ($response->headers->get('Content-Encoding')) {
            return false;
        }
        $type = $response->headers->get('Content-Type', '');
        foreach ($this->compressibleTypes as $allowed) {
            if (str_starts_with($type, $allowed)) {
                return true;
            }
        }
        return false;
    }
}
