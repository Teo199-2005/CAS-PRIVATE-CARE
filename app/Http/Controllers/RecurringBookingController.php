<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription;

class RecurringBookingController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get all bookings for the authenticated client (with recurring info)
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'recurring_bookings' => []
                ], 401);
            }
            
            // Get ALL completed/approved bookings so user can enable recurring on any of them
            $bookings = Booking::where('client_id', $user->id)
                ->whereIn('status', ['completed', 'approved', 'confirmed', 'in_progress'])
                ->whereNotNull('payment_status')
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Recurring bookings loaded', [
                'user_id' => $user->id,
                'count' => $bookings->count(),
                'ids' => $bookings->pluck('id')->toArray()
            ]);

            $formattedBookings = $bookings->map(function($booking) {
                return $this->formatRecurringBooking($booking);
            });

            return response()->json([
                'success' => true,
                'recurring_bookings' => $formattedBookings
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading recurring bookings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load recurring bookings',
                'recurring_bookings' => []
            ], 500);
        }
    }

    /**
     * Get details of a specific recurring booking chain
     */
    public function show($bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->with(['parentBooking', 'childBookings'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Get the root booking
        $rootBooking = $booking->root_booking;

        // Get all bookings in this chain
        $chain = $this->getBookingChain($rootBooking);

        return response()->json([
            'success' => true,
            'booking' => $this->formatRecurringBooking($booking),
            'root_booking' => $this->formatRecurringBooking($rootBooking),
            'chain' => $chain,
            'total_paid' => $chain->sum('amount'),
            'total_renewals' => $chain->count() - 1
        ]);
    }

    /**
     * Get upcoming renewals (within 5 days) for countdown display
     */
    public function getUpcomingRenewals(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'renewals' => []
                ], 401);
            }
            
            $now = now();
            $fiveDaysFromNow = now()->addDays(5);
            
            $upcomingRenewals = Booking::where('client_id', $user->id)
                ->where('recurring_service', true)
                ->where('auto_pay_enabled', true)
                ->where('recurring_status', 'active')
                ->whereNotNull('service_date')
                ->whereNotNull('duration_days')
                ->get()
                ->map(function($booking) use ($now, $fiveDaysFromNow) {
                    $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                    $endDate = $serviceDate->copy()->addDays($booking->duration_days);
                    $daysUntilRenewal = $now->startOfDay()->diffInDays($endDate->startOfDay(), false);
                    
                    // Only include bookings renewing within 5 days
                    if ($daysUntilRenewal >= 0 && $daysUntilRenewal <= 5) {
                        $hours = $this->extractHours($booking->duty_type);
                        $rate = $booking->hourly_rate ?? 45;
                        $days = $booking->duration_days ?? 15;
                        $amount = $hours * $days * $rate;
                        
                        return [
                            'booking_id' => $booking->id,
                            'service_type' => $booking->service_type,
                            'duration_days' => $days,
                            'hours_per_day' => $hours,
                            'amount' => $amount,
                            'renewal_date' => $endDate->format('F j, Y'),
                            'days_remaining' => $daysUntilRenewal
                        ];
                    }
                    
                    return null;
                })
                ->filter()
                ->values();
            
            return response()->json([
                'success' => true,
                'renewals' => $upcomingRenewals
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading upcoming renewals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load upcoming renewals',
                'renewals' => []
            ], 500);
        }
    }

    /**
     * Enable auto-pay for a booking
     */
    public function enableAutoPay(Request $request, $bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Check if client has payment method
        if (!$user->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please add a payment method first'
            ], 400);
        }

        $booking->update([
            'recurring_service' => true,
            'auto_pay_enabled' => true,
            'recurring_status' => 'active'
        ]);

        Log::info('Auto-pay enabled', [
            'booking_id' => $bookingId,
            'client_id' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Auto-pay enabled successfully',
            'booking' => $this->formatRecurringBooking($booking->fresh())
        ]);
    }

    /**
     * Cancel recurring for a booking
     */
    public function cancelRecurring(Request $request, $bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Cancel Stripe subscription if exists
        if ($booking->stripe_subscription_id) {
            try {
                $subscription = Subscription::retrieve($booking->stripe_subscription_id);
                $subscription->cancel();
            } catch (\Exception $e) {
                Log::warning('Could not cancel Stripe subscription', [
                    'booking_id' => $bookingId,
                    'subscription_id' => $booking->stripe_subscription_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $booking->update([
            'recurring_service' => false,
            'auto_pay_enabled' => false,
            'recurring_status' => 'cancelled'
        ]);

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'type' => 'recurring_cancelled',
            'title' => 'Recurring Service Cancelled',
            'message' => "You have cancelled recurring payments for booking #{$bookingId}. Your current service period will complete as scheduled, but no new bookings will be created automatically.",
            'data' => json_encode(['booking_id' => $bookingId]),
            'read' => false
        ]);

        Log::info('Recurring cancelled', [
            'booking_id' => $bookingId,
            'client_id' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurring payments cancelled. Your current booking will complete as scheduled.',
            'booking' => $this->formatRecurringBooking($booking->fresh())
        ]);
    }

    /**
     * Pause recurring for a booking
     */
    public function pauseRecurring(Request $request, $bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->update([
            'auto_pay_enabled' => false,
            'recurring_status' => 'paused'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurring payments paused',
            'booking' => $this->formatRecurringBooking($booking->fresh())
        ]);
    }

    /**
     * Resume recurring for a booking
     */
    public function resumeRecurring(Request $request, $bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Check if client has payment method
        if (!$user->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please add a payment method first'
            ], 400);
        }

        $booking->update([
            'recurring_service' => true,
            'auto_pay_enabled' => true,
            'recurring_status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurring payments resumed',
            'booking' => $this->formatRecurringBooking($booking->fresh())
        ]);
    }

    /**
     * Get the next charge date for a booking
     */
    public function getNextChargeDate($bookingId)
    {
        $user = auth()->user();
        
        $booking = Booking::where('client_id', $user->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $endDate = $booking->end_date;
        $nextChargeDate = $endDate->addDay();

        // Calculate amount
        $hours = $this->extractHours($booking->duty_type);
        $rate = $booking->hourly_rate ?? 45;
        $days = $booking->duration_days ?? 15;
        $amount = $hours * $days * $rate;

        return response()->json([
            'success' => true,
            'current_end_date' => $endDate->format('Y-m-d'),
            'next_charge_date' => $nextChargeDate->format('Y-m-d'),
            'next_charge_amount' => $amount,
            'is_active' => $booking->auto_pay_enabled && $booking->recurring_status === 'active'
        ]);
    }

    /**
     * Admin: Get all recurring bookings
     */
    public function adminIndex(Request $request)
    {
        $user = auth()->user();
        
        if ($user->user_type !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $recurringBookings = Booking::where('recurring_service', true)
            ->with(['client', 'childBookings'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($booking) {
                $formatted = $this->formatRecurringBooking($booking);
                $formatted['client_name'] = $booking->client->name ?? 'Unknown';
                $formatted['client_email'] = $booking->client->email ?? 'Unknown';
                return $formatted;
            });

        return response()->json([
            'success' => true,
            'recurring_bookings' => $recurringBookings,
            'stats' => [
                'total_recurring' => $recurringBookings->count(),
                'active' => $recurringBookings->where('recurring_status', 'active')->count(),
                'paused' => $recurringBookings->where('recurring_status', 'paused')->count(),
                'cancelled' => $recurringBookings->where('recurring_status', 'cancelled')->count(),
                'failed' => $recurringBookings->where('recurring_status', 'failed')->count(),
            ]
        ]);
    }

    /**
     * Format a booking for response
     */
    private function formatRecurringBooking($booking)
    {
        $hours = $this->extractHours($booking->duty_type);
        $rate = $booking->hourly_rate ?? 45;
        $days = $booking->duration_days ?? 15;
        $amount = $hours * $days * $rate;

        return [
            'id' => $booking->id,
            'parent_booking_id' => $booking->parent_booking_id,
            'service_type' => $booking->service_type,
            'duty_type' => $booking->duty_type,
            'duration_days' => $days,
            'hourly_rate' => $rate,
            'hours_per_day' => $hours,
            'amount' => $amount,
            'service_date' => $booking->service_date?->format('Y-m-d'),
            'end_date' => $booking->end_date?->format('Y-m-d'),
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'recurring_service' => $booking->recurring_service,
            'recurring_schedule' => $booking->recurring_schedule,
            'recurring_status' => $booking->recurring_status ?? 'active',
            'recurring_count' => $booking->recurring_count ?? 0,
            'auto_pay_enabled' => $booking->auto_pay_enabled,
            'next_payment_date' => $booking->next_payment_date?->format('Y-m-d'),
            'last_recurring_charge_date' => $booking->last_recurring_charge_date?->format('Y-m-d'),
            'recurring_failed_attempts' => $booking->recurring_failed_attempts ?? 0,
            'child_bookings_count' => $booking->childBookings?->count() ?? 0,
            'is_recurring_child' => $booking->is_recurring_child,
        ];
    }

    /**
     * Get all bookings in a chain
     */
    private function getBookingChain($rootBooking)
    {
        $chain = collect([$this->formatRecurringBooking($rootBooking)]);
        
        $this->addChildrenToChain($rootBooking, $chain);
        
        return $chain->sortBy('service_date')->values();
    }

    /**
     * Recursively add children to chain
     */
    private function addChildrenToChain($booking, &$chain)
    {
        foreach ($booking->childBookings as $child) {
            $chain->push($this->formatRecurringBooking($child));
            $this->addChildrenToChain($child, $chain);
        }
    }

    /**
     * Extract hours from duty type
     */
    private function extractHours($dutyType)
    {
        if (preg_match('/(\d+)/', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8;
    }
}
