<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Client;
use App\Models\Booking;
use App\Rules\ValidSSN;
use App\Rules\ValidITIN;
use App\Rules\ValidPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            $bookings = Booking::with(['client', 'assignments.caregiver.user', 'referralCode.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            $controller = $this;
            $data = $bookings->map(function($b) use ($controller) {
                // Calculate caregivers needed based on hours per day
                $caregiversNeeded = $controller->calculateCaregiversNeeded($b->duty_type);
                
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
                    'assignments' => $b->assignments ? $b->assignments->map(function($assignment) {
                        return [
                            'id' => $assignment->id,
                            'caregiver_id' => $assignment->caregiver_id,
                            'booking_id' => $assignment->booking_id,
                            'status' => $assignment->status,
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
            'user_type' => 'required|in:client,caregiver,admin',
            'status' => 'nullable|in:Active,Inactive,Suspended',
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
            }
            
            return $user;
        });

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'status' => 'sometimes|in:Active,Inactive,Suspended'
        ]);
        
        $user = User::findOrFail($id);
        $user->update($validated);
        return response()->json(['success' => true, 'user' => $user]);
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
        $applications = User::whereIn('user_type', ['caregiver', 'marketing', 'training_center'])
            ->where('status', 'pending')
            ->get()
            ->map(function($user) {
                // Determine partner type based on user_type
                $partnerType = $user->user_type;
                if ($user->user_type === 'caregiver') {
                    // Could be caregiver, housekeeping, or personal_assistant - default to caregiver for now
                    // You might want to add a partner_type field to users table if you need to distinguish
                    $partnerType = 'Caregiver';
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

            // Set the user's password to the requested dev password (123456) and save
            $devPassword = '123456';
            $user->password = Hash::make($devPassword);
            $user->save();

            // Mark the reset as completed
            DB::table('password_resets_custom')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Notify the user by email that their password was changed
            try {
                $title = 'Password Reset Approved';
                $message = "Your password has been reset by an administrator. Your temporary password is: {$devPassword}. Please log in and change your password immediately.";
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
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
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
            'caregiver_ids' => 'required|array'
        ]);

        // Validate booking exists
        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        // Validate caregivers exist
        $validCaregiverIds = \App\Models\Caregiver::whereIn('id', $validated['caregiver_ids'])->pluck('id')->toArray();
        if (count($validCaregiverIds) !== count($validated['caregiver_ids'])) {
            return response()->json(['success' => false, 'message' => 'One or more caregivers not found'], 404);
        }

        // Delete existing assignments
        DB::table('booking_assignments')->where('booking_id', $bookingId)->delete();

        // Create new assignments only if caregiver_ids is not empty
        if (!empty($validated['caregiver_ids'])) {
            foreach ($validated['caregiver_ids'] as $caregiverId) {
                DB::table('booking_assignments')->insert([
                    'booking_id' => $bookingId,
                    'caregiver_id' => $caregiverId,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Update the booking's assignment_status
        // Calculate caregivers needed based on hours per day
        $caregiversNeeded = $this->calculateCaregiversNeeded($booking->duty_type);
        $assignedCount = count($validated['caregiver_ids']);
        
        if ($assignedCount === 0) {
            $assignmentStatus = 'unassigned';
        } elseif ($assignedCount >= $caregiversNeeded) {
            $assignmentStatus = 'assigned';
        } else {
            $assignmentStatus = 'partial';
        }
        
        $booking->update(['assignment_status' => $assignmentStatus]);

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
            // Update the booking's assignment_status
            $booking = \App\Models\Booking::find($bookingId);
            if ($booking) {
                $remainingCount = DB::table('booking_assignments')->where('booking_id', $bookingId)->count();
                // Calculate caregivers needed based on hours per day
                $caregiversNeeded = $this->calculateCaregiversNeeded($booking->duty_type);
                
                if ($remainingCount === 0) {
                    $assignmentStatus = 'unassigned';
                } elseif ($remainingCount >= $caregiversNeeded) {
                    $assignmentStatus = 'assigned';
                } else {
                    $assignmentStatus = 'partial';
                }
                
                $booking->update(['assignment_status' => $assignmentStatus]);
            }
            
            return response()->json(['success' => true, 'message' => 'Caregiver unassigned successfully']);
        } else {
            return response()->json(['success' => false, 'error' => 'Failed to delete assignment'], 500);
        }
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
            $rate = $booking->hourly_rate ?: 45;
            return $hours * $booking->duration_days * $rate;
        });
        
        // Calculate pending payments from approved bookings
        $pendingBookings = Booking::whereIn('status', ['approved', 'confirmed'])->get();
        $pendingPayments = $pendingBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: 45;
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
                $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);
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
            $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);
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
        
        $salaries = $caregivers->map(function($caregiver) {
            // Get time trackings for this caregiver this month
            $timeTrackings = DB::table('time_trackings')
                ->where('caregiver_id', $caregiver->id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->get();
            
            $totalHours = $timeTrackings->sum('hours_worked');
            $rate = 28; // Caregiver rate $28/hr
            $amount = $totalHours * $rate;
            
            // Check if paid
            $unpaidHours = $timeTrackings->whereNull('paid_at')->sum('hours_worked');
            $status = $unpaidHours > 0 ? ($unpaidHours == $totalHours ? 'Pending' : 'Processing') : 'Paid';
            
            return [
                'id' => $caregiver->id,
                'caregiver' => $caregiver->user->name ?? 'Unknown',
                'hours' => number_format($totalHours, 1),
                'rate' => '$' . $rate . '/hr',
                'amount' => '$' . number_format($amount, 0),
                'period' => now()->format('M Y'),
                'status' => $totalHours > 0 ? $status : 'No Hours'
            ];
        })->filter(function($s) {
            return $s['hours'] > 0;
        })->values();
        
        return response()->json(['salaries' => $salaries]);
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
}