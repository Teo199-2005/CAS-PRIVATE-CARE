<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Booking;

/**
 * AccountDeletionController
 * 
 * Handles GDPR/CCPA compliant account deletion requests.
 * Provides both soft delete (anonymization) and hard delete options.
 * 
 * @see https://gdpr.eu/right-to-erasure-request/
 */
class AccountDeletionController extends Controller
{
    /**
     * Show the account deletion confirmation page
     */
    public function show()
    {
        return view('account.delete');
    }

    /**
     * Request account deletion
     * 
     * This endpoint initiates the account deletion process.
     * For safety, we require password confirmation.
     */
    public function requestDeletion(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
            'reason' => 'nullable|string|max:1000',
        ], [
            'password.required' => 'Please enter your password to confirm deletion.',
            'confirmation.required' => 'Please type DELETE to confirm.',
            'confirmation.in' => 'Please type DELETE exactly to confirm.',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Check for active bookings
        $activeBookings = $this->getActiveBookings($user);
        if ($activeBookings > 0) {
            return back()->withErrors([
                'deletion' => "You have {$activeBookings} active booking(s). Please cancel or complete them before deleting your account."
            ]);
        }

        try {
            DB::beginTransaction();

            // Log the deletion request
            Log::info('Account deletion requested', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'reason' => $request->reason ?? 'Not provided',
                'ip' => $request->ip(),
            ]);

            // Anonymize user data (GDPR compliant soft delete)
            $this->anonymizeUserData($user, $request->reason);

            DB::commit();

            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', 'Your account has been successfully deleted. We\'re sorry to see you go.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'deletion' => 'An error occurred while processing your request. Please try again or contact support.'
            ]);
        }
    }

    /**
     * Get count of active bookings for the user
     */
    protected function getActiveBookings(User $user): int
    {
        $count = 0;

        if ($user->user_type === 'client') {
            $count = Booking::where('client_id', $user->id)
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count();
        } elseif (in_array($user->user_type, ['caregiver', 'housekeeper'])) {
            // Check if assigned to any active bookings
            $count = DB::table('booking_assignments')
                ->join('bookings', 'booking_assignments.booking_id', '=', 'bookings.id')
                ->where('booking_assignments.caregiver_id', $user->id)
                ->whereIn('bookings.status', ['approved', 'in_progress'])
                ->count();
        }

        return $count;
    }

    /**
     * Anonymize user data for GDPR compliance
     * 
     * This method:
     * 1. Replaces personal data with anonymized placeholders
     * 2. Preserves business records (bookings, payments) with anonymized references
     * 3. Deletes truly personal data (SSN, photos, etc.)
     */
    protected function anonymizeUserData(User $user, ?string $reason = null): void
    {
        $anonymizedId = 'DELETED_' . $user->id . '_' . time();
        
        // Store deletion record for audit trail
        DB::table('account_deletions')->insert([
            'original_user_id' => $user->id,
            'original_email_hash' => hash('sha256', $user->email),
            'user_type' => $user->user_type,
            'deletion_reason' => $reason,
            'deleted_at' => now(),
            'created_at' => now(),
        ]);

        // Anonymize related records based on user type
        if ($user->user_type === 'client') {
            // Anonymize client record
            DB::table('clients')
                ->where('user_id', $user->id)
                ->update([
                    'first_name' => 'Deleted',
                    'last_name' => 'User',
                    'updated_at' => now(),
                ]);
        } elseif ($user->user_type === 'caregiver') {
            // Anonymize caregiver record
            DB::table('caregivers')
                ->where('user_id', $user->id)
                ->update([
                    'bio' => null,
                    'profile_image' => null,
                    'certifications' => null,
                    'training_certificate' => null,
                    'updated_at' => now(),
                ]);
        } elseif ($user->user_type === 'housekeeper') {
            // Anonymize housekeeper record
            DB::table('housekeepers')
                ->where('user_id', $user->id)
                ->update([
                    'bio' => null,
                    'profile_image' => null,
                    'certifications' => null,
                    'updated_at' => now(),
                ]);
        }

        // Delete notifications
        DB::table('notifications')
            ->where('user_id', $user->id)
            ->delete();

        // Anonymize reviews (keep the review text but anonymize the author)
        DB::table('reviews')
            ->where('reviewer_id', $user->id)
            ->update([
                'reviewer_name' => 'Deleted User',
            ]);

        // Delete payment methods
        DB::table('payment_methods')
            ->where('user_id', $user->id)
            ->delete();

        // Delete referral codes if marketing user
        if ($user->user_type === 'marketing') {
            DB::table('referral_codes')
                ->where('user_id', $user->id)
                ->update(['is_active' => false]);
        }

        // Delete avatar file if exists
        if ($user->avatar) {
            $avatarPath = storage_path('app/public/' . $user->avatar);
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        // Finally, anonymize and soft-delete the user record
        $user->update([
            'name' => 'Deleted User',
            'email' => $anonymizedId . '@deleted.local',
            'phone' => null,
            'address' => null,
            'city' => null,
            'county' => null,
            'borough' => null,
            'state' => null,
            'zip_code' => null,
            'date_of_birth' => null,
            'ssn' => null,
            'itin' => null,
            'ein' => null,
            'avatar' => null,
            'stripe_customer_id' => null,
            'stripe_account_id' => null,
            'stripe_connect_id' => null,
            'password' => Hash::make(str_random(64)), // Invalidate password
            'remember_token' => null,
            'session_token' => null,
            'status' => 'deleted',
            'deleted_at' => now(),
        ]);
    }

    /**
     * Export user data (GDPR Right to Portability)
     */
    public function exportData(Request $request)
    {
        $user = Auth::user();
        
        $data = [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip_code' => $user->zip_code,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at,
            ],
            'bookings' => [],
            'payments' => [],
            'reviews' => [],
        ];

        // Get bookings
        if ($user->user_type === 'client') {
            $bookings = Booking::where('client_id', $user->id)->get();
            $data['bookings'] = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service_type' => $booking->service_type,
                    'status' => $booking->status,
                    'start_date' => $booking->start_date,
                    'total_amount' => $booking->total_amount,
                    'created_at' => $booking->created_at,
                ];
            })->toArray();
        }

        // Get payments
        $payments = DB::table('payments')
            ->where('user_id', $user->id)
            ->orWhere('payer_id', $user->id)
            ->get();
        
        $data['payments'] = $payments->map(function ($payment) {
            return [
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_date' => $payment->created_at,
            ];
        })->toArray();

        // Get reviews
        $reviews = DB::table('reviews')
            ->where('reviewer_id', $user->id)
            ->get();
        
        $data['reviews'] = $reviews->map(function ($review) {
            return [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
            ];
        })->toArray();

        $filename = 'cas_private_care_data_export_' . date('Y-m-d') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }
}
