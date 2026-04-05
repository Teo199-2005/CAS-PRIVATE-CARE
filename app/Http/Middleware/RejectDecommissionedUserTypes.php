<?php

namespace App\Http\Middleware;

use App\DecommissionedUserTypes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out sessions for decommissioned user types (housekeeper, training center).
 */
class RejectDecommissionedUserTypes
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && DecommissionedUserTypes::isDecommissioned($user->user_type)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This account type is no longer supported. Please contact support if you need assistance.',
                ], 403);
            }

            return redirect('/login')->withErrors([
                'email' => 'This account type is no longer supported. Please contact support if you need assistance.',
            ]);
        }

        return $next($request);
    }
}
