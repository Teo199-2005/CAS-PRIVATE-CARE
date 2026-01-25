<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TwoFactorAuthentication;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

/**
 * Two-Factor Authentication Controller for Admin
 * 
 * Handles OTP generation, verification, and session management
 * for admin 2FA to achieve 100/100 security score.
 */
class TwoFactorController extends Controller
{
    /**
     * Admin user types that require 2FA
     */
    protected array $adminTypes = ['admin', 'adminstaff'];

    /**
     * Show 2FA verification page
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->user_type, $this->adminTypes)) {
            return redirect('/login');
        }
        
        return view('admin.2fa-verify', [
            'email' => $this->maskEmail($user->email),
        ]);
    }
    
    /**
     * Send OTP to admin email
     */
    public function sendOTP(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->user_type, $this->adminTypes)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Rate limit: max 3 OTP requests per 5 minutes
        $cacheKey = "2fa_otp_rate_{$user->id}";
        $attempts = cache()->get($cacheKey, 0);
        
        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please wait 5 minutes.',
            ], 429);
        }
        
        cache()->put($cacheKey, $attempts + 1, now()->addMinutes(5));
        
        // Generate OTP using static method
        $otp = $this->generateOTP($user);
        
        // Send OTP via email
        try {
            Mail::raw(
                "Your CAS Private Care admin verification code is: {$otp}\n\n" .
                "This code expires in 10 minutes.\n\n" .
                "If you did not request this code, please contact security immediately.\n\n" .
                "Time: " . now()->format('Y-m-d H:i:s') . " UTC\n" .
                "IP Address: " . $request->ip(),
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('CAS Private Care - Admin Verification Code');
                }
            );
            
            Log::channel('security')->info('2FA OTP sent', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email.',
                'email' => $this->maskEmail($user->email),
            ]);
        } catch (\Exception $e) {
            Log::channel('security')->error('2FA OTP send failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ], 500);
        }
    }
    
    /**
     * Verify OTP and grant access
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $user = Auth::user();
        
        if (!$user || !in_array($user->user_type, $this->adminTypes)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Verify OTP
        if ($this->verifyOTP($user, $request->code)) {
            $this->markVerified($user);
            
            Log::channel('security')->info('2FA verification successful', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            
            // Redirect based on user type
            $redirect = $user->user_type === 'admin' 
                ? '/admin/dashboard-vue' 
                : '/admin-staff/dashboard-vue';
            
            return response()->json([
                'success' => true,
                'redirect' => $redirect,
            ]);
        }
        
        Log::channel('security')->warning('2FA verification failed', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code. Please try again.',
        ], 401);
    }
    
    /**
     * Generate OTP for user
     */
    protected function generateOTP($user): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        Session::put('2fa_otp_' . $user->id, $otp);
        Session::put('2fa_otp_expires_' . $user->id, now()->addMinutes(10)->timestamp);
        
        return $otp;
    }
    
    /**
     * Verify OTP for user
     */
    protected function verifyOTP($user, string $code): bool
    {
        $storedOtp = Session::get('2fa_otp_' . $user->id);
        $expires = Session::get('2fa_otp_expires_' . $user->id);
        
        if (!$storedOtp || !$expires) {
            return false;
        }
        
        if (now()->timestamp > $expires) {
            Session::forget('2fa_otp_' . $user->id);
            Session::forget('2fa_otp_expires_' . $user->id);
            return false;
        }
        
        if ($storedOtp === $code) {
            Session::forget('2fa_otp_' . $user->id);
            Session::forget('2fa_otp_expires_' . $user->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Mark user as 2FA verified
     */
    protected function markVerified($user): void
    {
        Session::put('2fa_verified_' . $user->id, true);
        Session::put('2fa_verified_at_' . $user->id, now()->timestamp);
    }
    
    /**
     * Mask email for display (show first 2 and last 2 characters of local part)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $local = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($local) <= 4) {
            $masked = str_repeat('*', strlen($local));
        } else {
            $masked = substr($local, 0, 2) . str_repeat('*', strlen($local) - 4) . substr($local, -2);
        }
        
        return $masked . '@' . $domain;
    }

    /**
     * Resend OTP - alias for sendOTP with rate limit check
     */
    public function resendOTP(Request $request)
    {
        return $this->sendOTP($request);
    }
}
