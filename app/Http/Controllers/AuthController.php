<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\PasswordReset;
use App\Rules\ValidPhoneNumber;
use App\Rules\ValidNYPhoneNumber;
use App\Services\NotificationService;
use App\Services\EmailService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Check if contractor/partner is rejected (ONLY block rejected accounts)
            $partnerTypes = ['caregiver', 'housekeeper', 'marketing', 'training_center'];
            if (in_array($user->user_type, $partnerTypes) && $user->status === 'rejected') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your application has been rejected. Please contact support for more information.'])->withInput();
            }
            
            // Normalize status - set null status to 'pending' for partner accounts
            if (in_array($user->user_type, $partnerTypes) && $user->status === null) {
                $user->update(['status' => 'pending']);
            }

            // Allow login for ALL statuses (pending, Active, approved) - restrictions are handled in dashboard
            if ($user->user_type === 'admin') {
                // Check if user has Admin Staff role
                if ($user->role === 'Admin Staff') {
                    return redirect('/admin-staff/dashboard-vue');
                }
                return redirect('/admin/dashboard-vue');
            } elseif ($user->user_type === 'caregiver') {
                return redirect('/caregiver/dashboard-vue');
            } elseif ($user->user_type === 'housekeeper') {
                return redirect('/housekeeper/dashboard-vue');
            } elseif ($user->user_type === 'marketing') {
                return redirect('/marketing/dashboard-vue');
            } elseif (in_array($user->user_type, ['training', 'training_center'])) {
                return redirect('/training/dashboard-vue');
            } else {
                // Client - check for redirect parameter
                $redirectUrl = $request->input('redirect') ?? session('url.intended');
                if ($redirectUrl && str_starts_with($redirectUrl, '/')) {
                    session()->forget('url.intended');
                    return redirect($redirectUrl);
                }
                return redirect('/client/dashboard-vue');
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function register(Request $request)
    {
        // Validate basic fields first
        $validated = $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'email' => ['required', 'email', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'phone' => ['required', new ValidNYPhoneNumber],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}$/', 'max:5'],
            'password' => session('oauth_user') ? 'nullable' : 'required|min:8|max:255|confirmed',
            'user_type' => 'required|in:client,caregiver,housekeeper,marketing,training_center',
            'partner_type' => 'nullable|in:caregiver,housekeeper,housekeeping,personal_assistant,marketing_partner,training_center',
            'terms' => 'required|accepted'
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'zip_code.regex' => 'Please enter a valid 5-digit ZIP code.'
        ]);
        
        // Map partner_type to user_type if provided (for partner registrations)
        // This overrides the user_type from the form
        $partnerType = $validated['partner_type'] ?? null;
        if ($partnerType) {
            // Map partner types to user types
            $userTypeMap = [
                'caregiver' => 'caregiver',
                'housekeeper' => 'housekeeper',
                'housekeeping' => 'housekeeper',
                'personal_assistant' => 'caregiver',
                'marketing_partner' => 'marketing',
                'training_center' => 'training_center'
            ];
            $validated['user_type'] = $userTypeMap[$partnerType] ?? 'caregiver';
        }

        // Check if this is OAuth registration
        $oauthUser = session('oauth_user');
        $password = $oauthUser ? Hash::make(\Illuminate\Support\Str::random(16)) : Hash::make($validated['password']);
        
        // Clean phone number (remove formatting, keep only digits)
        $phoneNumber = preg_replace('/[^0-9]/', '', $validated['phone']);
        // Format as (XXX) XXX-XXXX for storage
        $formattedPhone = '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6, 4);
        
        // Set status based on user type - all partner types need approval
        $partnerTypes = ['caregiver', 'housekeeper', 'marketing', 'training_center'];
        $status = in_array($validated['user_type'], $partnerTypes) ? 'pending' : 'Active';
        
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $formattedPhone,
            'zip_code' => $validated['zip_code'],
            'password' => $password,
            'user_type' => $validated['user_type'],
            'status' => $status,
            'email_verified_at' => $oauthUser ? now() : null
        ]);

        if ($validated['user_type'] === 'client') {
            Client::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name']
            ]);
        } elseif ($validated['user_type'] === 'caregiver') {
            // Create caregiver record for caregiver, housekeeping, or personal_assistant
            // Note: first_name and last_name are stored in the users table, not caregivers table
            Caregiver::create([
                'user_id' => $user->id,
                'gender' => 'female',
                'availability_status' => 'available'
            ]);
        } elseif ($validated['user_type'] === 'housekeeper') {
            // Create housekeeper record
            \App\Models\Housekeeper::create([
                'user_id' => $user->id,
                'gender' => $validated['gender'] ?? 'female',
                'availability_status' => 'available',
                'years_experience' => $validated['years_experience'] ?? 0
            ]);
        } elseif ($validated['user_type'] === 'marketing') {
            // Create referral code for marketing partners (inactive until approved)
            \App\Models\ReferralCode::create([
                'user_id' => $user->id,
                'code' => \App\Models\ReferralCode::generateCode($user->id),
                'discount_per_hour' => 5.00,
                'commission_per_hour' => 1.00,
                'is_active' => false, // Will be activated when approved
                'usage_count' => 0,
                'total_commission_earned' => 0
            ]);
        }
        // For training_center, no additional model creation needed

        // Clear OAuth session data
        session()->forget('oauth_user');
        
        // Send welcome notification (in-app notification)
        try {
            NotificationService::notifyAccountCreated($user);
        } catch (\Exception $e) {
            \Log::warning("Failed to send welcome notification: " . $e->getMessage());
        }
        
        // Send welcome email
        try {
            EmailService::sendWelcomeEmail($user);
        } catch (\Exception $e) {
            \Log::warning("Failed to send welcome email: " . $e->getMessage());
        }
        
        // Send email verification (only if not OAuth, OAuth users are pre-verified)
        if (!$oauthUser && !$user->email_verified_at) {
            try {
                EmailService::sendVerificationEmail($user);
            } catch (\Exception $e) {
                \Log::warning("Failed to send verification email: " . $e->getMessage());
            }
        }
        
        // Success message for all users - they can login immediately
        return redirect('/login')->with('success', 'Your account has been created successfully! You can now login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Don't reveal if email exists for security
            return response()->json(['status' => 'If that email exists, a password reset link has been sent.']);
        }
        
        // Generate token
        $token = Str::random(64);
        $hashedToken = hash('sha256', $token);
        
        // Mark any existing pending resets for this email as completed (to invalidate old tokens)
        PasswordReset::where('email', $request->email)
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        
        // Create new password reset record
        PasswordReset::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'token' => $hashedToken,
            'status' => 'pending',
            'requested_at' => now()
        ]);
        
        // Send password reset email
        try {
            EmailService::sendPasswordResetEmail($user, $token);
            // Notify admins in-app that a password reset was requested
            try {
                \App\Services\NotificationService::create(
                    3, // default admin user id (consider making this dynamic)
                    'Password Reset Requested',
                    "A password reset was requested for {$user->email}.",
                    'System',
                    'normal'
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to create admin notification for password reset: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send password reset email: " . $e->getMessage());
            return response()->json(['message' => 'Failed to send reset email. Please try again later.'], 500);
        }
        
        return response()->json(['status' => 'If that email exists, a password reset link has been sent.']);
    }

    public function showResetPasswordForm($token)
    {
        $email = trim(request()->query('email', ''));
        
        // Verify token is valid
        $hashedToken = hash('sha256', $token);
        
        // First try to find by token and email (case-insensitive)
        $reset = null;
        if ($email) {
            $reset = PasswordReset::where('token', $hashedToken)
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
                ->where('status', 'pending')
                ->first();
        }
        
        // If not found with email, try just by token (in case email doesn't match exactly)
        if (!$reset) {
            $reset = PasswordReset::where('token', $hashedToken)
                ->where('status', 'pending')
                ->first();
        }
        
        // Check if token is expired (60 minutes)
        $valid = false;
        $resetEmail = $email;
        
        if ($reset && $reset->requested_at) {
            $expiresAt = Carbon::parse($reset->requested_at)->addMinutes(60);
            $valid = now()->isBefore($expiresAt);
            
            // Always use email from database if we found the reset record
            if ($reset->email) {
                $resetEmail = $reset->email;
            }
            
            // If expired, log for debugging
            if (!$valid) {
                \Log::info('Password reset token expired', [
                    'email' => $reset->email,
                    'requested_at' => $reset->requested_at,
                    'expires_at' => $expiresAt,
                    'now' => now()
                ]);
            }
        } else {
            // Log when token is not found
            \Log::warning('Password reset token not found', [
                'hashed_token' => substr($hashedToken, 0, 10) . '...',
                'email_param' => $email,
                'token_length' => strlen($token),
                'all_pending_resets' => PasswordReset::where('status', 'pending')->count()
            ]);
        }
        
        return view('reset-password', [
            'token' => $token,
            'email' => $resetEmail,
            'valid' => $valid
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        // Verify token
        $hashedToken = hash('sha256', $validated['token']);
        
        // Try to find by token and email (case-insensitive)
        $reset = PasswordReset::where('token', $hashedToken)
            ->whereRaw('LOWER(email) = ?', [strtolower($validated['email'])])
            ->where('status', 'pending')
            ->first();
        
        // If not found, try just by token
        if (!$reset) {
            $reset = PasswordReset::where('token', $hashedToken)
                ->where('status', 'pending')
                ->first();
        }
        
        if (!$reset) {
            \Log::warning('Password reset token validation failed', [
                'email' => $validated['email'],
                'hashed_token' => substr($hashedToken, 0, 10) . '...'
            ]);
            return redirect()->back()->with('error', 'Invalid or expired reset token. Please request a new password reset link.');
        }
        
        // Check if token is expired (60 minutes)
        if ($reset->requested_at) {
            $expiresAt = Carbon::parse($reset->requested_at)->addMinutes(60);
            if (now()->isAfter($expiresAt)) {
                return redirect()->back()->with('error', 'This password reset link has expired. Please request a new one.');
            }
        }
        
        // Find user
        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
        
        // Update password
        $user->password = Hash::make($validated['password']);
        $user->save();
        
        // Mark reset as completed
        $reset->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        return redirect('/login')->with('success', 'Your password has been reset successfully. You can now login with your new password.');
    }

    public function sendVerificationEmail(Request $request)
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.'
            ], 400);
        }
        
        try {
            EmailService::sendVerificationEmail($user);
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully. Please check your inbox.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send verification email: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again later.'
            ], 500);
        }
    }

    public function sendOTP(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }
        
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.'
            ], 400);
        }
        
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);
        
        // Store OTP in database
        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => hash('sha256', Str::random(64)),
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
                'created_at' => now()
            ]
        );
        
        // Send OTP via email
        try {
            EmailService::sendOTPEmail($user, $otp);
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email address.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP email: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }
        
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.'
            ], 400);
        }
        
        // Find OTP record
        $verification = DB::table('email_verification_tokens')
            ->where('email', $user->email)
            ->first();
        
        if (!$verification || !$verification->otp) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP found. Please request a new one.'
            ], 400);
        }
        
        // Check if OTP is expired
        if (now()->isAfter(Carbon::parse($verification->otp_expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }
        
        // Verify OTP
        if ($verification->otp !== $validated['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }
        
        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();
        
        // Delete verification token
        DB::table('email_verification_tokens')
            ->where('email', $user->email)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!'
        ]);
    }

    public function verifyEmail($token)
    {
        $hashedToken = hash('sha256', $token);
        
        // Find the verification token
        $verification = DB::table('email_verification_tokens')
            ->where('token', $hashedToken)
            ->first();
        
        if (!$verification) {
            return redirect('/login')->with('error', 'Invalid verification link.');
        }
        
        // Check if token is expired (24 hours)
        $createdAt = Carbon::parse($verification->created_at);
        $expiresAt = $createdAt->addHours(24);
        
        if (now()->isAfter($expiresAt)) {
            DB::table('email_verification_tokens')->where('token', $hashedToken)->delete();
            return redirect('/login')->with('error', 'Verification link has expired. Please request a new one.');
        }
        
        // Find user by email
        $user = User::where('email', $verification->email)->first();
        
        if (!$user) {
            return redirect('/login')->with('error', 'User not found.');
        }
        
        // Verify email
        $user->email_verified_at = now();
        $user->save();
        
        // Delete the verification token
        DB::table('email_verification_tokens')->where('token', $hashedToken)->delete();
        
        // Auto-login if not already logged in
        if (!Auth::check()) {
            Auth::login($user);
        }
        
        // Redirect based on user type
        $redirectRoute = match($user->user_type) {
            'admin' => ($user->role === 'Admin Staff') ? '/admin-staff/dashboard-vue' : '/admin/dashboard-vue',
            'caregiver' => '/caregiver/dashboard-vue',
            'marketing' => '/marketing/dashboard-vue',
            'training', 'training_center' => '/training/dashboard-vue',
            default => '/client/dashboard-vue',
        };
        
        return redirect($redirectRoute)->with('success', 'Your email has been verified successfully!');
    }

    public function redirectToProvider($provider)
    {
        // Check if credentials are configured
        $config = config("services.{$provider}");
        
        // Check for empty or placeholder values
        $invalidValues = [
            'your_google_client_id',
            'your_facebook_app_id',
            'your_facebook_app_secret',
            'your_google_client_secret',
            null,
            '',
        ];
        
        if (empty($config['client_id']) || in_array($config['client_id'], $invalidValues, true)) {
            return redirect('/register')->with('error', ucfirst($provider) . ' OAuth is not configured. Please contact administrator.');
        }
        
        // Additional validation for Facebook - App ID should be numeric
        if ($provider === 'facebook' && !is_numeric($config['client_id'])) {
            return redirect('/register')->with('error', 'Facebook OAuth is not properly configured. Please contact administrator.');
        }
        
        // Store the referrer to know if coming from registration
        $referrer = request()->headers->get('referer');
        session(['oauth_referrer' => $referrer]);
        
        // Extract partner type from current URL or referrer URL if present
        $partnerType = request()->query('partner');
        if (!$partnerType && $referrer && preg_match('/[?&]partner=([^&]+)/', $referrer, $matches)) {
            $partnerType = urldecode($matches[1]);
        }
        if ($partnerType) {
            session(['oauth_partner_type' => $partnerType]);
            \Log::info('OAuth redirect - storing partner type:', ['partner_type' => $partnerType, 'referrer' => $referrer]);
        } else {
            \Log::info('OAuth redirect - no partner type found', ['referrer' => $referrer, 'query' => request()->query()]);
        }
        
        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            \Log::error("OAuth redirect error for {$provider}: " . $e->getMessage());
            return redirect('/register')->with('error', 'Unable to connect with ' . ucfirst($provider) . '. Please try again later or contact administrator.');
        }
    }

    public function handleProviderCallback($provider)
    {
        try {
            // For development environment, disable SSL verification
            if (config('app.env') === 'local') {
                $socialUser = Socialite::driver($provider)
                    ->setHttpClient(new \GuzzleHttp\Client([
                        'verify' => false
                    ]))
                    ->user();
            } else {
                $socialUser = Socialite::driver($provider)->user();
            }
            
            // Check if user already exists
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if ($user) {
                // ONLY block rejected contractor/partner accounts
                $partnerTypes = ['caregiver', 'marketing', 'training_center'];
                if (in_array($user->user_type, $partnerTypes) && $user->status === 'rejected') {
                    session()->forget('oauth_referrer');
                    return redirect('/login')->withErrors(['email' => 'Your application has been rejected. Please contact support for more information.']);
                }
                
                // Normalize status - set null status to 'pending' for partner accounts
                if (in_array($user->user_type, $partnerTypes) && $user->status === null) {
                    $user->update(['status' => 'pending']);
                }
                
                // Allow login for all other statuses (pending, Active, approved)
                Auth::login($user);
                session()->forget('oauth_referrer');
                
                // Redirect based on user type
                if ($user->user_type === 'admin') {
                    return redirect('/admin/dashboard-vue');
                } elseif ($user->user_type === 'caregiver') {
                    return redirect('/caregiver/dashboard-vue');
                } elseif ($user->user_type === 'housekeeper') {
                    return redirect('/housekeeper/dashboard-vue');
                } elseif ($user->user_type === 'marketing') {
                    return redirect('/marketing/dashboard-vue');
                } elseif (in_array($user->user_type, ['training', 'training_center'])) {
                    return redirect('/training/dashboard-vue');
                } else {
                    return redirect('/client/dashboard-vue');
                }
            } else {
                // New user - check if coming from registration
                $referrer = session('oauth_referrer', '');
                $isFromRegistration = str_contains($referrer, '/register');
                
                if ($isFromRegistration) {
                    // Store OAuth data in session and redirect back to registration
                    session([
                        'oauth_user' => [
                            'name' => $socialUser->getName(),
                            'email' => $socialUser->getEmail(),
                            'provider' => $provider
                        ]
                    ]);
                    
                    // Preserve partner type if it was selected
                    $partnerType = session('oauth_partner_type');
                    \Log::info('OAuth callback - partner type from session:', ['partner_type' => $partnerType]);
                    \Log::info('OAuth callback - all session data:', ['session' => session()->all()]);
                    session()->forget('oauth_referrer');
                    
                    // Redirect back to registration with partner type preserved
                    $redirectUrl = '/register';
                    if ($partnerType) {
                        $redirectUrl .= '?partner=' . urlencode($partnerType);
                        session()->forget('oauth_partner_type');
                        \Log::info('Redirecting with partner type:', ['url' => $redirectUrl]);
                    } else {
                        // Try to get from referrer as fallback
                        $referrer = session('oauth_referrer', '');
                        if ($referrer && preg_match('/[?&]partner=([^&]+)/', $referrer, $matches)) {
                            $partnerType = urldecode($matches[1]);
                            $redirectUrl .= '?partner=' . urlencode($partnerType);
                            \Log::info('Found partner type in referrer:', ['partner_type' => $partnerType, 'url' => $redirectUrl]);
                        } else {
                            \Log::warning('No partner type found in session or referrer during OAuth callback');
                        }
                    }
                    
                    return redirect($redirectUrl)->with('oauth_success', 'Please complete your registration below.');
                } else {
                    // Direct OAuth login - create account and login
                    $names = explode(' ', $socialUser->getName(), 2);
                    $firstName = $names[0] ?? '';
                    $lastName = $names[1] ?? '';
                    
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                        'user_type' => 'client',
                        'status' => 'Active',
                        'email_verified_at' => now()
                    ]);
                    
                    Client::create([
                        'user_id' => $user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName
                    ]);
                    
                    // Send welcome notification
                    try {
                        NotificationService::notifyAccountCreated($user);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send welcome notification: " . $e->getMessage());
                    }
                    
                    Auth::login($user);
                    return redirect('/client/dashboard-vue');
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('OAuth callback error: ' . $e->getMessage());
            session()->forget(['oauth_referrer', 'oauth_user']);
            return redirect('/register')->with('error', 'Unable to login with ' . ucfirst($provider) . ': ' . $e->getMessage());
        }
    }
}