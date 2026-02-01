<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optimized Session Management Middleware
 * 
 * Improves session handling during pilot testing:
 * - Prevents session conflicts when switching accounts
 * - Clears stale session data
 * - Optimizes session regeneration
 * - Reduces session-related rate limit issues
 */
class OptimizedSessionManagement
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before authentication: Clean up any conflicting session data
        if ($request->isMethod('POST') && $request->is('login')) {
            $this->prepareForLogin($request);
        }
        
        $response = $next($request);
        
        // After authentication: Optimize session
        if ($request->isMethod('POST') && $request->is('login') && $response->isSuccessful()) {
            $this->optimizeSession($request);
        }
        
        // On logout: Complete cleanup
        if ($request->isMethod('POST') && $request->is('logout')) {
            $this->cleanupSession($request);
        }
        
        return $response;
    }
    
    /**
     * Prepare session for login (prevents conflicts)
     */
    protected function prepareForLogin(Request $request): void
    {
        // Don't start a new session if one doesn't exist yet
        if (!$request->hasSession()) {
            return;
        }
        
        $session = $request->session();
        
        // Clear any previous authentication data
        $session->forget([
            'auth',
            'password_hash_web',
            'login.id',
            'login.remember',
        ]);
        
        // Keep CSRF token and flash data
        $session->keep(['_token', '_flash']);
    }
    
    /**
     * Optimize session after successful login
     */
    protected function optimizeSession(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }
        
        $session = $request->session();
        
        // Regenerate session ID for security
        $session->regenerate();
        
        // Mark session as recently authenticated
        $session->put('last_auth_check', now()->timestamp);
        
        // Store user type for faster access
        if ($user = $request->user()) {
            $session->put('user_type', $user->user_type);
            $session->put('user_role', $user->role ?? $user->user_type);
        }
    }
    
    /**
     * Complete session cleanup on logout
     */
    protected function cleanupSession(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }
        
        $session = $request->session();
        
        // Store CSRF token before flush
        $csrfToken = $session->token();
        
        // Flush all session data
        $session->flush();
        
        // Regenerate session with new ID
        $session->regenerate(true);
        
        // Restore CSRF token
        $session->put('_token', $csrfToken);
        
        // Clear all authentication guards
        auth()->guard('web')->logout();
        
        // Invalidate remember token
        if ($user = $request->user()) {
            $user->setRememberToken(null);
            $user->save();
        }
    }
}

