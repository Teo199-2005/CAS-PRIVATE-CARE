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
     * Housekeeper Vue Dashboard (decommissioned)
     */
    public function housekeeperDashboardVue()
    {
        abort(410, 'Housekeeper dashboard is no longer supported.');
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
     * Training Dashboard (decommissioned)
     */
    public function trainingDashboardVue()
    {
        abort(410, 'Training center dashboard is no longer supported.');
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

        return redirect('/caregiver/dashboard-vue?payroll_onboarding=1')
            ->with('info', 'W-2 caregivers use Payroll onboarding on the dashboard (direct deposit), not Stripe Connect.');
    }

    /**
     * Connect Bank Account - Housekeeper (decommissioned)
     */
    public function connectBankAccountHousekeeper()
    {
        abort(410, 'Housekeeper Stripe Connect is no longer supported.');
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
     * Connect Bank Account - Training (decommissioned)
     */
    public function connectBankAccountTraining()
    {
        abort(410, 'Training center onboarding is no longer supported.');
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

        return redirect('/caregiver/dashboard-vue?payroll_onboarding=1')
            ->with('info', 'Stripe Connect is retired for W-2 caregivers. Use Payroll onboarding on your dashboard.');
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
