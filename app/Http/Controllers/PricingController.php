<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PricingService;
use Illuminate\Http\Request;

/**
 * PricingController
 * 
 * Handles pricing-related API endpoints.
 */
class PricingController extends Controller
{
    /**
     * Get pricing breakdown
     */
    public function breakdown(Request $request)
    {
        $hasReferral = $request->boolean('has_referral', false);
        $hasTrainingCenter = $request->boolean('has_training_center', false);
        $hours = (float) $request->input('hours', 1);
        
        return response()->json([
            'success' => true,
            'data' => PricingService::calculateBreakdown($hours, $hasReferral, $hasTrainingCenter),
            'summary' => PricingService::getPricingSummary($hasReferral, $hasTrainingCenter),
        ]);
    }

    /**
     * Get pricing rates
     */
    public function rates()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'client_rate_no_referral' => PricingService::CLIENT_RATE_NO_REFERRAL,
                'client_rate_with_referral' => PricingService::CLIENT_RATE_WITH_REFERRAL,
                'caregiver_rate' => PricingService::CAREGIVER_RATE,
            ]
        ]);
    }

    /**
     * Add payment data to a booking (quick fix endpoint)
     */
    public function addPaymentData($id)
    {
        $booking = Booking::find($id);
        
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
    }
}
