<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-Factor Authentication Middleware
 * 
 * Enforces 2FA for admin accounts to achieve 100/100 security score.
 * Uses time-based OTP with email delivery.
 */
class TwoFactorAuthentication
{
    /**
     * Admin user types that require 2FA
     */
    protected array $adminTypes = ['admin', 'adminstaff'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Only enforce 2FA for admin users
        if (!in_array($user->user_type, $this->adminTypes)) {
            return $next($request);
        }
        
        // Check if 2FA is already verified for this session
        $twoFactorVerified = session('2fa_verified_' . $user->id, false);
        $twoFactorExpiry = session('2fa_verified_at_' . $user->id);
        
        // 2FA expires after 8 hours for security
        $expiryTime = 8 * 60 * 60; // 8 hours in seconds
        
        if ($twoFactorVerified && $twoFactorExpiry) {
            if (time() - $twoFactorExpiry < $expiryTime) {
                return $next($request);
            }
            // 2FA expired, require re-verification
            session()->forget(['2fa_verified_' . $user->id, '2fa_verified_at_' . $user->id]);
        }
        
        // If requesting 2FA verification page, allow it
        if ($request->is('admin/2fa*') || $request->is('api/admin/2fa*')) {
            return $next($request);
        }
        
        // Redirect to 2FA verification
        return redirect()->route('admin.2fa.verify');
    }
    
    /**
     * Generate a 6-digit OTP code
     */
    public static function generateOTP(int $userId): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in cache for 10 minutes
        Cache::put("2fa_otp_{$userId}", $code, now()->addMinutes(10));
        
        return $code;
    }
    
    /**
     * Verify OTP code
     */
    public static function verifyOTP(int $userId, string $code): bool
    {
        $storedCode = Cache::get("2fa_otp_{$userId}");
        
        if (!$storedCode) {
            return false;
        }
        
        if (hash_equals($storedCode, $code)) {
            // Clear the OTP after successful verification
            Cache::forget("2fa_otp_{$userId}");
            return true;
        }
        
        return false;
    }
    
    /**
     * Mark 2FA as verified for the session
     */
    public static function markVerified(): void
    {
        session([
            '2fa_verified' => true,
            '2fa_verified_at' => time(),
        ]);
    }
    
    /**
     * Invalidate 2FA for the session
     */
    public static function invalidate(): void
    {
        session()->forget(['2fa_verified', '2fa_verified_at']);
    }
}
