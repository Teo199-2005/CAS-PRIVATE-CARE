<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\Payment;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function clientStats(Request $request): JsonResponse
    {
        // Require authentication - no more demo fallbacks
        $clientId = $request->query('client_id') ?: auth()->id();
        
        if (!$clientId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        // Get all bookings for the client with assignments loaded
        $allBookings = Booking::where('client_id', $clientId)
            ->with([
                'assignments.caregiver.user:id,name,email,phone',
                'assignments.caregiver:id,user_id',
                'payments' // Load payment relationship
            ])
            ->get();
        $confirmedBookings = $allBookings->where('status', 'confirmed');
        $completedBookings = $allBookings->where('status', 'completed');
        $approvedBookings = $allBookings->where('status', 'approved');
        
        // Calculate total spent from completed bookings AND bookings with successful payments
        // Check both Payment model records AND booking payment_status field
        $paidBookings = $allBookings->filter(function($booking) {
            return $booking->payments->where('status', 'completed')->isNotEmpty()
                || $booking->payment_status === 'paid';
        });
        $spendingBookings = $completedBookings->merge($paidBookings)->unique('id');
        
        $totalSpent = $spendingBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type);
            $calculatedSpent = $hours * $booking->duration_days * $rate;
            
            // Debug: Log the calculation for troubleshooting
            \Log::info('Booking calculation', [
                'booking_id' => $booking->id,
                'service_type' => $booking->service_type,
                'duty_type' => $booking->duty_type,
                'hours' => $hours,
                'duration_days' => $booking->duration_days,
                'hourly_rate' => $booking->hourly_rate,
                'default_rate' => $this->getDefaultRate($booking->service_type),
                'final_rate' => $rate,
                'calculated_spent' => $calculatedSpent,
                'has_payment_record' => $booking->payments->where('status', 'completed')->isNotEmpty(),
                'booking_payment_status' => $booking->payment_status
            ]);
            
            return $calculatedSpent;
        });
        
        // Calculate this month's spending
        $thisMonthBookings = $spendingBookings->filter(function($booking) {
            return $booking->service_date >= now()->startOfMonth();
        });
        
        $thisMonthSpent = $thisMonthBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type);
            return $hours * $booking->duration_days * $rate;
        });
        
        // Calculate average monthly spending
        $avgMonthlySpent = $totalSpent > 0 ? $totalSpent / max(1, now()->diffInMonths($allBookings->min('created_at')) + 1) : 0;
        
        // Calculate total hours from completed bookings AND paid bookings
        $totalHours = $spendingBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            return $hours * $booking->duration_days;
        });
        
        // Count active bookings (approved/confirmed/in_progress AND has completed payment)
        $activeBookings = $allBookings->filter(function($booking) {
            $hasCompletedPayment = $booking->payments->where('status', 'completed')->isNotEmpty() 
                || $booking->payment_status === 'paid';
            return in_array($booking->status, ['approved', 'confirmed', 'in_progress']) 
                && $hasCompletedPayment;
        })->count();
        
        // Calculate amount due from approved/confirmed (active) bookings that are NOT yet paid
        $activeBookingsList = $allBookings->filter(function($booking) {
            return in_array($booking->status, ['approved', 'confirmed', 'in_progress']);
        });
        
        $amountDue = $activeBookingsList->filter(function($booking) {
            // Only include bookings that don't have a completed payment (check both sources)
            return $booking->payments->where('status', 'completed')->isEmpty() 
                && $booking->payment_status !== 'paid';
        })
            ->sum(function($booking) {
                $hours = $this->extractHours($booking->duty_type);
                $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type, !empty($booking->referral_code));
                return $hours * $booking->duration_days * $rate;
            });
        
        // Calculate this month's amount due (also exclude paid bookings)
        $thisMonthAmountDue = $activeBookingsList->filter(function($booking) {
            // Only include bookings that don't have a completed payment (check both sources)
            return $booking->payments->where('status', 'completed')->isEmpty()
                && $booking->payment_status !== 'paid';
        })
            ->filter(function($booking) {
                $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                $endDate = $serviceDate->copy()->addDays($booking->duration_days);
                return $serviceDate->month === now()->month || $endDate->month === now()->month;
            })->sum(function($booking) {
                $hours = $this->extractHours($booking->duty_type);
                $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type, !empty($booking->referral_code));
                return $hours * $booking->duration_days * $rate;
            });
        
        // Get coverage date range from active bookings
        $coverageStart = null;
        $coverageEnd = null;
        if ($activeBookingsList->count() > 0) {
            $coverageStart = $activeBookingsList->min('service_date');
            $coverageEnd = $activeBookingsList->map(function($booking) {
                return \Carbon\Carbon::parse($booking->service_date)->addDays($booking->duration_days)->format('Y-m-d');
            })->max();
        }
        
        // Build transaction history from completed/approved bookings
        $transactions = $allBookings->sortByDesc('service_date')->take(10)->map(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type, !empty($booking->referral_code));
            $amount = $hours * $booking->duration_days * $rate;
            
            // Get assigned caregiver name
            $caregiverName = 'Pending Assignment';
            if ($booking->assignments && $booking->assignments->count() > 0) {
                $assignment = $booking->assignments->first();
                if ($assignment->caregiver && $assignment->caregiver->user) {
                    $caregiverName = $assignment->caregiver->user->name;
                }
            }
            
            return [
                'id' => $booking->id,
                'date' => \Carbon\Carbon::parse($booking->service_date)->format('M d, Y'),
                'service' => $booking->service_type,
                'caregiver' => $caregiverName,
                'amount' => number_format($amount, 2),
                'status' => ucfirst($booking->status),
            ];
        })->values()->toArray();
        
        return response()->json([
            'total_bookings' => $allBookings->count(),
            'active_bookings' => $activeBookings,
            'ongoing_contracts' => $activeBookings,
            'amount_due' => $amountDue,
            'this_month_amount_due' => $thisMonthAmountDue,
            'coverage_start' => $coverageStart,
            'coverage_end' => $coverageEnd,
            'total_spent' => $totalSpent,
            'this_month_spent' => $thisMonthSpent,
            'avg_monthly_spent' => $avgMonthlySpent,
            'total_hours' => $totalHours,
            'avg_rating' => 4.9, // This would come from reviews table
            'active_caregivers' => $activeBookings, // Simplified
            'transactions' => $transactions,
            'my_bookings' => Booking::where('client_id', $clientId)
                ->with([
                    'assignedCaregiver.user:id,name,email,phone,avatar',
                    'assignments.caregiver.user:id,name,email,phone,avatar',
                    'assignments.caregiver:id,user_id'
                ])
                ->latest()
                ->get()
        ]);
    }
    
    private function extractHours($dutyType)
    {
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8; // Default to 8 hours
    }
    
    /**
     * Get default hourly rate for a service type
     * 
     * Pricing breakdown (without referral code, with training center):
     * - Caregiver: $28.00/hr
     * - Agency: $16.50/hr
     * - Training Center: $0.50/hr
     * Total: $45/hr
     * 
     * Pricing breakdown (without referral code, NO training center):
     * - Caregiver: $28.00/hr
     * - Agency: $17.00/hr (gets training's $0.50)
     * Total: $45/hr
     * 
     * Pricing breakdown (with referral code, with training center):
     * - Caregiver: $28.00/hr
     * - Agency (net): $10.50/hr
     * - Marketing Associate: $1.00/hr
     * - Training Center: $0.50/hr
     * Total: $40/hr
     * 
     * Pricing breakdown (with referral code, NO training center):
     * - Caregiver: $28.00/hr
     * - Agency (net): $11.00/hr (gets training's $0.50)
     * - Marketing Associate: $1.00/hr
     * Total: $40/hr
     */
    private function getDefaultRate($serviceType, $hasReferral = false)
    {
        // Use PricingService for consistent rates
        $clientRate = PricingService::getClientRate($hasReferral);
        
        // Housekeeping services use dedicated pricing with referral support
        if (in_array($serviceType, ['Housekeeping', 'House Cleaning'])) {
            return PricingService::getHousekeeperClientRate($hasReferral);
        }
        
        // Non-care services have different rates
        $nonCareRates = [
            'Personal Assistant' => 30
        ];
        
        return $nonCareRates[$serviceType] ?? $clientRate;
    }
    
    private function createSampleBookings($clientId)
    {
        $bookings = [
            [
                'client_id' => $clientId,
                'service_type' => 'Elderly Care',
                'duty_type' => '8 Hours Duty',
                'borough' => 'Manhattan',
                'service_date' => now()->addDays(5),
                'duration_days' => 15,
                'hourly_rate' => 25.00,
                'gender_preference' => 'no_preference',
                'client_age' => 75,
                'mobility_level' => 'assisted',
                'street_address' => '123 Main Street',
                'apartment_unit' => 'Apt 4B',
                'special_instructions' => 'Patient prefers morning care routine',
                'status' => 'pending'
            ],
            [
                'client_id' => $clientId,
                'service_type' => 'Personal Care',
                'duty_type' => '12 Hours Duty',
                'borough' => 'Brooklyn',
                'service_date' => now()->addDays(7),
                'duration_days' => 30,
                'hourly_rate' => 22.00,
                'gender_preference' => 'female',
                'client_age' => 68,
                'mobility_level' => 'independent',
                'street_address' => '456 Oak Avenue',
                'special_instructions' => 'Needs assistance with medication reminders',
                'status' => 'confirmed'
            ],
            [
                'client_id' => $clientId,
                'service_type' => 'Companion Care',
                'duty_type' => '8 Hours Duty',
                'borough' => 'Queens',
                'service_date' => now()->subDays(10),
                'duration_days' => 15,
                'hourly_rate' => 20.00,
                'gender_preference' => 'no_preference',
                'client_age' => 72,
                'mobility_level' => 'independent',
                'street_address' => '789 Pine Street',
                'special_instructions' => 'Enjoys classical music and reading',
                'status' => 'completed'
            ]
        ];
        
        foreach ($bookings as $booking) {
            Booking::create($booking);
        }
    }

    public function caregivers(): JsonResponse
    {
        $caregivers = Caregiver::with('user')
            ->get()
            ->map(function($caregiver) {
                // Use user's uploaded avatar if available, otherwise use stock image
                $avatar = null;
                if ($caregiver->user && $caregiver->user->avatar) {
                    $avatar = $caregiver->user->avatar;
                    // Ensure proper path format
                    if (!str_starts_with($avatar, '/') && !str_starts_with($avatar, 'http')) {
                        $avatar = '/storage/' . $avatar;
                    }
                }
                
                return [
                    'id' => $caregiver->id,
                    'user_id' => $caregiver->user_id,
                    'name' => $caregiver->user->name,
                    'specialty' => is_array($caregiver->specializations) ? implode(' & ', $caregiver->specializations) : 'General Care',
                    'rating' => (float) $caregiver->rating,
                    'reviews' => $caregiver->total_reviews,
                    'experience' => $caregiver->years_experience,
                    'certifications' => is_array($caregiver->certifications) ? implode(', ', $caregiver->certifications) : 'Licensed Caregiver',
                    'availability' => $caregiver->availability_status,
                    'image' => $avatar ?: $this->getCaregiverImage($caregiver->gender, $caregiver->id),
                    'avatar' => $avatar,
                    'hasCustomAvatar' => !empty($avatar),
                    'initials' => $this->getInitials($caregiver->user->name),
                    'phone' => $caregiver->user->phone ?: '(212) 555-' . str_pad($caregiver->id, 4, '0', STR_PAD_LEFT),
                    'email' => $caregiver->user->email,
                    'bio' => $caregiver->bio,
                    'skills' => $caregiver->skills,
                    'hourly_rate' => $caregiver->hourly_rate
                ];
            });
            
        return response()->json([
            'caregivers' => $caregivers
        ]);
    }
    
    public function housekeepers(): JsonResponse
    {
        $housekeepers = \App\Models\Housekeeper::with('user')
            ->get()
            ->map(function($housekeeper) {
                // Use user's uploaded avatar if available, otherwise use stock image
                $avatar = null;
                if ($housekeeper->user && $housekeeper->user->avatar) {
                    $avatar = $housekeeper->user->avatar;
                    // Ensure proper path format
                    if (!str_starts_with($avatar, '/') && !str_starts_with($avatar, 'http')) {
                        $avatar = '/storage/' . $avatar;
                    }
                }
                
                return [
                    'id' => $housekeeper->id,
                    'user_id' => $housekeeper->user_id,
                    'name' => $housekeeper->user->name,
                    'specialty' => is_array($housekeeper->specializations) ? implode(' & ', $housekeeper->specializations) : 'General Cleaning',
                    'rating' => (float) $housekeeper->rating,
                    'reviews' => $housekeeper->total_reviews,
                    'experience' => $housekeeper->years_experience,
                    'certifications' => is_array($housekeeper->certifications) ? implode(', ', $housekeeper->certifications) : 'Licensed Housekeeper',
                    'availability' => $housekeeper->availability_status,
                    'image' => $avatar ?: $this->getHousekeeperImage($housekeeper->gender, $housekeeper->id),
                    'avatar' => $avatar,
                    'hasCustomAvatar' => !empty($avatar),
                    'initials' => $this->getInitials($housekeeper->user->name),
                    'phone' => $housekeeper->user->phone ?: '(212) 555-' . str_pad($housekeeper->id, 4, '0', STR_PAD_LEFT),
                    'email' => $housekeeper->user->email,
                    'bio' => $housekeeper->bio,
                    'skills' => $housekeeper->skills,
                    'hourly_rate' => $housekeeper->hourly_rate
                ];
            });
            
        return response()->json([
            'housekeepers' => $housekeepers
        ]);
    }
    
    private function getInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }

    public function adminStats(): JsonResponse
    {
        $totalUsers = \App\Models\User::count();
        $totalCaregivers = \App\Models\User::where('user_type', 'caregiver')->count();
        $totalClients = \App\Models\User::where('user_type', 'client')->count();
        $totalAdmins = \App\Models\User::where('user_type', 'admin')->count();
        $totalMarketing = \App\Models\User::where('user_type', 'marketing')->count();
        $totalTraining = \App\Models\User::where('user_type', 'training')->count();
        $totalHousekeepers = \App\Models\User::where('user_type', 'housekeeper')->count();
        
        // Get active bookings (approved/confirmed/in_progress AND has completed payment)
        $activeBookings = Booking::with('payments')
            ->get()
            ->filter(function($booking) {
                $hasCompletedPayment = $booking->payments->where('status', 'completed')->isNotEmpty();
                return in_array($booking->status, ['approved', 'confirmed', 'in_progress']) 
                    && $hasCompletedPayment;
            })
            ->count();
        
        // Get total revenue - try Stripe first, fallback to local payments
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        
        // Try to get more accurate total from Stripe
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $balance = \Stripe\Balance::retrieve();
            
            // Get total from Stripe charges (succeeded only)
            $charges = \Stripe\Charge::all(['limit' => 100]);
            $stripeTotal = 0;
            foreach ($charges->data as $charge) {
                if ($charge->status === 'succeeded') {
                    $stripeTotal += $charge->amount / 100;
                }
            }
            
            // Use Stripe total if it's higher (more accurate)
            if ($stripeTotal > $totalRevenue) {
                $totalRevenue = $stripeTotal;
            }
        } catch (\Exception $e) {
            // Stripe fetch failed, use local payment data
            \Log::warning('Failed to fetch Stripe revenue: ' . $e->getMessage());
        }
        
        // Get recent bookings for activity feed
        $recentBookings = Booking::with(['client'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'client' => [
                        'name' => $booking->client->name ?? 'Unknown Client'
                    ],
                    'service_type' => $booking->service_type ?? 'Caregiver',
                    'status' => $booking->status,
                    'created_at' => $booking->created_at
                ];
            });
        
        // Calculate month-over-month growth
        $lastMonthUsers = \App\Models\User::where('created_at', '<', now()->startOfMonth())->count();
        $userGrowth = $lastMonthUsers > 0 ? round((($totalUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;
        
        // Calculate booking growth (only count paid active bookings from last week)
        $lastWeekActiveBookings = Booking::with('payments')
            ->where('created_at', '<', now()->startOfWeek())
            ->get()
            ->filter(function($booking) {
                $hasCompletedPayment = $booking->payments->where('status', 'completed')->isNotEmpty();
                return in_array($booking->status, ['approved', 'confirmed', 'in_progress']) 
                    && $hasCompletedPayment;
            })
            ->count();
        $bookingGrowth = $lastWeekActiveBookings > 0 ? round((($activeBookings - $lastWeekActiveBookings) / $lastWeekActiveBookings) * 100, 1) : 0;
        
        // Calculate real analytics data
        // Client spending from completed payments
        $totalClientSpending = Payment::where('status', 'completed')->sum('amount');
        $avgClientSpending = $totalClients > 0 ? $totalClientSpending / $totalClients : 0;
        
        // Caregiver earnings from payments table + time tracking
        $caregiverEarningsFromPayments = Payment::where('status', 'completed')->sum('caregiver_amount');
        $caregiverEarningsFromTimeTracking = 0;
        try {
            $caregiverEarningsFromTimeTracking = \App\Models\TimeTracking::where('payment_status', 'paid')->sum('caregiver_earnings');
        } catch (\Exception $e) {
            // Time tracking table may not exist
        }
        $totalCaregiverEarnings = $caregiverEarningsFromPayments + $caregiverEarningsFromTimeTracking;
        $avgCaregiverEarnings = $totalCaregivers > 0 ? $totalCaregiverEarnings / $totalCaregivers : 0;
        
        // Housekeeper earnings from payments table
        $totalHousekeeperEarnings = Payment::where('status', 'completed')->sum('housekeeper_amount');
        $avgHousekeeperEarnings = $totalHousekeepers > 0 ? $totalHousekeeperEarnings / $totalHousekeepers : 0;
        
        // New clients this week
        $newClientsThisWeek = \App\Models\User::where('user_type', 'client')
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
        
        // Available caregivers (those with availability_status 'available')
        $availableCaregivers = Caregiver::where('availability_status', 'available')->count();
        
        // Top rated caregivers (4+ star rating)
        $topRatedCaregivers = Caregiver::where('rating', '>=', 4)->count();
        
        return response()->json([
            'total_users' => $totalUsers,
            'total_clients' => $totalClients,
            'total_caregivers' => $totalCaregivers,
            'total_housekeepers' => $totalHousekeepers,
            'total_admins' => $totalAdmins,
            'total_marketing' => $totalMarketing,
            'total_training' => $totalTraining,
            'active_bookings' => $activeBookings,
            'total_revenue' => $totalRevenue,
            'user_growth' => $userGrowth,
            'booking_growth' => $bookingGrowth,
            'recent_bookings' => $recentBookings,
            'pending_applications' => \App\Models\User::where('user_type', 'caregiver')->whereDoesntHave('caregiver')->get(),
            // Real analytics data
            'avg_client_spending' => round($avgClientSpending, 2),
            'avg_caregiver_earnings' => round($avgCaregiverEarnings, 2),
            'avg_housekeeper_earnings' => round($avgHousekeeperEarnings, 2),
            'new_clients_this_week' => $newClientsThisWeek,
            'available_caregivers' => $availableCaregivers,
            'top_rated_caregivers' => $topRatedCaregivers,
            'total_caregiver_earnings' => round($totalCaregiverEarnings, 2),
            'total_housekeeper_earnings' => round($totalHousekeeperEarnings, 2),
        ]);
    }

    /**
     * Get quick caregiver contacts for admin dashboard
     */
    public function quickCaregivers(): JsonResponse
    {
        $caregivers = Caregiver::with('user')
            ->take(5)
            ->get()
            ->map(function ($caregiver) {
                $name = $caregiver->user->name ?? 'Unknown';
                $nameParts = explode(' ', $name);
                $initials = '';
                foreach ($nameParts as $part) {
                    if (!empty($part)) {
                        $initials .= strtoupper($part[0]);
                    }
                }
                $initials = substr($initials, 0, 2);
                
                // Check if caregiver has active assignments
                $hasActiveAssignment = BookingAssignment::where('caregiver_id', $caregiver->id)
                    ->where('status', 'assigned')
                    ->exists();
                
                return [
                    'id' => $caregiver->id,
                    'name' => $name,
                    'initials' => $initials,
                    'available' => !$hasActiveAssignment,
                    'phone' => $caregiver->user->phone ?? '(555) 000-0000',
                    'borough' => $caregiver->user->borough ?? 'N/A'
                ];
            });
        
        return response()->json($caregivers);
    }

       public function caregiverStats($caregiverId): JsonResponse
    {
        $caregiver = Caregiver::find($caregiverId);
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver not found'], 404);
        }
        
        $today = now()->toDateString();
        
        // Get active assignments for this caregiver (bookings that cover today OR are upcoming)
        $activeAssignments = \App\Models\BookingAssignment::with(['booking.client'])
            ->where('caregiver_id', $caregiverId)
            ->where('status', 'assigned')
            ->whereHas('booking', function($query) use ($today) {
                $query->whereIn('status', ['approved', 'confirmed'])
                      ->where(function($q) use ($today) {
                          // Active: service has started and hasn't ended yet
                          $q->where(function($subQ) use ($today) {
                              $subQ->where('service_date', '<=', $today)
                                   ->whereRaw('DATE_ADD(service_date, INTERVAL duration_days DAY) >= ?', [$today]);
                          })
                          // OR Upcoming: service starts in the future
                          ->orWhere('service_date', '>', $today);
                      });
            })
            ->get();
        
        // Sort: prioritize active assignments (started) over upcoming ones, then by service date
        $activeAssignments = $activeAssignments->sortBy(function($assignment) use ($today) {
            $serviceDate = $assignment->booking->service_date;
            // Prioritize active assignments (started) over upcoming ones
            $priority = $serviceDate <= $today ? 0 : 1;
            return $priority . '_' . $serviceDate;
        })->values();
        
        // Calculate earnings (sample calculation)
        $totalEarnings = 3200.00;
        $monthlyEarnings = 1200.00;
        $weeklyEarnings = 800.00;
        $pendingBalance = 450.00;
        
        // Get transactions (sample data for now)
        $transactions = [
            [
                'id' => 1,
                'type' => 'payment',
                'description' => 'Payment from client',
                'amount' => 120.00,
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(1)->toDateTimeString()
            ]
        ];
        
        return response()->json([
            'total_clients' => 24,
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'weekly_earnings' => $weeklyEarnings,
            'pending_balance' => $pendingBalance,
            'rating' => $caregiver->rating ?? 4.9,
            'active_assignments' => $activeAssignments->map(function($assignment) {
                $booking = $assignment->booking;
                $serviceDate = $booking->service_date;
                $durationDays = $booking->duration_days ?? 1;
                $startTime = $booking->start_time;
                
                // Calculate end date
                $endDate = null;
                if ($serviceDate && $durationDays) {
                    $endDate = \Carbon\Carbon::parse($serviceDate)->addDays($durationDays)->format('Y-m-d');
                }
                
                // Format start_time if it's a Carbon instance
                $formattedStartTime = null;
                if ($startTime) {
                    if ($startTime instanceof \Carbon\Carbon) {
                        $formattedStartTime = $startTime->format('H:i:s');
                    } else {
                        $formattedStartTime = is_string($startTime) ? $startTime : (string)$startTime;
                    }
                }
                
                return [
                    'booking' => [
                        'client' => [
                            'name' => $booking->client->name ?? 'Unknown Client'
                        ],
                        'service_type' => $booking->service_type,
                        'service_date' => $serviceDate,
                        'start_time' => $formattedStartTime,
                        'duration_days' => $durationDays,
                        'end_date' => $endDate,
                        'day_schedules' => $booking->day_schedules,
                        'status' => $booking->status
                    ]
                ];
            })->toArray(),
            'transactions' => $transactions
        ]);
    }

    public function adminUsers(): JsonResponse
    {
        return response()->json([
            'users' => \App\Models\User::with(['client', 'caregiver'])->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'type' => ucfirst($user->user_type),
                    'status' => $user->status ?? 'Active',
                    'joined' => $user->created_at->format('M Y'),
                    'caregiver' => $user->caregiver ? [
                        'id' => $user->caregiver->id,
                        'rating' => $user->caregiver->rating,
                        'preferred_hourly_rate_min' => $user->caregiver->preferred_hourly_rate_min,
                        'preferred_hourly_rate_max' => $user->caregiver->preferred_hourly_rate_max
                    ] : null
                ];
            })
        ]);
    }

    public function getNotifications($userId): JsonResponse
    {
        return response()->json([
            'notifications' => \App\Models\Notification::where('user_id', $userId)
                ->latest()
                ->get()
        ]);
    }

    public function getAvailableClients(): JsonResponse
    {
        $availableClients = Booking::whereNull('caregiver_id')
            ->where('status', 'pending')
            ->with('client')
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->client->id,
                    'name' => $booking->client->name,
                    'age' => 70,
                    'careType' => $booking->service_type,
                    'location' => 'Manhattan',
                    'hourlyRate' => 30,
                    'initials' => strtoupper(substr($booking->client->name, 0, 1) . substr(explode(' ', $booking->client->name)[1] ?? 'X', 0, 1)),
                    'avatarColor' => 'success'
                ];
            });
        
        return response()->json([
            'clients' => $availableClients
        ]);
    }

    public function clientAvailableYears(): JsonResponse
    {
        $clientId = auth()->id() ?? \App\Models\User::where('user_type', 'client')->first()?->id;
        $currentYear = now()->year;
        
        // Get years from actual booking dates, but only up to current year
        $bookingYears = Booking::where('client_id', $clientId)
            ->selectRaw('YEAR(service_date) as year')
            ->distinct()
            ->pluck('year')
            ->filter(function($year) use ($currentYear) {
                return $year <= $currentYear;
            })
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
            
        // If no bookings, show current year
        if (empty($bookingYears)) {
            $bookingYears = [$currentYear];
        }
        
        return response()->json(['years' => $bookingYears]);
    }

    public function clientSpendingData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $year = $request->get('year', now()->year);
        $clientId = auth()->id() ?? \App\Models\User::where('user_type', 'client')->first()?->id;
        
        $completedBookings = Booking::where('client_id', $clientId)
            ->where('status', 'completed')
            ->get();
            
        // Filter by year only if we have bookings in that year
        if ($year != now()->year) {
            $completedBookings = $completedBookings->filter(function($booking) use ($year) {
                return \Carbon\Carbon::parse($booking->service_date)->year == $year;
            });
        }
            
        // Remove the early return and fix the logic
        $spendingData = [];
        $labels = [];
        
        if ($period === 'week') {
            // Last 4 weeks of the selected year
            $startOfYear = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
            $endOfYear = \Carbon\Carbon::create($year, 12, 31)->endOfYear();
            $currentWeek = now()->year == $year ? now()->weekOfYear : 52;
            
            for ($i = 3; $i >= 0; $i--) {
                $weekNum = max(1, $currentWeek - $i);
                $weekStart = $startOfYear->copy()->addWeeks($weekNum - 1)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                $labels[] = 'Week ' . $weekNum;
                
                $weekSpending = $completedBookings->filter(function($booking) use ($weekStart, $weekEnd) {
                    $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                    return $serviceDate->between($weekStart, $weekEnd);
                })->sum(function($booking) {
                    $hours = $this->extractHours($booking->duty_type);
                    $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type);
                    return $hours * $booking->duration_days * $rate;
                });
                
                $spendingData[] = $weekSpending;
            }
        } elseif ($period === 'year') {
            // All 12 months of the selected year
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = \Carbon\Carbon::create($year, $i, 1)->startOfMonth();
                $monthEnd = \Carbon\Carbon::create($year, $i, 1)->endOfMonth();
                $labels[] = $monthStart->format('M Y');
                
                $monthSpending = $completedBookings->filter(function($booking) use ($monthStart, $monthEnd) {
                    $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                    return $serviceDate->between($monthStart, $monthEnd);
                })->sum(function($booking) {
                    $hours = $this->extractHours($booking->duty_type);
                    $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type);
                    return $hours * $booking->duration_days * $rate;
                });
                
                $spendingData[] = $monthSpending;
            }
        } else {
            // Last 4 months of the selected year
            $currentMonth = now()->year == $year ? now()->month : 12;
            
            for ($i = 3; $i >= 0; $i--) {
                $monthNum = max(1, $currentMonth - $i);
                $monthStart = \Carbon\Carbon::create($year, $monthNum, 1)->startOfMonth();
                $monthEnd = \Carbon\Carbon::create($year, $monthNum, 1)->endOfMonth();
                $labels[] = $monthStart->format('M Y');
                
                $monthSpending = $completedBookings->filter(function($booking) use ($monthStart, $monthEnd) {
                    $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                    return $serviceDate->between($monthStart, $monthEnd);
                })->sum(function($booking) {
                    $hours = $this->extractHours($booking->duty_type);
                    $rate = $booking->hourly_rate ?: $this->getDefaultRate($booking->service_type);
                    return $hours * $booking->duration_days * $rate;
                });
                
                $spendingData[] = $monthSpending;
            }
        }
        
        // Show real data even if it's zero
        return response()->json([
            'labels' => $labels,
            'data' => $spendingData
        ]);
    }

    private function getCaregiverImage($gender, $id)
    {
        $femaleImages = [
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=300&h=200&fit=crop'
        ];
        
        $maleImages = [
            'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&h=200&fit=crop',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=200&fit=crop'
        ];
        
        if ($gender === 'female') {
            return $femaleImages[($id - 1) % count($femaleImages)];
        } else {
            return $maleImages[($id - 1) % count($maleImages)];
        }
    }

    private function getHousekeeperImage($gender, $id)
    {
        // Use same images as caregivers for housekeepers
        return $this->getCaregiverImage($gender, $id);
    }
}

