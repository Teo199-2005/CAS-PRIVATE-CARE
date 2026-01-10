<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;

class PaymentService
{
    /**
     * Create a payment record when a booking is completed
     */
    public static function createPaymentForCompletedBooking(Booking $booking): ?Payment
    {
        // Check if payment already exists
        $existingPayment = Payment::where('booking_id', $booking->id)->first();
        if ($existingPayment) {
            return $existingPayment;
        }
        
        // Calculate amounts
        $hours = self::extractHours($booking->duty_type);
        $clientRate = $booking->hourly_rate ?: PricingService::getClientRate(!empty($booking->referral_code_id));
        $caregiverRate = PricingService::CAREGIVER_RATE;
        
        $totalAmount = $hours * $booking->duration_days * $clientRate;
        $caregiverAmount = $hours * $booking->duration_days * $caregiverRate;
        $platformFee = $totalAmount - $caregiverAmount;
        
        // Get the primary caregiver from assignments
        $assignment = $booking->assignments()->where('status', 'assigned')->first();
        $caregiverId = $assignment ? $assignment->caregiver_id : null;
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'caregiver_id' => $caregiverId,
            'amount' => $totalAmount,
            'processing_fee' => 0, // No processing fee for manual/legacy payments
            'platform_fee' => $platformFee,
            'caregiver_amount' => $caregiverAmount,
            'payment_method' => $booking->payment_method ?? 'credit_card',
            'status' => 'completed',
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'paid_at' => now(),
            'notes' => "Payment for {$booking->service_type} - {$booking->duration_days} days"
        ]);
        
        // Create transaction record for the caregiver
        if ($caregiverId) {
            self::createCaregiverTransaction($caregiverId, $caregiverAmount, $booking->id);
        }
        
        // Notify client
        NotificationService::notifyPaymentReceived(
            $booking->client_id,
            $totalAmount,
            $booking->service_type
        );
        
        return $payment;
    }
    
    /**
     * Create a transaction record for caregiver earnings
     */
    public static function createCaregiverTransaction(int $caregiverId, float $amount, int $bookingId): Transaction
    {
        return Transaction::create([
            'caregiver_id' => $caregiverId,
            'type' => 'payment',
            'description' => "Earnings from booking #{$bookingId}",
            'amount' => $amount,
            'status' => 'completed',
            'method' => 'Bank Transfer'
        ]);
    }
    
    /**
     * Extract hours from duty type string
     */
    private static function extractHours(string $dutyType): int
    {
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8; // Default to 8 hours
    }
}

