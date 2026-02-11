<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\ReferralCode;
use App\Models\TimeTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MarketingStaffController
 * 
 * Handles marketing staff dashboard data and statistics.
 * Commission is $1.00 per actual hour worked (from time_trackings).
 */
class MarketingStaffController extends Controller
{
    /**
     * Get marketing staff statistics
     */
    public function stats(Request $request)
    {
        $userId = $request->user()?->id ?? auth()->id();
        if (!$userId) {
            return response()->json([
                'my_clients' => 0,
                'active_bookings' => 0,
                'total_commission' => 0,
                'pending_commission' => 0,
                'account_balance' => 0,
                'clients' => [],
                'weekly_summary' => ['clients_acquired' => 0, 'target' => 10, 'previous_payout' => 0, 'previous_payout_date' => null]
            ]);
        }
        
        $referralCode = ReferralCode::where('user_id', $userId)->first();
        
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
        
        // All bookings with this referral code (paid filter removed so client always shows; commission still from time_trackings)
        $bookings = Booking::where('referral_code_id', $referralCode->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $activeBookings = $bookings->whereIn('status', ['approved', 'confirmed', 'assigned']);
        
        // Get ACTUAL commissions from time_trackings (accurate data)
        $timeTrackings = TimeTracking::where('marketing_partner_id', $userId)
            ->whereNotNull('marketing_partner_commission')
            ->get();
        
        $totalCommission = $timeTrackings->sum('marketing_partner_commission') ?? 0;
        $pendingCommission = $timeTrackings->whereNull('marketing_commission_paid_at')
            ->sum('marketing_partner_commission') ?? 0;
        $paidCommission = $timeTrackings->whereNotNull('marketing_commission_paid_at')
            ->sum('marketing_partner_commission') ?? 0;
        
        // Get last payout info
        $lastPayout = $timeTrackings->whereNotNull('marketing_commission_paid_at')
            ->sortByDesc('marketing_commission_paid_at')
            ->first();
        
        $clients = $this->formatClients($bookings, $userId);
        
        $thisWeekClients = $bookings->filter(function($b) {
            return $b->created_at >= now()->startOfWeek();
        })->pluck('client_id')->unique()->count();
        
        // Monthly commission from time trackings
        $monthlyCommission = $timeTrackings->filter(function($tt) {
            return $tt->work_date && $tt->work_date >= now()->startOfMonth();
        })->sum('marketing_partner_commission') ?? 0;
        
        return response()->json([
            'my_clients' => $bookings->pluck('client_id')->unique()->count(),
            'active_bookings' => $activeBookings->count(),
            'total_commission' => number_format($totalCommission, 2),
            'pending_commission' => number_format($pendingCommission, 2),
            'paid_commission' => number_format($paidCommission, 2),
            'monthly_commission' => number_format($monthlyCommission, 2),
            'account_balance' => number_format($pendingCommission, 2), // Unpaid balance
            'clients' => $clients,
            'weekly_summary' => [
                'clients_acquired' => $thisWeekClients,
                'target' => 10,
                'previous_payout' => $lastPayout ? number_format($paidCommission, 2) : '0.00',
                'previous_payout_date' => $lastPayout && $lastPayout->marketing_commission_paid_at 
                    ? Carbon::parse($lastPayout->marketing_commission_paid_at)->format('M d, Y') 
                    : null
            ],
            'referral_code' => $referralCode->code
        ]);
    }

    /**
     * Format clients data with actual commission from time_trackings
     */
    protected function formatClients($bookings, $userId)
    {
        return $bookings->groupBy('client_id')->map(function($clientBookings, $clientId) use ($userId) {
            $client = $clientBookings->first()->client;
            if (!$client) {
                return null;
            }
            
            // Get ACTUAL hours and commission from time_trackings for this client
            $clientTimeTrackings = TimeTracking::where('marketing_partner_id', $userId)
                ->where('client_id', $clientId)
                ->whereNotNull('clock_out_time')
                ->get();
            
            $totalHours = $clientTimeTrackings->sum('hours_worked') ?? 0;
            $commission = $clientTimeTrackings->sum('marketing_partner_commission') ?? 0;
            
            // Calculate total spent by client
            $totalSpent = $clientTimeTrackings->sum('client_charge') ?? 0;
            
            return [
                'id' => $clientId,
                'name' => $client->name ?? 'Unknown',
                'email' => $client->email ?? '',
                'phone' => $client->phone ?? '',
                'borough' => $client->borough ?? 'N/A',
                'status' => $clientBookings->whereIn('status', ['approved', 'confirmed', 'assigned'])->count() > 0 
                    ? 'Active' 
                    : 'Inactive',
                'totalHours' => number_format($totalHours, 1),
                'totalSpent' => number_format($totalSpent, 2),
                'contractDate' => $clientBookings->min('created_at') 
                    ? Carbon::parse($clientBookings->min('created_at'))->format('Y-m-d') 
                    : '-',
                'commission' => number_format($commission, 2)
            ];
        })->filter()->values();
    }
}
