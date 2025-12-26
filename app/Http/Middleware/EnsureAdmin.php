<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request - ensure user is admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        if (auth()->user()->user_type !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
            }
            return redirect('/login')->withErrors(['error' => 'Admin access required']);
        }

        return $next($request);
    }
}

