<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Booking;
use App\Rules\ValidPhoneNumber;
use App\Rules\ValidNYZipCode;
use App\Services\MarketingTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin controller for staff management (marketing staff, training centers, admin staff).
 * Extracted from AdminController as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 1.1
 */
class StaffAdminController extends Controller
{
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
                // Only count bookings that are approved, confirmed, or completed
                $approvedBookings = Booking::where('referral_code_id', $referralCode->id)
                    ->whereIn('status', ['approved', 'confirmed', 'completed'])
                    ->get();
                
                // Count unique clients who used this referral code
                $uniqueClientUserIds = $approvedBookings->pluck('client_id')->unique();
                $clientsAcquired = $uniqueClientUserIds->count();
                
                // Calculate total hours and commission from time trackings
                if ($uniqueClientUserIds->count() > 0 && DB::getSchemaBuilder()->hasTable('time_trackings')) {
                    $clientIds = \App\Models\Client::whereIn('user_id', $uniqueClientUserIds)
                        ->pluck('id');
                    
                    if ($clientIds->count() > 0) {
                        $timeTrackings = DB::table('time_trackings')
                            ->whereIn('client_id', $clientIds)
                            ->whereNotNull('clock_out_time')
                            ->where('status', 'completed')
                            ->get();
                        
                        $totalHours = $timeTrackings->sum('hours_worked') ?? 0;
                        
                        $commissionRate = $referralCode->commission_per_hour ?? 1.00;
                        $commissionEarned = $totalHours * $commissionRate;
                    }
                }
                
                // Fallback estimate from booking duration
                if ($totalHours == 0) {
                    foreach ($approvedBookings as $booking) {
                        $hoursPerDay = 8;
                        if (preg_match('/(\d+)\s*hours?/i', $booking->duty_type ?? '', $matches)) {
                            $hoursPerDay = (int)$matches[1];
                        }
                        $totalHours += ($booking->duration_days ?? 0) * $hoursPerDay;
                    }
                }
            }

            // Tier based on PAID client count only (Silver 1-5, Gold 6-10, Platinum 11+)
            $tierData = MarketingTierService::getTierAndRateForUser($user->id);
            $tier = $tierData['tier'];
            $tierLabel = $tierData['label'];
            $commissionPerHour = (float) $tierData['rate'];

            // Commission earned: sum of stored marketing_partner_commission from time_trackings (actual recorded commissions)
            if (DB::getSchemaBuilder()->hasTable('time_trackings')) {
                $commissionEarned = (float) DB::table('time_trackings')
                    ->where('marketing_partner_id', $user->id)
                    ->whereNotNull('marketing_partner_commission')
                    ->sum('marketing_partner_commission');
            }
            
            $nameParts = array_filter(explode(' ', trim($user->name ?? ''), 2));
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            $locationParts = array_filter([$user->city ?? '', $user->state ?? '']);
            $location = implode(', ', $locationParts);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'displayName' => $user->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'zip_code' => $user->zip_code ?? '',
                'location' => $location,
                'city' => $user->city ?? '',
                'state' => $user->state ?? '',
                'county' => $user->county ?? '',
                'status' => $user->status ?? 'Active',
                'referralCode' => $referralCode ? $referralCode->code : 'N/A',
                'clientsAcquired' => $clientsAcquired,
                'paidClientCount' => $tierData['active_client_count'] ?? 0,
                'tier' => $tier,
                'tierLabel' => $tierLabel,
                'commissionPerHour' => $commissionPerHour,
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
            'discount_per_hour' => 3.00,
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
        
        DB::transaction(function() use ($user) {
            \App\Models\ReferralCode::where('user_id', $user->id)->delete();
            $user->delete();
        });

        return response()->json(['success' => true]);
    }

    /**
     * Get all training centers with their statistics.
     * Admin list: all statuses. Dropdown (active_only=1): only Active.
     */
    public function getTrainingCenters(Request $request)
    {
        try {
            $query = User::whereIn('user_type', ['training_center', 'training']);
            if ($request->boolean('active_only')) {
                $query->where('status', 'Active');
            }
            $trainingCenterUsers = $query->orderBy('name')->get();
            
            Log::info('Training centers query found ' . $trainingCenterUsers->count() . ' users');

            $centers = $trainingCenterUsers->map(function ($user) {
                try {
                    $caregiverCount = 0;
                    try {
                        $caregiverCount = Caregiver::where('training_center_id', $user->id)
                            ->where('training_center_approval_status', 'approved')
                            ->count();
                    } catch (\Exception $e) {
                        Log::warning("Error counting caregivers for training center {$user->id}: " . $e->getMessage());
                    }
                    
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
                                        $commissionEarned += $hours * 0.50;
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::debug("Error calculating hours for caregiver {$caregiver->id}: " . $e->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        Log::debug("Error fetching caregivers for training center {$user->id}: " . $e->getMessage());
                    }
                    
                    return [
                        'id' => $user->id,
                        'name' => trim($user->name ?? $user->email ?? '') ?: 'Unknown',
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
                    return [
                        'id' => $user->id,
                        'name' => trim($user->name ?? $user->email ?? '') ?: 'Unknown',
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

            // For dropdowns: list of names (CAS training center partners only)
            $trainingCenterNames = $centers->map(fn ($c) => $c['name'] ?? '')->filter()->values()->toArray();

            return response()->json([
                'centers' => $centers,
                'trainingCenters' => $trainingCenterNames,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getTrainingCenters: " . $e->getMessage());
            return response()->json(['centers' => [], 'trainingCenters' => []]);
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
            'zip_code' => ['nullable', 'string', 'max:10', new ValidNYZipCode],
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:Active,Inactive'
        ]);

        $temporaryPassword = $validated['password'] ?? \Illuminate\Support\Str::random(16);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($temporaryPassword),
            'user_type' => 'training_center',
            'status' => $validated['status']
        ];

        if (isset($validated['phone'])) $userData['phone'] = $validated['phone'];
        if (isset($validated['date_of_birth'])) $userData['date_of_birth'] = $validated['date_of_birth'];
        if (isset($validated['address'])) $userData['address'] = $validated['address'];
        if (isset($validated['state'])) $userData['state'] = $validated['state'];
        if (isset($validated['county'])) $userData['county'] = $validated['county'];
        if (isset($validated['city'])) $userData['city'] = $validated['city'];
        if (isset($validated['zip_code'])) $userData['zip_code'] = $validated['zip_code'];

        $user = DB::transaction(function() use ($userData) {
            return User::create($userData);
        });

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
                'zip_code' => ['nullable', 'string', 'max:10', new ValidNYZipCode],
                'status' => 'sometimes|in:Active,Inactive'
            ]);

            $user = User::where('id', $id)->whereIn('user_type', ['training_center', 'training'])->first();
            
            if (!$user) {
                Log::warning("Training center not found for update", ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Training center not found. Please refresh the page and try again.'
                ], 404);
            }
            
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
        $user = User::where('id', $id)->whereIn('user_type', ['training_center', 'training'])->firstOrFail();
        
        DB::transaction(function() use ($user) {
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
     * Get all admin staff users
     */
    public function getAdminStaff()
    {
        $adminStaffUsers = User::where('user_type', 'admin')
            ->where('role', 'Admin Staff')
            ->orderBy('created_at', 'desc')
            ->get();

        $staff = $adminStaffUsers->map(function ($user) {
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

            $pagePermissions = $validated['page_permissions'] ?? $this->getDefaultAdminStaffPermissions();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'user_type' => 'admin',
                'role' => 'Admin Staff',
                'status' => $validated['status'],
                'email_verified_at' => now(),
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
            Log::error('storeAdminStaff error: ' . $e->getMessage());
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
            Log::error('updateAdminStaff error: ' . $e->getMessage());
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

    /**
     * Get default page permissions for admin staff
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
            'email-marketing' => true,
            'payments' => true,
            'analytics' => true,
            'profile' => true,
        ];
    }
}
