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
        
        // Normalize user types (handle training vs training_center)
        $userType = $user->user_type;
        if ($userType === 'training_center') {
            $userType = 'training';
        }
        
        if (!in_array($userType, $types) && !in_array($user->user_type, $types)) {
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
        return match($userType) {
            'admin' => '/admin/dashboard-vue',
            'caregiver' => '/caregiver/dashboard-vue',
            'marketing' => '/marketing/dashboard-vue',
            'training', 'training_center' => '/training/dashboard-vue',
            default => '/client/dashboard-vue',
        };
    }
}

