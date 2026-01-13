<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardRedirectController
 * 
 * Handles dashboard access and role-based redirections.
 * Each user type has their own dashboard with proper access control.
 */
class DashboardRedirectController extends Controller
{
    /**
     * Client Dashboard
     */
    public function clientDashboard()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'client') {
            return $this->redirectToUserDashboard($user);
        }
        
        return view('client-dashboard-vue');
    }

    /**
     * Client Payment Setup Page
     */
    public function clientPaymentSetup()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'client') {
            return redirect('/client/dashboard');
        }
        
        return view('client-payment-setup');
    }

    /**
     * Caregiver Dashboard
     */
    public function caregiverDashboard()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        
        return view('caregiver-dashboard');
    }

    /**
     * Caregiver Vue Dashboard
     */
    public function caregiverDashboardVue()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        
        // Block rejected accounts
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'Your application has been rejected. Please contact support for more information.'
            ]);
        }
        
        return view('caregiver-dashboard-vue');
    }

    /**
     * Housekeeper Vue Dashboard
     */
    public function housekeeperDashboardVue()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'housekeeper') {
            return redirect('/login');
        }
        
        // Block rejected accounts
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'Your application has been rejected. Please contact support for more information.'
            ]);
        }
        
        return view('housekeeper-dashboard-vue');
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboardVue()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'admin') {
            return redirect('/login');
        }
        
        // Admin Staff users go to their own dashboard
        if ($user->role === 'Admin Staff') {
            return redirect('/admin-staff/dashboard-vue');
        }
        
        return view('admin-dashboard-vue');
    }

    /**
     * Admin Staff Dashboard
     */
    public function adminStaffDashboardVue()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'admin' || $user->role !== 'Admin Staff') {
            return redirect('/login');
        }
        
        return view('admin-staff-dashboard-vue');
    }

    /**
     * Marketing Dashboard
     */
    public function marketingDashboardVue()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'marketing') {
            return redirect('/login');
        }
        
        // Block rejected accounts
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'Your application has been rejected. Please contact support for more information.'
            ]);
        }
        
        return view('marketing-dashboard-vue');
    }

    /**
     * Training Dashboard
     */
    public function trainingDashboardVue()
    {
        $user = auth()->user();
        
        if (!in_array($user->user_type, ['training', 'training_center'])) {
            return redirect('/login');
        }
        
        // Block rejected accounts
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'Your application has been rejected. Please contact support for more information.'
            ]);
        }
        
        return view('training-dashboard-vue');
    }

    /**
     * Connect Bank Account - Caregiver
     */
    public function connectBankAccount()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        
        return view('connect-bank-account');
    }

    /**
     * Connect Bank Account - Housekeeper
     */
    public function connectBankAccountHousekeeper()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return redirect('/login');
        }
        
        return view('connect-bank-account-housekeeper');
    }

    /**
     * Connect Bank Account - Marketing
     */
    public function connectBankAccountMarketing()
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'marketing') {
            return redirect('/login');
        }
        
        return view('connect-bank-account-marketing');
    }

    /**
     * Connect Bank Account - Training
     */
    public function connectBankAccountTraining()
    {
        $user = auth()->user();
        
        if (!in_array($user->user_type, ['training', 'training_center'])) {
            return redirect('/login');
        }
        
        return view('connect-bank-account-training');
    }

    /**
     * Link Payment Method - Client
     */
    public function linkPaymentMethod()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'client') {
            return redirect('/login');
        }
        
        return view('link-payment-method');
    }

    /**
     * Connect Payment Method - Client
     */
    public function connectPaymentMethod()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'client') {
            return redirect('/login');
        }
        
        return view('client-connect-payment');
    }

    /**
     * Stripe Connect Onboarding - Caregiver
     */
    public function stripeConnectOnboarding()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        
        return view('stripe-connect-onboarding');
    }

    /**
     * Redirect user to their appropriate dashboard based on user type
     */
    protected function redirectToUserDashboard($user)
    {
        $route = match($user->user_type) {
            'admin' => '/admin/dashboard-vue',
            'caregiver' => '/caregiver/dashboard-vue',
            'marketing' => '/marketing/dashboard-vue',
            'training', 'training_center' => '/training/dashboard-vue',
            default => '/login',
        };
        
        return redirect($route);
    }
}
