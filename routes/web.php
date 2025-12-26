<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CaregiverController;

// Public Routes
Route::get('/', [LandingController::class, 'index']);
Route::get('/api/landing/stats', [LandingController::class, 'stats']); // Public stats endpoint
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']); // Sitemap
Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', function () {
    return view('register');
})->name('register');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
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
    return view('client-dashboard');
});
    Route::get('/client/dashboard-vue', function () {
        if (auth()->user()->user_type !== 'client') {
            return redirect('/login');
        }
        return view('client-dashboard-vue');
    })->name('client.dashboard');
    
    // Caregiver Dashboard - accessible by caregivers
Route::get('/caregiver/dashboard', function () {
        if (auth()->user()->user_type !== 'caregiver') {
            return redirect('/login');
        }
    return view('caregiver-dashboard');
});
Route::get('/caregiver/dashboard-vue', function () {
        if (auth()->user()->user_type !== 'caregiver') {
            return redirect('/login');
        }
    return view('caregiver-dashboard-vue');
    })->name('caregiver.dashboard');
    
    // Admin Dashboard - accessible by admins only
Route::get('/admin/dashboard-vue', function () {
        if (auth()->user()->user_type !== 'admin') {
            return redirect('/login');
        }
    return view('admin-dashboard-vue');
    })->name('admin.dashboard');
Route::get('/admin/settings', [AdminController::class, 'settings']);
Route::post('/admin/settings', [AdminController::class, 'updateSettings']);

    // Marketing Dashboard - accessible by marketing staff
    Route::get('/marketing/dashboard-vue', function () {
        if (auth()->user()->user_type !== 'marketing') {
            return redirect('/login');
        }
        return view('marketing-dashboard-vue');
    })->name('marketing.dashboard');
    
    // Training Dashboard - accessible by training centers
    Route::get('/training/dashboard-vue', function () {
        if (!in_array(auth()->user()->user_type, ['training', 'training_center'])) {
            return redirect('/login');
        }
        return view('training-dashboard-vue');
    })->name('training.dashboard');
    
    // Book Service Form - accessible by clients
Route::get('/book-service', [BookingController::class, 'create']);
Route::post('/bookings', [BookingController::class, 'store']);
    
    // Profile - accessible by all authenticated users
Route::get('/profile', [ProfileController::class, 'show']);
Route::post('/profile/update', [ProfileController::class, 'update']);

    // Caregiver available clients page
Route::get('/available-clients', [CaregiverController::class, 'availableClients']);
});

// API Routes with Authentication
Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    
    // Booking endpoints
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy']);
    Route::get('/bookings/{id}/assignments', [\App\Http\Controllers\BookingController::class, 'getAssignments']);
    Route::get('/bookings-with-assignments', [\App\Http\Controllers\BookingController::class, 'indexWithAssignments']);
    Route::post('/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
    
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
    
    // Receipts
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
    
    // Location data (public)
    Route::get('/location-data', function() {
        $jsonPath = storage_path('app/data/ny_accurate_counties.json');
        if (file_exists($jsonPath)) {
            return response()->json(json_decode(file_get_contents($jsonPath), true));
        }
        return response()->json(['error' => 'Location data not found'], 404);
    });
    
    // === ADMIN ONLY ROUTES ===
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
        
        // Booking assignment management
        Route::post('/bookings/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignCaregivers']);
        Route::post('/bookings/{id}/unassign', [\App\Http\Controllers\AdminController::class, 'unassignCaregiver']);
        
        // Payment & analytics
        Route::get('/admin/payment-stats', [\App\Http\Controllers\AdminController::class, 'getPaymentStats']);
        Route::get('/admin/transactions', [\App\Http\Controllers\AdminController::class, 'getTransactions']);
        Route::get('/admin/client-payments', [\App\Http\Controllers\AdminController::class, 'getClientPayments']);
        Route::get('/admin/caregiver-salaries', [\App\Http\Controllers\AdminController::class, 'getCaregiverSalaries']);
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
        Route::get('/training/stats', function(\Illuminate\Http\Request $request) {
            $userId = auth()->id();
            
            $caregivers = \App\Models\Caregiver::where('training_center_id', $userId)
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
