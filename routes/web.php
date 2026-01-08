<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CaregiverController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;

// Public Routes
Route::get('/', [LandingController::class, 'index']);
Route::get('/api/landing/stats', [LandingController::class, 'stats']); // Public stats endpoint
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']); // Sitemap

// SEO-Optimized Pages
Route::get('/caregiver-new-york', function () {
    return view('caregiver-new-york');
})->name('caregiver-new-york');
Route::get('/hire-caregiver-new-york', function () {
    return view('hire-caregiver-new-york');
})->name('hire-caregiver-new-york');
Route::get('/caregiver-brooklyn', function () {
    return view('caregiver-brooklyn');
})->name('caregiver-brooklyn');
Route::get('/caregiver-manhattan', function () {
    return view('caregiver-manhattan');
})->name('caregiver-manhattan');
Route::get('/caregiver-queens', function () {
    return view('caregiver-queens');
})->name('caregiver-queens');
Route::get('/caregiver-bronx', function () {
    return view('caregiver-bronx');
})->name('caregiver-bronx');
Route::get('/caregiver-staten-island', function () {
    return view('caregiver-staten-island');
})->name('caregiver-staten-island');
Route::get('/contractor-partner', function () {
    return view('contractor-partner');
})->name('contractor-partner');
Route::get('/faq', function () {
    return view('faq');
})->name('faq');
Route::get('/about', function () {
    return view('about');
})->name('about');
Route::get('/housekeeping-personal-assistant', function () {
    return view('housekeeping-personal-assistant');
})->name('housekeeping-personal-assistant');
Route::get('/housekeeping-new-york', function () {
    return view('housekeeping-new-york');
})->name('housekeeping-new-york');
Route::get('/personal-assistant-new-york', function () {
    return view('personal-assistant-new-york');
})->name('personal-assistant-new-york');
Route::get('/training-center', function () {
    return view('training-center');
})->name('training-center');

// Blog routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', function () {
    return view('register');
})->name('register');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::get('/reset-password/{token}', [\App\Http\Controllers\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\AuthController::class, 'resetPassword'])->name('password.update');

// Email Verification Routes
Route::post('/email/verification-notification', [\App\Http\Controllers\AuthController::class, 'sendVerificationEmail'])->middleware('auth')->name('verification.send');
Route::get('/verify-email/{token}', [\App\Http\Controllers\AuthController::class, 'verifyEmail'])->name('verification.verify');

// Public API Routes (no authentication required)
Route::prefix('api')->middleware(['web'])->group(function () {
    // ZIP code lookup (public)
    Route::get('/zipcode-lookup/{zip}', function($zip) {
        // Validate ZIP code format
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format'
            ], 400);
        }
        
        // ZIP code to location mapping for New York
        $zipCodeMap = [
            '10001' => 'Manhattan, NY', '10002' => 'Manhattan, NY', '10003' => 'Manhattan, NY', '10004' => 'Manhattan, NY',
            '10005' => 'Manhattan, NY', '10006' => 'Manhattan, NY', '10007' => 'Manhattan, NY', '10009' => 'Manhattan, NY',
            '10010' => 'Manhattan, NY', '10011' => 'Manhattan, NY', '10012' => 'Manhattan, NY', '10013' => 'Manhattan, NY',
            '10014' => 'Manhattan, NY', '10016' => 'Manhattan, NY', '10017' => 'Manhattan, NY', '10018' => 'Manhattan, NY',
            '10019' => 'Manhattan, NY', '10020' => 'Manhattan, NY', '10021' => 'Manhattan, NY', '10022' => 'Manhattan, NY',
            '10023' => 'Manhattan, NY', '10024' => 'Manhattan, NY', '10025' => 'Manhattan, NY', '10026' => 'Manhattan, NY',
            '10027' => 'Manhattan, NY', '10028' => 'Manhattan, NY', '10029' => 'Manhattan, NY', '10030' => 'Manhattan, NY',
            '10031' => 'Manhattan, NY', '10032' => 'Manhattan, NY', '10033' => 'Manhattan, NY', '10034' => 'Manhattan, NY',
            '10035' => 'Manhattan, NY', '10036' => 'Manhattan, NY', '10037' => 'Manhattan, NY', '10038' => 'Manhattan, NY',
            '10039' => 'Manhattan, NY', '10040' => 'Manhattan, NY', '10044' => 'Manhattan, NY', '10065' => 'Manhattan, NY',
            '10069' => 'Manhattan, NY', '10075' => 'Manhattan, NY', '10128' => 'Manhattan, NY', '10280' => 'Manhattan, NY',
            '11201' => 'Brooklyn, NY', '11203' => 'Brooklyn, NY', '11204' => 'Brooklyn, NY', '11205' => 'Brooklyn, NY',
            '11206' => 'Brooklyn, NY', '11207' => 'Brooklyn, NY', '11208' => 'Brooklyn, NY', '11209' => 'Brooklyn, NY',
            '11210' => 'Brooklyn, NY', '11211' => 'Brooklyn, NY', '11212' => 'Brooklyn, NY', '11213' => 'Brooklyn, NY',
            '11214' => 'Brooklyn, NY', '11215' => 'Brooklyn, NY', '11216' => 'Brooklyn, NY', '11217' => 'Brooklyn, NY',
            '11218' => 'Brooklyn, NY', '11219' => 'Brooklyn, NY', '11220' => 'Brooklyn, NY', '11221' => 'Brooklyn, NY',
            '11222' => 'Brooklyn, NY', '11223' => 'Brooklyn, NY', '11224' => 'Brooklyn, NY', '11225' => 'Brooklyn, NY',
            '11226' => 'Brooklyn, NY', '11228' => 'Brooklyn, NY', '11229' => 'Brooklyn, NY', '11230' => 'Brooklyn, NY',
            '11231' => 'Brooklyn, NY', '11232' => 'Brooklyn, NY', '11233' => 'Brooklyn, NY', '11234' => 'Brooklyn, NY',
            '11235' => 'Brooklyn, NY', '11236' => 'Brooklyn, NY', '11237' => 'Brooklyn, NY', '11238' => 'Brooklyn, NY',
            '11239' => 'Brooklyn, NY',
            '11354' => 'Flushing, NY', '11355' => 'Flushing, NY', '11356' => 'Flushing, NY', '11357' => 'Flushing, NY',
            '11358' => 'Flushing, NY', '11360' => 'Bayside, NY', '11361' => 'Bayside, NY', '11362' => 'Bayside, NY',
            '11363' => 'Bayside, NY', '11364' => 'Bayside, NY', '11365' => 'Fresh Meadows, NY', '11366' => 'Fresh Meadows, NY',
            '11367' => 'Fresh Meadows, NY', '11368' => 'Corona, NY', '11369' => 'East Elmhurst, NY', '11370' => 'Elmhurst, NY',
            '11371' => 'Elmhurst, NY', '11372' => 'Jackson Heights, NY', '11373' => 'Jackson Heights, NY', '11374' => 'Rego Park, NY',
            '11375' => 'Forest Hills, NY', '11377' => 'Woodside, NY', '11378' => 'Maspeth, NY', '11379' => 'Middle Village, NY',
            '11385' => 'Ridgewood, NY',
            '10451' => 'Bronx, NY', '10452' => 'Bronx, NY', '10453' => 'Bronx, NY', '10454' => 'Bronx, NY',
            '10455' => 'Bronx, NY', '10456' => 'Bronx, NY', '10457' => 'Bronx, NY', '10458' => 'Bronx, NY',
            '10459' => 'Bronx, NY', '10460' => 'Bronx, NY', '10461' => 'Bronx, NY', '10462' => 'Bronx, NY',
            '10463' => 'Bronx, NY', '10464' => 'Bronx, NY', '10465' => 'Bronx, NY', '10466' => 'Bronx, NY',
            '10467' => 'Bronx, NY', '10468' => 'Bronx, NY', '10469' => 'Bronx, NY', '10470' => 'Bronx, NY',
            '10471' => 'Bronx, NY', '10472' => 'Bronx, NY', '10473' => 'Bronx, NY', '10474' => 'Bronx, NY',
            '10475' => 'Bronx, NY',
            '10301' => 'Staten Island, NY', '10302' => 'Staten Island, NY', '10303' => 'Staten Island, NY',
            '10304' => 'Staten Island, NY', '10305' => 'Staten Island, NY', '10306' => 'Staten Island, NY',
            '10307' => 'Staten Island, NY', '10308' => 'Staten Island, NY', '10309' => 'Staten Island, NY',
            '10310' => 'Staten Island, NY', '10311' => 'Staten Island, NY', '10312' => 'Staten Island, NY',
            '10314' => 'Staten Island, NY'
        ];
        
        $location = $zipCodeMap[$zip] ?? 'New York, NY';
        
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip
        ]);
    });
});
Route::get('/auth/{provider}', [\App\Http\Controllers\AuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\AuthController::class, 'handleProviderCallback']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
Route::post('/password/email', [\App\Http\Controllers\AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1');

// Protected Dashboard Routes with Role-Based Access
Route::middleware(['auth'])->group(function () {
    // Client Dashboard - accessible by clients
Route::get('/client/dashboard', function () {
        if (auth()->user()->user_type !== 'client') {
            $userType = auth()->user()->user_type;
            $route = match($userType) {
                'admin' => '/admin/dashboard-vue',
                'caregiver' => '/caregiver/dashboard-vue',
                'marketing' => '/marketing/dashboard-vue',
                'training', 'training_center' => '/training/dashboard-vue',
                default => '/login',
            };
            return redirect($route);
        }
    return view('client-dashboard-vue');
});
    Route::get('/client/dashboard-vue', function () {
        if (auth()->user()->user_type !== 'client') {
            return redirect('/login');
        }
        return view('client-dashboard-vue');
    })->name('client.dashboard');
    
    // Payment Page - accessible by authenticated clients
    Route::get('/payment', function () {
        $bookingId = request()->query('booking_id');
        
        if (!$bookingId) {
            return redirect('/client/dashboard')->with('error', 'No booking specified');
        }
        
        // Load booking data
        $booking = \App\Models\Booking::with(['client', 'assignments.caregiver.user', 'referralCode'])
            ->where('id', $bookingId)
            ->first();
            
        if (!$booking) {
            return redirect('/client/dashboard')->with('error', 'Booking not found');
        }
        
        // Verify ownership (if authenticated)
        if (auth()->check() && auth()->user()->user_type === 'client') {
            if ($booking->client_id !== auth()->id()) {
                return redirect('/client/dashboard')->with('error', 'Unauthorized access');
            }
        }
        
        // Pass Stripe publishable key to the view
        $stripeKey = config('stripe.key');
        
        return view('payment', compact('booking', 'bookingId', 'stripeKey'));
    })->name('payment');
    
    // Payment Success Page - ALSO UPDATES DATABASE
    Route::get('/payment-success', function () {
        $bookingId = request()->query('booking_id');
        $paymentIntentId = request()->query('payment_intent');
        
        \Log::info("=== PAYMENT SUCCESS PAGE LOADED ===", [
            'booking_id' => $bookingId,
            'payment_intent' => $paymentIntentId,
            'all_params' => request()->all()
        ]);
        
        if (!$bookingId) {
            return redirect('/client/dashboard')->with('error', 'No booking specified');
        }
        
        // Load booking data
        $booking = \App\Models\Booking::with(['client', 'assignments.caregiver.user'])
            ->where('id', $bookingId)
            ->first();
            
        if (!$booking) {
            return redirect('/client/dashboard')->with('error', 'Booking not found');
        }
        
        // ALWAYS UPDATE DATABASE IF NOT PAID (even without Stripe verification for localhost)
        if ($booking->payment_status !== 'paid') {
            try {
                \Log::info("Payment Success - Booking not paid, updating now", [
                    'booking_id' => $bookingId,
                    'current_status' => $booking->payment_status
                ]);
                
                // Calculate amount from booking
                $hours = 8; // Default
                if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
                    $hours = (int)$matches[1];
                }
                $rate = $booking->assigned_hourly_rate ?: 28;
                $amount = $hours * $booking->duration_days * $rate;
                $platformFee = $amount * 0.10;
                $caregiverAmount = $amount * 0.90;
                
                // Try to verify with Stripe if payment_intent provided
                if ($paymentIntentId) {
                    try {
                        $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
                        
                        if ($paymentIntent->status === 'succeeded') {
                            $amount = $paymentIntent->amount / 100;
                            $platformFee = $amount * 0.10;
                            $caregiverAmount = $amount * 0.90;
                        }
                        
                        \Log::info("Stripe verification successful", [
                            'stripe_status' => $paymentIntent->status,
                            'stripe_amount' => $paymentIntent->amount
                        ]);
                    } catch (\Exception $stripeError) {
                        \Log::warning("Stripe verification failed, using calculated amount", [
                            'error' => $stripeError->getMessage()
                        ]);
                    }
                }
                
                // Update booking
                \DB::table('bookings')->where('id', $booking->id)->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'payment_date' => now(),
                    'updated_at' => now(),
                ]);
                
                // Check if payment record already exists
                $existingPayment = \DB::table('payments')->where('booking_id', $booking->id)->first();
                
                if (!$existingPayment) {
                    \DB::table('payments')->insert([
                        'booking_id' => $booking->id,
                        'client_id' => $booking->client_id,
                        'amount' => $amount,
                        'platform_fee' => $platformFee,
                        'caregiver_amount' => $caregiverAmount,
                        'payment_method' => 'credit_card',
                        'status' => 'completed',
                        'transaction_id' => $paymentIntentId ?: 'payment_' . time(),
                        'paid_at' => now(),
                        'notes' => 'Stripe payment completed',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    \Log::info("=== PAYMENT RECORD CREATED ===", [
                        'booking_id' => $booking->id,
                        'amount' => $amount,
                        'platform_fee' => $platformFee,
                        'caregiver_amount' => $caregiverAmount
                    ]);
                }
                
                // Refresh booking object
                $booking = \App\Models\Booking::with(['client', 'assignments.caregiver.user'])
                    ->where('id', $bookingId)
                    ->first();
                    
            } catch (\Exception $e) {
                \Log::error("Payment Success - Error updating database: " . $e->getMessage(), [
                    'booking_id' => $bookingId,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::info("Payment Success - Booking already paid", [
                'booking_id' => $bookingId
            ]);
        }
        
        return view('payment-success', compact('booking', 'bookingId', 'paymentIntentId'));
    })->name('payment.success');
    
    // Caregiver Dashboard - accessible by caregivers
Route::get('/caregiver/dashboard', function () {
        if (auth()->user()->user_type !== 'caregiver') {
            return redirect('/login');
        }
    return view('caregiver-dashboard');
});
Route::get('/caregiver/dashboard-vue', function () {
        $user = auth()->user();
        if ($user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        // ONLY block rejected accounts - pending accounts can access dashboard but with limited features
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors(['email' => 'Your application has been rejected. Please contact support for more information.']);
        }
        return view('caregiver-dashboard-vue');
    })->name('caregiver.dashboard');

    // Custom Bank Onboarding Page - Caregivers only
    Route::get('/connect-bank-account', function () {
        $user = auth()->user();
        if (!$user || $user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        return view('connect-bank-account');
    })->name('connect.bank.account');

    // Stripe Connect Onboarding Page - Caregivers only
    Route::get('/stripe-connect-onboarding', function () {
        $user = auth()->user();
        if (!$user || $user->user_type !== 'caregiver') {
            return redirect('/login');
        }
        return view('stripe-connect-onboarding');
    })->name('stripe.connect.onboarding');
    
    // API Test Page (for debugging)
    Route::get('/api-test', function () {
        return view('api-test');
    })->name('api.test');

    // Admin Dashboard - accessible by admins only
Route::get('/admin/dashboard-vue', function () {
        $user = auth()->user();
        if ($user->user_type !== 'admin') {
            return redirect('/login');
        }
        // Check if user has Admin Staff role
        if ($user->role === 'Admin Staff') {
            return redirect('/admin-staff/dashboard-vue');
        }
    return view('admin-dashboard-vue');
    })->name('admin.dashboard');

    // Admin Staff Dashboard - accessible by Admin Staff only
    Route::get('/admin-staff/dashboard-vue', function () {
        $user = auth()->user();
        if ($user->user_type !== 'admin' || $user->role !== 'Admin Staff') {
            return redirect('/login');
        }
        return view('admin-staff-dashboard-vue');
    })->name('admin-staff.dashboard');

Route::get('/admin/settings', [AdminController::class, 'settings']);
Route::post('/admin/settings', [AdminController::class, 'updateSettings']);

    // Marketing Dashboard - accessible by marketing staff
    Route::get('/marketing/dashboard-vue', function () {
        $user = auth()->user();
        if ($user->user_type !== 'marketing') {
            return redirect('/login');
        }
        // ONLY block rejected accounts - pending accounts can access dashboard but with limited features
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors(['email' => 'Your application has been rejected. Please contact support for more information.']);
        }
        return view('marketing-dashboard-vue');
    })->name('marketing.dashboard');
    
    // Marketing Bank Onboarding - Stripe Connect
    Route::get('/connect-bank-account-marketing', function () {
        $user = auth()->user();
        if ($user->user_type !== 'marketing') {
            return redirect('/login');
        }
        return view('connect-bank-account-marketing');
    })->name('marketing.connect.bank');
    
    // Training Dashboard - accessible by training centers
    Route::get('/training/dashboard-vue', function () {
        $user = auth()->user();
        if (!in_array($user->user_type, ['training', 'training_center'])) {
            return redirect('/login');
        }
        // ONLY block rejected accounts - pending accounts can access dashboard but with limited features
        if ($user->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors(['email' => 'Your application has been rejected. Please contact support for more information.']);
        }
        return view('training-dashboard-vue');
    })->name('training.dashboard');
    
    // Training Bank Onboarding - Stripe Connect
    Route::get('/connect-bank-account-training', function () {
        $user = auth()->user();
        if (!in_array($user->user_type, ['training', 'training_center'])) {
            return redirect('/login');
        }
        return view('connect-bank-account-training');
    })->name('training.connect.bank');
    
    // Book Service Form - accessible by clients
Route::get('/book-service', [BookingController::class, 'create']);
Route::post('/bookings', [BookingController::class, 'store']);
    
    // Profile - accessible by all authenticated users
Route::get('/profile', [ProfileController::class, 'show']);
Route::post('/profile/update', [ProfileController::class, 'update']);

    // Caregiver available clients page
Route::get('/available-clients', [CaregiverController::class, 'availableClients']);
});

// Public API Routes (no authentication required)
Route::prefix('api')->middleware(['web'])->group(function () {
    // ZIP code lookup (public)
    Route::get('/zipcode-lookup/{zip}', function($zip) {
        // Validate ZIP code format
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format'
            ], 400);
        }
        
        // ZIP code to location mapping for New York
        $zipCodeMap = [
            '10001' => 'Manhattan, NY', '10002' => 'Manhattan, NY', '10003' => 'Manhattan, NY', '10004' => 'Manhattan, NY',
            '10005' => 'Manhattan, NY', '10006' => 'Manhattan, NY', '10007' => 'Manhattan, NY', '10009' => 'Manhattan, NY',
            '10010' => 'Manhattan, NY', '10011' => 'Manhattan, NY', '10012' => 'Manhattan, NY', '10013' => 'Manhattan, NY',
            '10014' => 'Manhattan, NY', '10016' => 'Manhattan, NY', '10017' => 'Manhattan, NY', '10018' => 'Manhattan, NY',
            '10019' => 'Manhattan, NY', '10020' => 'Manhattan, NY', '10021' => 'Manhattan, NY', '10022' => 'Manhattan, NY',
            '10023' => 'Manhattan, NY', '10024' => 'Manhattan, NY', '10025' => 'Manhattan, NY', '10026' => 'Manhattan, NY',
            '10027' => 'Manhattan, NY', '10028' => 'Manhattan, NY', '10029' => 'Manhattan, NY', '10030' => 'Manhattan, NY',
            '10031' => 'Manhattan, NY', '10032' => 'Manhattan, NY', '10033' => 'Manhattan, NY', '10034' => 'Manhattan, NY',
            '10035' => 'Manhattan, NY', '10036' => 'Manhattan, NY', '10037' => 'Manhattan, NY', '10038' => 'Manhattan, NY',
            '10039' => 'Manhattan, NY', '10040' => 'Manhattan, NY', '10044' => 'Manhattan, NY', '10065' => 'Manhattan, NY',
            '10069' => 'Manhattan, NY', '10075' => 'Manhattan, NY', '10128' => 'Manhattan, NY', '10280' => 'Manhattan, NY',
            '11201' => 'Brooklyn, NY', '11203' => 'Brooklyn, NY', '11204' => 'Brooklyn, NY', '11205' => 'Brooklyn, NY',
            '11206' => 'Brooklyn, NY', '11207' => 'Brooklyn, NY', '11208' => 'Brooklyn, NY', '11209' => 'Brooklyn, NY',
            '11210' => 'Brooklyn, NY', '11211' => 'Brooklyn, NY', '11212' => 'Brooklyn, NY', '11213' => 'Brooklyn, NY',
            '11214' => 'Brooklyn, NY', '11215' => 'Brooklyn, NY', '11216' => 'Brooklyn, NY', '11217' => 'Brooklyn, NY',
            '11218' => 'Brooklyn, NY', '11219' => 'Brooklyn, NY', '11220' => 'Brooklyn, NY', '11221' => 'Brooklyn, NY',
            '11222' => 'Brooklyn, NY', '11223' => 'Brooklyn, NY', '11224' => 'Brooklyn, NY', '11225' => 'Brooklyn, NY',
            '11226' => 'Brooklyn, NY', '11228' => 'Brooklyn, NY', '11229' => 'Brooklyn, NY', '11230' => 'Brooklyn, NY',
            '11231' => 'Brooklyn, NY', '11232' => 'Brooklyn, NY', '11233' => 'Brooklyn, NY', '11234' => 'Brooklyn, NY',
            '11235' => 'Brooklyn, NY', '11236' => 'Brooklyn, NY', '11237' => 'Brooklyn, NY', '11238' => 'Brooklyn, NY',
            '11239' => 'Brooklyn, NY',
            '11354' => 'Flushing, NY', '11355' => 'Flushing, NY', '11356' => 'Flushing, NY', '11357' => 'Flushing, NY',
            '11358' => 'Flushing, NY', '11360' => 'Bayside, NY', '11361' => 'Bayside, NY', '11362' => 'Bayside, NY',
            '11363' => 'Bayside, NY', '11364' => 'Bayside, NY', '11365' => 'Fresh Meadows, NY', '11366' => 'Fresh Meadows, NY',
            '11367' => 'Fresh Meadows, NY', '11368' => 'Corona, NY', '11369' => 'East Elmhurst, NY', '11370' => 'Elmhurst, NY',
            '11371' => 'Elmhurst, NY', '11372' => 'Jackson Heights, NY', '11373' => 'Jackson Heights, NY', '11374' => 'Rego Park, NY',
            '11375' => 'Forest Hills, NY', '11377' => 'Woodside, NY', '11378' => 'Maspeth, NY', '11379' => 'Middle Village, NY',
            '11385' => 'Ridgewood, NY',
            '10451' => 'Bronx, NY', '10452' => 'Bronx, NY', '10453' => 'Bronx, NY', '10454' => 'Bronx, NY',
            '10455' => 'Bronx, NY', '10456' => 'Bronx, NY', '10457' => 'Bronx, NY', '10458' => 'Bronx, NY',
            '10459' => 'Bronx, NY', '10460' => 'Bronx, NY', '10461' => 'Bronx, NY', '10462' => 'Bronx, NY',
            '10463' => 'Bronx, NY', '10464' => 'Bronx, NY', '10465' => 'Bronx, NY', '10466' => 'Bronx, NY',
            '10467' => 'Bronx, NY', '10468' => 'Bronx, NY', '10469' => 'Bronx, NY', '10470' => 'Bronx, NY',
            '10471' => 'Bronx, NY', '10472' => 'Bronx, NY', '10473' => 'Bronx, NY', '10474' => 'Bronx, NY',
            '10475' => 'Bronx, NY',
            '10301' => 'Staten Island, NY', '10302' => 'Staten Island, NY', '10303' => 'Staten Island, NY',
            '10304' => 'Staten Island, NY', '10305' => 'Staten Island, NY', '10306' => 'Staten Island, NY',
            '10307' => 'Staten Island, NY', '10308' => 'Staten Island, NY', '10309' => 'Staten Island, NY',
            '10310' => 'Staten Island, NY', '10311' => 'Staten Island, NY', '10312' => 'Staten Island, NY',
            '10314' => 'Staten Island, NY'
        ];
        
        $location = $zipCodeMap[$zip] ?? 'New York, NY';
        
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip
        ]);
    });
});

// API Routes with Authentication
Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    
    // Application Status Endpoints (for contractors/partners)
    Route::get('/caregiver/application-status', function () {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus,
            'application' => ['status' => $approvalStatus]
        ]);
    });
    
    Route::get('/marketing/application-status', function () {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    });
    
    Route::get('/training/application-status', function () {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    });
    
    // Booking endpoints
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy']);
    Route::get('/bookings/{id}/assignments', [\App\Http\Controllers\BookingController::class, 'getAssignments']);
    Route::get('/bookings-with-assignments', [\App\Http\Controllers\BookingController::class, 'indexWithAssignments']);
    Route::post('/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
    
    // Quick fix: Ensure booking has payment data
    Route::post('/bookings/{id}/add-payment-data', function($id) {
        $booking = \App\Models\Booking::find($id);
        if ($booking) {
            $booking->update([
                'duration_days' => $booking->duration_days ?? 15,
                'duty_type' => $booking->duty_type ?? '8 Hours',
                'hourly_rate' => $booking->hourly_rate ?? 40,
                'hours' => $booking->hours ?? ($booking->duration_days ?? 15) * 8,
            ]);
            return response()->json([
                'success' => true,
                'booking' => $booking,
                'message' => 'Payment data added successfully'
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
    });
    
    // Client stats and data
    Route::get('/client/stats', [\App\Http\Controllers\DashboardController::class, 'clientStats']);
    Route::get('/client/available-years', [\App\Http\Controllers\DashboardController::class, 'clientAvailableYears']);
    Route::get('/client/spending-data', [\App\Http\Controllers\DashboardController::class, 'clientSpendingData']);
    Route::get('/client/top-caregivers', function(\Illuminate\Http\Request $request) {
        $clientId = $request->query('client_id') ?: auth()->id();
        
        $assignments = \App\Models\BookingAssignment::whereHas('booking', function($q) use ($clientId) {
            $q->where('client_id', $clientId)->where('status', 'completed');
        })->with('caregiver.user')->get();
        
        $caregiverBookings = $assignments->groupBy('caregiver_id')->map(function($group, $caregiverId) {
            $caregiver = $group->first()->caregiver;
            return [
                'name' => $caregiver->user->name ?? 'Unknown',
                'bookings' => $group->count()
            ];
        })->sortByDesc('bookings')->take(5)->values();
        
        if ($caregiverBookings->isEmpty()) {
            return response()->json(['caregivers' => []]);
        }
        
        $maxBookings = $caregiverBookings->first()['bookings'];
        
        $result = $caregiverBookings->map(function($c) use ($maxBookings) {
        return [
                'name' => $c['name'],
                'bookings' => $c['bookings'],
                'percentage' => $maxBookings > 0 ? round(($c['bookings'] / $maxBookings) * 100) : 0
            ];
        });
        
        return response()->json(['caregivers' => $result]);
    });
    
    // Caregiver data
    Route::get('/caregivers', [\App\Http\Controllers\DashboardController::class, 'caregivers']);
    Route::get('/caregiver/{id}/stats', [\App\Http\Controllers\DashboardController::class, 'caregiverStats']);
    Route::get('/caregiver/{id}/earnings-report', [\App\Http\Controllers\CaregiverController::class, 'getEarningsReport']);
    Route::post('/caregiver/earnings-report-pdf', [\App\Http\Controllers\CaregiverController::class, 'generateEarningsReportPdf']);
    Route::get('/available-clients', [\App\Http\Controllers\CaregiverController::class, 'getAvailableClients']);
    Route::post('/apply-client/{id}', [\App\Http\Controllers\CaregiverController::class, 'applyForClient']);
    
    // Caregiver payment data (dynamic - no hardcoded values)
    Route::get('/caregiver/payment-data', function(\Illuminate\Http\Request $request) {
        $user = auth()->user();
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver not found'], 404);
        }
        
        // Get all time tracking records
        $timeTrackings = \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
            ->with(['client.user', 'booking'])
            ->orderBy('work_date', 'desc')
            ->get();
        
        // Calculate totals
        $totalEarnings = $timeTrackings->sum('caregiver_earnings') ?? 0;
        $pendingEarnings = $timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings') ?? 0;
        $paidEarnings = $timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings') ?? 0;
        
        // Get last payment - sum all payments from the same paid_at date
        $lastPaymentGroup = $timeTrackings->where('payment_status', 'paid')
            ->where('paid_at', '!=', null)
            ->sortByDesc('paid_at')
            ->first();
        
        if ($lastPaymentGroup) {
            // Get all records paid at the same time
            $lastPaymentDate = $lastPaymentGroup->paid_at;
            $lastPaymentAmount = $timeTrackings->where('payment_status', 'paid')
                ->filter(function($tt) use ($lastPaymentDate) {
                    return $tt->paid_at && $tt->paid_at->eq($lastPaymentDate);
                })
                ->sum('caregiver_earnings');
            $lastPaymentDateFormatted = $lastPaymentDate->format('M d, Y');
        } else {
            $lastPaymentAmount = 0;
            $lastPaymentDateFormatted = 'No payments yet';
        }
        
        // Calculate current week earnings for account balance
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek();
        
        $weeklyEarnings = $timeTrackings->filter(function($tt) use ($startOfWeek, $endOfWeek) {
            $workDate = \Carbon\Carbon::parse($tt->work_date);
            return $workDate->between($startOfWeek, $endOfWeek) && $tt->payment_status === 'pending';
        })->sum('caregiver_earnings') ?? 0;
        
        // Calculate next payout date (next Friday)
        $today = \Carbon\Carbon::now();
        $nextFriday = $today->copy()->next(\Carbon\Carbon::FRIDAY);
        if ($today->isFriday()) {
            $nextFriday = $today->copy()->addWeek();
        }
        
        // Get Stripe connection status
        $stripeConnected = !empty($user->stripe_connect_id);
        $stripeOnboardingComplete = $user->stripe_onboarding_complete ?? false;
        
        // Get transactions (from time_trackings)
        $transactions = $timeTrackings->map(function($tt) {
            $clientName = 'Unknown';
            if ($tt->client && $tt->client->user) {
                $clientName = $tt->client->user->name;
            } elseif ($tt->booking && $tt->booking->client) {
                $clientName = $tt->booking->client->name ?? 'Client';
            }
            
            return [
                'id' => $tt->id,
                'date' => \Carbon\Carbon::parse($tt->work_date)->format('M d, Y'),
                'type' => $tt->payment_status === 'paid' ? 'Payment' : 'Pending',
                'description' => "Service for {$clientName}",
                'amount' => number_format($tt->caregiver_earnings ?? 0, 2),
                'status' => $tt->payment_status === 'paid' ? 'Completed' : 'Pending',
                'method' => $tt->payment_status === 'paid' ? 'Bank Transfer' : 'N/A',
                'hours_worked' => round($tt->hours_worked ?? 0, 2),
                'hourly_rate' => $tt->assigned_hourly_rate ?? 28.00,
                'client_name' => $clientName,
                'work_date' => $tt->work_date,
                'paid_at' => $tt->paid_at ? $tt->paid_at->format('M d, Y') : null,
            ];
        })->values();
        
        // Payment summary
        $paymentSummary = [
            'total_earnings' => number_format($paidEarnings, 2),
            'pending_earnings' => number_format($pendingEarnings, 2),
            'last_payment_amount' => number_format($lastPaymentAmount, 2),
            'last_payment_date' => $lastPaymentDateFormatted,
            'account_balance' => number_format($pendingEarnings, 2), // Total pending, not just weekly
            'next_payout_date' => $nextFriday->format('M d, Y'),
            'payout_frequency' => 'Weekly',
            'payout_method' => $stripeConnected ? 'Bank Transfer (Stripe)' : 'Not Connected',
        ];
        
        // Stripe connection info
        $stripeInfo = [
            'connected' => $stripeConnected,
            'onboarding_complete' => $stripeOnboardingComplete,
            'account_id' => $user->stripe_connect_id,
            'needs_setup' => !$stripeConnected || !$stripeOnboardingComplete,
        ];
        
        return response()->json([
            'success' => true,
            'payment_summary' => $paymentSummary,
            'transactions' => $transactions,
            'stripe_info' => $stripeInfo,
            'statistics' => [
                'total_hours_worked' => round($timeTrackings->sum('hours_worked') ?? 0, 2),
                'total_sessions' => $timeTrackings->count(),
                'paid_sessions' => $timeTrackings->where('payment_status', 'paid')->count(),
                'pending_sessions' => $timeTrackings->where('payment_status', 'pending')->count(),
                'average_hours_per_session' => $timeTrackings->count() > 0 
                    ? round($timeTrackings->avg('hours_worked') ?? 0, 2) 
                    : 0,
            ]
        ]);
    });
    
    // Caregiver schedule events
    Route::get('/caregiver/schedule-events', function(\Illuminate\Http\Request $request) {
        $caregiverId = $request->query('caregiver_id');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        
        if (!$caregiverId) {
            // Get caregiver ID from authenticated user
            $user = auth()->user();
            if ($user && $user->user_type === 'caregiver') {
                $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
                $caregiverId = $caregiver?->id;
            }
        }
        
        if (!$caregiverId) {
            return response()->json(['events' => []]);
        }
        
        $assignments = \App\Models\BookingAssignment::where('caregiver_id', $caregiverId)
            ->with('booking.client')
                ->get();

        $events = [];
        
        foreach ($assignments as $assignment) {
            $booking = $assignment->booking;
            if (!$booking) continue;
            
            $startDate = \Carbon\Carbon::parse($booking->service_date);
            $endDate = $startDate->copy()->addDays($booking->duration_days);
            
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->month == $month && $currentDate->year == $year) {
                    $day = $currentDate->day;
                    
                    $status = 'scheduled';
                    if ($currentDate->isPast()) {
                        $status = $booking->status === 'completed' ? 'completed' : 'confirmed';
                    }
                    if ($booking->status === 'cancelled') {
                        $status = 'cancelled';
                    }
                    
                    if (!isset($events[$day])) {
                        $events[$day] = [];
                    }
                    
                    $alreadyAdded = collect($events[$day])->contains('booking_id', $booking->id);
                    if (!$alreadyAdded) {
                        $events[$day][] = [
                            'booking_id' => $booking->id,
                            'client' => $booking->client->name ?? 'Unknown Client',
                            'service' => $booking->service_type,
                            'time' => $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('g:i A') : '9:00 AM',
                            'status' => $status
                        ];
                    }
                }
                $currentDate->addDay();
            }
        }
        
        return response()->json(['events' => $events]);
    });
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'delete']);
    Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'deleteAll']);
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'updateProfile']);
    
    // Time Tracking
    Route::post('/time-tracking/clock-in', [\App\Http\Controllers\TimeTrackingController::class, 'clockIn']);
    Route::post('/time-tracking/clock-out', [\App\Http\Controllers\TimeTrackingController::class, 'clockOut']);
    Route::get('/time-tracking/current-session/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getCurrentSession']);
    Route::get('/time-tracking/weekly-history/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getWeeklyHistory']);
    Route::get('/time-tracking/today-summary/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getTodaySummary']);
    
    // Referral Codes
    Route::get('/referral-codes/my-code', [\App\Http\Controllers\ReferralCodeController::class, 'getMyCode']);
    Route::post('/referral-codes/validate', [\App\Http\Controllers\ReferralCodeController::class, 'validateCode']);
    
    // Training Centers (for caregiver profile dropdown)
    Route::get('/training-centers', function() {
        $trainingCenters = \App\Models\User::whereIn('user_type', ['training_center', 'training'])
            ->where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($user) {
                return $user->name;
            })
            ->values()
            ->toArray();
        
        return response()->json(['trainingCenters' => $trainingCenters]);
    });
    
    // Payment Receipts (New - with payment details) - MUST come BEFORE generic receipts route
    Route::get('/receipts/payment/{bookingId}', [\App\Http\Controllers\ReceiptController::class, 'generatePaymentReceipt'])->name('receipt.payment');
    Route::get('/receipts/payment/{bookingId}/download', [\App\Http\Controllers\ReceiptController::class, 'downloadPaymentReceipt'])->name('receipt.payment.download');
    
    // Receipts (generic)
    Route::get('/receipts/{bookingId}', [\App\Http\Controllers\ReceiptController::class, 'generate']);
    Route::get('/receipts/{bookingId}/download', [\App\Http\Controllers\ReceiptController::class, 'download']);
    
    // Pricing
    Route::get('/pricing/breakdown', function(\Illuminate\Http\Request $request) {
        $hasReferral = $request->boolean('has_referral', false);
        $hasTrainingCenter = $request->boolean('has_training_center', false);
        $hours = (float) $request->input('hours', 1);
        
        return response()->json([
            'success' => true,
            'data' => \App\Services\PricingService::calculateBreakdown($hours, $hasReferral, $hasTrainingCenter),
            'summary' => \App\Services\PricingService::getPricingSummary($hasReferral, $hasTrainingCenter),
        ]);
    });
    
    Route::get('/pricing/rates', function() {
        return response()->json([
            'success' => true,
            'data' => [
                'client_rate_no_referral' => \App\Services\PricingService::CLIENT_RATE_NO_REFERRAL,
                'client_rate_with_referral' => \App\Services\PricingService::CLIENT_RATE_WITH_REFERRAL,
                'caregiver_rate' => \App\Services\PricingService::CAREGIVER_RATE,
            ]
        ]);
    });
    
    // ZIP code lookup (public)
    Route::get('/zipcode-lookup/{zip}', function($zip) {
        // Validate ZIP code format
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format'
            ], 400);
        }
        
        // ZIP code to location mapping for New York
        $zipCodeMap = [
            '10001' => 'Manhattan, NY', '10002' => 'Manhattan, NY', '10003' => 'Manhattan, NY', '10004' => 'Manhattan, NY',
            '10005' => 'Manhattan, NY', '10006' => 'Manhattan, NY', '10007' => 'Manhattan, NY', '10009' => 'Manhattan, NY',
            '10010' => 'Manhattan, NY', '10011' => 'Manhattan, NY', '10012' => 'Manhattan, NY', '10013' => 'Manhattan, NY',
            '10014' => 'Manhattan, NY', '10016' => 'Manhattan, NY', '10017' => 'Manhattan, NY', '10018' => 'Manhattan, NY',
            '10019' => 'Manhattan, NY', '10020' => 'Manhattan, NY', '10021' => 'Manhattan, NY', '10022' => 'Manhattan, NY',
            '10023' => 'Manhattan, NY', '10024' => 'Manhattan, NY', '10025' => 'Manhattan, NY', '10026' => 'Manhattan, NY',
            '10027' => 'Manhattan, NY', '10028' => 'Manhattan, NY', '10029' => 'Manhattan, NY', '10030' => 'Manhattan, NY',
            '10031' => 'Manhattan, NY', '10032' => 'Manhattan, NY', '10033' => 'Manhattan, NY', '10034' => 'Manhattan, NY',
            '10035' => 'Manhattan, NY', '10036' => 'Manhattan, NY', '10037' => 'Manhattan, NY', '10038' => 'Manhattan, NY',
            '10039' => 'Manhattan, NY', '10040' => 'Manhattan, NY', '10044' => 'Manhattan, NY', '10065' => 'Manhattan, NY',
            '10069' => 'Manhattan, NY', '10075' => 'Manhattan, NY', '10128' => 'Manhattan, NY', '10280' => 'Manhattan, NY',
            '11201' => 'Brooklyn, NY', '11203' => 'Brooklyn, NY', '11204' => 'Brooklyn, NY', '11205' => 'Brooklyn, NY',
            '11206' => 'Brooklyn, NY', '11207' => 'Brooklyn, NY', '11208' => 'Brooklyn, NY', '11209' => 'Brooklyn, NY',
            '11210' => 'Brooklyn, NY', '11211' => 'Brooklyn, NY', '11212' => 'Brooklyn, NY', '11213' => 'Brooklyn, NY',
            '11214' => 'Brooklyn, NY', '11215' => 'Brooklyn, NY', '11216' => 'Brooklyn, NY', '11217' => 'Brooklyn, NY',
            '11218' => 'Brooklyn, NY', '11219' => 'Brooklyn, NY', '11220' => 'Brooklyn, NY', '11221' => 'Brooklyn, NY',
            '11222' => 'Brooklyn, NY', '11223' => 'Brooklyn, NY', '11224' => 'Brooklyn, NY', '11225' => 'Brooklyn, NY',
            '11226' => 'Brooklyn, NY', '11228' => 'Brooklyn, NY', '11229' => 'Brooklyn, NY', '11230' => 'Brooklyn, NY',
            '11231' => 'Brooklyn, NY', '11232' => 'Brooklyn, NY', '11233' => 'Brooklyn, NY', '11234' => 'Brooklyn, NY',
            '11235' => 'Brooklyn, NY', '11236' => 'Brooklyn, NY', '11237' => 'Brooklyn, NY', '11238' => 'Brooklyn, NY',
            '11239' => 'Brooklyn, NY',
            '11354' => 'Flushing, NY', '11355' => 'Flushing, NY', '11356' => 'Flushing, NY', '11357' => 'Flushing, NY',
            '11358' => 'Flushing, NY', '11360' => 'Bayside, NY', '11361' => 'Bayside, NY', '11362' => 'Bayside, NY',
            '11363' => 'Bayside, NY', '11364' => 'Bayside, NY', '11365' => 'Fresh Meadows, NY', '11366' => 'Fresh Meadows, NY',
            '11367' => 'Fresh Meadows, NY', '11368' => 'Corona, NY', '11369' => 'East Elmhurst, NY', '11370' => 'Elmhurst, NY',
            '11371' => 'Elmhurst, NY', '11372' => 'Jackson Heights, NY', '11373' => 'Jackson Heights, NY', '11374' => 'Rego Park, NY',
            '11375' => 'Forest Hills, NY', '11377' => 'Woodside, NY', '11378' => 'Maspeth, NY', '11379' => 'Middle Village, NY',
            '11385' => 'Ridgewood, NY',
            '10451' => 'Bronx, NY', '10452' => 'Bronx, NY', '10453' => 'Bronx, NY', '10454' => 'Bronx, NY',
            '10455' => 'Bronx, NY', '10456' => 'Bronx, NY', '10457' => 'Bronx, NY', '10458' => 'Bronx, NY',
            '10459' => 'Bronx, NY', '10460' => 'Bronx, NY', '10461' => 'Bronx, NY', '10462' => 'Bronx, NY',
            '10463' => 'Bronx, NY', '10464' => 'Bronx, NY', '10465' => 'Bronx, NY', '10466' => 'Bronx, NY',
            '10467' => 'Bronx, NY', '10468' => 'Bronx, NY', '10469' => 'Bronx, NY', '10470' => 'Bronx, NY',
            '10471' => 'Bronx, NY', '10472' => 'Bronx, NY', '10473' => 'Bronx, NY', '10474' => 'Bronx, NY',
            '10475' => 'Bronx, NY',
            '10301' => 'Staten Island, NY', '10302' => 'Staten Island, NY', '10303' => 'Staten Island, NY',
            '10304' => 'Staten Island, NY', '10305' => 'Staten Island, NY', '10306' => 'Staten Island, NY',
            '10307' => 'Staten Island, NY', '10308' => 'Staten Island, NY', '10309' => 'Staten Island, NY',
            '10310' => 'Staten Island, NY', '10311' => 'Staten Island, NY', '10312' => 'Staten Island, NY',
            '10314' => 'Staten Island, NY'
        ];
        
        $location = $zipCodeMap[$zip] ?? 'New York, NY';
        
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip
        ]);
    });
});

// Public API Routes (no authentication required)  
Route::prefix('api')->middleware(['web'])->group(function () {
    // ZIP code lookup (public)
    Route::get('/zipcode-lookup/{zip}', function($zip) {
        // Validate ZIP code format
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format'
            ], 400);
        }
        
        // ZIP code to location mapping for New York
        $zipCodeMap = [
            '10001' => 'Manhattan, NY', '10002' => 'Manhattan, NY', '10003' => 'Manhattan, NY', '10004' => 'Manhattan, NY',
            '10005' => 'Manhattan, NY', '10006' => 'Manhattan, NY', '10007' => 'Manhattan, NY', '10009' => 'Manhattan, NY',
            '10010' => 'Manhattan, NY', '10011' => 'Manhattan, NY', '10012' => 'Manhattan, NY', '10013' => 'Manhattan, NY',
            '10014' => 'Manhattan, NY', '10016' => 'Manhattan, NY', '10017' => 'Manhattan, NY', '10018' => 'Manhattan, NY',
            '10019' => 'Manhattan, NY', '10020' => 'Manhattan, NY', '10021' => 'Manhattan, NY', '10022' => 'Manhattan, NY',
            '10023' => 'Manhattan, NY', '10024' => 'Manhattan, NY', '10025' => 'Manhattan, NY', '10026' => 'Manhattan, NY',
            '10027' => 'Manhattan, NY', '10028' => 'Manhattan, NY', '10029' => 'Manhattan, NY', '10030' => 'Manhattan, NY',
            '10031' => 'Manhattan, NY', '10032' => 'Manhattan, NY', '10033' => 'Manhattan, NY', '10034' => 'Manhattan, NY',
            '10035' => 'Manhattan, NY', '10036' => 'Manhattan, NY', '10037' => 'Manhattan, NY', '10038' => 'Manhattan, NY',
            '10039' => 'Manhattan, NY', '10040' => 'Manhattan, NY', '10044' => 'Manhattan, NY', '10065' => 'Manhattan, NY',
            '10069' => 'Manhattan, NY', '10075' => 'Manhattan, NY', '10128' => 'Manhattan, NY', '10280' => 'Manhattan, NY',
            '11201' => 'Brooklyn, NY', '11203' => 'Brooklyn, NY', '11204' => 'Brooklyn, NY', '11205' => 'Brooklyn, NY',
            '11206' => 'Brooklyn, NY', '11207' => 'Brooklyn, NY', '11208' => 'Brooklyn, NY', '11209' => 'Brooklyn, NY',
            '11210' => 'Brooklyn, NY', '11211' => 'Brooklyn, NY', '11212' => 'Brooklyn, NY', '11213' => 'Brooklyn, NY',
            '11214' => 'Brooklyn, NY', '11215' => 'Brooklyn, NY', '11216' => 'Brooklyn, NY', '11217' => 'Brooklyn, NY',
            '11218' => 'Brooklyn, NY', '11219' => 'Brooklyn, NY', '11220' => 'Brooklyn, NY', '11221' => 'Brooklyn, NY',
            '11222' => 'Brooklyn, NY', '11223' => 'Brooklyn, NY', '11224' => 'Brooklyn, NY', '11225' => 'Brooklyn, NY',
            '11226' => 'Brooklyn, NY', '11228' => 'Brooklyn, NY', '11229' => 'Brooklyn, NY', '11230' => 'Brooklyn, NY',
            '11231' => 'Brooklyn, NY', '11232' => 'Brooklyn, NY', '11233' => 'Brooklyn, NY', '11234' => 'Brooklyn, NY',
            '11235' => 'Brooklyn, NY', '11236' => 'Brooklyn, NY', '11237' => 'Brooklyn, NY', '11238' => 'Brooklyn, NY',
            '11239' => 'Brooklyn, NY',
            '11354' => 'Flushing, NY', '11355' => 'Flushing, NY', '11356' => 'Flushing, NY', '11357' => 'Flushing, NY',
            '11358' => 'Flushing, NY', '11360' => 'Bayside, NY', '11361' => 'Bayside, NY', '11362' => 'Bayside, NY',
            '11363' => 'Bayside, NY', '11364' => 'Bayside, NY', '11365' => 'Fresh Meadows, NY', '11366' => 'Fresh Meadows, NY',
            '11367' => 'Fresh Meadows, NY', '11368' => 'Corona, NY', '11369' => 'East Elmhurst, NY', '11370' => 'Elmhurst, NY',
            '11371' => 'Elmhurst, NY', '11372' => 'Jackson Heights, NY', '11373' => 'Jackson Heights, NY', '11374' => 'Rego Park, NY',
            '11375' => 'Forest Hills, NY', '11377' => 'Woodside, NY', '11378' => 'Maspeth, NY', '11379' => 'Middle Village, NY',
            '11385' => 'Ridgewood, NY',
            '10451' => 'Bronx, NY', '10452' => 'Bronx, NY', '10453' => 'Bronx, NY', '10454' => 'Bronx, NY',
            '10455' => 'Bronx, NY', '10456' => 'Bronx, NY', '10457' => 'Bronx, NY', '10458' => 'Bronx, NY',
            '10459' => 'Bronx, NY', '10460' => 'Bronx, NY', '10461' => 'Bronx, NY', '10462' => 'Bronx, NY',
            '10463' => 'Bronx, NY', '10464' => 'Bronx, NY', '10465' => 'Bronx, NY', '10466' => 'Bronx, NY',
            '10467' => 'Bronx, NY', '10468' => 'Bronx, NY', '10469' => 'Bronx, NY', '10470' => 'Bronx, NY',
            '10471' => 'Bronx, NY', '10472' => 'Bronx, NY', '10473' => 'Bronx, NY', '10474' => 'Bronx, NY',
            '10475' => 'Bronx, NY',
            '10301' => 'Staten Island, NY', '10302' => 'Staten Island, NY', '10303' => 'Staten Island, NY',
            '10304' => 'Staten Island, NY', '10305' => 'Staten Island, NY', '10306' => 'Staten Island, NY',
            '10307' => 'Staten Island, NY', '10308' => 'Staten Island, NY', '10309' => 'Staten Island, NY',
            '10310' => 'Staten Island, NY', '10311' => 'Staten Island, NY', '10312' => 'Staten Island, NY',
            '10314' => 'Staten Island, NY'
        ];
        
        $location = $zipCodeMap[$zip] ?? 'New York, NY';
        
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip
        ]);
    });
    
    // Location data (public)
    Route::get('/location-data', function() {
        $jsonPath = storage_path('app/data/ny_accurate_counties.json');
        if (file_exists($jsonPath)) {
            return response()->json(json_decode(file_get_contents($jsonPath), true));
        }
        return response()->json(['error' => 'Location data not found'], 404);
    });
    
    // Check if email exists (public)
    Route::get('/check-email-exists/{email}', function($email) {
        $exists = \App\Models\User::where('email', $email)->exists();
        return response()->json([
            'exists' => $exists,
            'email' => $email
        ]);
    });
});

// API Routes with Authentication
Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    Route::middleware(['user.type:admin'])->group(function () {
        Route::get('/admin/stats', [\App\Http\Controllers\DashboardController::class, 'adminStats']);
        Route::get('/admin/users', [\App\Http\Controllers\DashboardController::class, 'adminUsers']);
        Route::post('/admin/users', [\App\Http\Controllers\AdminController::class, 'storeUser']);
        Route::put('/admin/users/{id}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::put('/admin/users/{id}/status', [\App\Http\Controllers\AdminController::class, 'updateUserStatus']);
        Route::put('/admin/caregivers/{id}/status', [\App\Http\Controllers\AdminController::class, 'updateCaregiverStatus']);
        Route::delete('/admin/users/{id}', [\App\Http\Controllers\AdminController::class, 'deleteUser']);
        Route::get('/admin/applications', [\App\Http\Controllers\AdminController::class, 'getApplications']);
        Route::post('/admin/applications/{id}/approve', [\App\Http\Controllers\AdminController::class, 'approveApplication']);
        Route::post('/admin/applications/{id}/reject', [\App\Http\Controllers\AdminController::class, 'rejectApplication']);
        
        // Marketing Staff Management
        Route::get('/admin/marketing-staff', [\App\Http\Controllers\AdminController::class, 'getMarketingStaff']);
        Route::post('/admin/marketing-staff', [\App\Http\Controllers\AdminController::class, 'storeMarketingStaff']);
        Route::put('/admin/marketing-staff/{id}', [\App\Http\Controllers\AdminController::class, 'updateMarketingStaff']);
        Route::delete('/admin/marketing-staff/{id}', [\App\Http\Controllers\AdminController::class, 'deleteMarketingStaff']);
        
        // Admin Staff Management
        Route::get('/admin/admin-staff', [\App\Http\Controllers\AdminController::class, 'getAdminStaff']);
        Route::post('/admin/admin-staff', [\App\Http\Controllers\AdminController::class, 'storeAdminStaff']);
        Route::put('/admin/admin-staff/{id}', [\App\Http\Controllers\AdminController::class, 'updateAdminStaff']);
        Route::delete('/admin/admin-staff/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAdminStaff']);
        
        // Training Center Management
        Route::get('/admin/training-centers', [\App\Http\Controllers\AdminController::class, 'getTrainingCenters']);
        Route::post('/admin/training-centers', [\App\Http\Controllers\AdminController::class, 'storeTrainingCenter']);
        Route::put('/admin/training-centers/{id}', [\App\Http\Controllers\AdminController::class, 'updateTrainingCenter']);
        Route::delete('/admin/training-centers/{id}', [\App\Http\Controllers\AdminController::class, 'deleteTrainingCenter']);
        Route::get('/admin/training-centers/{id}/caregivers', [\App\Http\Controllers\AdminController::class, 'getTrainingCenterCaregivers']);
        
        Route::get('/admin/password-resets', [\App\Http\Controllers\AdminController::class, 'getPasswordResets']);
        Route::post('/admin/password-resets/{id}/process', [\App\Http\Controllers\AdminController::class, 'processPasswordReset']);
        Route::get('/admin/announcements', [\App\Http\Controllers\AdminController::class, 'getAnnouncements']);
        Route::post('/admin/announcements', [\App\Http\Controllers\AdminController::class, 'storeAnnouncement']);
        Route::post('/admin/test-email', [\App\Http\Controllers\AdminController::class, 'sendTestEmail']);
        
        // Booking assignment management
        Route::post('/bookings/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignCaregivers']);
        Route::post('/bookings/{id}/unassign', [\App\Http\Controllers\AdminController::class, 'unassignCaregiver']);
        
        // Payment & analytics
        Route::get('/admin/payment-stats', [\App\Http\Controllers\AdminController::class, 'getPaymentStats']);
        Route::get('/admin/transactions', [\App\Http\Controllers\AdminController::class, 'getTransactions']);
        Route::get('/admin/client-payments', [\App\Http\Controllers\AdminController::class, 'getClientPayments']);
        Route::get('/admin/caregiver-salaries', [\App\Http\Controllers\AdminController::class, 'getCaregiverSalaries']);
        Route::post('/admin/pay-caregiver', [\App\Http\Controllers\AdminController::class, 'payCaregiver']);
        
        // Commission payments
        Route::get('/admin/marketing-commissions', [\App\Http\Controllers\AdminController::class, 'getMarketingCommissions']);
        Route::post('/admin/pay-marketing-commission/{id}', [\App\Http\Controllers\AdminController::class, 'payMarketingCommission']);
        Route::get('/admin/training-commissions', [\App\Http\Controllers\AdminController::class, 'getTrainingCommissions']);
        Route::post('/admin/pay-training-commission/{id}', [\App\Http\Controllers\AdminController::class, 'payTrainingCommission']);
        
        // Payment Monitoring Dashboard
        Route::get('/admin/money-flow-dashboard', [\App\Http\Controllers\PaymentMonitoringController::class, 'getMoneyFlowDashboard']);
        Route::get('/admin/verify-payout/{id}', [\App\Http\Controllers\PaymentMonitoringController::class, 'verifyPayoutDetails']);
        Route::get('/admin/reconciliation-report', [\App\Http\Controllers\PaymentMonitoringController::class, 'getReconciliationReport']);
        
        // PDF Report Generation
        Route::get('/admin/financial-report/pdf', [\App\Http\Controllers\AdminReportController::class, 'generateFinancialReport']);
        
        Route::get('/admin/top-performers', [\App\Http\Controllers\AdminController::class, 'getTopPerformers']);
        Route::get('/admin/recent-activity', [\App\Http\Controllers\AdminController::class, 'getRecentActivity']);
    // All bookings for admin
    Route::get('/admin/bookings', [\App\Http\Controllers\AdminController::class, 'getAllBookings']);
        Route::get('/admin/time-tracking', [\App\Http\Controllers\TimeTrackingController::class, 'getAdminTimeTracking']);
        
        // Referral code management (admin)
        Route::get('/referral-codes', [\App\Http\Controllers\ReferralCodeController::class, 'index']);
        Route::post('/referral-codes', [\App\Http\Controllers\ReferralCodeController::class, 'store']);
        Route::put('/referral-codes/{id}', [\App\Http\Controllers\ReferralCodeController::class, 'update']);
        Route::get('/referral-codes/commission-stats', [\App\Http\Controllers\ReferralCodeController::class, 'getCommissionStats']);
    });
    
    // === MARKETING STAFF ROUTES ===
    Route::middleware(['user.type:marketing'])->group(function () {
        Route::get('/marketing/stats', function(\Illuminate\Http\Request $request) {
            $userId = auth()->id();
            
            $referralCode = \App\Models\ReferralCode::where('user_id', $userId)->first();
            
            if (!$referralCode) {
                return response()->json([
                    'my_clients' => 0,
                    'active_bookings' => 0,
                    'total_commission' => 0,
                    'pending_commission' => 0,
                    'account_balance' => 0,
                    'clients' => [],
                    'weekly_summary' => [
                        'clients_acquired' => 0,
                        'target' => 10,
                        'previous_payout' => 0,
                        'previous_payout_date' => null
                    ]
                ]);
            }
            
            $bookings = \App\Models\Booking::where('referral_code_id', $referralCode->id)
                ->with('client')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $completedBookings = $bookings->where('status', 'completed');
            $activeBookings = $bookings->whereIn('status', ['approved', 'confirmed']);
            
            $totalCommission = $completedBookings->sum(function($b) {
                $hours = 8;
                if (preg_match('/(\d+)\s*Hours?/i', $b->duty_type, $matches)) {
                    $hours = (int)$matches[1];
                }
                return $hours * $b->duration_days * 1.00;
            });
            
            $pendingCommission = $activeBookings->sum(function($b) {
                $hours = 8;
                if (preg_match('/(\d+)\s*Hours?/i', $b->duty_type, $matches)) {
                    $hours = (int)$matches[1];
                }
                return $hours * $b->duration_days * 1.00;
            });
            
            $clients = $bookings->groupBy('client_id')->map(function($clientBookings, $clientId) {
                $client = $clientBookings->first()->client;
                $completed = $clientBookings->where('status', 'completed');
                $totalHours = $completed->sum(function($b) {
                    $hours = 8;
                    if (preg_match('/(\d+)\s*Hours?/i', $b->duty_type, $matches)) {
                        $hours = (int)$matches[1];
                    }
                    return $hours * $b->duration_days;
                });
                $totalSpent = $completed->sum(function($b) {
                    $hours = 8;
                    if (preg_match('/(\d+)\s*Hours?/i', $b->duty_type, $matches)) {
                        $hours = (int)$matches[1];
                    }
                    return $hours * $b->duration_days * ($b->hourly_rate ?: 40);
                });
                $commission = $totalHours * 1.00;
                
                return [
                    'id' => $clientId,
                    'name' => $client->name ?? 'Unknown',
                    'email' => $client->email ?? '',
                    'phone' => $client->phone ?? '',
                    'borough' => $client->borough ?? 'N/A',
                    'status' => $clientBookings->whereIn('status', ['approved', 'confirmed'])->count() > 0 ? 'Active' : 'Inactive',
                    'totalHours' => $totalHours,
                    'totalSpent' => number_format($totalSpent, 2),
                    'contractDate' => $clientBookings->min('created_at') ? \Carbon\Carbon::parse($clientBookings->min('created_at'))->format('Y-m-d') : '-',
                    'commission' => number_format($commission, 2)
                ];
            })->values();
            
            $thisWeekClients = $bookings->filter(function($b) {
                return $b->created_at >= now()->startOfWeek();
            })->pluck('client_id')->unique()->count();
            
            return response()->json([
                'my_clients' => $bookings->pluck('client_id')->unique()->count(),
                'active_bookings' => $activeBookings->count(),
                'total_commission' => $totalCommission,
                'pending_commission' => $pendingCommission,
                'account_balance' => $totalCommission,
                'clients' => $clients,
                'weekly_summary' => [
                    'clients_acquired' => $thisWeekClients,
                    'target' => 10,
                    'previous_payout' => $totalCommission * 0.8,
                    'previous_payout_date' => now()->subWeek()->format('M d, Y')
                ],
                'referral_code' => $referralCode->code
            ]);
        });
    });
    
    // === TRAINING CENTER ROUTES ===
    Route::middleware(['user.type:training,training_center'])->group(function () {
        // Get pending caregiver requests
        Route::get('/training/pending-caregivers', function(\Illuminate\Http\Request $request) {
            $userId = auth()->id();
            
            $pendingCaregivers = \App\Models\Caregiver::where('training_center_id', $userId)
                ->where('training_center_approval_status', 'pending')
                ->with('user')
                ->get()
                ->map(function ($caregiver) {
                    return [
                        'id' => $caregiver->id,
                        'name' => $caregiver->user->name ?? 'Unknown',
                        'email' => $caregiver->user->email ?? '',
                        'phone' => $caregiver->user->phone ?? '',
                        'years_experience' => $caregiver->years_experience ?? 0,
                        'specializations' => $caregiver->specializations ?? [],
                        'bio' => $caregiver->bio ?? '',
                        'requested_at' => $caregiver->updated_at ? $caregiver->updated_at->format('M d, Y') : ''
                    ];
                });
            
            return response()->json(['pendingCaregivers' => $pendingCaregivers]);
        });
        
        // Approve caregiver request
        Route::post('/training/caregivers/{id}/approve', function($id) {
            $userId = auth()->id();
            
            $caregiver = \App\Models\Caregiver::where('id', $id)
                ->where('training_center_id', $userId)
                ->where('training_center_approval_status', 'pending')
                ->firstOrFail();
            
            $caregiver->update(['training_center_approval_status' => 'approved']);
            
            return response()->json(['success' => true, 'message' => 'Caregiver approved successfully']);
        });
        
        // Reject caregiver request
        Route::post('/training/caregivers/{id}/reject', function($id) {
            $userId = auth()->id();
            
            $caregiver = \App\Models\Caregiver::where('id', $id)
                ->where('training_center_id', $userId)
                ->where('training_center_approval_status', 'pending')
                ->firstOrFail();
            
            $caregiver->update([
                'training_center_approval_status' => 'rejected',
                'training_center_id' => null,
                'has_training_center' => false
            ]);
            
            return response()->json(['success' => true, 'message' => 'Caregiver request rejected']);
        });
        
        Route::get('/training/stats', function(\Illuminate\Http\Request $request) {
            $userId = auth()->id();
            
            // Only show approved caregivers
            $caregivers = \App\Models\Caregiver::where('training_center_id', $userId)
                ->where('training_center_approval_status', 'approved')
                ->with('user')
                ->get();
            
            $totalHours = 0;
            $totalRevenue = 0;
            $caregiverData = [];
            
            foreach ($caregivers as $caregiver) {
                $timeTrackings = \Illuminate\Support\Facades\DB::table('time_trackings')
                    ->where('caregiver_id', $caregiver->id)
                    ->where('status', 'completed')
                    ->get();
                
                $hours = $timeTrackings->sum('hours_worked');
                $earnings = $hours * 0.50;
                $totalHours += $hours;
                $totalRevenue += $earnings;
                
                $hasActiveBooking = \App\Models\BookingAssignment::where('caregiver_id', $caregiver->id)
                    ->where('status', 'assigned')
                    ->whereHas('booking', function($q) {
                        $q->whereIn('status', ['approved', 'confirmed']);
                    })
                    ->exists();
                
                $caregiverData[] = [
                    'id' => $caregiver->id,
                    'name' => $caregiver->user->name ?? 'Unknown',
                    'email' => $caregiver->user->email ?? '',
                    'phone' => $caregiver->user->phone ?? '',
                    'borough' => $caregiver->user->borough ?? 'N/A',
                    'course' => 'Certified Care Training',
                    'certification' => 'Certified',
                    'earnings' => number_format($earnings, 2),
                    'status' => $hasActiveBooking ? 'Ongoing Contract' : 'No Contract'
                ];
            }
            
            $thisWeekDeployed = \App\Models\BookingAssignment::whereIn('caregiver_id', $caregivers->pluck('id'))
                ->where('assigned_at', '>=', now()->startOfWeek())
                ->count();
            
            return response()->json([
                'total_caregivers' => $caregivers->count(),
                'total_revenue' => $totalRevenue,
                'total_hours' => $totalHours,
                'account_balance' => $totalRevenue,
                'caregivers' => $caregiverData,
                'weekly_summary' => [
                    'deployed_caregivers' => $thisWeekDeployed,
                    'target' => 10,
                    'previous_payout' => $totalRevenue * 0.8,
                    'previous_payout_date' => now()->subWeek()->format('M d, Y')
                ]
            ]);
        });
    });
});

// Development-only routes (should be disabled in production)
if (app()->environment('local', 'development')) {
    // Artisan command to update booking status
    Route::get('/api/update-booking-status', function() {
        \Artisan::call('bookings:update-status');
        return response()->json([
            'success' => true,
            'message' => 'Booking status update completed',
            'output' => \Artisan::output()
        ]);
    });
    
    // Reseed bookings
    Route::get('/reseed-bookings', function() {
        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\BookingAssignment::truncate();
            \App\Models\Booking::truncate();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            (new \Database\Seeders\BookingSeeder())->run();
            return 'Bookings and assignments reseeded successfully!';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });
    
    // Database migrations
Route::get('/migrate-names', function() {
    try {
        \Illuminate\Support\Facades\Schema::table('clients', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('clients', 'first_name')) {
                $table->string('first_name')->nullable()->after('user_id');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('clients', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
        
        \Illuminate\Support\Facades\Schema::table('caregivers', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('caregivers', 'first_name')) {
                $table->string('first_name')->nullable()->after('user_id');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('caregivers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
        
            return 'First name and last name columns added!';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/migrate-status', function() {
    try {
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('Active')->after('user_type');
            }
        });
        return 'Status column added to users table!';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
}

// ============================================
// STRIPE PAYMENT INTEGRATION ROUTES
// ============================================

Route::middleware(['auth'])->prefix('api/stripe')->group(function () {
    // Client Payment Intents & Methods (NEW - Enhanced with Payment Element)
    Route::post('/create-payment-intent', [App\Http\Controllers\ClientPaymentController::class, 'createPaymentIntent']);
    Route::post('/create-setup-intent', [App\Http\Controllers\ClientPaymentController::class, 'createSetupIntent']);
    Route::post('/attach-payment-method', [App\Http\Controllers\ClientPaymentController::class, 'attachPaymentMethod']);
    Route::post('/charge-saved-method', [App\Http\Controllers\ClientPaymentController::class, 'chargeSavedMethod']);
    Route::delete('/delete-payment-method', [App\Http\Controllers\ClientPaymentController::class, 'deletePaymentMethod']);
    
    // Legacy Client Payment Methods (keep for backward compatibility)
    Route::get('/setup-intent', [App\Http\Controllers\StripeController::class, 'createSetupIntent']);
    Route::post('/save-payment-method', [App\Http\Controllers\StripeController::class, 'savePaymentMethod']);
    
    // Caregiver/Partner Bank Connection
    Route::post('/create-onboarding-link', [App\Http\Controllers\StripeController::class, 'createOnboardingLink']);
    Route::post('/create-account-session', [App\Http\Controllers\StripeController::class, 'createAccountSession']);
    Route::post('/connect-bank-account', [App\Http\Controllers\StripeController::class, 'connectBankAccount']);
    Route::post('/connect-payout-method', [App\Http\Controllers\StripeController::class, 'connectPayoutMethod']);
    Route::get('/connection-status', [App\Http\Controllers\StripeController::class, 'getConnectionStatus']);
    
    // Marketing Staff Bank Connection
    Route::post('/connect-bank-account-marketing', [App\Http\Controllers\StripeController::class, 'connectMarketingBankAccount']);
    
    // Training Center Bank Connection
    Route::post('/connect-bank-account-training', [App\Http\Controllers\StripeController::class, 'connectTrainingBankAccount']);
    
    // Admin Payment Processing
    Route::post('/process-payment/{timeTrackingId}', [App\Http\Controllers\StripeController::class, 'processPayment'])
        ->middleware('user.type:admin,adminstaff');
    Route::get('/payment-preview/{timeTrackingId}', [App\Http\Controllers\StripeController::class, 'getPaymentPreview'])
        ->middleware('user.type:admin,adminstaff');
    Route::get('/pending-payments', [App\Http\Controllers\StripeController::class, 'getPendingPayments'])
        ->middleware('user.type:admin,adminstaff');
    Route::post('/batch-process', [App\Http\Controllers\StripeController::class, 'batchProcessPayments'])
        ->middleware('user.type:admin,adminstaff');
    
    // Admin Commission Payments
    Route::post('/admin/pay-marketing-commission/{userId}', [App\Http\Controllers\StripeController::class, 'payMarketingCommission'])
        ->middleware('user.type:admin,adminstaff');
    Route::post('/admin/pay-training-commission/{userId}', [App\Http\Controllers\StripeController::class, 'payTrainingCommission'])
        ->middleware('user.type:admin,adminstaff');
});

// Client Saved Payment Methods & Booking Updates API
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/client/payment-methods', [App\Http\Controllers\ClientPaymentController::class, 'getPaymentMethods']);
    
    // Booking payment status update
    Route::post('/bookings/update-payment-status', function(\Illuminate\Http\Request $request) {
        try {
            \Log::info('Payment status update request received', [
                'booking_id' => $request->input('booking_id'),
                'payment_intent_id' => $request->input('payment_intent_id')
            ]);
            
            $bookingId = $request->input('booking_id');
            $paymentIntentId = $request->input('payment_intent_id');
            
            if (!$bookingId) {
                return response()->json(['success' => false, 'message' => 'No booking ID provided']);
            }
            
            $booking = App\Models\Booking::find($bookingId);
            
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
            }
            
            // Update the booking record
            $booking->update([
                'payment_status' => 'paid',
                'payment_intent_id' => $paymentIntentId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_date' => now()
            ]);
            
            // Calculate the booking amount
            $hours = 8; // Default
            if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
                $hours = (int)$matches[1];
            }
            $rate = $booking->assigned_hourly_rate ?: 28;
            $amount = $hours * $booking->duration_days * $rate;
            
            // Platform fee calculation (10%)
            $platformFee = $amount * 0.10;
            $caregiverAmount = $amount * 0.90;
            
            // Create a Payment record if it doesn't exist
            $existingPayment = App\Models\Payment::where('booking_id', $booking->id)
                ->where('status', 'completed')
                ->first();
            
            if (!$existingPayment) {
                \DB::table('payments')->insert([
                    'booking_id' => $booking->id,
                    'client_id' => $booking->client_id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount,
                    'payment_method' => 'credit_card',
                    'status' => 'completed',
                    'transaction_id' => $paymentIntentId,
                    'paid_at' => now(),
                    'notes' => 'Stripe payment via Stripe Elements',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info('Payment record created for booking #' . $booking->id, [
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount,
                    'payment_intent_id' => $paymentIntentId
                ]);
            }
            
            return response()->json([
                'success' => true,
                'booking' => $booking,
                'receipt_url' => route('receipt.payment', ['bookingId' => $booking->id])
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Payment status update error: ' . $e->getMessage(), [
                'booking_id' => $request->input('booking_id'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    });
});

// Stripe Webhook (no auth required)
Route::post('/api/stripe/webhook', [App\Http\Controllers\StripeController::class, 'webhook']);
