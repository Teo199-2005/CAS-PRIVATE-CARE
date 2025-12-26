<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ReferralCode;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $clientId = Auth::id();
        $bookings = Booking::with(['client', 'assignments.caregiver.user'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Log only in non-production environments for debugging
        if (config('app.env') !== 'production') {
            \Log::debug('Client bookings query result', [
                'client_id' => $clientId,
                'total_bookings' => $bookings->count(),
                'booking_statuses' => $bookings->pluck('status')->toArray()
            ]);
        }

        // Explicitly map all fields for frontend compatibility
        $data = $bookings->map(function($b) {
            return [
                'id' => $b->id,
                'client_id' => $b->client_id,
                'service_type' => $b->service_type,
                'duty_type' => $b->duty_type,
                'borough' => $b->borough,
                'city' => $b->city,
                'county' => $b->county,
                'service_date' => $b->service_date ? $b->service_date->toDateString() : null,
                'start_time' => $b->start_time,
                'duration_days' => $b->duration_days,
                'hourly_rate' => $b->hourly_rate,
                'total_budget' => $b->total_budget,
                'payment_method' => $b->payment_method,
                'gender_preference' => $b->gender_preference,
                'language_preference' => $b->language_preference,
                'background_check_level' => $b->background_check_level,
                'specific_skills' => $b->specific_skills,
                'client_age' => $b->client_age,
                'mobility_level' => $b->mobility_level,
                'medical_conditions' => $b->medical_conditions,
                'transportation_needed' => $b->transportation_needed,
                'recurring_service' => $b->recurring_service,
                'recurring_schedule' => $b->recurring_schedule,
                'urgency_level' => $b->urgency_level,
                'street_address' => $b->street_address,
                'apartment_unit' => $b->apartment_unit,
                'special_instructions' => $b->special_instructions,
                'status' => $b->status,
                'assignment_status' => $b->assignment_status,
                'submitted_at' => $b->submitted_at ? $b->submitted_at->toIso8601String() : null,
                'created_at' => $b->created_at ? $b->created_at->toIso8601String() : null,
                'updated_at' => $b->updated_at ? $b->updated_at->toIso8601String() : null,
                'referral_code_id' => $b->referral_code_id,
                'referral_discount_applied' => $b->referral_discount_applied,
                // Add related client info if needed
                'client' => $b->client ? [
                    'id' => $b->client->id,
                    'name' => $b->client->name
                ] : null,
                // Add assignments if needed
                'assignments' => $b->assignments,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('book-service-enhanced');
    }

    public function store(Request $request)
    {
        try {
            // Determine client_id: allow admins to specify, otherwise use authenticated user
            $user = Auth::user();
            $clientId = $request->client_id;
            
            // If admin is creating booking, allow client_id to be specified
            if ($user && $user->user_type === 'admin' && $clientId) {
                // Verify the client exists
                $client = \App\Models\User::where('id', $clientId)->where('user_type', 'client')->first();
                if (!$client) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid client selected'
                    ], 422);
                }
            } else {
                // Regular client booking
                $clientId = Auth::id();
            }
            
            // Handle referral code if provided
            $referralCodeId = null;
            $referralDiscountApplied = null;
            
            if ($request->referral_code) {
                $referralCode = ReferralCode::findValidCode($request->referral_code);
                if ($referralCode) {
                    $referralCodeId = $referralCode->id;
                    $referralDiscountApplied = $referralCode->discount_per_hour;
                    // Increment usage count
                    $referralCode->increment('usage_count');
                }
            }
            
            // Calculate hourly rate (base $45, or discounted if referral applied)
            $hourlyRate = $request->hourly_rate ?: 45;
            if ($referralDiscountApplied) {
                $hourlyRate = 45 - $referralDiscountApplied; // $40 with $5 discount
            }
            
            // Handle start_time - default to 09:00:00 if not provided
            $startTime = $request->start_time ?: '09:00:00';
            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime)) {
                // If format is HH:MM or HH:MM AM/PM, convert it
                $startTime = '09:00:00';
            }
            
            // Parse duty_type to extract hours if needed
            $dutyType = $request->duty_type ?: '8 Hours';
            
            // Use database transaction to ensure data consistency
            $booking = DB::transaction(function() use ($request, $clientId, $dutyType, $startTime, $hourlyRate, $referralCodeId, $referralDiscountApplied, $user) {
                return Booking::create([
                    'client_id' => $clientId,
                    'service_type' => $request->service_type ?: 'Caregiver',
                    'duty_type' => $dutyType,
                    'borough' => $request->borough ?: ($request->city ?: ($request->county ?: 'Manhattan')),
                    'city' => $request->city,
                    'county' => $request->county,
                    'service_date' => $request->service_date ?: now()->addDay(),
                    'start_time' => $startTime,
                    'duration_days' => $request->duration_days ?: 15,
                    'hourly_rate' => $hourlyRate,
                    'gender_preference' => $request->gender_preference ?: 'no_preference',
                    'specific_skills' => $request->specific_skills ?: [],
                    'client_age' => $request->client_age,
                    'mobility_level' => $request->mobility_level,
                    'medical_conditions' => $request->medical_conditions ?: [],
                    'transportation_needed' => $request->boolean('transportation_needed', false),
                    'street_address' => $request->street_address,
                    'apartment_unit' => $request->apartment_unit,
                    'special_instructions' => $request->special_instructions,
                    'status' => $request->status ?: ($user && $user->user_type === 'admin' ? 'approved' : 'pending'),
                    'submitted_at' => now(),
                    'referral_code_id' => $referralCodeId,
                    'referral_discount_applied' => $referralDiscountApplied
                ]);
            });

            // Log booking creation for debugging
            try {
                Log::info('Booking created', [
                    'id' => $booking->id ?? null,
                    'client_id' => $booking->client_id ?? null,
                    'status' => $booking->status ?? null,
                    'service_date' => $booking->service_date ?? null,
                    'created_at' => $booking->created_at ?? null,
                    'submitted_at' => $booking->submitted_at ?? null,
                ]);
            } catch (\Exception $e) {
                // continue silently if logging fails
            }

            // Return JSON response for API calls
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service booked successfully!',
                    'booking' => $booking
                ], 201);
            }

            return redirect()->back()->with('success', 'Service booked successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit booking. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to submit booking. Please try again.');
        }
    }

    public function show(Booking $booking)
    {
        $booking->load(['client', 'assignments.caregiver.user']);
        
        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        try {
            // Allow admin users to update any booking, or clients to update their own
            $user = Auth::user();
            if (!$user || ($user->user_type !== 'admin' && $booking->client_id !== $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $updateData = [];
            
            // Allow status updates (for admin)
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            
            // Allow other field updates
            if ($request->has('service_type')) $updateData['service_type'] = $request->service_type;
            if ($request->has('duty_type')) $updateData['duty_type'] = $request->duty_type;
            if ($request->has('city') || $request->has('county')) $updateData['borough'] = $request->city ?: $request->county;
            if ($request->has('service_date')) $updateData['service_date'] = $request->service_date;
            if ($request->has('duration_days')) $updateData['duration_days'] = $request->duration_days;
            if ($request->has('gender_preference')) $updateData['gender_preference'] = $request->gender_preference;
            if ($request->has('specific_skills')) $updateData['specific_skills'] = $request->specific_skills;
            if ($request->has('client_age')) $updateData['client_age'] = $request->client_age;
            if ($request->has('mobility_level')) $updateData['mobility_level'] = $request->mobility_level;
            if ($request->has('medical_conditions')) $updateData['medical_conditions'] = $request->medical_conditions;
            if ($request->has('transportation_needed')) $updateData['transportation_needed'] = $request->boolean('transportation_needed');
            if ($request->has('street_address')) $updateData['street_address'] = $request->street_address;
            if ($request->has('apartment_unit')) $updateData['apartment_unit'] = $request->apartment_unit;
            if ($request->has('special_instructions')) $updateData['special_instructions'] = $request->special_instructions;
            
            $booking->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Booking $booking)
    {
        try {
            $user = Auth::user();
            
            // Only admins can delete bookings
            if (!$user || $user->user_type !== 'admin') {
                \Log::warning("Unauthorized booking deletion attempt by user {$user->id}");
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Only admins can delete bookings'
                ], 403);
            }
            
            \Log::info("Attempting to delete booking ID: {$booking->id}");
            
            // Delete associated assignments first
            try {
                $assignmentsDeleted = $booking->assignments()->delete();
                \Log::info("Deleted {$assignmentsDeleted} booking assignments for booking {$booking->id}");
            } catch (\Exception $e) {
                \Log::warning("Error deleting assignments for booking {$booking->id}: " . $e->getMessage());
            }
            
            // Delete any related reviews
            try {
                $reviewsDeleted = \App\Models\Review::where('booking_id', $booking->id)->delete();
                \Log::info("Deleted {$reviewsDeleted} reviews for booking {$booking->id}");
            } catch (\Exception $e) {
                \Log::warning("Error deleting reviews for booking {$booking->id}: " . $e->getMessage());
            }
            
            // Delete any related payments
            try {
                $paymentsDeleted = \App\Models\Payment::where('booking_id', $booking->id)->delete();
                \Log::info("Deleted {$paymentsDeleted} payments for booking {$booking->id}");
            } catch (\Exception $e) {
                \Log::warning("Error deleting payments for booking {$booking->id}: " . $e->getMessage());
            }
            
            // Delete the booking
            $booking->delete();
            \Log::info("Successfully deleted booking {$booking->id}");

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Booking not found: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting booking: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete booking: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAssignments($id)
    {
        $booking = Booking::with(['assignments.caregiver.user'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $booking->assignments
        ]);
    }

    public function indexWithAssignments()
    {
        try {
            // Auto-update booking status before fetching
            $this->updateExpiredBookings();
            
            $bookings = Booking::with([
                'client', 
                'assignments.caregiver.user:id,name,email,phone' // Explicitly include phone field
            ])
                ->select('bookings.*') // Ensure all booking fields are selected
                ->orderBy('service_date', 'desc')
                ->get();

            // Log only in non-production environments for debugging
            if (config('app.env') !== 'production') {
                \Log::debug('Admin bookings query result', [
                    'total_bookings' => $bookings->count(),
                    'booking_statuses' => $bookings->pluck('status')->toArray()
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in indexWithAssignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ]);
        }
    }
    
    public function approve(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $oldStatus = $booking->status;
            $booking->update(['status' => 'approved']);
            
            // Send notification to client
            NotificationService::notifyBookingApproved($booking);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve booking: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function reject(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $booking->update(['status' => 'rejected']);
            
            // Send notification to client
            NotificationService::notifyBookingRejected($booking, $request->reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking rejected'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject booking: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateExpiredBookings()
    {
        try {
            $bookings = Booking::whereIn('status', ['approved', 'confirmed'])->get();
            $updated = 0;
            
            foreach ($bookings as $booking) {
                $serviceDate = \Carbon\Carbon::parse($booking->service_date);
                $endDate = $serviceDate->copy()->addDays($booking->duration_days);
                $now = \Carbon\Carbon::now();
                
                if ($endDate->isPast()) {
                    $booking->update(['status' => 'completed']);
                    $updated++;
                    
                    // Create payment record when booking completes
                    try {
                        PaymentService::createPaymentForCompletedBooking($booking);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to create payment for booking {$booking->id}: " . $e->getMessage());
                    }
                    
                    // Send completion notifications
                    try {
                        NotificationService::notifyBookingCompleted($booking);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send completion notifications for booking {$booking->id}: " . $e->getMessage());
                    }
                    
                    \Log::info('Updated booking to completed', ['booking_id' => $booking->id]);
                }
            }
            
            \Log::info('Booking update summary', ['updated_count' => $updated]);
        } catch (\Exception $e) {
            \Log::error('Error updating expired bookings: ' . $e->getMessage());
        }
    }
}