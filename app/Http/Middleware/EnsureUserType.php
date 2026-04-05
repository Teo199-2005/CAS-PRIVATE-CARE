<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$types  Allowed user types
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        $user = auth()->user();
        
        // Normalize user types (case-insensitive match)
        $userType = $user->user_type;
        $userTypeLower = is_string($userType) ? strtolower($userType) : (string) $userType;
        $typesLower = array_map('strtolower', $types);
        
        if (!in_array($userTypeLower, $typesLower) && !in_array(is_string($user->user_type) ? strtolower($user->user_type) : (string) $user->user_type, $typesLower)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Required role: ' . implode(' or ', $types)], 403);
            }
            
            // Redirect to appropriate dashboard
            return redirect($this->getDashboardRoute($user->user_type));
        }

        return $next($request);
    }
    
    /**
     * Get the dashboard route for a user type
     */
    private function getDashboardRoute(string $userType): string
    {
        return match ($userType) {
            'admin' => '/admin/dashboard-vue',
            'caregiver' => '/caregiver/dashboard-vue',
            'marketing' => '/marketing/dashboard-vue',
            'housekeeper', 'training', 'training_center' => '/login',
            default => '/client/dashboard-vue',
        };
    }
}

