<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Skip verification check for admin users
        if ($user && $user->user_type === 'admin') {
            return $next($request);
        }
        
        // Check if email is not verified
        if ($user && !$user->email_verified_at) {
            // For API requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'verified' => false,
                    'message' => 'Please verify your email address to continue.'
                ], 403);
            }
            
            // For web requests, the Vue component will handle the modal
            // We just pass the verification status
            $request->attributes->set('email_not_verified', true);
        }
        
        return $next($request);
    }
}
