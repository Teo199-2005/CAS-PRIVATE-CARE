<?php

namespace App\Http\Controllers;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralCodeController extends Controller
{
    /**
     * Get the referral code for the current marketing user
     */
    public function getMyCode(Request $request)
    {
        $user = $request->user() ?? User::where('user_type', 'marketing')->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $referralCode = ReferralCode::where('user_id', $user->id)->first();

        if (!$referralCode) {
            // Auto-generate a code for marketing staff
            $referralCode = ReferralCode::create([
                'user_id' => $user->id,
                'code' => ReferralCode::generateCode($user->id),
                'discount_per_hour' => 5.00, // Client gets $5/hr discount
                'commission_per_hour' => 1.00, // Marketing earns $1/hr
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $referralCode->code,
                'discount_per_hour' => $referralCode->discount_per_hour,
                'commission_per_hour' => $referralCode->commission_per_hour,
                'usage_count' => $referralCode->usage_count,
                'total_commission_earned' => $referralCode->total_commission_earned,
                'is_active' => $referralCode->is_active,
            ]
        ]);
    }

    /**
     * Validate a referral code (used when client is booking)
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20'
        ]);

        $code = strtoupper(trim($request->code));
        $referralCode = ReferralCode::findValidCode($code);

        if (!$referralCode) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid or inactive referral code'
            ]);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'data' => [
                'code' => $referralCode->code,
                'discount_per_hour' => $referralCode->discount_per_hour,
                'marketing_name' => $referralCode->user->name ?? 'CAS Marketing',
            ],
            'message' => "Referral code valid! You'll save \${$referralCode->discount_per_hour}/hr on your booking."
        ]);
    }

    /**
     * Get all referral codes (admin only)
     */
    public function index()
    {
        $codes = ReferralCode::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $codes
        ]);
    }

    /**
     * Create a referral code for a marketing staff member
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'nullable|string|max:20|unique:referral_codes,code',
        ]);

        $existingCode = ReferralCode::where('user_id', $request->user_id)->first();
        if ($existingCode) {
            return response()->json([
                'success' => false,
                'message' => 'User already has a referral code: ' . $existingCode->code
            ], 400);
        }

        $code = $request->code ?? ReferralCode::generateCode($request->user_id);

        $referralCode = ReferralCode::create([
            'user_id' => $request->user_id,
            'code' => strtoupper($code),
            'discount_per_hour' => 5.00,
            'commission_per_hour' => 1.00,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $referralCode,
            'message' => 'Referral code created successfully'
        ]);
    }

    /**
     * Update a referral code
     */
    public function update(Request $request, $id)
    {
        $referralCode = ReferralCode::findOrFail($id);

        $request->validate([
            'code' => 'nullable|string|max:20|unique:referral_codes,code,' . $id,
            'discount_per_hour' => 'nullable|numeric|min:0',
            'commission_per_hour' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('code')) {
            $referralCode->code = strtoupper($request->code);
        }
        if ($request->has('discount_per_hour')) {
            $referralCode->discount_per_hour = $request->discount_per_hour;
        }
        if ($request->has('commission_per_hour')) {
            $referralCode->commission_per_hour = $request->commission_per_hour;
        }
        if ($request->has('is_active')) {
            $referralCode->is_active = $request->is_active;
        }

        $referralCode->save();

        return response()->json([
            'success' => true,
            'data' => $referralCode,
            'message' => 'Referral code updated successfully'
        ]);
    }

    /**
     * Get commission stats for a marketing user
     */
    public function getCommissionStats(Request $request)
    {
        $user = $request->user() ?? User::where('user_type', 'marketing')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $referralCode = ReferralCode::where('user_id', $user->id)->first();

        if (!$referralCode) {
            return response()->json([
                'success' => true,
                'data' => [
                    'code' => null,
                    'total_referrals' => 0,
                    'total_commission' => 0,
                    'monthly_commission' => 0,
                ]
            ]);
        }

        // Get bookings using this referral code
        $bookings = $referralCode->bookings()
            ->with('client:id,name')
            ->get();

        // Calculate monthly commission (current month)
        $monthlyBookings = $referralCode->bookings()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $monthlyCommission = $monthlyBookings->sum(function ($booking) use ($referralCode) {
            $hoursPerDay = $this->extractHours($booking->duty_type);
            return $hoursPerDay * $booking->duration_days * $referralCode->commission_per_hour;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $referralCode->code,
                'total_referrals' => $referralCode->usage_count,
                'total_commission' => $referralCode->total_commission_earned,
                'monthly_commission' => $monthlyCommission,
                'recent_bookings' => $bookings->take(5)->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'client' => $booking->client->name ?? 'Unknown',
                        'service' => $booking->service_type,
                        'date' => $booking->created_at->format('M d, Y'),
                    ];
                }),
            ]
        ]);
    }

    private function extractHours($dutyType)
    {
        if (!$dutyType) return 8;
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int) $matches[1];
        }
        return 8;
    }
}
