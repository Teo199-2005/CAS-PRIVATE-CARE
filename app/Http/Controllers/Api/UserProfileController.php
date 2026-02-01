<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Caregiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * User Profile API Controller
 * 
 * Handles user profile operations via API endpoints.
 * Moved from inline route closures for better maintainability.
 */
class UserProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Update user profile by ID
     * PUT /api/user/{id}/profile
     */
    public function updateProfile($id, Request $request)
    {
        try {
            // SECURITY FIX: Verify user is authenticated
            $authUser = auth('web')->user();
            if (!$authUser) {
                return $this->unauthorizedResponse('Unauthenticated');
            }
            
            // SECURITY FIX: Verify user can only update their own profile
            // Admins can update any profile, regular users can only update their own
            if ($authUser->id != $id && $authUser->user_type !== 'admin') {
                Log::warning('Unauthorized profile update attempt', [
                    'auth_user_id' => $authUser->id,
                    'target_user_id' => $id,
                    'ip' => $request->ip()
                ]);
                return $this->forbiddenResponse('You can only update your own profile');
            }
            
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'borough' => 'required|string|max:100',
                'state' => 'required|string|max:50',
                'zip_code' => 'required|string|max:20',
                'date_of_birth' => 'required|date',
            ]);
            
            $user->update($validated);
            
            Log::info('Profile updated', [
                'user_id' => $user->id,
                'updated_by' => $authUser->id,
                'fields' => array_keys($validated)
            ]);
            
            return $this->successResponse([
                'user' => $user->fresh()
            ], 'Profile updated successfully');
            
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Get user profile
     * GET /api/profile
     * Caregiver/housekeeper use ProfileController so training_center_name, certifications, etc. match the form.
     */
    public function getProfile(Request $request)
    {
        $user = $request->query('user_id')
            ? User::find($request->query('user_id'))
            : (auth('web')->user() ?? $this->getDemoUser($request->query('user_type')));

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        // Caregiver and housekeeper: use ProfileController so response has training_center_name (deduped), certifications, etc.
        if (in_array(strtolower($user->user_type ?? ''), ['caregiver', 'housekeeper'])) {
            return app(\App\Http\Controllers\ProfileController::class)->getProfile();
        }

        $user->refresh();
        $caregiver = $user->user_type === 'caregiver' ? Caregiver::where('user_id', $user->id)->first() : null;

        $response = $this->successResponse([
            'user' => $this->formatUserData($user),
            'caregiver' => $caregiver ? $this->formatCaregiverData($caregiver) : null
        ]);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        return $response;
    }

    /**
     * Update admin profile
     * POST /api/profile/update
     * Caregiver/housekeeper requests are delegated to ProfileController so training center, experience, etc. are saved.
     */
    public function updateAdminProfile(Request $request)
    {
        try {
            $user = auth('web')->user() ?? User::where('user_type', 'admin')->first();
            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            // Caregiver and housekeeper use full profile update (training center, experience, certifications, etc.)
            if (in_array(strtolower($user->user_type ?? ''), ['caregiver', 'housekeeper'])) {
                return app(\App\Http\Controllers\ProfileController::class)->updateProfile($request);
            }

            $rules = [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
                'role' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'county' => 'nullable|string|max:100',
                'borough' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:50',
                'zip' => 'nullable|string|max:20',
                'zip_code' => 'nullable|string|max:20',
                'birthdate' => 'nullable|date',
                'date_of_birth' => 'nullable|date',
            ];
            $validated = $request->validate($rules);

            $update = [
                'name' => trim(($validated['firstName'] ?? '') . ' ' . ($validated['lastName'] ?? '')),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->phone,
                'department' => $validated['department'] ?? $user->department,
                'role' => $validated['role'] ?? $user->role,
            ];
            if (array_key_exists('address', $validated)) $update['address'] = $validated['address'];
            // Permanent: persist city/county/borough from validated or request (contractors + client profiles).
            $city = array_key_exists('city', $validated) ? $validated['city'] : $request->input('city');
            $county = array_key_exists('county', $validated) ? $validated['county'] : $request->input('county');
            $borough = array_key_exists('borough', $validated) ? $validated['borough'] : $request->input('borough');
            if ($city !== null) $update['city'] = $city === '' ? null : (string) $city;
            if ($county !== null) $update['county'] = $county === '' ? null : (string) $county;
            if ($borough !== null) $update['borough'] = $borough === '' ? null : (string) $borough;
            if (array_key_exists('state', $validated)) $update['state'] = $validated['state'];
            if (isset($validated['zip'])) $update['zip_code'] = $validated['zip'];
            if (isset($validated['zip_code'])) $update['zip_code'] = $validated['zip_code'];
            if (isset($validated['birthdate'])) $update['date_of_birth'] = $validated['birthdate'];
            if (isset($validated['date_of_birth'])) $update['date_of_birth'] = $validated['date_of_birth'];

            $user->update($update);

            return $this->successResponse([
                'user' => $this->formatUserData($user->fresh())
            ], 'Profile updated successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            Log::error('Admin profile update failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Change user password
     * POST /api/profile/change-password
     */
    public function changePassword(Request $request)
    {
        try {
            // Get authenticated user or admin for demo
            $user = auth('web')->user() ?? User::where('user_type', 'admin')->first();
            
            if (!$user) {
                return $this->notFoundResponse('User not found');
            }
            
            $validated = $request->validate([
                'currentPassword' => 'required|string',
                'newPassword' => 'required|string|min:8',
                'confirmPassword' => 'required|string|same:newPassword'
            ]);
            
            // Verify current password
            if (!Hash::check($validated['currentPassword'], $user->password)) {
                return $this->errorResponse('Current password is incorrect', 422);
            }
            
            // Use DB update to avoid any model events or relationships
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($validated['newPassword']),
                    'updated_at' => now()
                ]);
            
            Log::info('Password changed', ['user_id' => $user->id]);
            
            return $this->successResponse([], 'Password changed successfully');
            
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            Log::error('Password change failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Get demo user based on type (for development only)
     */
    private function getDemoUser(?string $userType): ?User
    {
        if (!$userType) {
            return User::where('name', 'Demo Client')->first();
        }
        
        return match($userType) {
            'caregiver' => User::where('name', 'Demo Caregiver')->first(),
            'admin' => User::where('user_type', 'admin')->first(),
            'marketing' => User::where('user_type', 'marketing')->first(),
            'training' => User::where('user_type', 'training')->first(),
            default => User::where('name', 'Demo Client')->first()
        };
    }

    /**
     * Format user data for API response
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'county' => $user->county,
            'borough' => $user->borough,
            'state' => $user->state,
            'zip_code' => $user->zip_code,
            'date_of_birth' => $user->date_of_birth,
            'avatar' => $user->avatar,
            'user_type' => $user->user_type,
            'role' => $user->role,
            'department' => $user->department,
            'created_at' => $user->created_at,
            'stripe_connect_id' => $user->stripe_connect_id ?? null,
            'bank_account_last_four' => $user->bank_account_last_four ?? null,
            'bank_name' => $user->bank_name ?? null,
        ];
    }

    /**
     * Format caregiver data for API response
     */
    private function formatCaregiverData(Caregiver $caregiver): array
    {
        return [
            'id' => $caregiver->id,
            'years_experience' => $caregiver->years_experience,
            'specializations' => $caregiver->specializations,
            'bio' => $caregiver->bio,
            'training_certificate' => $caregiver->training_certificate,
            'training_center_id' => $caregiver->training_center_id,
            'training_center_name' => $caregiver->trainingCenter ? (trim($caregiver->trainingCenter->name ?? $caregiver->trainingCenter->email ?? '') ?: null) : null,
            'training_center_approval_status' => $caregiver->training_center_approval_status,
            'has_hha' => $caregiver->has_hha,
            'hha_number' => $caregiver->hha_number,
            'has_cna' => $caregiver->has_cna,
            'cna_number' => $caregiver->cna_number,
            'has_rn' => $caregiver->has_rn,
            'rn_number' => $caregiver->rn_number,
            'preferred_hourly_rate_min' => $caregiver->preferred_hourly_rate_min,
            'preferred_hourly_rate_max' => $caregiver->preferred_hourly_rate_max
        ];
    }
}
