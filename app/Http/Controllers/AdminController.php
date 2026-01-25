<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Client;
use App\Models\Booking;
use App\Rules\ValidSSN;
use App\Rules\ValidITIN;
use App\Rules\ValidPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use App\Services\NotificationService;
use App\Services\EmailService;

class AdminController extends Controller
{
    /**
     * Get all bookings for admin dashboard (full details)
     */
    public function getAllBookings()
    {
        try {
            $bookings = Booking::with(['client', 'assignments.caregiver.user', 'referralCode.user', 'payments'])
                ->orderBy('created_at', 'desc')
                ->get();

            $controller = $this;
            $data = $bookings->map(function($b) use ($controller) {
                // Use stored caregivers_needed if available, otherwise calculate from duty_type
                $caregiversNeeded = $b->caregivers_needed ?? $controller->calculateCaregiversNeeded($b->duty_type);

                // Load housekeeper assignments from dedicated table (if present)
                $housekeeperAssignments = [];
                if (Schema::hasTable('booking_housekeeper_assignments')) {
                    $housekeeperAssignments = DB::table('booking_housekeeper_assignments')
                        ->leftJoin('housekeepers', 'housekeepers.id', '=', 'booking_housekeeper_assignments.housekeeper_id')
                        ->leftJoin('users as housekeeper_users', 'housekeeper_users.id', '=', 'housekeepers.user_id')
                        ->where('booking_id', $b->id)
                        ->select([
                            'booking_housekeeper_assignments.*',
                            'housekeeper_users.id as housekeeper_user_id',
                            'housekeeper_users.name as housekeeper_user_name',
                            'housekeeper_users.email as housekeeper_user_email',
                            'housekeeper_users.phone as housekeeper_user_phone',
                        ])
                        ->get()
                        ->map(function ($a) {
                            return [
                                'id' => $a->id,
                                'booking_id' => $a->booking_id,
                                'housekeeper_id' => $a->housekeeper_id,
                                'provider_type' => 'housekeeper',
                                'status' => $a->status,
                                'assigned_hourly_rate' => $a->assigned_hourly_rate,
                                'assignment_order' => $a->assignment_order,
                                'is_active' => $a->is_active,
                                'start_date' => $a->start_date,
                                'end_date' => $a->end_date,
                                'expected_days' => $a->expected_days,

                                // keep shape consistent with caregiver assignments
                                'housekeeper' => [
                                    'id' => $a->housekeeper_id,
                                    'user' => $a->housekeeper_user_id ? [
                                        'id' => $a->housekeeper_user_id,
                                        'name' => $a->housekeeper_user_name,
                                        'email' => $a->housekeeper_user_email,
                                        'phone' => $a->housekeeper_user_phone,
                                    ] : null,
                                ],
                            ];
                        })
                        ->toArray();
                }
                
                // Calculate assignment status based on actual assignments count
                $assignedCount = ($b->assignments ? $b->assignments->count() : 0) + count($housekeeperAssignments);
                if ($assignedCount === 0) {
                    $assignmentStatus = 'unassigned';
                } elseif ($assignedCount >= $caregiversNeeded) {
                    $assignmentStatus = 'assigned';
                } else {
                    $assignmentStatus = 'partial';
                }
                
                // Get payment status from payments table
                $hasCompletedPayment = $b->payments && $b->payments->where('status', 'completed')->isNotEmpty();
                $paymentStatus = $hasCompletedPayment ? 'paid' : 'unpaid';
                
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
                    'starting_time' => $b->start_time, // Alias for frontend compatibility
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
                    'day_schedules' => $b->day_schedules,
                    'urgency_level' => $b->urgency_level,
                    'street_address' => $b->street_address,
                    'apartment_unit' => $b->apartment_unit,
                    'special_instructions' => $b->special_instructions,
                    'status' => $b->status,
                    'assignment_status' => $assignmentStatus,
                    'payment_status' => $paymentStatus,
                    'stripe_payment_intent_id' => $b->stripe_payment_intent_id,
                    'payment_date' => $b->payment_date,
                    'submitted_at' => $b->submitted_at ? (is_string($b->submitted_at) ? $b->submitted_at : $b->submitted_at->toIso8601String()) : null,
                    'created_at' => $b->created_at ? (is_string($b->created_at) ? $b->created_at : $b->created_at->toIso8601String()) : null,
                    'updated_at' => $b->updated_at ? (is_string($b->updated_at) ? $b->updated_at : $b->updated_at->toIso8601String()) : null,
                    'referral_code_id' => $b->referral_code_id,
                    'referral_discount_applied' => $b->referral_discount_applied,
                    'caregivers_needed' => $caregiversNeeded,
                    'referral_code' => $b->referralCode ? [
                        'id' => $b->referralCode->id,
                        'code' => $b->referralCode->code,
                        'user' => $b->referralCode->user ? [
                            'id' => $b->referralCode->user->id,
                            'name' => $b->referralCode->user->name,
                            'email' => $b->referralCode->user->email
                        ] : null
                    ] : null,
                    'client' => $b->client ? [
                        'id' => $b->client->id,
                        'name' => $b->client->name
                    ] : null,
                    'assignments' => array_values(array_merge(
                        // caregiver assignments
                        $b->assignments ? $b->assignments->map(function($assignment) {
                        return [
                            'id' => $assignment->id,
                            'caregiver_id' => $assignment->caregiver_id,
                            'booking_id' => $assignment->booking_id,
                            'provider_type' => 'caregiver',
                            'status' => $assignment->status,
                            'assigned_hourly_rate' => $assignment->assigned_hourly_rate,
                            'caregiver' => $assignment->caregiver ? [
                                'id' => $assignment->caregiver->id,
                                'user' => $assignment->caregiver->user ? [
                                    'id' => $assignment->caregiver->user->id,
                                    'name' => $assignment->caregiver->user->name,
                                    'email' => $assignment->caregiver->user->email,
                                    'phone' => $assignment->caregiver->user->phone,
                                ] : null
                            ] : null
                        ];
                    })->toArray() : [],

                        // housekeeper assignments
                        $housekeeperAssignments
                    )),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAllBookings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

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
            'zip_code' => ['nullable','string','regex:/^\d{5}(-\d{4})?$/'],
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
            // Add file upload validation if file upload is present in this form
            // 'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        // Sanitize bio field to prevent XSS
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        // Generate secure random password for new users created by admin
        // TODO: Send password reset email to new user so they can set their own password
        // For now, generate a secure random password (user will need to use "Forgot Password")
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
                // Note: training_center is stored in User model, not Caregiver model
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

    public function updateUser(Request $request, $id)
    {
    $id = (int) $id;
    $user = User::with('caregiver')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            // IMPORTANT: ignore the route param id (the record being updated)
            // to avoid false "email already taken" when the payload contains the same email.
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'phone' => ['sometimes', 'nullable', new ValidPhoneNumber, 'max:20'],
            'date_of_birth' => 'sometimes|nullable|date|before:today|after:1900-01-01',
            'address' => 'sometimes|nullable|string|max:255',
            'state' => 'sometimes|nullable|string|max:50',
            'county' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'borough' => 'sometimes|nullable|string|max:100',
            'zip_code' => ['sometimes', 'nullable', 'string', 'regex:/^\d{5}(-\d{4})?$/'],
            'status' => 'sometimes|in:Active,Inactive,Suspended',

            // training center selection from admin caregiver edit modal
            // (string name of a user_type training_center/training)
            'training_center' => 'sometimes|nullable|string|max:255',

            // caregiver-specific fields (optional)
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

        // Update the User table fields.
        // IMPORTANT: some installs/DBs may not have every optional column (e.g. `county`).
        // Only write columns that exist to avoid SQLSTATE[42S22] "Unknown column".
        $existingUserColumns = [];
        try {
            $existingUserColumns = DB::getSchemaBuilder()->getColumnListing('users');
        } catch (\Exception $e) {
            // If schema inspection fails, fall back to a minimal safe set.
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

            // Map the selected training center name to caregiver.training_center_id.
            // If it's not a known center, treat it as a custom center.
            if (array_key_exists('training_center', $validated)) {
                $tcName = trim((string) ($validated['training_center'] ?? ''));

                if ($tcName === '') {
                    $caregiverUpdate['training_center_id'] = null;
                    $caregiverUpdate['has_training_center'] = false;
                    // column is enum(['pending','approved','rejected'])
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
                        // Not found in list -> store as custom if schema supports it
                        $caregiverUpdate['training_center_id'] = null;
                        $caregiverUpdate['has_training_center'] = true;
                        // Treat custom center as pending (enum-safe)
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

    public function updateUserStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended'
        ]);
        
        $user = User::findOrFail($id);
        $user->update(['status' => $validated['status']]);
        
        return response()->json(['success' => true]);
    }

    public function updateCaregiverStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended'
        ]);
        
        $caregiver = Caregiver::findOrFail($id);
        $caregiver->user->update(['status' => $validated['status']]);
        
        return response()->json(['success' => true]);
    }

    public function deleteUser($id)
    {
        try {
            \Log::info("Attempting to delete user ID: {$id}");
            $user = User::with(['caregiver', 'client'])->findOrFail($id);
            \Log::info("Found user: {$user->name} (Type: {$user->user_type})");
            
            // Use database transaction to ensure all related records are deleted atomically
            DB::transaction(function() use ($user, $id) {
                // Handle related records based on user type
                if ($user->user_type === 'caregiver') {
                \Log::info("Deleting caregiver-related records for user {$id}");
                // Delete caregiver record and related data
                $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
                if ($caregiver) {
                    $caregiverId = $caregiver->id;
                    // Delete booking assignments
                    try {
                        $assignmentsDeleted = \App\Models\BookingAssignment::where('caregiver_id', $caregiverId)->delete();
                        \Log::info("Deleted {$assignmentsDeleted} booking assignments");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting assignments: " . $e->getMessage());
                    }
                    
                    // Delete time trackings
                    try {
                        $trackingsDeleted = \App\Models\TimeTracking::where('caregiver_id', $caregiverId)->delete();
                        \Log::info("Deleted {$trackingsDeleted} time trackings");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting time trackings: " . $e->getMessage());
                    }
                    
                    // Delete reviews where caregiver is involved
                    try {
                        $reviewsDeleted = \App\Models\Review::where('caregiver_id', $caregiverId)->delete();
                        \Log::info("Deleted {$reviewsDeleted} reviews");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting reviews: " . $e->getMessage());
                    }
                    
                    // Delete payments
                    try {
                        $paymentsDeleted = \App\Models\Payment::where('caregiver_id', $caregiverId)->delete();
                        \Log::info("Deleted {$paymentsDeleted} payments");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting payments: " . $e->getMessage());
                    }
                    
                    // Delete caregiver record
                    try {
                        $caregiver->delete();
                        \Log::info("Deleted caregiver record");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting caregiver record: " . $e->getMessage());
                    }
                }
            } elseif ($user->user_type === 'client') {
                \Log::info("Deleting client-related records for user {$id}");
                // Delete client record and related data
                $client = \App\Models\Client::where('user_id', $user->id)->first();
                if ($client) {
                    // Get booking IDs first
                    $bookingIds = \App\Models\Booking::where('client_id', $user->id)->pluck('id');
                    
                    // Delete booking assignments first
                    if ($bookingIds->count() > 0) {
                        try {
                            $assignmentsDeleted = \App\Models\BookingAssignment::whereIn('booking_id', $bookingIds)->delete();
                            \Log::info("Deleted {$assignmentsDeleted} booking assignments");
                        } catch (\Exception $e) {
                            \Log::warning("Error deleting booking assignments: " . $e->getMessage());
                        }
                    }
                    
                    // Delete bookings
                    try {
                        $bookingsDeleted = \App\Models\Booking::where('client_id', $user->id)->delete();
                        \Log::info("Deleted {$bookingsDeleted} bookings");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting bookings: " . $e->getMessage());
                    }
                    
                    // Delete reviews
                    try {
                        $reviewsDeleted = \App\Models\Review::where('client_id', $user->id)->delete();
                        \Log::info("Deleted {$reviewsDeleted} reviews");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting reviews: " . $e->getMessage());
                    }
                    
                    // Delete payments
                    try {
                        $paymentsDeleted = \App\Models\Payment::where('client_id', $user->id)->delete();
                        \Log::info("Deleted {$paymentsDeleted} payments");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting payments: " . $e->getMessage());
                    }
                    
                    // Delete client record
                    try {
                        $client->delete();
                        \Log::info("Deleted client record");
                    } catch (\Exception $e) {
                        \Log::warning("Error deleting client record: " . $e->getMessage());
                    }
                }
            } elseif ($user->user_type === 'marketing') {
                \Log::info("Deleting marketing staff-related records for user {$id}");
                // Delete referral codes
                try {
                    $codesDeleted = \App\Models\ReferralCode::where('user_id', $user->id)->delete();
                    \Log::info("Deleted {$codesDeleted} referral codes");
                } catch (\Exception $e) {
                    \Log::warning("Error deleting referral codes: " . $e->getMessage());
                }
            }
            
            // Delete notifications
            try {
                $notificationsDeleted = \App\Models\Notification::where('user_id', $user->id)->delete();
                \Log::info("Deleted {$notificationsDeleted} notifications");
            } catch (\Exception $e) {
                \Log::warning("Error deleting notifications: " . $e->getMessage());
            }
            
            // Finally delete the user
            $user->delete();
            \Log::info("Successfully deleted user {$id}");
            });
            
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("User not found: {$id}");
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting user ' . $id . ': ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getApplications()
    {
        // Get all pending users (contractors/partners) from users table
        $applications = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'pending')
            ->get()
            ->map(function($user) {
                // Determine partner type based on user_type
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
                    'documents' => 'Complete', // You can add document checking logic here
                    'applied_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                    'user_type' => $user->user_type
                ];
            });
        
        return response()->json([
            'applications' => $applications
        ]);
    }

    public function approveApplication($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'Active']);
        
        // Activate referral code for marketing users
        if ($user->user_type === 'marketing') {
            $referralCode = \App\Models\ReferralCode::where('user_id', $user->id)->first();
            if ($referralCode) {
                // Activate existing referral code
                $referralCode->update(['is_active' => true]);
            } else {
                // Create referral code if it doesn't exist (for legacy users)
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
        
        return response()->json([
            'success' => true,
            'message' => 'Application approved successfully' . ($emailSent ? '. ' . $emailMessage : ''),
            'email_sent' => $emailSent,
            'email_message' => $emailMessage
        ]);
    }

    public function rejectApplication($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);
        
        // Send in-app notification
        try {
            NotificationService::notifyAccountRejected($user);
        } catch (\Exception $e) {
            Log::warning("Failed to send rejection notification: " . $e->getMessage());
        }
        
        // Send rejection email
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

    public function unapproveApplication($id)
    {
        $user = User::findOrFail($id);

        // Revert an accidentally approved contractor back to the application pipeline.
        $user->status = 'pending';

        // If your onboarding/tax fields exist (added by the payroll/tax feature), reset them too.
        // These checks avoid errors on older DBs/environments.
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

        // Deactivate Stripe payment account - clear their connected account
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

        return response()->json(['success' => true, 'message' => 'Application unapproved successfully. Payment account deactivated.']);
    }

    public function processPasswordReset($id)
    {
        try {
            $reset = DB::table('password_resets_custom')->where('id', $id)->first();

            if (!$reset) {
                return response()->json(['success' => false, 'message' => 'Password reset request not found.'], 404);
            }

            // Find the user either by user_id or email
            $user = null;
            if (!empty($reset->user_id)) {
                $user = \App\Models\User::find($reset->user_id);
            }
            if (!$user && !empty($reset->email)) {
                $user = \App\Models\User::where('email', $reset->email)->first();
            }

            if (!$user) {
                // Still mark the reset as completed to avoid retry loops, but indicate failure
                DB::table('password_resets_custom')->where('id', $id)->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                return response()->json(['success' => false, 'message' => 'Associated user not found. Reset marked completed.'], 404);
            }

            // Generate a secure random temporary password (12 characters with letters, numbers, and special chars)
            $temporaryPassword = \Illuminate\Support\Str::random(12);
            $user->password = Hash::make($temporaryPassword);
            $user->save();

            // Mark the reset as completed
            DB::table('password_resets_custom')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Notify the user by email that their password was changed
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

    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'recipients' => 'required|in:all,caregivers,clients',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);

        // Save announcement
        $announcement = DB::table('announcements')->insertGetId($validated + ['created_at' => now(), 'updated_at' => now()]);
        
        // Create notifications for recipients
        $users = collect();
        
        if ($validated['recipients'] === 'all') {
            $users = User::all();
        } elseif ($validated['recipients'] === 'caregivers') {
            $users = User::where('user_type', 'caregiver')->get();
        } elseif ($validated['recipients'] === 'clients') {
            $users = User::where('user_type', 'client')->get();
        }
        
        $emailsSent = 0;
        foreach ($users as $user) {
            // Create in-app notification
            DB::table('notifications')->insert([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => 'System',
                'priority' => $validated['priority'],
                'read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send email notification
            try {
                EmailService::sendAnnouncementEmail(
                    $user->email,
                    $validated['title'],
                    $validated['message'],
                    $validated['type']
                );
                $emailsSent++;
            } catch (\Exception $e) {
                Log::warning("Failed to send announcement email to {$user->email}: " . $e->getMessage());
            }
        }
        
        $emailSummary = "Emails sent to {$emailsSent} out of {$users->count()} recipients";
        return response()->json([
            'success' => true, 
            'id' => $announcement, 
            'notifications_sent' => $users->count(), 
            'emails_sent' => $emailsSent,
            'message' => "Announcement sent successfully. {$emailSummary}."
        ]);
    }

    public function getAnnouncements()
    {
        return response()->json([
            'announcements' => DB::table('announcements')->latest()->take(10)->get()
        ]);
    }

    public function settings()
    {
        $landing = [
            'hero_title' => AppSetting::getValue('landing.hero_title', 'Find Trusted Caregivers & Housekeepers'),
            'hero_subtitle' => AppSetting::getValue('landing.hero_subtitle', 'Care you can trust. Help at home you can rely on.'),
            'hero_cta_text' => AppSetting::getValue('landing.hero_cta_text', 'Find Caregivers & Housekeepers'),
            'hero_cta_url' => AppSetting::getValue('landing.hero_cta_url', '/register'),
        ];

        return view('admin.settings', compact('landing'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            // Landing
            'landing.hero_title' => 'nullable|string|max:255',
            'landing.hero_subtitle' => 'nullable|string|max:500',
            'landing.hero_cta_text' => 'nullable|string|max:255',
            'landing.hero_cta_url' => 'nullable|string|max:255',
        ]);

        foreach (($validated['landing'] ?? []) as $key => $value) {
            AppSetting::setValue('landing.' . $key, $value);
        }

        return back()->with('success', 'Settings updated');
    }

    /**
     * Send a test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $email = $request->input('email');
            \App\Mail\TestEmail::class;
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\TestEmail());
            
            Log::info("Test email sent to {$email}");
            
            return response()->json([
                'success' => true,
                'message' => "Test email sent successfully to {$email}"
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send test email: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Failed to send test email: " . $e->getMessage()
            ], 500);
        }
    }

    public function assignCaregivers(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'caregiver_ids' => 'required|array',
            'assigned_rates' => 'required|array',
            'assigned_rates.*' => 'required|numeric|min:0', // Basic validation, detailed check below
            'caregivers_needed' => 'sometimes|integer|min:1'
        ]);

        // Validate booking exists
        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        // Update caregivers_needed if provided
        if (isset($validated['caregivers_needed']) && $validated['caregivers_needed'] != $booking->caregivers_needed) {
            $booking->caregivers_needed = $validated['caregivers_needed'];
            $booking->save();
        }

        // Validate caregivers exist and rates are within their preferred range
        foreach ($validated['caregiver_ids'] as $caregiverId) {
            $caregiver = \App\Models\Caregiver::find($caregiverId);
            if (!$caregiver) {
                return response()->json(['success' => false, 'message' => "Caregiver ID {$caregiverId} not found"], 404);
            }
            
            // Validate rate is within caregiver's preferred range
            $assignedRate = $validated['assigned_rates'][$caregiverId] ?? null;
            if (!$assignedRate) {
                return response()->json(['success' => false, 'message' => "Assigned rate required for caregiver ID {$caregiverId}"], 422);
            }
            
            $min = $caregiver->preferred_hourly_rate_min ?? 20;
            $max = $caregiver->preferred_hourly_rate_max ?? 50;
            
            if ($assignedRate < $min || $assignedRate > $max) {
                $caregiverName = $caregiver->user->name ?? "Caregiver {$caregiverId}";
                return response()->json([
                    'success' => false, 
                    'message' => "Rate \${$assignedRate} is outside {$caregiverName}'s preferred range (\${$min} - \${$max})"
                ], 422);
            }
        }

        // Delete existing assignments
        DB::table('booking_assignments')->where('booking_id', $bookingId)->delete();

        // Create new assignments only if caregiver_ids is not empty
        if (!empty($validated['caregiver_ids'])) {
            // Calculate days per caregiver (typically 15 days each)
            $daysPerCaregiver = 15;
            $serviceDate = \Carbon\Carbon::parse($booking->service_date);
            
            foreach ($validated['caregiver_ids'] as $index => $caregiverId) {
                $order = $index + 1; // 1-based ordering
                
                // Calculate start and end dates for this caregiver
                $startDate = $serviceDate->copy()->addDays(($order - 1) * $daysPerCaregiver);
                $endDate = $startDate->copy()->addDays($daysPerCaregiver - 1);
                
                // First caregiver is active, others are pending
                $isActive = ($order === 1);
                
                // Get assigned rate for this caregiver
                $assignedRate = $validated['assigned_rates'][$caregiverId];
                
                DB::table('booking_assignments')->insert([
                    'booking_id' => $bookingId,
                    'caregiver_id' => $caregiverId,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'assignment_order' => $order,
                    'is_active' => $isActive,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'expected_days' => $daysPerCaregiver,
                    'assigned_hourly_rate' => $assignedRate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Update booking's assigned_hourly_rate if single caregiver
            if (count($validated['caregiver_ids']) === 1) {
                $booking->update([
                    'assigned_hourly_rate' => $validated['assigned_rates'][$validated['caregiver_ids'][0]]
                ]);
            }
        }

        // Assignment status is tracked via the booking_assignments table
        // No need to update a separate assignment_status column
        
        return response()->json(['success' => true]);
    }

    public function unassignCaregiver(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'caregiver_id' => 'required|integer'
        ]);

        // Log the request for debugging
        \Log::info('Unassign request', [
            'booking_id' => $bookingId,
            'caregiver_id' => $validated['caregiver_id']
        ]);

        // Check if assignment exists before deleting
        $assignment = DB::table('booking_assignments')
            ->where('booking_id', $bookingId)
            ->where('caregiver_id', $validated['caregiver_id'])
            ->first();

        if (!$assignment) {
            \Log::warning('Assignment not found', [
                'booking_id' => $bookingId,
                'caregiver_id' => $validated['caregiver_id']
            ]);
            return response()->json(['success' => false, 'error' => 'Assignment not found'], 404);
        }

        $deleted = DB::table('booking_assignments')
            ->where('booking_id', $bookingId)
            ->where('caregiver_id', $validated['caregiver_id'])
            ->delete();

        \Log::info('Assignment deleted', [
            'booking_id' => $bookingId,
            'caregiver_id' => $validated['caregiver_id'],
            'deleted_count' => $deleted
        ]);

        if ($deleted) {
            // Assignment status is tracked via the booking_assignments table count
            // No need to update a separate assignment_status column
            
            return response()->json(['success' => true, 'message' => 'Caregiver unassigned successfully']);
        } else {
            return response()->json(['success' => false, 'error' => 'Failed to delete assignment'], 500);
        }
    }

    /**
     * Get housekeeper schedule for a booking.
     * Returns: { schedule: { days: string[], schedules: object } | null }
     */
    public function getHousekeeperSchedule(Request $request, $bookingId, $housekeeperId)
    {
        try {
            if (!Schema::hasTable('housekeeper_schedules')) {
                return response()->json(['success' => true, 'schedule' => null]);
            }

            $row = DB::table('housekeeper_schedules')
                ->where('booking_id', $bookingId)
                ->where('housekeeper_id', $housekeeperId)
                ->first();

            if (!$row) {
                return response()->json(['success' => true, 'schedule' => null]);
            }

            return response()->json([
                'success' => true,
                'schedule' => [
                    'days' => json_decode($row->days, true) ?: [],
                    'schedules' => json_decode($row->schedules, true) ?: (object)[],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getHousekeeperSchedule: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load schedule'], 500);
        }
    }

    /**
     * Upsert housekeeper schedule for a booking.
     */
    public function updateHousekeeperSchedule(Request $request, $bookingId, $housekeeperId)
    {
        $validated = $request->validate([
            'days' => 'nullable|array',
            'days.*' => 'string',
            'schedules' => 'nullable|array',
        ]);

        try {
            if (!Schema::hasTable('housekeeper_schedules')) {
                return response()->json(['success' => false, 'message' => 'Scheduling not available'], 400);
            }

            $days = $validated['days'] ?? [];
            $schedules = $validated['schedules'] ?? [];

            DB::table('housekeeper_schedules')->updateOrInsert(
                [
                    'booking_id' => $bookingId,
                    'housekeeper_id' => $housekeeperId,
                ],
                [
                    'days' => json_encode(array_values(array_unique($days))),
                    'schedules' => json_encode($schedules),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in updateHousekeeperSchedule: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save schedule'], 500);
        }
    }

    /**
     * Unassign a housekeeper from a booking.
     */
    public function unassignHousekeeper(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'housekeeper_id' => 'required|integer',
        ]);

        try {
            if (!Schema::hasTable('booking_housekeeper_assignments')) {
                return response()->json(['success' => false, 'error' => 'Housekeeper assignments table not found'], 400);
            }

            $housekeeperId = (int) $validated['housekeeper_id'];

            $deletedAssignments = DB::table('booking_housekeeper_assignments')
                ->where('booking_id', $bookingId)
                ->where('housekeeper_id', $housekeeperId)
                ->delete();

            // Clean up schedule for this booking/housekeeper
            if (Schema::hasTable('housekeeper_schedules')) {
                $deletedSchedules = DB::table('housekeeper_schedules')
                    ->where('booking_id', $bookingId)
                    ->where('housekeeper_id', $housekeeperId)
                    ->delete();
            }

            // Idempotent response: even if nothing was deleted, the end state is "unassigned".
            return response()->json([
                'success' => true,
                'deleted_assignments' => $deletedAssignments,
                'message' => $deletedAssignments > 0 ? 'Housekeeper unassigned' : 'Housekeeper already unassigned'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in unassignHousekeeper: ' . $e->getMessage());
            // If something goes wrong after the delete already happened, don't block UX.
            // Return a 200 with success=false would still show an error toast; instead return success=true with a warning.
            return response()->json([
                'success' => true,
                'warning' => 'Unassigned, but cleanup may be incomplete'
            ]);
        }
    }

    /**
     * Assign housekeepers to a booking (mirrors assignCaregivers but uses booking_assignments.provider_type=housekeeper)
     */
    public function assignHousekeepers(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'housekeeper_ids' => 'required|array',
            'assigned_rates' => 'required|array',
            'assigned_rates.*' => 'required|numeric|min:0',
            'housekeepers_needed' => 'sometimes|integer|min:1'
        ]);

        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        // Update caregivers_needed if provided (booking column name is still caregivers_needed)
        if (isset($validated['housekeepers_needed']) && $validated['housekeepers_needed'] != $booking->caregivers_needed) {
            $booking->caregivers_needed = $validated['housekeepers_needed'];
            $booking->save();
        }

        // Validate housekeepers exist
        foreach ($validated['housekeeper_ids'] as $housekeeperId) {
            $housekeeper = \App\Models\Housekeeper::with('user')->find($housekeeperId);
            if (!$housekeeper) {
                return response()->json(['success' => false, 'message' => "Housekeeper ID {$housekeeperId} not found"], 404);
            }

            $assignedRate = $validated['assigned_rates'][$housekeeperId] ?? null;
            if ($assignedRate === null) {
                return response()->json(['success' => false, 'message' => "Assigned rate required for housekeeper ID {$housekeeperId}"], 422);
            }
        }

        // Ensure we have a dedicated table for housekeeper assignments.
        // The existing `booking_assignments` table requires `caregiver_id` (NOT NULL).
        // So we cannot safely store housekeeper rows there without changing schema.
        if (!DB::getSchemaBuilder()->hasTable('booking_housekeeper_assignments')) {
            return response()->json([
                'success' => false,
                'message' => 'Housekeeper assignments table is missing. Please run migrations.'
            ], 500);
        }

        // Delete existing housekeeper assignments for this booking only
        DB::table('booking_housekeeper_assignments')
            ->where('booking_id', $bookingId)
            ->delete();

        if (!empty($validated['housekeeper_ids'])) {
            $daysPerWorker = 15;
            $serviceDate = \Carbon\Carbon::parse($booking->service_date);

            foreach ($validated['housekeeper_ids'] as $index => $housekeeperId) {
                $order = $index + 1;
                $startDate = $serviceDate->copy()->addDays(($order - 1) * $daysPerWorker);
                $endDate = $startDate->copy()->addDays($daysPerWorker - 1);

                $isActive = ($order === 1);
                $assignedRate = $validated['assigned_rates'][$housekeeperId];

                DB::table('booking_housekeeper_assignments')->insert([
                    'booking_id' => $bookingId,
                    'housekeeper_id' => $housekeeperId,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'assignment_order' => $order,
                    'is_active' => $isActive,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'expected_days' => $daysPerWorker,
                    'assigned_hourly_rate' => $assignedRate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // If single housekeeper assigned, store assigned_hourly_rate on booking (same column used)
            if (count($validated['housekeeper_ids']) === 1) {
                $booking->update([
                    'assigned_hourly_rate' => $validated['assigned_rates'][$validated['housekeeper_ids'][0]]
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get all marketing staff with their referral code statistics
     */
    public function getMarketingStaff()
    {
        $marketingUsers = User::where('user_type', 'marketing')
            ->orderBy('created_at', 'desc')
            ->get();

        $staff = $marketingUsers->map(function ($user) {
            // Get referral code for this user
            $referralCode = \App\Models\ReferralCode::where('user_id', $user->id)->first();
            
            // Calculate statistics
            $clientsAcquired = 0;
            $totalHours = 0;
            $commissionEarned = 0;
            
            if ($referralCode) {
                // Only count bookings that are approved, confirmed, or completed (not pending)
                // These are the bookings where work has started or is ongoing
                $approvedBookings = Booking::where('referral_code_id', $referralCode->id)
                    ->whereIn('status', ['approved', 'confirmed', 'completed'])
                    ->get();
                
                // Count unique clients who used this referral code (only from approved bookings)
                $uniqueClientUserIds = $approvedBookings->pluck('client_id')->unique();
                $clientsAcquired = $uniqueClientUserIds->count();
                
                // Calculate total hours from time_trackings for clients with approved bookings
                // Only count hours worked for bookings that are approved/confirmed/completed
                if ($uniqueClientUserIds->count() > 0 && DB::getSchemaBuilder()->hasTable('time_trackings')) {
                    // Get client IDs from the clients table that match the user_ids from approved bookings
                    $clientIds = \App\Models\Client::whereIn('user_id', $uniqueClientUserIds)
                        ->pluck('id');
                    
                    if ($clientIds->count() > 0) {
                        // Get time trackings for these clients (only completed time trackings)
                        $timeTrackings = DB::table('time_trackings')
                            ->whereIn('client_id', $clientIds)
                            ->whereNotNull('clock_out_time')
                            ->where('status', 'completed')
                            ->get();
                        
                        $totalHours = $timeTrackings->sum('hours_worked') ?? 0;
                        
                        // Calculate commission using the referral code's commission_per_hour rate
                        $commissionRate = $referralCode->commission_per_hour ?? 1.00;
                        $commissionEarned = $totalHours * $commissionRate;
                    }
                }
                
                // If no time trackings yet, use booking duration as fallback estimate (but don't count for commission)
                if ($totalHours == 0) {
                    foreach ($approvedBookings as $booking) {
                        // Estimate hours from duty_type (default 8 hours per day)
                        $hoursPerDay = 8;
                        if (preg_match('/(\d+)\s*hours?/i', $booking->duty_type ?? '', $matches)) {
                            $hoursPerDay = (int)$matches[1];
                        }
                        $totalHours += ($booking->duration_days ?? 0) * $hoursPerDay;
                    }
                    // Don't calculate commission from estimates, only from actual time trackings
                }
            }
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'status' => $user->status ?? 'Active',
                'referralCode' => $referralCode ? $referralCode->code : 'N/A',
                'clientsAcquired' => $clientsAcquired,
                'totalHours' => number_format($totalHours, 1),
                'commissionEarned' => number_format($commissionEarned, 2),
                'joined' => $user->created_at->format('M d, Y'),
            ];
        });

        return response()->json(['staff' => $staff]);
    }

    /**
     * Store new marketing staff
     */
    public function storeMarketingStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstName' => 'nullable|string',
            'lastName' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:50',
            'county' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'status' => 'required|in:Active,Inactive'
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'user_type' => 'marketing',
            'status' => $validated['status']
        ];

        if (isset($validated['date_of_birth'])) $userData['date_of_birth'] = $validated['date_of_birth'];
        if (isset($validated['address'])) $userData['address'] = $validated['address'];
        if (isset($validated['state'])) $userData['state'] = $validated['state'];
        if (isset($validated['county'])) $userData['county'] = $validated['county'];
        if (isset($validated['city'])) $userData['city'] = $validated['city'];
        if (isset($validated['zip_code'])) $userData['zip_code'] = $validated['zip_code'];

        $user = User::create($userData);

        // Create referral code for the new marketing staff
        $referralCode = \App\Models\ReferralCode::create([
            'user_id' => $user->id,
            'code' => \App\Models\ReferralCode::generateCode($user->id),
            'discount_per_hour' => 5.00,
            'commission_per_hour' => 1.00,
            'is_active' => true,
            'usage_count' => 0,
            'total_commission_earned' => 0
        ]);

        return response()->json([
            'success' => true,
            'staff' => $user,
            'referral_code' => $referralCode->code
        ]);
    }

    /**
     * Update marketing staff
     */
    public function updateMarketingStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|in:Active,Inactive'
        ]);

        $user = User::where('id', $id)->where('user_type', 'marketing')->firstOrFail();
        $user->update($validated);

        return response()->json(['success' => true, 'staff' => $user]);
    }

    /**
     * Delete marketing staff
     */
    public function deleteMarketingStaff($id)
    {
        $user = User::where('id', $id)->where('user_type', 'marketing')->firstOrFail();
        
        // Use database transaction for data consistency
        DB::transaction(function() use ($user) {
            // Also delete their referral code
            \App\Models\ReferralCode::where('user_id', $user->id)->delete();
            
            $user->delete();
        });

        return response()->json(['success' => true]);
    }

    /**
     * Get all training centers with their statistics
     */
    public function getTrainingCenters()
    {
        try {
            // Also include 'training' user_type for backward compatibility
            $trainingCenterUsers = User::whereIn('user_type', ['training_center', 'training'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('Training centers query found ' . $trainingCenterUsers->count() . ' users');
            
            // Debug: Log all user types to help diagnose
            $allUserTypes = User::select('user_type')->distinct()->pluck('user_type')->toArray();
            Log::info('All user types in database: ' . json_encode($allUserTypes));
            
            // Debug: Check if training@demo.com exists
            $testUser = User::where('email', 'training@demo.com')->first();
            if ($testUser) {
                Log::info('Found training@demo.com user with user_type: ' . $testUser->user_type);
            } else {
                Log::warning('training@demo.com user not found in database');
            }

            $centers = $trainingCenterUsers->map(function ($user) {
                try {
                    // Count caregivers using this training center
                    $caregiverCount = 0;
                    try {
                        $caregiverCount = Caregiver::where('training_center_id', $user->id)
                            ->where('training_center_approval_status', 'approved')
                            ->count();
                    } catch (\Exception $e) {
                        Log::warning("Error counting caregivers for training center {$user->id}: " . $e->getMessage());
                    }
                    
                    // Calculate total hours and commission from time trackings
                    $totalHours = 0;
                    $commissionEarned = 0;
                    
                    try {
                        $caregiversWithCenter = Caregiver::where('training_center_id', $user->id)
                            ->where('training_center_approval_status', 'approved')
                            ->get();
                        
                        foreach ($caregiversWithCenter as $caregiver) {
                            try {
                                if (DB::getSchemaBuilder()->hasTable('time_trackings')) {
                                    $timeTrackings = DB::table('time_trackings')
                                        ->where('caregiver_id', $caregiver->id)
                                        ->whereNotNull('clock_out_time')
                                        ->get();
                                    
                                    foreach ($timeTrackings as $tracking) {
                                        $hours = $tracking->hours_worked ?? 0;
                                        $totalHours += $hours;
                                        $commissionEarned += $hours * 0.50; // $0.50/hr commission
                                    }
                                }
                            } catch (\Exception $e) {
                                // Silently continue if there's an error calculating hours
                                Log::debug("Error calculating hours for caregiver {$caregiver->id}: " . $e->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        Log::debug("Error fetching caregivers for training center {$user->id}: " . $e->getMessage());
                    }
                    
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'Unknown',
                        'email' => $user->email ?? '',
                        'phone' => $user->phone ?? '',
                        'address' => $user->address ?? '',
                        'date_of_birth' => $user->date_of_birth ?? null,
                        'state' => $user->state ?? null,
                        'county' => $user->county ?? null,
                        'city' => $user->city ?? null,
                        'zip_code' => $user->zip_code ?? null,
                        'status' => $user->status ?? 'Active',
                        'caregiverCount' => $caregiverCount,
                        'totalHours' => number_format($totalHours, 1),
                        'commissionEarned' => number_format($commissionEarned, 2),
                        'joined' => $user->created_at ? $user->created_at->format('M d, Y') : '',
                    ];
                } catch (\Exception $e) {
                    Log::error("Error processing training center {$user->id}: " . $e->getMessage());
                    // Return basic data even if statistics fail
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'Unknown',
                        'email' => $user->email ?? '',
                        'phone' => $user->phone ?? '',
                        'address' => $user->address ?? '',
                        'date_of_birth' => $user->date_of_birth ?? null,
                        'state' => $user->state ?? null,
                        'county' => $user->county ?? null,
                        'city' => $user->city ?? null,
                        'zip_code' => $user->zip_code ?? null,
                        'status' => $user->status ?? 'Active',
                        'caregiverCount' => 0,
                        'totalHours' => '0.0',
                        'commissionEarned' => '0.00',
                        'joined' => $user->created_at ? $user->created_at->format('M d, Y') : '',
                    ];
                }
            });

            return response()->json(['centers' => $centers]);
        } catch (\Exception $e) {
            Log::error("Error in getTrainingCenters: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Return 200 with empty array so frontend doesn't show error toast
            // The table will show "No data available" instead
            return response()->json([
                'centers' => []
            ]);
        }
    }

    /**
     * Store new training center
     */
    public function storeTrainingCenter(Request $request)
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
            'zip_code' => ['nullable','string','regex:/^\d{5}(-\d{4})?$/'],
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:Active,Inactive'
        ]);

        // Generate secure random password for new training centers created by admin
        // User will need to use "Forgot Password" to set their own password
        $temporaryPassword = $validated['password'] ?? \Illuminate\Support\Str::random(16);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($temporaryPassword),
            'user_type' => 'training_center',
            'status' => $validated['status']
        ];

        // Add optional fields
        if (isset($validated['phone'])) $userData['phone'] = $validated['phone'];
        if (isset($validated['date_of_birth'])) $userData['date_of_birth'] = $validated['date_of_birth'];
        if (isset($validated['address'])) $userData['address'] = $validated['address'];
        if (isset($validated['state'])) $userData['state'] = $validated['state'];
        if (isset($validated['county'])) $userData['county'] = $validated['county'];
        if (isset($validated['city'])) $userData['city'] = $validated['city'];
        if (isset($validated['zip_code'])) $userData['zip_code'] = $validated['zip_code'];

        // Use database transaction for data consistency
        $user = DB::transaction(function() use ($userData) {
            return User::create($userData);
        });

        // Send notification to new user
        try {
            \App\Services\NotificationService::notifyAccountCreated($user);
        } catch (\Exception $e) {
            Log::warning("Failed to send account creation notification to training center {$user->id}: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'center' => $user]);
    }

    /**
     * Update training center
     */
    public function updateTrainingCenter(Request $request, $id)
    {
        try {
            // Ensure ID is an integer
            $id = (int) $id;
            
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid training center ID.'
                ], 400);
            }
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'phone' => ['nullable', new ValidPhoneNumber, 'max:20'],
                'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
                'address' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:50',
                'county' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'zip_code' => ['nullable','string','regex:/^\d{5}(-\d{4})?$/'],
                'status' => 'sometimes|in:Active,Inactive'
            ]);

            // Also include 'training' user_type for backward compatibility
            $user = User::where('id', $id)->whereIn('user_type', ['training_center', 'training'])->first();
            
            if (!$user) {
                Log::warning("Training center not found for update", [
                    'id' => $id,
                    'user_type_check' => User::where('id', $id)->value('user_type'),
                    'exists' => User::where('id', $id)->exists()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Training center not found. Please refresh the page and try again.'
                ], 404);
            }
            
            // Build update data
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['email'])) $updateData['email'] = $validated['email'];
            if (isset($validated['phone'])) $updateData['phone'] = $validated['phone'];
            if (isset($validated['date_of_birth'])) $updateData['date_of_birth'] = $validated['date_of_birth'];
            if (isset($validated['address'])) $updateData['address'] = $validated['address'];
            if (isset($validated['state'])) $updateData['state'] = $validated['state'];
            if (isset($validated['county'])) $updateData['county'] = $validated['county'];
            if (isset($validated['city'])) $updateData['city'] = $validated['city'];
            if (isset($validated['zip_code'])) $updateData['zip_code'] = $validated['zip_code'];
            if (isset($validated['status'])) $updateData['status'] = $validated['status'];
            
            $user->update($updateData);

            return response()->json(['success' => true, 'center' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error updating training center {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update training center. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete training center
     */
    public function deleteTrainingCenter($id)
    {
        // Also include 'training' user_type for backward compatibility
        $user = User::where('id', $id)->whereIn('user_type', ['training_center', 'training'])->firstOrFail();
        
        // Use database transaction for data consistency
        DB::transaction(function() use ($user) {
            // Remove training center association from caregivers
            Caregiver::where('training_center_id', $user->id)->update([
                'training_center_id' => null,
                'has_training_center' => false,
                'training_center_approval_status' => null
            ]);
            
            $user->delete();
        });

        return response()->json(['success' => true]);
    }

    /**
     * Get caregivers for a specific training center
     */
    public function getTrainingCenterCaregivers($id)
    {
        // Only show approved caregivers
        $caregivers = Caregiver::where('training_center_id', $id)
            ->where('training_center_approval_status', 'approved')
            ->with('user')
            ->get()
            ->map(function ($caregiver) {
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->user->name ?? 'Unknown',
                    'email' => $caregiver->user->email ?? '',
                    'status' => $caregiver->user->status ?? 'Active',
                ];
            });

        return response()->json(['caregivers' => $caregivers]);
    }

    /**
     * Get payment statistics for admin dashboard
     */
    public function getPaymentStats()
    {
        // Calculate total revenue from completed bookings
        $completedBookings = Booking::where('status', 'completed')->get();
        $totalRevenue = $completedBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: 45; // Client rate for revenue
            return $hours * $booking->duration_days * $rate;
        });
        
        // Calculate pending payments from approved bookings
        $pendingBookings = Booking::whereIn('status', ['approved', 'confirmed'])->get();
        $pendingPayments = $pendingBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: 45; // Client rate for revenue
            return $hours * $booking->duration_days * $rate;
        });
        $pendingCount = $pendingBookings->count();
        
        // Calculate salaries due from time trackings (completed time trackings)
        $timeTrackings = DB::table('time_trackings')
            ->where('status', 'completed')
            ->whereNotNull('clock_out_time')
            ->get();
        $salariesDue = $timeTrackings->sum(function($tracking) {
            return ($tracking->hours_worked ?? 0) * 28; // Caregiver rate $28/hr
        });
        $caregiversWithSalary = $timeTrackings->pluck('caregiver_id')->unique()->count();
        
        // Processing fees (2.5% of total revenue)
        $processingFees = $totalRevenue * 0.025;
        
        return response()->json([
            'stats' => [
                ['title' => 'Total Revenue', 'value' => '$' . number_format($totalRevenue, 0), 'icon' => 'mdi-currency-usd', 'color' => 'success', 'change' => '+15%', 'changeColor' => 'success--text'],
                ['title' => 'Pending Payments', 'value' => '$' . number_format($pendingPayments, 0), 'icon' => 'mdi-clock', 'color' => 'warning', 'change' => $pendingCount . ' pending', 'changeColor' => 'warning--text'],
                ['title' => 'Salaries Due', 'value' => '$' . number_format($salariesDue, 0), 'icon' => 'mdi-account-cash', 'color' => 'info', 'change' => $caregiversWithSalary . ' caregivers', 'changeColor' => 'info--text'],
                ['title' => 'Processing Fees', 'value' => '$' . number_format($processingFees, 0), 'icon' => 'mdi-percent', 'color' => 'error', 'change' => '2.5% avg', 'changeColor' => 'error--text'],
            ]
        ]);
    }

    /**
     * Get recent transactions for admin dashboard
     */
    public function getTransactions()
    {
        $transactions = DB::table('transactions')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'date' => \Carbon\Carbon::parse($t->created_at)->format('Y-m-d'),
                    'description' => $t->description,
                    'type' => ucfirst($t->type),
                    'amount' => number_format($t->amount, 0),
                    'status' => ucfirst($t->status),
                    'reference' => $t->reference ?? 'TXN-' . str_pad($t->id, 3, '0', STR_PAD_LEFT),
                    'time' => \Carbon\Carbon::parse($t->created_at)->diffForHumans()
                ];
            });
        
        // If no transactions exist, generate from bookings
        if ($transactions->isEmpty()) {
            $bookings = Booking::with('client')
                ->whereIn('status', ['completed', 'approved'])
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get();
            
            $transactions = $bookings->map(function($b) {
                $hours = $this->extractHours($b->duty_type);
                $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45); // Client payment amount
                return [
                    'id' => $b->id,
                    'date' => \Carbon\Carbon::parse($b->updated_at)->format('Y-m-d'),
                    'description' => 'Payment from ' . ($b->client->name ?? 'Client'),
                    'type' => 'Payment',
                    'amount' => number_format($amount, 0),
                    'status' => $b->status === 'completed' ? 'Completed' : 'Pending',
                    'reference' => 'PAY-' . str_pad($b->id, 3, '0', STR_PAD_LEFT),
                    'time' => \Carbon\Carbon::parse($b->updated_at)->diffForHumans()
                ];
            });
        }
        
        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Get client payments for admin dashboard
     */
    public function getClientPayments()
    {
        $bookings = Booking::with('client')
            ->whereIn('status', ['approved', 'confirmed', 'completed', 'pending'])
            ->orderBy('service_date', 'desc')
            ->take(20)
            ->get();
        
        $payments = $bookings->map(function($b) {
            $hours = $this->extractHours($b->duty_type);
            $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45); // Client payment amount
            $dueDate = \Carbon\Carbon::parse($b->service_date);
            $isPast = $dueDate->isPast();
            
            $status = 'Pending';
            if ($b->status === 'completed') {
                $status = 'Paid';
            } elseif ($isPast && $b->status !== 'completed') {
                $status = 'Overdue';
            }
            
            return [
                'id' => $b->id,
                'client' => $b->client->name ?? 'Unknown Client',
                'service' => $b->service_type . ' - ' . ($hours * $b->duration_days) . 'hrs',
                'amount' => '$' . number_format($amount, 0),
                'dueDate' => $dueDate->format('Y-m-d'),
                'status' => $status
            ];
        });
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get caregiver salaries for admin dashboard
     */
    public function getCaregiverSalaries()
    {
        $caregivers = Caregiver::with('user')->get();
        
        $payments = $caregivers->map(function($caregiver) {
            // Get time trackings for this caregiver this month
            $timeTrackings = \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->orderBy('work_date', 'desc')
                ->get();
            
            $totalHours = $timeTrackings->sum('hours_worked');
            $totalEarnings = $timeTrackings->sum('caregiver_earnings');
            $rate = $totalHours > 0 ? ($totalEarnings / $totalHours) : 28;
            
            // Check payment status
            $unpaidRecords = $timeTrackings->whereNull('paid_at');
            $unpaidHours = $unpaidRecords->sum('hours_worked');
            $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');
            
            if ($totalHours == 0) {
                $status = 'No Hours';
            } elseif ($unpaidHours == 0) {
                $status = 'Paid';
            } elseif ($unpaidHours == $totalHours) {
                $status = 'Pending';
            } else {
                $status = 'Partial';
            }
            
            // Get bank account status
            $bankConnected = !empty($caregiver->user->stripe_connect_id);
            
            return [
                'id' => $caregiver->id,
                'caregiver' => $caregiver->user->name ?? 'Unknown',
                'caregiver_email' => $caregiver->user->email ?? '',
                'total_hours' => round($totalHours, 2),
                'hours_display' => number_format($totalHours, 1) . ' hrs',
                'rate' => '$' . number_format($rate, 2) . '/hr',
                'total_amount' => $totalEarnings,
                'amount_display' => '$' . number_format($totalEarnings, 2),
                'unpaid_hours' => round($unpaidHours, 2),
                'unpaid_amount' => $unpaidAmount,
                'unpaid_display' => '$' . number_format($unpaidAmount, 2),
                'period' => now()->format('M Y'),
                'status' => $status,
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'days_worked' => $timeTrackings->count(),
                'stripe_connect_id' => $caregiver->user->stripe_connect_id,
                'can_pay' => $bankConnected && $unpaidAmount > 0
            ];
        })->filter(function($payment) {
            return $payment['total_hours'] > 0;
        })->values();
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get housekeeper salaries for admin dashboard
     */
    public function getHousekeeperSalaries()
    {
        $housekeepers = Housekeeper::with('user')->get();

        $payments = $housekeepers->map(function($housekeeper) {
            // Get time trackings for this housekeeper this month
            $timeTrackings = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->orderBy('work_date', 'desc')
                ->get();

            $totalHours = $timeTrackings->sum('hours_worked');
            $totalEarnings = $timeTrackings->sum('caregiver_earnings');

            // Use computed rate (fallback to housekeeper hourly_rate, then 25)
            $rate = $totalHours > 0
                ? ($totalEarnings / $totalHours)
                : ($housekeeper->hourly_rate ?? 25);

            // Check payment status
            $unpaidRecords = $timeTrackings->whereNull('paid_at');
            $unpaidHours = $unpaidRecords->sum('hours_worked');
            $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');

            if ($totalHours == 0) {
                $status = 'No Hours';
            } elseif ($unpaidHours == 0) {
                $status = 'Paid';
            } elseif ($unpaidHours == $totalHours) {
                $status = 'Pending';
            } else {
                $status = 'Partial';
            }

            // Get bank account status
            $bankConnected = !empty($housekeeper->user->stripe_connect_id);

            return [
                'id' => $housekeeper->id,
                'housekeeper' => $housekeeper->user->name ?? 'Unknown',
                'housekeeper_email' => $housekeeper->user->email ?? '',
                'total_hours' => round($totalHours, 2),
                'hours_display' => number_format($totalHours, 1) . ' hrs',
                'rate' => '$' . number_format($rate, 2) . '/hr',
                'total_amount' => $totalEarnings,
                'amount_display' => '$' . number_format($totalEarnings, 2),
                'unpaid_hours' => round($unpaidHours, 2),
                'unpaid_amount' => $unpaidAmount,
                'unpaid_display' => '$' . number_format($unpaidAmount, 2),
                'period' => now()->format('M Y'),
                'status' => $status,
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'days_worked' => $timeTrackings->count(),
                'stripe_connect_id' => $housekeeper->user->stripe_connect_id,
                'can_pay' => $bankConnected && $unpaidAmount > 0
            ];
        })->filter(function($payment) {
            return $payment['total_hours'] > 0;
        })->values();

        return response()->json(['payments' => $payments]);
    }

    /**
     * Process payment to caregiver
     */
    public function payCaregiver(Request $request)
    {
        try {
            $validated = $request->validate([
                'caregiver_id' => 'required|exists:caregivers,id',
                'amount' => 'required|numeric|min:0.01' // SECURITY: min:0.01 prevents $0 transfers
            ]);

            // SECURITY: Wrap in transaction with row locking to prevent race conditions
            return DB::transaction(function () use ($validated) {
                $caregiver = Caregiver::with('user')->findOrFail($validated['caregiver_id']);
                
                // Check if caregiver has bank account connected
                if (empty($caregiver->user->stripe_connect_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Caregiver has not connected their bank account'
                    ], 400);
                }

                // SECURITY: Lock rows for update to prevent concurrent payments
                $unpaidRecords = \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereNull('paid_at')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidRecords->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No unpaid hours found for this caregiver'
                    ], 400);
                }

                $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');
                
                // Validate amount matches
                if (abs($unpaidAmount - $validated['amount']) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount does not match unpaid earnings'
                    ], 400);
                }

                // Create Stripe payout with idempotency key
                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                
                // SECURITY: Idempotency key prevents duplicate transfers if admin clicks twice
                $idempotencyKey = 'caregiver_payout_' . $caregiver->id . '_' . $unpaidRecords->pluck('id')->implode('_');
                
                $payout = $stripe->transfers->create([
                    'amount' => intval($validated['amount'] * 100), // Convert to cents
                    'currency' => 'usd',
                    'destination' => $caregiver->user->stripe_connect_id,
                    'description' => "Payment for " . $unpaidRecords->count() . " work sessions",
                    'metadata' => [
                        'caregiver_id' => $caregiver->id,
                        'record_ids' => $unpaidRecords->pluck('id')->implode(','),
                        'payment_type' => 'caregiver_earnings'
                    ]
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);
                
                Log::info('Stripe transfer created', [
                    'transfer_id' => $payout->id,
                    'amount' => $validated['amount'],
                    'destination' => $caregiver->user->stripe_connect_id,
                    'idempotency_key' => $idempotencyKey
                ]);

                // Mark all unpaid records as paid (inside transaction)
                $unpaidRecords->each(function($record) use ($payout) {
                    $record->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid',
                        'stripe_transfer_id' => $payout->id
                    ]);
                });

                Log::info('Caregiver payment processed', [
                    'caregiver_id' => $caregiver->id,
                    'caregiver_name' => $caregiver->user->name,
                    'amount' => $validated['amount'],
                    'records_paid' => $unpaidRecords->count(),
                    'stripe_connect_id' => $caregiver->user->stripe_connect_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'amount' => $validated['amount'],
                    'caregiver' => $caregiver->user->name,
                    'records_paid' => $unpaidRecords->count(),
                    'transfer_id' => $payout->id
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Caregiver payment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment to housekeeper
     */
    public function payHousekeeper(Request $request)
    {
        try {
            $validated = $request->validate([
                'housekeeper_id' => 'required|exists:housekeepers,id',
                'amount' => 'required|numeric|min:0.01' // SECURITY: min:0.01 prevents $0 transfers
            ]);

            // SECURITY: Wrap in transaction with row locking to prevent race conditions
            return DB::transaction(function () use ($validated) {
                $housekeeper = Housekeeper::with('user')->findOrFail($validated['housekeeper_id']);
                
                // Check if housekeeper has bank account connected
                if (empty($housekeeper->user->stripe_connect_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Housekeeper has not connected their bank account'
                    ], 400);
                }

                // SECURITY: Lock rows for update to prevent concurrent payments
                $unpaidRecords = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereNull('paid_at')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidRecords->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No unpaid hours found for this housekeeper'
                    ], 400);
                }

                $unpaidAmount = $unpaidRecords->sum('caregiver_earnings'); // Using same field for earnings
                
                // Validate amount matches
                if (abs($unpaidAmount - $validated['amount']) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount does not match unpaid earnings'
                    ], 400);
                }

                // Create Stripe payout with idempotency key
                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                
                // SECURITY: Idempotency key prevents duplicate transfers if admin clicks twice
                $idempotencyKey = 'housekeeper_payout_' . $housekeeper->id . '_' . $unpaidRecords->pluck('id')->implode('_');
                
                $payout = $stripe->transfers->create([
                    'amount' => intval($validated['amount'] * 100), // Convert to cents
                    'currency' => 'usd',
                    'destination' => $housekeeper->user->stripe_connect_id,
                    'description' => "Payment for " . $unpaidRecords->count() . " work sessions",
                    'metadata' => [
                        'housekeeper_id' => $housekeeper->id,
                        'record_ids' => $unpaidRecords->pluck('id')->implode(','),
                        'payment_type' => 'housekeeper_earnings'
                    ]
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);
                
                Log::info('Stripe transfer created for housekeeper', [
                    'transfer_id' => $payout->id,
                    'amount' => $validated['amount'],
                    'destination' => $housekeeper->user->stripe_connect_id,
                    'idempotency_key' => $idempotencyKey
                ]);

                // Mark all unpaid records as paid (inside transaction)
                $unpaidRecords->each(function($record) use ($payout) {
                    $record->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid',
                        'stripe_transfer_id' => $payout->id
                    ]);
                });

                Log::info('Housekeeper payment processed', [
                    'housekeeper_id' => $housekeeper->id,
                    'housekeeper_name' => $housekeeper->user->name,
                    'amount' => $validated['amount'],
                    'records_paid' => $unpaidRecords->count(),
                    'stripe_connect_id' => $housekeeper->user->stripe_connect_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'amount' => $validated['amount'],
                    'housekeeper' => $housekeeper->user->name,
                    'records_paid' => $unpaidRecords->count(),
                    'transfer_id' => $payout->id
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Housekeeper payment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketing commissions for admin dashboard
     */
    public function getMarketingCommissions()
    {
        // Some installs don't have this column yet. Don't crash the whole admin UI.
        if (Schema::hasTable('time_trackings') && !Schema::hasColumn('time_trackings', 'marketing_commission_paid_at')) {
            Log::warning('Missing time_trackings.marketing_commission_paid_at. Returning empty marketing commissions.');
            return response()->json(['commissions' => []]);
        }

        $marketingStaff = User::where('user_type', 'marketing')
            ->with('referralCode')
            ->get();
        
        $commissions = $marketingStaff->map(function($user) {
            // Get total and pending commissions from time_trackings
            $totalCommission = \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->sum('marketing_partner_commission');
            
            $pendingCommission = \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->whereNull('marketing_commission_paid_at')
                ->sum('marketing_partner_commission');
            
            $paidCommission = $totalCommission - $pendingCommission;
            
            // Get referral code
            $referralCode = $user->referralCode ? $user->referralCode->code : 'N/A';
            
            // Count how many clients used their code
            $clientsReferred = \App\Models\Booking::whereHas('client.user', function($q) use ($user) {
                $q->where('referred_by', $user->referralCode?->id ?? 0);
            })->distinct('client_id')->count('client_id');
            
            // Get bank account status
            $bankConnected = !empty($user->stripe_connect_id);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $referralCode,
                'clients_referred' => $clientsReferred,
                'total_commission' => $totalCommission,
                'pending_commission' => $pendingCommission,
                'paid_commission' => $paidCommission,
                'total_display' => '$' . number_format($totalCommission, 2),
                'pending_display' => '$' . number_format($pendingCommission, 2),
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'payment_status' => $pendingCommission > 0 ? 'Pending' : 'Paid',
                'stripe_connect_id' => $user->stripe_connect_id,
                'can_pay' => $bankConnected && $pendingCommission > 0
            ];
        })->filter(function($commission) {
            return $commission['total_commission'] > 0;
        })->values();
        
        return response()->json(['commissions' => $commissions]);
    }

    /**
     * Pay marketing commission to staff member
     */
    public function payMarketingCommission($userId)
    {
        // SECURITY: Wrap in transaction with row locking to prevent race conditions and double payments
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            
            // SECURITY: Lock rows for update to prevent concurrent payments
            $pendingRecords = \App\Models\TimeTracking::where('marketing_partner_id', $userId)
                ->whereNull('marketing_commission_paid_at')
                ->lockForUpdate()
                ->get();
            
            $pendingCommission = $pendingRecords->sum('marketing_partner_commission');
            
            if ($pendingCommission <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commission to pay'
                ], 400);
            }
            
            // Check if bank is connected
            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank account not connected. Please ask the marketing staff to connect their bank account first.'
                ], 400);
            }
            
            // Transfer via Stripe Connect with idempotency key
            \Stripe\Stripe::setApiKey(config('stripe.secret'));
            
            // SECURITY: Idempotency key prevents duplicate transfers if admin clicks twice
            $idempotencyKey = 'marketing_commission_' . $userId . '_' . $pendingRecords->pluck('id')->implode('_');
            
            $transfer = \Stripe\Transfer::create([
                'amount' => (int)($pendingCommission * 100), // Convert to cents
                'currency' => 'usd',
                'destination' => $user->stripe_connect_id,
                'description' => "Marketing commission payment for " . $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => 'marketing',
                    'commission_amount' => $pendingCommission,
                    'record_count' => $pendingRecords->count()
                ]
            ], [
                'idempotency_key' => $idempotencyKey
            ]);
            
            // Mark all pending commissions as paid (inside transaction)
            \App\Models\TimeTracking::whereIn('id', $pendingRecords->pluck('id'))
                ->update([
                    'marketing_commission_paid_at' => now(),
                    'marketing_commission_stripe_transfer_id' => $transfer->id
                ]);
            
            Log::info('Marketing commission payment processed', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'amount' => $pendingCommission,
                'records_paid' => $pendingRecords->count(),
                'transfer_id' => $transfer->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'transfer_id' => $transfer->id,
                'amount' => $pendingCommission
            ]);
        }); // End transaction
    }

    /**
     * Get training center commissions for admin dashboard
     */
    public function getTrainingCommissions()
    {
        $trainingCenters = User::where('user_type', 'training')
            ->get();
        
        $commissions = $trainingCenters->map(function($user) {
            // Get total and pending commissions from time_trackings (using training_center_user_id)
            $totalCommission = \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->sum('training_center_commission');
            
            $pendingCommission = \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->where('training_paid', 0)
                ->sum('training_center_commission');
            
            $paidCommission = $totalCommission - $pendingCommission;
            
            // Count how many caregivers they trained
            $caregiversTrained = \App\Models\Caregiver::where('training_center_id', $user->id)->count();
            
            // Get bank account status
            $bankConnected = !empty($user->stripe_connect_id);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'center_name' => $user->business_name ?? $user->name,
                'caregivers_trained' => $caregiversTrained,
                'total_commission' => $totalCommission,
                'pending_commission' => $pendingCommission,
                'paid_commission' => $paidCommission,
                'total_display' => '$' . number_format($totalCommission, 2),
                'pending_display' => '$' . number_format($pendingCommission, 2),
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'payment_status' => $pendingCommission > 0 ? 'Pending' : 'Paid',
                'stripe_connect_id' => $user->stripe_connect_id,
                'can_pay' => $bankConnected && $pendingCommission > 0
            ];
        })->filter(function($commission) {
            return $commission['total_commission'] > 0;
        })->values();
        
        return response()->json(['commissions' => $commissions]);
    }

    /**
     * Pay training center commission
     */
    public function payTrainingCommission($userId)
    {
        // SECURITY: Wrap in transaction with row locking to prevent race conditions and double payments
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            
            // SECURITY: Lock rows for update to prevent concurrent payments
            $pendingRecords = \App\Models\TimeTracking::where('training_center_id', $userId)
                ->whereNull('training_commission_paid_at')
                ->lockForUpdate()
                ->get();
            
            $pendingCommission = $pendingRecords->sum('training_center_commission');
            
            if ($pendingCommission <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commission to pay'
                ], 400);
            }
            
            // Check if bank is connected
            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank account not connected. Please ask the training center to connect their bank account first.'
                ], 400);
            }
            
            // Transfer via Stripe Connect with idempotency key
            \Stripe\Stripe::setApiKey(config('stripe.secret'));
            
            // SECURITY: Idempotency key prevents duplicate transfers if admin clicks twice
            $idempotencyKey = 'training_commission_' . $userId . '_' . $pendingRecords->pluck('id')->implode('_');
            
            $transfer = \Stripe\Transfer::create([
                'amount' => (int)($pendingCommission * 100), // Convert to cents
                'currency' => 'usd',
                'destination' => $user->stripe_connect_id,
                'description' => "Training center commission payment for " . $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => 'training',
                    'commission_amount' => $pendingCommission,
                    'record_count' => $pendingRecords->count()
                ]
            ], [
                'idempotency_key' => $idempotencyKey
            ]);
            
            // Mark all pending commissions as paid (inside transaction)
            \App\Models\TimeTracking::whereIn('id', $pendingRecords->pluck('id'))
                ->update([
                    'training_commission_paid_at' => now(),
                    'training_commission_stripe_transfer_id' => $transfer->id
                ]);
            
            Log::info('Training commission payment processed', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'amount' => $pendingCommission,
                'records_paid' => $pendingRecords->count(),
                'transfer_id' => $transfer->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'transfer_id' => $transfer->id,
                'amount' => $pendingCommission
            ]);
        }); // End transaction
    }

    /**
     * Get top performing caregivers for admin dashboard
     */
    public function getTopPerformers()
    {
        $caregivers = Caregiver::with('user')
            ->orderBy('rating', 'desc')
            ->take(10)
            ->get()
            ->map(function($caregiver) {
                $rating = $caregiver->rating ?? 0;
                $color = $rating >= 4.5 ? 'success' : ($rating >= 4.0 ? 'info' : 'warning');
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->user->name ?? 'Unknown',
                    'rating' => number_format($rating, 1),
                    'color' => $color,
                    'reviews' => $caregiver->total_reviews ?? 0
                ];
            });
        
        return response()->json(['performers' => $caregivers]);
    }

    /**
     * Get recent activity for admin dashboard
     */
    public function getRecentActivity()
    {
        $activities = collect();
        
        // Recent bookings
        $recentBookings = Booking::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($b) {
                return [
                    'id' => 'booking-' . $b->id,
                    'text' => 'New booking from ' . ($b->client->name ?? 'client'),
                    'time' => $b->created_at->diffForHumans(),
                    'icon' => 'mdi-calendar-plus',
                    'color' => 'success'
                ];
            });
        $activities = $activities->merge($recentBookings);
        
        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($u) {
                return [
                    'id' => 'user-' . $u->id,
                    'text' => ucfirst($u->user_type) . ' registered: ' . $u->name,
                    'time' => $u->created_at->diffForHumans(),
                    'icon' => 'mdi-account-plus',
                    'color' => $u->user_type === 'caregiver' ? 'info' : 'primary'
                ];
            });
        $activities = $activities->merge($recentUsers);
        
        return response()->json(['activities' => $activities->sortByDesc('time')->take(10)->values()]);
    }

    /**
     * Calculate number of caregivers needed based on hours per day
     * 8 hours per day = 1 caregiver
     * 12 hours per day = 2 caregivers
     * 24 hours per day = 3 caregivers
     */
    private function calculateCaregiversNeeded($dutyType)
    {
        $hoursPerDay = $this->extractHours($dutyType);
        
        if ($hoursPerDay <= 8) {
            return 1;
        } elseif ($hoursPerDay <= 12) {
            return 2;
        } elseif ($hoursPerDay <= 24) {
            return 3;
        } else {
            // For more than 24 hours, calculate proportionally (every 8 hours = 1 caregiver)
            return ceil($hoursPerDay / 8);
        }
    }

    /**
     * Extract hours from duty_type string
     */
    private function extractHours($dutyType)
    {
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8;
    }

    /**
     * Get all admin staff users
     */
    public function getAdminStaff()
    {
        $adminStaffUsers = User::where('user_type', 'admin')
            ->where('role', 'Admin Staff')
            ->orderBy('created_at', 'desc')
            ->get();

        $staff = $adminStaffUsers->map(function ($user) {
            // Parse page_permissions from JSON
            $pagePermissions = $user->page_permissions;
            if (is_string($pagePermissions)) {
                $pagePermissions = json_decode($pagePermissions, true);
            }
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'status' => $user->status ?? 'Active',
                'email_verified' => $user->email_verified_at ? 'Yes' : 'No',
                'joined' => $user->created_at->format('M d, Y'),
                'last_login' => $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y H:i') : 'Never',
                'page_permissions' => $pagePermissions ?? $this->getDefaultAdminStaffPermissions(),
            ];
        });

        return response()->json(['staff' => $staff]);
    }

    /**
     * Get default page permissions for admin staff (all pages enabled by default)
     */
    private function getDefaultAdminStaffPermissions()
    {
        return [
            'dashboard' => true,
            'notifications' => true,
            'users' => true,
            'caregivers' => true,
            'housekeepers' => true,
            'clients' => true,
            'admin-staff' => true,
            'marketing-staff' => true,
            'training-centers' => true,
            'pending' => true,
            'password-resets' => true,
            'client-bookings' => true,
            'time-tracking' => true,
            'reviews' => true,
            'announcements' => true,
            'payments' => true,
            'analytics' => true,
            'profile' => true,
        ];
    }

    /**
     * Store new admin staff
     */
    public function storeAdminStaff(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
                'status' => 'required|in:Active,Inactive',
                'page_permissions' => 'nullable|array'
            ]);

            // Use default permissions if not provided
            $pagePermissions = $validated['page_permissions'] ?? $this->getDefaultAdminStaffPermissions();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'user_type' => 'admin',
                'role' => 'Admin Staff',
                'status' => $validated['status'],
                'email_verified_at' => now(), // Auto-verify admin staff
                'page_permissions' => json_encode($pagePermissions)
            ];

            $user = User::create($userData);

            return response()->json([
                'success' => true,
                'staff' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('storeAdminStaff error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update admin staff
     */
    public function updateAdminStaff(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'status' => 'sometimes|in:Active,Inactive',
                'password' => 'nullable|string|min:8',
                'page_permissions' => 'nullable|array'
            ]);

            $user = User::where('id', $id)
                ->where('user_type', 'admin')
                ->where('role', 'Admin Staff')
                ->firstOrFail();
            
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['email'])) $updateData['email'] = $validated['email'];
            if (isset($validated['phone'])) $updateData['phone'] = $validated['phone'];
            if (isset($validated['status'])) $updateData['status'] = $validated['status'];
            if (isset($validated['password'])) $updateData['password'] = Hash::make($validated['password']);
            if (isset($validated['page_permissions'])) $updateData['page_permissions'] = json_encode($validated['page_permissions']);
            
            $user->update($updateData);

            return response()->json(['success' => true, 'staff' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('updateAdminStaff error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete admin staff
     */
    public function deleteAdminStaff($id)
    {
        $user = User::where('id', $id)
            ->where('user_type', 'admin')
            ->where('role', 'Admin Staff')
            ->firstOrFail();
        
        $user->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get current admin staff user's page permissions
     */
    public function getAdminStaffPermissions()
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'admin' || $user->role !== 'Admin Staff') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $pagePermissions = $user->page_permissions;
        if (is_string($pagePermissions)) {
            $pagePermissions = json_decode($pagePermissions, true);
        }

        return response()->json([
            'permissions' => $pagePermissions ?? $this->getDefaultAdminStaffPermissions()
        ]);
    }

    /**`n     * Get all users with their related data
     */
    public function getUsers()
    {
        try {
            $users = User::with(['caregiver', 'client'])->orderBy('created_at', 'desc')->get();
            
            $usersData = $users->map(function($u) {
                $data = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'type' => ucfirst($u->user_type),
                    'status' => $u->status ?? 'Active',
                    'joined' => $u->created_at ? $u->created_at->format('M Y') : null,
                    'created_at' => $u->created_at ? $u->created_at->toISOString() : null,
                    'email_verified_at' => $u->email_verified_at ? $u->email_verified_at->toISOString() : null,
                    'zip_code' => $u->zip_code,
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
            
            return response()->json(['users' => $usersData]);
        } catch (\Exception $e) {
            Log::error('Error in getUsers: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Minimal caregivers list for admin dashboard table.
     *
     * Contract:
     * - Returns JSON always.
     * - Includes canonical `zip_code` from `users.zip_code`.
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
                        'joined' => $u->created_at ? $u->created_at->format('M Y') : null,
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
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'zip_code' => $u->zip_code,
                        'status' => $u->status ?? 'Active',
                        'joined' => $u->created_at ? $u->created_at->format('M d, Y') : null,
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
     * Admin caregiver profile for modal.
     * Returns JSON: { user: {...}, caregiver: {...} }
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

}
