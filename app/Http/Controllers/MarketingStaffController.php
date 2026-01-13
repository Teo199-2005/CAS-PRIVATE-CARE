<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\ReferralCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MarketingStaffController
 * 
 * Handles marketing staff dashboard data and statistics.
 */
class MarketingStaffController extends Controller
{
    /**
     * Get marketing staff statistics
     */
    public function stats(Request $request)
    {
        $userId = auth()->id();
        
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
        
        $bookings = Booking::where('referral_code_id', $referralCode->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $completedBookings = $bookings->where('status', 'completed');
        $activeBookings = $bookings->whereIn('status', ['approved', 'confirmed']);
        
        $totalCommission = $this->calculateCommission($completedBookings);
        $pendingCommission = $this->calculateCommission($activeBookings);
        
        $clients = $this->formatClients($bookings);
        
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
    }

    /**
     * Calculate commission for bookings
     */
    protected function calculateCommission($bookings)
    {
        return $bookings->sum(function($b) {
            $hours = $this->extractHours($b->duty_type);
            return $hours * $b->duration_days * 1.00;
        });
    }

    /**
     * Extract hours from duty type
     */
    protected function extractHours(?string $dutyType): int
    {
        if ($dutyType && preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8;
    }

    /**
     * Format clients data
     */
    protected function formatClients($bookings)
    {
        return $bookings->groupBy('client_id')->map(function($clientBookings, $clientId) {
            $client = $clientBookings->first()->client;
            $completed = $clientBookings->where('status', 'completed');
            
            $totalHours = $completed->sum(function($b) {
                return $this->extractHours($b->duty_type) * $b->duration_days;
            });
            
            $totalSpent = $completed->sum(function($b) {
                $hours = $this->extractHours($b->duty_type);
                return $hours * $b->duration_days * ($b->hourly_rate ?: 40);
            });
            
            $commission = $totalHours * 1.00;
            
            return [
                'id' => $clientId,
                'name' => $client->name ?? 'Unknown',
                'email' => $client->email ?? '',
                'phone' => $client->phone ?? '',
                'borough' => $client->borough ?? 'N/A',
                'status' => $clientBookings->whereIn('status', ['approved', 'confirmed'])->count() > 0 
                    ? 'Active' 
                    : 'Inactive',
                'totalHours' => $totalHours,
                'totalSpent' => number_format($totalSpent, 2),
                'contractDate' => $clientBookings->min('created_at') 
                    ? Carbon::parse($clientBookings->min('created_at'))->format('Y-m-d') 
                    : '-',
                'commission' => number_format($commission, 2)
            ];
        })->values();
    }
}
