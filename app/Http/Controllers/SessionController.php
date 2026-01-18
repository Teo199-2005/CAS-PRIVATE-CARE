<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Generate a new session token for the current admin user
     * Called after login to establish a session
     */
    public function generateSessionToken(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        // Only enforce for admin users (Master Admin)
        if ($user->user_type !== 'admin') {
            return response()->json(['message' => 'Session tracking not required for this user type']);
        }
        
        // Generate new session token
        $sessionToken = Str::random(64);
        
        // Update user's session token
        $user->update([
            'session_token' => $sessionToken,
            'session_started_at' => Carbon::now()
        ]);
        
        // Store token in session for comparison
        session(['admin_session_token' => $sessionToken]);
        
        return response()->json([
            'success' => true,
            'session_token' => $sessionToken,
            'message' => 'Session token generated'
        ]);
    }
    
    /**
     * Validate current session token
     * Returns whether this session is still valid or has been superseded
     */
    public function validateSession(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['valid' => false, 'reason' => 'not_authenticated']);
        }
        
        // Only enforce for admin users (Master Admin)
        if ($user->user_type !== 'admin') {
            return response()->json(['valid' => true, 'message' => 'Session tracking not required']);
        }
        
        // Skip for Admin Staff role - they don't need single session enforcement
        if ($user->role === 'Admin Staff') {
            return response()->json(['valid' => true, 'message' => 'Admin Staff session']);
        }
        
        // Get session token from current session
        $currentSessionToken = session('admin_session_token');
        
        // If no session token stored, this might be an old session - generate one
        if (!$currentSessionToken) {
            $sessionToken = Str::random(64);
            $user->update([
                'session_token' => $sessionToken,
                'session_started_at' => Carbon::now()
            ]);
            session(['admin_session_token' => $sessionToken]);
            return response()->json(['valid' => true, 'message' => 'Session token initialized']);
        }
        
        // Refresh user data from database
        $user->refresh();
        
        // Compare stored token with database token
        if ($user->session_token !== $currentSessionToken) {
            // Session has been superseded by a newer login
            return response()->json([
                'valid' => false,
                'reason' => 'session_superseded',
                'message' => 'Another device has logged into this account',
                'new_session_at' => $user->session_started_at ? Carbon::parse($user->session_started_at)->format('M d, Y h:i A') : null
            ]);
        }
        
        return response()->json([
            'valid' => true,
            'message' => 'Session is valid'
        ]);
    }
    
    /**
     * Heartbeat endpoint - called periodically to check session validity
     */
    public function heartbeat(Request $request)
    {
        return $this->validateSession($request);
    }
    
    /**
     * Clear session token on logout
     */
    public function clearSession(Request $request)
    {
        $user = Auth::user();
        
        if ($user && $user->user_type === 'admin') {
            // Get current session token
            $currentSessionToken = session('admin_session_token');
            
            // Only clear if this session's token matches (to prevent clearing newer sessions)
            if ($currentSessionToken && $user->session_token === $currentSessionToken) {
                $user->update([
                    'session_token' => null,
                    'session_started_at' => null
                ]);
            }
        }
        
        session()->forget('admin_session_token');
        
        return response()->json(['success' => true, 'message' => 'Session cleared']);
    }
}
