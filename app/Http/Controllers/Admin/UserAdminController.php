<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Client;
use App\Rules\ValidSSN;
use App\Rules\ValidITIN;
use App\Rules\ValidPhoneNumber;
use App\Rules\ValidNYZipCode;
use App\Services\NotificationService;
use App\Services\EmailService;
use App\Services\ZipCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin controller for user-related operations.
 * Extracted from AdminController as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 1.1
 */
class UserAdminController extends Controller
{
    /**
     * Create a new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'phone' => ['nullable', new ValidPhoneNumber, 'max:20'],
            'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:50',
            'county' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'borough' => 'nullable|string|max:100',
            'zip_code' => ['nullable', 'string', 'max:10', new ValidNYZipCode],
            'ssn' => ['nullable', new ValidSSN, 'max:11'],
            'itin' => ['nullable', new ValidITIN, 'max:11'],
            'years_experience' => 'nullable|integer|min:0|max:50',
            'training_center' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'user_type' => 'required|in:client,caregiver,housekeeper,admin',
            'status' => 'nullable|in:Active,Inactive,Suspended',
            // Housekeeper-specific fields
            'hourly_rate' => 'nullable|numeric|min:0|max:500',
            'has_own_supplies' => 'nullable|boolean',
            'available_for_transport' => 'nullable|boolean',
            'skills' => 'nullable|array',
            'specializations' => 'nullable|array',
        ]);
        
        // Sanitize bio field to prevent XSS
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        // Generate secure random password for new users created by admin
        $temporaryPassword = \Illuminate\Support\Str::random(16);
        
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($temporaryPassword),
            'user_type' => $validated['user_type'],
            'status' => $validated['status'] ?? 'Active'
        ];

        // Add optional fields
        if (isset($validated['phone'])) $userData['phone'] = $validated['phone'];
        if (isset($validated['date_of_birth'])) $userData['date_of_birth'] = $validated['date_of_birth'];
        if (isset($validated['address'])) $userData['address'] = $validated['address'];
        if (isset($validated['state'])) $userData['state'] = $validated['state'];
        if (isset($validated['county'])) $userData['county'] = $validated['county'];
        if (isset($validated['city'])) $userData['city'] = $validated['city'];
        if (isset($validated['borough'])) $userData['borough'] = $validated['borough'];
        if (isset($validated['zip_code'])) $userData['zip_code'] = $validated['zip_code'];
        if (isset($validated['ssn'])) $userData['ssn'] = $validated['ssn'];
        if (isset($validated['itin'])) $userData['itin'] = $validated['itin'];

        // Use database transaction to ensure data consistency
        $user = DB::transaction(function() use ($userData, $validated) {
            $user = User::create($userData);

            if ($validated['user_type'] === 'client') {
                $clientData = ['user_id' => $user->id];
                if (isset($validated['firstName'])) $clientData['first_name'] = $validated['firstName'];
                if (isset($validated['lastName'])) $clientData['last_name'] = $validated['lastName'];
                Client::create($clientData);
            } elseif ($validated['user_type'] === 'caregiver') {
                $caregiverData = [
                    'user_id' => $user->id,
                    'gender' => 'female',
                    'availability_status' => 'available'
                ];
                if (isset($validated['firstName'])) $caregiverData['first_name'] = $validated['firstName'];
                if (isset($validated['lastName'])) $caregiverData['last_name'] = $validated['lastName'];
                if (isset($validated['years_experience'])) $caregiverData['years_experience'] = $validated['years_experience'];
                if (isset($validated['bio'])) $caregiverData['bio'] = $validated['bio'];
                Caregiver::create($caregiverData);
            } elseif ($validated['user_type'] === 'housekeeper') {
                $housekeeperData = [
                    'user_id' => $user->id,
                    'gender' => 'female',
                    'availability_status' => 'available'
                ];
                if (isset($validated['years_experience'])) $housekeeperData['years_experience'] = $validated['years_experience'];
                if (isset($validated['bio'])) $housekeeperData['bio'] = $validated['bio'];
                if (isset($validated['hourly_rate'])) $housekeeperData['hourly_rate'] = $validated['hourly_rate'];
                if (isset($validated['has_own_supplies'])) $housekeeperData['has_own_supplies'] = $validated['has_own_supplies'];
                if (isset($validated['available_for_transport'])) $housekeeperData['available_for_transport'] = $validated['available_for_transport'];
                if (isset($validated['skills'])) $housekeeperData['skills'] = $validated['skills'];
                if (isset($validated['specializations'])) $housekeeperData['specializations'] = $validated['specializations'];
                Housekeeper::create($housekeeperData);
            }
            
            return $user;
        });

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, $id)
    {
        $id = (int) $id;
        $user = User::with('caregiver')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'phone' => ['sometimes', 'nullable', new ValidPhoneNumber, 'max:20'],
            'date_of_birth' => 'sometimes|nullable|date|before:today|after:1900-01-01',
            'address' => 'sometimes|nullable|string|max:255',
            'state' => 'sometimes|nullable|string|max:50',
            'county' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'borough' => 'sometimes|nullable|string|max:100',
            'zip_code' => ['sometimes', 'nullable', 'string', 'max:10', new ValidNYZipCode],
            'status' => 'sometimes|in:Active,Inactive,Suspended',
            'training_center' => 'sometimes|nullable|string|max:255',
            'years_experience' => 'sometimes|nullable|integer|min:0|max:50',
            'bio' => 'sometimes|nullable|string|max:1000',
            'preferred_hourly_rate_min' => 'sometimes|nullable|numeric|min:0',
            'preferred_hourly_rate_max' => 'sometimes|nullable|numeric|min:0',
            'has_hha' => 'sometimes|boolean',
            'hha_number' => 'sometimes|nullable|string|max:255',
            'has_cna' => 'sometimes|boolean',
            'cna_number' => 'sometimes|nullable|string|max:255',
            'has_rn' => 'sometimes|boolean',
            'rn_number' => 'sometimes|nullable|string|max:255',
        ]);

        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        // Get existing user columns
        $existingUserColumns = [];
        try {
            $existingUserColumns = DB::getSchemaBuilder()->getColumnListing('users');
        } catch (\Exception $e) {
            $existingUserColumns = ['name','email','phone','date_of_birth','address','city','borough','state','zip_code','status'];
        }

        $userUpdate = [];
        foreach (['name','email','phone','date_of_birth','address','state','county','city','borough','zip_code','status'] as $field) {
            if (!in_array($field, $existingUserColumns, true)) {
                continue;
            }
            if (array_key_exists($field, $validated)) {
                $userUpdate[$field] = $validated[$field];
            }
        }
        if (!empty($userUpdate)) {
            $user->update($userUpdate);
        }

        // Update caregiver table fields if applicable
        if ($user->user_type === 'caregiver') {
            $caregiver = $user->caregiver ?: Caregiver::firstOrCreate(['user_id' => $user->id]);

            $caregiverUpdate = [];
            if (array_key_exists('years_experience', $validated)) $caregiverUpdate['years_experience'] = $validated['years_experience'];
            if (array_key_exists('bio', $validated)) $caregiverUpdate['bio'] = $validated['bio'];
            if (array_key_exists('preferred_hourly_rate_min', $validated)) $caregiverUpdate['preferred_hourly_rate_min'] = $validated['preferred_hourly_rate_min'];
            if (array_key_exists('preferred_hourly_rate_max', $validated)) $caregiverUpdate['preferred_hourly_rate_max'] = $validated['preferred_hourly_rate_max'];
            if (array_key_exists('has_hha', $validated)) $caregiverUpdate['has_hha'] = (bool) $validated['has_hha'];
            if (array_key_exists('hha_number', $validated)) $caregiverUpdate['hha_number'] = $validated['hha_number'];
            if (array_key_exists('has_cna', $validated)) $caregiverUpdate['has_cna'] = (bool) $validated['has_cna'];
            if (array_key_exists('cna_number', $validated)) $caregiverUpdate['cna_number'] = $validated['cna_number'];
            if (array_key_exists('has_rn', $validated)) $caregiverUpdate['has_rn'] = (bool) $validated['has_rn'];
            if (array_key_exists('rn_number', $validated)) $caregiverUpdate['rn_number'] = $validated['rn_number'];

            // Map training center name to caregiver.training_center_id
            if (array_key_exists('training_center', $validated)) {
                $tcName = trim((string) ($validated['training_center'] ?? ''));

                if ($tcName === '') {
                    $caregiverUpdate['training_center_id'] = null;
                    $caregiverUpdate['has_training_center'] = false;
                    $caregiverUpdate['training_center_approval_status'] = null;
                    if (DB::getSchemaBuilder()->hasColumn('caregivers', 'custom_training_center')) {
                        $caregiverUpdate['custom_training_center'] = null;
                    }
                } else {
                    $centerUser = User::whereIn('user_type', ['training_center', 'training'])
                        ->where('name', $tcName)
                        ->first();

                    if ($centerUser) {
                        $caregiverUpdate['training_center_id'] = $centerUser->id;
                        $caregiverUpdate['has_training_center'] = true;
                        $caregiverUpdate['training_center_approval_status'] = 'approved';
                        if (DB::getSchemaBuilder()->hasColumn('caregivers', 'custom_training_center')) {
                            $caregiverUpdate['custom_training_center'] = null;
                        }
                    } else {
                        $caregiverUpdate['training_center_id'] = null;
                        $caregiverUpdate['has_training_center'] = true;
                        $caregiverUpdate['training_center_approval_status'] = 'pending';
                        if (DB::getSchemaBuilder()->hasColumn('caregivers', 'custom_training_center')) {
                            $caregiverUpdate['custom_training_center'] = $tcName;
                        }
                    }
                }
            }

            if (!empty($caregiverUpdate)) {
                $caregiver->update($caregiverUpdate);
            }
        }

        return response()->json(['success' => true, 'user' => $user->fresh(['caregiver'])]);
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended'
        ]);
        
        $user = User::findOrFail($id);
        $user->update(['status' => $validated['status']]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Update caregiver status
     */
    public function updateCaregiverStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended'
        ]);
        
        $caregiver = Caregiver::findOrFail($id);
        $caregiver->user->update(['status' => $validated['status']]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete a user and all related records
     */
    public function deleteUser($id)
    {
        try {
            Log::info("Attempting to delete user ID: {$id}");
            $user = User::with(['caregiver', 'client'])->findOrFail($id);
            Log::info("Found user: {$user->name} (Type: {$user->user_type})");
            
            DB::transaction(function() use ($user, $id) {
                if ($user->user_type === 'caregiver') {
                    Log::info("Deleting caregiver-related records for user {$id}");
                    $caregiver = Caregiver::where('user_id', $user->id)->first();
                    if ($caregiver) {
                        $caregiverId = $caregiver->id;
                        
                        try {
                            $assignmentsDeleted = \App\Models\BookingAssignment::where('caregiver_id', $caregiverId)->delete();
                            Log::info("Deleted {$assignmentsDeleted} booking assignments");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting assignments: " . $e->getMessage());
                        }
                        
                        try {
                            $trackingsDeleted = \App\Models\TimeTracking::where('caregiver_id', $caregiverId)->delete();
                            Log::info("Deleted {$trackingsDeleted} time trackings");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting time trackings: " . $e->getMessage());
                        }
                        
                        try {
                            $reviewsDeleted = \App\Models\Review::where('caregiver_id', $caregiverId)->delete();
                            Log::info("Deleted {$reviewsDeleted} reviews");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting reviews: " . $e->getMessage());
                        }
                        
                        try {
                            $paymentsDeleted = \App\Models\Payment::where('caregiver_id', $caregiverId)->delete();
                            Log::info("Deleted {$paymentsDeleted} payments");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting payments: " . $e->getMessage());
                        }
                        
                        try {
                            $caregiver->delete();
                            Log::info("Deleted caregiver record");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting caregiver record: " . $e->getMessage());
                        }
                    }
                } elseif ($user->user_type === 'client') {
                    Log::info("Deleting client-related records for user {$id}");
                    $client = Client::where('user_id', $user->id)->first();
                    if ($client) {
                        $bookingIds = \App\Models\Booking::where('client_id', $user->id)->pluck('id');
                        
                        if ($bookingIds->count() > 0) {
                            try {
                                $assignmentsDeleted = \App\Models\BookingAssignment::whereIn('booking_id', $bookingIds)->delete();
                                Log::info("Deleted {$assignmentsDeleted} booking assignments");
                            } catch (\Exception $e) {
                                Log::warning("Error deleting booking assignments: " . $e->getMessage());
                            }
                        }
                        
                        try {
                            $bookingsDeleted = \App\Models\Booking::where('client_id', $user->id)->delete();
                            Log::info("Deleted {$bookingsDeleted} bookings");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting bookings: " . $e->getMessage());
                        }
                        
                        try {
                            $reviewsDeleted = \App\Models\Review::where('client_id', $user->id)->delete();
                            Log::info("Deleted {$reviewsDeleted} reviews");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting reviews: " . $e->getMessage());
                        }
                        
                        try {
                            $paymentsDeleted = \App\Models\Payment::where('client_id', $user->id)->delete();
                            Log::info("Deleted {$paymentsDeleted} payments");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting payments: " . $e->getMessage());
                        }
                        
                        try {
                            $client->delete();
                            Log::info("Deleted client record");
                        } catch (\Exception $e) {
                            Log::warning("Error deleting client record: " . $e->getMessage());
                        }
                    }
                } elseif ($user->user_type === 'marketing') {
                    Log::info("Deleting marketing staff-related records for user {$id}");
                    try {
                        $codesDeleted = \App\Models\ReferralCode::where('user_id', $user->id)->delete();
                        Log::info("Deleted {$codesDeleted} referral codes");
                    } catch (\Exception $e) {
                        Log::warning("Error deleting referral codes: " . $e->getMessage());
                    }
                }
                
                try {
                    $notificationsDeleted = \App\Models\Notification::where('user_id', $user->id)->delete();
                    Log::info("Deleted {$notificationsDeleted} notifications");
                } catch (\Exception $e) {
                    Log::warning("Error deleting notifications: " . $e->getMessage());
                }
                
                $user->delete();
                Log::info("Successfully deleted user {$id}");
            });
            
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("User not found: {$id}");
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting user ' . $id . ': ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending applications
     */
    public function getApplications()
    {
        $applications = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'pending')
            ->get()
            ->map(function($user) {
                $partnerType = $user->user_type;
                if ($user->user_type === 'caregiver') {
                    $partnerType = 'Caregiver';
                } elseif ($user->user_type === 'housekeeper') {
                    $partnerType = 'Housekeeper';
                } elseif ($user->user_type === 'marketing') {
                    $partnerType = 'Marketing Partner';
                } elseif ($user->user_type === 'training_center') {
                    $partnerType = 'Training Center';
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'type' => $partnerType,
                    'documents' => 'Complete',
                    'applied_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                    'user_type' => $user->user_type
                ];
            });
        
        return response()->json([
            'applications' => $applications
        ]);
    }

    /**
     * Approve an application
     */
    public function approveApplication($id)
    {
        try {
            Log::info("Approving application for user ID: {$id}");
            
            $user = User::findOrFail($id);
            
            // Update status to Active and mark W9 as submitted/verified
            // (Admin approval means W9 was submitted physically in office)
            $user->update([
                'status' => 'Active',
                'w9_submitted' => true,
                'w9_submitted_at' => now(),
                'w9_verified' => true,
                'w9_verified_at' => now()
            ]);
            
            Log::info("User status updated to Active and W9 marked as verified for user ID: {$id}");
        
        // Activate referral code for marketing users
        if ($user->user_type === 'marketing') {
            $referralCode = \App\Models\ReferralCode::where('user_id', $user->id)->first();
            if ($referralCode) {
                $referralCode->update(['is_active' => true]);
            } else {
                \App\Models\ReferralCode::create([
                    'user_id' => $user->id,
                    'code' => \App\Models\ReferralCode::generateCode($user->id),
                    'discount_per_hour' => 5.00,
                    'commission_per_hour' => 1.00,
                    'is_active' => true,
                    'usage_count' => 0,
                    'total_commission_earned' => 0
                ]);
            }
        }
        
        // Send in-app notification
        try {
            NotificationService::notifyAccountApproved($user);
        } catch (\Exception $e) {
            Log::warning("Failed to send approval notification: " . $e->getMessage());
        }
        
        // Send approval email
        $emailSent = false;
        $emailMessage = '';
        try {
            EmailService::sendContractorApprovedEmail($user);
            $emailSent = true;
            $emailMessage = "Approval email sent to {$user->email}";
        } catch (\Exception $e) {
            Log::warning("Failed to send approval email: " . $e->getMessage());
            $emailMessage = "Failed to send email to {$user->email}: " . $e->getMessage();
        }
        
            Log::info("Application approved successfully for user ID: {$id}");
            
            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully' . ($emailSent ? '. ' . $emailMessage : ''),
                'email_sent' => $emailSent,
                'email_message' => $emailMessage
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to approve application for user ID: {$id}. Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an application
     */
    public function rejectApplication($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);
        
        try {
            NotificationService::notifyAccountRejected($user);
        } catch (\Exception $e) {
            Log::warning("Failed to send rejection notification: " . $e->getMessage());
        }
        
        $emailSent = false;
        $emailMessage = '';
        try {
            EmailService::sendContractorRejectedEmail($user);
            $emailSent = true;
            $emailMessage = "Rejection email sent to {$user->email}";
        } catch (\Exception $e) {
            Log::warning("Failed to send rejection email: " . $e->getMessage());
            $emailMessage = "Failed to send email to {$user->email}: " . $e->getMessage();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Application rejected successfully' . ($emailSent ? '. ' . $emailMessage : ''),
            'email_sent' => $emailSent,
            'email_message' => $emailMessage
        ]);
    }

    /**
     * Unapprove an application (revert approval)
     */
    public function unapproveApplication($id)
    {
        try {
            $id = (int) $id;
            $user = User::findOrFail($id);

            $contractorTypes = ['caregiver', 'housekeeper', 'marketing', 'training_center'];
            if (! in_array($user->user_type, $contractorTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only contractor applications (caregiver, housekeeper, marketing, training center) can be unapproved.',
                ], 422);
            }

            $user->status = 'pending';

            $attributes = $user->getAttributes();

            if (array_key_exists('onboarding_complete', $attributes)) {
                $user->onboarding_complete = 0;
            }
            if (array_key_exists('onboarding_completed_at', $attributes)) {
                $user->onboarding_completed_at = null;
            }
            if (array_key_exists('w9_verified', $attributes)) {
                $user->w9_verified = 0;
            }
            if (array_key_exists('w9_verified_at', $attributes)) {
                $user->w9_verified_at = null;
            }

            // Deactivate Stripe payment account
            if (array_key_exists('stripe_account_id', $attributes)) {
                $user->stripe_account_id = null;
            }
            if (array_key_exists('stripe_onboarding_complete', $attributes)) {
                $user->stripe_onboarding_complete = false;
            }
            if (array_key_exists('stripe_customer_id', $attributes)) {
                $user->stripe_customer_id = null;
            }
            if (array_key_exists('stripe_connect_id', $attributes)) {
                $user->stripe_connect_id = null;
            }

            $user->save();

            // Clear Stripe fields on related contractor record if present
            if ($user->user_type === 'caregiver' && $user->caregiver) {
                $caregiver = $user->caregiver;
                $caregiverAttrs = $caregiver->getAttributes();
                if (array_key_exists('stripe_account_id', $caregiverAttrs)) {
                    $caregiver->stripe_account_id = null;
                    $caregiver->save();
                }
            }
            if ($user->user_type === 'housekeeper' && $user->housekeeper) {
                $housekeeper = $user->housekeeper;
                $hkAttrs = $housekeeper->getAttributes();
                if (array_key_exists('stripe_connect_id', $hkAttrs)) {
                    $housekeeper->stripe_connect_id = null;
                    $housekeeper->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Application unapproved successfully. Payment account deactivated.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Unapprove: user not found', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'User not found. Make sure you are using the correct user ID.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Unapprove application failed: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to unapprove application. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get password reset requests
     */
    public function getPasswordResets()
    {
        $rows = DB::table('password_resets_custom')
            ->leftJoin('users', 'password_resets_custom.user_id', '=', 'users.id')
            ->select('password_resets_custom.*', 'users.user_type')
            ->orderBy('password_resets_custom.requested_at', 'desc')
            ->get();

        $resets = $rows->map(function ($r) {
            return [
                'id' => $r->id,
                'email' => $r->email,
                'userType' => isset($r->user_type) ? ucfirst($r->user_type) : 'Unknown',
                'requestedAt' => $r->requested_at ? \Carbon\Carbon::parse($r->requested_at)->format('M d, Y h:i A') : null,
                'status' => isset($r->status) ? ucfirst(strtolower($r->status)) : 'Pending'
            ];
        });

        return response()->json(['resets' => $resets]);
    }

    /**
     * Process a password reset request
     */
    public function processPasswordReset($id)
    {
        try {
            $reset = DB::table('password_resets_custom')->where('id', $id)->first();

            if (!$reset) {
                return response()->json(['success' => false, 'message' => 'Password reset request not found.'], 404);
            }

            $user = null;
            if (!empty($reset->user_id)) {
                $user = User::find($reset->user_id);
            }
            if (!$user && !empty($reset->email)) {
                $user = User::where('email', $reset->email)->first();
            }

            if (!$user) {
                DB::table('password_resets_custom')->where('id', $id)->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                return response()->json(['success' => false, 'message' => 'Associated user not found. Reset marked completed.'], 404);
            }

            $temporaryPassword = \Illuminate\Support\Str::random(12);
            $user->password = Hash::make($temporaryPassword);
            $user->save();

            DB::table('password_resets_custom')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            try {
                $title = 'Password Reset Approved';
                $message = "Your password has been reset by an administrator. Your temporary password is: {$temporaryPassword}. Please log in and change your password immediately for security.";
                EmailService::sendAnnouncementEmail($user->email, $title, $message, 'info');
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('Failed to send password reset notification: ' . $e->getMessage());
                $emailSent = false;
            }

            return response()->json(['success' => true, 'email_sent' => $emailSent]);
        } catch (\Exception $e) {
            Log::error('Error processing password reset (id: ' . $id . '): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all users with their related data
     */
    public function getUsers()
    {
        try {
            $users = User::with(['caregiver', 'client'])->orderBy('created_at', 'desc')->get();
            
            $usersData = $users->map(function($u) {
                // Use client relation zip_code as fallback for client-type users when user.zip_code is empty
                $zipCode = $u->zip_code;
                if (($zipCode === null || $zipCode === '') && $u->user_type === 'client' && $u->relationLoaded('client') && $u->client) {
                    $zipCode = $u->client->getAttribute('zip_code');
                }
                $zipCode = $zipCode !== null && $zipCode !== '' ? (string) $zipCode : $zipCode;

                $data = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'type' => ucfirst($u->user_type),
                    'status' => $u->status ?? 'Active',
                    'joined' => $u->created_at ? $u->created_at->format('M d, Y') : null,
                    'created_at' => $u->created_at ? $u->created_at->toISOString() : null,
                    'email_verified_at' => $u->email_verified_at ? $u->email_verified_at->toISOString() : null,
                    'zip_code' => $zipCode,
                    'zip' => $zipCode,
                    'address' => $u->address,
                    'city' => $u->city,
                    'state' => $u->state,
                    'county' => $u->county,
                    'borough' => $u->borough,
                    'date_of_birth' => $u->date_of_birth,
                ];
                
                if ($u->user_type === 'caregiver' && $u->caregiver) {
                    $data['caregiver'] = [
                        'id' => $u->caregiver->id,
                        'rating' => $u->caregiver->rating,
                        'preferred_hourly_rate_min' => $u->caregiver->preferred_hourly_rate_min,
                        'preferred_hourly_rate_max' => $u->caregiver->preferred_hourly_rate_max,
                    ];
                }
                
                return $data;
            });
            
            $response = response()->json(['users' => $usersData]);
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->header('Pragma', 'no-cache');
            return $response;
        } catch (\Exception $e) {
            Log::error('Error in getUsers: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Minimal caregivers list for admin dashboard table
     */
    public function getCaregivers()
    {
        try {
            $caregivers = User::query()
                ->where('user_type', 'caregiver')
                ->with('caregiver')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $caregivers
                ->filter(fn($u) => $u->caregiver && $u->caregiver->id)
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'zip_code' => $u->zip_code,
                        'city' => $u->city,
                        'county' => $u->county,
                        'borough' => $u->borough,
                        'joined' => $u->created_at ? $u->created_at->format('M d, Y') : null,
                        'caregiver' => [
                            'id' => $u->caregiver->id,
                            'rating' => $u->caregiver->rating,
                            'preferred_hourly_rate_min' => $u->caregiver->preferred_hourly_rate_min,
                            'preferred_hourly_rate_max' => $u->caregiver->preferred_hourly_rate_max,
                        ],
                    ];
                })
                ->values();

            return response()->json(['caregivers' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in getCaregivers: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all housekeepers for admin dashboard
     */
    public function getHousekeepers()
    {
        try {
            $housekeepers = User::query()
                ->where('user_type', 'housekeeper')
                ->with('housekeeper')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $housekeepers
                ->filter(fn($u) => $u->housekeeper && $u->housekeeper->id)
                ->map(function ($u) {
                    $zip = $u->zip_code ? trim((string) $u->zip_code) : '';
                    $location = null;
                    if ($zip !== '') {
                        $location = ZipCodeService::lookupZipCode($zip);
                    }
                    if ($location === null && ($u->city || $u->state)) {
                        $location = trim(implode(', ', array_filter([$u->city, $u->state]))) ?: null;
                    }
                    if ($location === null && $zip !== '') {
                        $location = $zip;
                    }
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'zip_code' => $u->zip_code,
                        'city' => $u->city,
                        'county' => $u->county,
                        'borough' => $u->borough,
                        'state' => $u->state,
                        'status' => $u->status ?? 'Active',
                        'joined' => $u->created_at ? $u->created_at->format('M d, Y') : null,
                        'location' => $location,
                        'years_experience' => (int) ($u->housekeeper->years_experience ?? 0),
                        'housekeeper' => [
                            'id' => $u->housekeeper->id,
                            'rating' => $u->housekeeper->rating,
                            'years_experience' => $u->housekeeper->years_experience,
                            'hourly_rate' => $u->housekeeper->hourly_rate,
                        ],
                    ];
                })
                ->values();

            return response()->json(['housekeepers' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in getHousekeepers: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin caregiver profile for modal
     */
    public function getCaregiverProfile($userId)
    {
        try {
            $user = User::with('caregiver')->where('user_type', 'caregiver')->findOrFail($userId);
            $caregiver = $user->caregiver;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'zip_code' => $user->zip_code,
                    'borough' => $user->borough,
                    'county' => $user->county,
                    'city' => $user->city,
                    'state' => $user->state,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
                'caregiver' => $caregiver ? [
                    'id' => $caregiver->id,
                    'rating' => $caregiver->rating,
                    'preferred_hourly_rate_min' => $caregiver->preferred_hourly_rate_min,
                    'preferred_hourly_rate_max' => $caregiver->preferred_hourly_rate_max,
                    'has_hha' => (bool) $caregiver->has_hha,
                    'hha_number' => $caregiver->hha_number,
                    'has_cna' => (bool) $caregiver->has_cna,
                    'cna_number' => $caregiver->cna_number,
                    'has_rn' => (bool) $caregiver->has_rn,
                    'rn_number' => $caregiver->rn_number,
                    'bio' => $caregiver->bio,
                    'training_certificate' => $caregiver->training_certificate,
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCaregiverProfile: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin housekeeper profile for modal (full details like caregiver)
     */
    public function getHousekeeperProfile($userId)
    {
        try {
            $user = User::with('housekeeper')->where('user_type', 'housekeeper')->findOrFail($userId);
            $housekeeper = $user->housekeeper;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'zip_code' => $user->zip_code,
                    'borough' => $user->borough,
                    'county' => $user->county,
                    'city' => $user->city,
                    'state' => $user->state,
                    'date_of_birth' => $user->date_of_birth,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
                'housekeeper' => $housekeeper ? [
                    'id' => $housekeeper->id,
                    'rating' => $housekeeper->rating,
                    'years_experience' => $housekeeper->years_experience,
                    'hourly_rate' => $housekeeper->hourly_rate,
                    'bio' => $housekeeper->bio,
                    'specializations' => $housekeeper->specializations,
                    'has_own_supplies' => (bool) $housekeeper->has_own_supplies,
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getHousekeeperProfile: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
