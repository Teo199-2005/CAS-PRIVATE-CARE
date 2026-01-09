<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;

class ProcessRecurringBookings extends Command
{
    protected $signature = 'bookings:process-recurring {--dry-run : Run without making changes}';
    protected $description = 'Process recurring bookings - create new bookings and charge clients automatically';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('       🔄 PROCESSING RECURRING BOOKINGS');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->info('');
        }

        // Initialize Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        // Find bookings that need recurring processing
        $eligibleBookings = $this->getEligibleBookings();
        
        if ($eligibleBookings->isEmpty()) {
            $this->info('✅ No recurring bookings to process at this time.');
            $this->info('');
            return 0;
        }

        $this->info("📋 Found {$eligibleBookings->count()} booking(s) eligible for recurring processing:");
        $this->info('');

        $processed = 0;
        $failed = 0;

        foreach ($eligibleBookings as $booking) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📦 Processing Booking #{$booking->id}");
            $this->line("   Client: " . ($booking->client->name ?? 'Unknown'));
            $this->line("   Service: {$booking->service_type} - {$booking->duty_type}");
            $this->line("   Duration: {$booking->duration_days} days");
            $this->line("   End Date: " . $this->getBookingEndDate($booking)->format('M d, Y'));
            
            try {
                if (!$dryRun) {
                    $result = $this->processRecurringBooking($booking);
                    if ($result['success']) {
                        $processed++;
                        $this->info("   ✅ SUCCESS: New booking #{$result['new_booking_id']} created and charged");
                    } else {
                        $failed++;
                        $this->error("   ❌ FAILED: {$result['error']}");
                    }
                } else {
                    $this->info("   ✅ [DRY RUN] Would create new booking and charge client");
                    $processed++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("   ❌ ERROR: " . $e->getMessage());
                Log::error('ProcessRecurringBookings error', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $this->info('');
        }

        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info("📊 SUMMARY: Processed: {$processed} | Failed: {$failed}");
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('');

        return 0;
    }

    /**
     * Get bookings eligible for recurring processing
     */
    private function getEligibleBookings()
    {
        $today = Carbon::today();

        return Booking::with('client')
            ->where('recurring_service', true)
            ->where('auto_pay_enabled', true)
            ->whereIn('status', ['approved', 'confirmed', 'completed'])
            ->where('payment_status', 'paid')
            ->where(function($query) {
                $query->where('recurring_status', 'active')
                      ->orWhereNull('recurring_status');
            })
            ->get()
            ->filter(function($booking) use ($today) {
                // Check if booking has ended
                $endDate = $this->getBookingEndDate($booking);
                if (!$endDate->isPast() && !$endDate->isToday()) {
                    return false;
                }
                
                // Check if we already created the next booking
                $nextBookingExists = Booking::where('parent_booking_id', $booking->id)->exists();
                if ($nextBookingExists) {
                    return false;
                }
                
                // Check if client has payment method
                $client = $booking->client;
                if (!$client || !$client->stripe_customer_id) {
                    return false;
                }
                
                return true;
            });
    }

    /**
     * Calculate booking end date
     */
    private function getBookingEndDate($booking)
    {
        $startDate = Carbon::parse($booking->service_date);
        return $startDate->addDays($booking->duration_days ?? 15);
    }

    /**
     * Process a single recurring booking
     */
    private function processRecurringBooking($originalBooking)
    {
        $client = $originalBooking->client;
        
        // Calculate the new start date (day after original ends)
        $newStartDate = $this->getBookingEndDate($originalBooking)->addDay();
        
        // Calculate the amount to charge
        $hours = $this->extractHours($originalBooking->duty_type);
        $rate = $originalBooking->hourly_rate ?? 45;
        $days = $originalBooking->duration_days ?? 15;
        $amount = $hours * $days * $rate;

        // Step 1: Create the new booking first (as pending payment)
        $newBooking = $this->createNewBooking($originalBooking, $newStartDate);
        
        // Step 2: Charge the client
        $chargeResult = $this->chargeClient($client, $newBooking, $amount);
        
        if (!$chargeResult['success']) {
            // Update booking with failed status
            $newBooking->update([
                'status' => 'pending',
                'payment_status' => 'failed',
                'recurring_status' => 'failed',
            ]);
            
            // Update original booking's failed attempts
            $originalBooking->increment('recurring_failed_attempts');
            
            // Send failure notification
            $this->sendFailureNotification($client, $originalBooking, $chargeResult['error']);
            
            return [
                'success' => false,
                'error' => $chargeResult['error']
            ];
        }

        // Step 3: Update the new booking with payment info
        $newBooking->update([
            'status' => 'approved',
            'payment_status' => 'paid',
            'payment_date' => now(),
            'stripe_payment_intent_id' => $chargeResult['payment_intent_id'],
            'recurring_status' => 'active',
        ]);

        // Step 4: Update the original booking
        $originalBooking->update([
            'last_recurring_charge_date' => now(),
            'recurring_count' => ($originalBooking->recurring_count ?? 0) + 1,
        ]);

        // Step 5: Create payment record
        $this->createPaymentRecord($newBooking, $amount, $chargeResult['payment_intent_id']);

        // Step 6: Send success notification
        $this->sendSuccessNotification($client, $newBooking, $amount);

        // Step 7: Try to auto-assign the same caregiver(s)
        $this->autoAssignCaregivers($originalBooking, $newBooking);

        return [
            'success' => true,
            'new_booking_id' => $newBooking->id,
            'amount_charged' => $amount,
            'payment_intent_id' => $chargeResult['payment_intent_id']
        ];
    }

    /**
     * Create a new booking based on the original
     */
    private function createNewBooking($originalBooking, $newStartDate)
    {
        return Booking::create([
            'client_id' => $originalBooking->client_id,
            'parent_booking_id' => $originalBooking->id,
            'service_type' => $originalBooking->service_type,
            'duty_type' => $originalBooking->duty_type,
            'borough' => $originalBooking->borough,
            'city' => $originalBooking->city,
            'county' => $originalBooking->county,
            'service_date' => $newStartDate,
            'start_time' => $originalBooking->start_time,
            'duration_days' => $originalBooking->duration_days,
            'hourly_rate' => $originalBooking->hourly_rate,
            'total_budget' => $originalBooking->total_budget,
            'payment_method' => $originalBooking->payment_method,
            'gender_preference' => $originalBooking->gender_preference,
            'language_preference' => $originalBooking->language_preference,
            'day_schedules' => $originalBooking->day_schedules,
            'recurring_service' => true,
            'recurring_schedule' => $originalBooking->recurring_schedule,
            'auto_pay_enabled' => true,
            'recurring_status' => 'active',
            'street_address' => $originalBooking->street_address,
            'apartment_unit' => $originalBooking->apartment_unit,
            'special_instructions' => $originalBooking->special_instructions,
            'client_age' => $originalBooking->client_age,
            'mobility_level' => $originalBooking->mobility_level,
            'medical_conditions' => $originalBooking->medical_conditions,
            'referral_code_id' => $originalBooking->referral_code_id,
            'referral_discount_applied' => $originalBooking->referral_discount_applied,
            'status' => 'pending',
            'payment_status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Charge the client's saved payment method
     */
    private function chargeClient($client, $booking, $amount)
    {
        try {
            // Get customer's default payment method
            $customer = Customer::retrieve($client->stripe_customer_id);
            $paymentMethodId = $customer->invoice_settings->default_payment_method 
                ?? $customer->default_source;

            if (!$paymentMethodId) {
                // Try to get from payment methods list
                $paymentMethods = \Stripe\PaymentMethod::all([
                    'customer' => $client->stripe_customer_id,
                    'type' => 'card',
                ]);
                
                if (empty($paymentMethods->data)) {
                    return [
                        'success' => false,
                        'error' => 'No payment method on file'
                    ];
                }
                $paymentMethodId = $paymentMethods->data[0]->id;
            }

            // Create and confirm payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // Convert to cents
                'currency' => 'usd',
                'customer' => $client->stripe_customer_id,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
                'description' => "Recurring booking #{$booking->id} - {$booking->service_type}",
                'metadata' => [
                    'booking_id' => $booking->id,
                    'client_id' => $client->id,
                    'type' => 'recurring',
                    'parent_booking_id' => $booking->parent_booking_id,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'payment_intent_id' => $paymentIntent->id
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "Payment status: {$paymentIntent->status}"
                ];
            }
        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'error' => 'Card declined: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create payment record in database
     */
    private function createPaymentRecord($booking, $amount, $paymentIntentId)
    {
        $hours = $this->extractHours($booking->duty_type);
        $days = $booking->duration_days ?? 15;
        $platformFee = $amount * 0.10; // 10% platform fee
        $caregiverAmount = $amount * 0.90;

        return Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'caregiver_amount' => $caregiverAmount,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'stripe_payment_intent_id' => $paymentIntentId,
            'paid_at' => now(),
            'notes' => 'Automatic recurring payment',
        ]);
    }

    /**
     * Auto-assign caregivers from original booking
     */
    private function autoAssignCaregivers($originalBooking, $newBooking)
    {
        // Get assignments from original booking
        $originalAssignments = $originalBooking->assignments ?? collect();
        
        if ($originalAssignments->isEmpty()) {
            // Check if there's an assigned_caregiver_id
            if ($originalBooking->assigned_caregiver_id) {
                \App\Models\BookingAssignment::create([
                    'booking_id' => $newBooking->id,
                    'caregiver_id' => $originalBooking->assigned_caregiver_id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }
            return;
        }

        // Copy assignments to new booking
        foreach ($originalAssignments as $assignment) {
            \App\Models\BookingAssignment::create([
                'booking_id' => $newBooking->id,
                'caregiver_id' => $assignment->caregiver_id,
                'hourly_rate' => $assignment->hourly_rate,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Send success notification
     */
    private function sendSuccessNotification($client, $booking, $amount)
    {
        // Create in-app notification
        Notification::create([
            'user_id' => $client->id,
            'type' => 'Payments',
            'title' => 'Recurring Payment Successful',
            'message' => "Your recurring booking has been renewed! Booking #{$booking->id} starts on " . 
                        Carbon::parse($booking->service_date)->format('M d, Y') . 
                        ". Amount charged: $" . number_format($amount, 2),
            'read' => false,
        ]);

        // Log for admin
        Log::info('Recurring payment successful', [
            'client_id' => $client->id,
            'booking_id' => $booking->id,
            'amount' => $amount
        ]);
    }

    /**
     * Send failure notification
     */
    private function sendFailureNotification($client, $booking, $error)
    {
        // Notify client
        Notification::create([
            'user_id' => $client->id,
            'type' => 'Payments',
            'title' => 'Recurring Payment Failed',
            'message' => "We couldn't process your recurring payment for booking #{$booking->id}. " .
                        "Please update your payment method to continue service. Error: {$error}",
            'read' => false,
        ]);

        // Notify admins
        $admins = User::where('user_type', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'Payments',
                'title' => 'Client Recurring Payment Failed',
                'message' => "Recurring payment failed for client {$client->name} (Booking #{$booking->id}). Error: {$error}",
                'read' => false,
            ]);
        }

        Log::warning('Recurring payment failed', [
            'client_id' => $client->id,
            'booking_id' => $booking->id,
            'error' => $error
        ]);
    }

    /**
     * Extract hours from duty type string
     */
    private function extractHours($dutyType)
    {
        if (preg_match('/(\d+)/', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8; // Default to 8 hours
    }
}
