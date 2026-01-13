<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\TimeTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * CaregiverDataController
 * 
 * Handles caregiver-specific data endpoints including:
 * - Payment data and earnings
 * - Past bookings history
 * - Schedule events
 * - Top caregivers for clients
 */
class CaregiverDataController extends Controller
{
    /**
     * Get caregiver payment data (dynamic - no hardcoded values)
     */
    public function paymentData(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver not found'], 404);
        }
        
        // Get all time tracking records
        $timeTrackings = TimeTracking::where('caregiver_id', $caregiver->id)
            ->with(['client.user', 'booking'])
            ->orderBy('work_date', 'desc')
            ->get();
        
        // Calculate totals
        $paidEarnings = $timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings') ?? 0;
        $pendingEarnings = $timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings') ?? 0;
        
        // Get last payment info
        $lastPaymentData = $this->getLastPaymentData($timeTrackings);
        
        // Calculate next payout date (next Friday)
        $nextFriday = $this->getNextPayoutDate();
        
        // Get Stripe connection status
        $stripeConnected = !empty($user->stripe_connect_id);
        $stripeOnboardingComplete = $user->stripe_onboarding_complete ?? false;
        
        // Get transactions
        $transactions = $this->formatTransactions($timeTrackings);
        
        // Payment summary
        $paymentSummary = [
            'total_earnings' => number_format($paidEarnings, 2),
            'pending_earnings' => number_format($pendingEarnings, 2),
            'last_payment_amount' => number_format($lastPaymentData['amount'], 2),
            'last_payment_date' => $lastPaymentData['date'],
            'account_balance' => number_format($pendingEarnings, 2),
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
    }

    /**
     * Get caregiver past bookings with time tracking details
     */
    public function pastBookings(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['success' => false, 'bookings' => []]);
        }
        
        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['success' => false, 'bookings' => []]);
        }
        
        // Get all booking assignments for this caregiver
        $assignments = BookingAssignment::where('caregiver_id', $caregiver->id)
            ->with(['booking.client.user'])
            ->get();
        
        $bookingsData = [];
        
        foreach ($assignments as $assignment) {
            $booking = $assignment->booking;
            if (!$booking) continue;
            
            $bookingsData[] = $this->formatBookingData($booking, $assignment, $caregiver);
        }
        
        // Sort by start date (most recent first)
        usort($bookingsData, function($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });
        
        return response()->json([
            'success' => true,
            'bookings' => $bookingsData
        ]);
    }

    /**
     * Get caregiver schedule events
     */
    public function scheduleEvents(Request $request)
    {
        $caregiverId = $request->query('caregiver_id');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        
        if (!$caregiverId) {
            $user = auth()->user();
            if ($user && $user->user_type === 'caregiver') {
                $caregiver = Caregiver::where('user_id', $user->id)->first();
                $caregiverId = $caregiver?->id;
            }
        }
        
        if (!$caregiverId) {
            return response()->json(['events' => []]);
        }
        
        $assignments = BookingAssignment::where('caregiver_id', $caregiverId)
            ->with('booking.client')
            ->get();

        $events = $this->buildCalendarEvents($assignments, $month, $year);
        
        return response()->json(['events' => $events]);
    }

    /**
     * Get top caregivers for a client
     */
    public function topCaregivers(Request $request)
    {
        $clientId = $request->query('client_id') ?: auth()->id();
        
        $assignments = BookingAssignment::whereHas('booking', function($q) use ($clientId) {
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
    }

    /**
     * Get training centers for dropdown
     */
    public function trainingCenters()
    {
        $trainingCenters = User::whereIn('user_type', ['training_center', 'training'])
            ->where('status', 'Active')
            ->orderBy('name', 'asc')
            ->pluck('name')
            ->values()
            ->toArray();
        
        return response()->json(['trainingCenters' => $trainingCenters]);
    }

    // ==================== Helper Methods ====================

    protected function getLastPaymentData($timeTrackings): array
    {
        $lastPaymentGroup = $timeTrackings->where('payment_status', 'paid')
            ->where('paid_at', '!=', null)
            ->sortByDesc('paid_at')
            ->first();
        
        if ($lastPaymentGroup) {
            $lastPaymentDate = $lastPaymentGroup->paid_at;
            $lastPaymentAmount = $timeTrackings->where('payment_status', 'paid')
                ->filter(fn($tt) => $tt->paid_at && $tt->paid_at->eq($lastPaymentDate))
                ->sum('caregiver_earnings');
            
            return [
                'amount' => $lastPaymentAmount,
                'date' => $lastPaymentDate->format('M d, Y')
            ];
        }
        
        return [
            'amount' => 0,
            'date' => 'No payments yet'
        ];
    }

    protected function getNextPayoutDate(): Carbon
    {
        $today = Carbon::now();
        $nextFriday = $today->copy()->next(Carbon::FRIDAY);
        
        if ($today->isFriday()) {
            $nextFriday = $today->copy()->addWeek();
        }
        
        return $nextFriday;
    }

    protected function formatTransactions($timeTrackings)
    {
        return $timeTrackings->map(function($tt) {
            $clientName = 'Unknown';
            if ($tt->client && $tt->client->user) {
                $clientName = $tt->client->user->name;
            } elseif ($tt->booking && $tt->booking->client) {
                $clientName = $tt->booking->client->name ?? 'Client';
            }
            
            return [
                'id' => $tt->id,
                'date' => Carbon::parse($tt->work_date)->format('M d, Y'),
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
    }

    protected function formatBookingData($booking, $assignment, $caregiver): array
    {
        $timeTrackings = TimeTracking::where('booking_id', $booking->id)
            ->where('caregiver_id', $caregiver->id)
            ->get();
        
        $totalHours = $timeTrackings->sum('hours_worked');
        $totalEarnings = $timeTrackings->sum('caregiver_earnings');
        
        $paidCount = $timeTrackings->where('payment_status', 'paid')->count();
        $pendingCount = $timeTrackings->where('payment_status', 'pending')->count();
        
        if ($totalHours == 0) {
            $paymentStatus = 'No Hours';
        } elseif ($paidCount == $timeTrackings->count()) {
            $paymentStatus = 'Paid';
        } elseif ($pendingCount == $timeTrackings->count()) {
            $paymentStatus = 'Pending';
        } else {
            $paymentStatus = 'Partial';
        }
        
        $assignedRate = $assignment->assigned_hourly_rate ?? 28.00;
        $startDate = Carbon::parse($booking->service_date);
        $endDate = $startDate->copy()->addDays($booking->duration_days - 1);
        
        return [
            'id' => $booking->id,
            'client' => $booking->client->user->name ?? $booking->client->name ?? 'Unknown Client',
            'service_type' => ucfirst($booking->service_type ?? 'Caregiver'),
            'start_date' => $startDate->format('M d, Y'),
            'end_date' => $endDate->format('M d, Y'),
            'duration' => $booking->duration_days . ' days',
            'total_hours' => number_format($totalHours, 1),
            'assigned_rate' => number_format($assignedRate, 2),
            'total_earnings' => number_format($totalEarnings, 2),
            'payment_status' => $paymentStatus,
            'sessions_count' => $timeTrackings->count(),
            'booking_status' => $booking->status ?? 'completed',
        ];
    }

    protected function buildCalendarEvents($assignments, $month, $year): array
    {
        $events = [];
        
        foreach ($assignments as $assignment) {
            $booking = $assignment->booking;
            if (!$booking) continue;
            
            $startDate = Carbon::parse($booking->service_date);
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
                            'time' => $booking->start_time 
                                ? Carbon::parse($booking->start_time)->format('g:i A') 
                                : '9:00 AM',
                            'status' => $status
                        ];
                    }
                }
                $currentDate->addDay();
            }
        }
        
        return $events;
    }
}
