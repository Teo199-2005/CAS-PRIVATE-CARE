<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Rules\NotInPasswordHistory;
use App\Rules\ValidNYZipCode;
use App\Services\PasswordHistoryService;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponseTrait;
    public function show()
    {
        $user = Auth::user();
        $client = $user->client;
        return view('profile-enhanced', compact('user', 'client'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . Auth::id(),
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/'
            ],
            'phone' => 'required|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'borough' => 'required|string',
            'state' => 'required|string',
            'zip_code' => ['required', 'string', 'max:10', new ValidNYZipCode],
            'emergency_contact_name' => 'required|string',
            'emergency_contact_phone' => 'required|string',
            'emergency_contact_relationship' => 'required|string',
            'mobility_level' => 'nullable|in:independent,assisted,wheelchair,bedridden',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
            'insurance_provider' => 'nullable|string',
            'insurance_policy_number' => 'nullable|string',
            'preferred_language' => 'nullable|string',
            'account_type' => 'nullable|string',
            'timezone' => 'nullable|string',
            'bio' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'ssn' => ['nullable', new \App\Rules\ValidSSN, 'max:11'],
            'itin' => ['nullable', new \App\Rules\ValidITIN, 'max:11'],
            'current_password' => 'nullable|string',
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed', new NotInPasswordHistory(Auth::user())],
            'notifications' => 'nullable|array'
        ]);

        $user = Auth::user();
        
        // Sanitize bio field to prevent XSS
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }
        // Update user table
        $user->update([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email']
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $validated['profile_photo'] = $path;
        }

        // Handle password change with history tracking
        if ($validated['current_password'] && $validated['new_password']) {
            if (Hash::check($validated['current_password'], $user->password)) {
                $hashedPassword = Hash::make($validated['new_password']);
                
                // Record in password history before updating
                $historyService = new PasswordHistoryService();
                $historyService->recordPassword($user, $hashedPassword);
                
                $user->update(['password' => $hashedPassword]);
            }
        }

        // Convert comma-separated strings to JSON arrays
        $jsonFields = ['medical_conditions', 'allergies', 'medications'];
        foreach ($jsonFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = array_map('trim', explode(',', $validated[$field]));
            }
        }

        // Handle notifications
        $validated['notification_preferences'] = $validated['notifications'] ?? [];
        $validated['two_factor_enabled'] = $request->has('two_factor_enabled');

        // Update or create client record
        $user->client()->updateOrCreate(
            ['user_id' => $user->id],
            array_intersect_key($validated, array_flip([
                'date_of_birth', 'mobility_level', 'medical_conditions', 'allergies',
                'medications', 'emergency_contact_name', 'emergency_contact_phone',
                'emergency_contact_relationship', 'insurance_provider', 'insurance_policy_number',
                'preferred_language', 'account_type', 'two_factor_enabled',
                'notification_preferences', 'timezone', 'bio', 'profile_photo'
            ]))
        );

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function updateProfile(Request $request)
    {
        try {
            // Build validation rules
            $rules = [
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/'
                ],
                'phone' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'county' => 'nullable|string|max:100',
                'borough' => 'nullable|string',
                'state' => 'nullable|string',
                // accept either 'zip' (frontend older endpoints) or 'zip_code' (newer endpoints)
                'zip' => ['nullable', 'string', 'max:10', new ValidNYZipCode],
                'zip_code' => ['nullable', 'string', 'max:10', new ValidNYZipCode],
                'ein' => 'nullable|string',
                'experience' => 'nullable|integer',
                'trainingCenter' => 'nullable|string',
                'customTrainingCenter' => 'nullable|string',
                'bio' => 'nullable|string|max:1000',
                'department' => 'nullable|string',
                'role' => 'nullable|string',
                'ssn' => ['nullable', new \App\Rules\ValidSSN, 'max:11'],
                'itin' => ['nullable', new \App\Rules\ValidITIN, 'max:11'],
                'hasHHA' => 'nullable|boolean',
                'hhaNumber' => 'nullable|string|max:255',
                'hasCNA' => 'nullable|boolean',
                'cnaNumber' => 'nullable|string|max:255',
                'hasRN' => 'nullable|boolean',
                'rnNumber' => 'nullable|string|max:255',
                'preferredHourlyRateMin' => 'nullable|numeric|min:20|max:50',
                'preferredHourlyRateMax' => 'nullable|numeric|min:20|max:50',
                // Housekeeper-specific
                'cleaningSpecialties' => 'nullable|array',
                'cleaningSpecialties.*' => 'nullable|string|max:100',
                'specializations' => 'nullable|array',
                'specializations.*' => 'nullable|string|max:100',
                'hourly_rate' => 'nullable|numeric|min:0',
                'hasOwnSupplies' => 'nullable|boolean'
            ];
            
            // Only validate file if it's actually being uploaded
            if ($request->hasFile('trainingCertificate')) {
                $rules['trainingCertificate'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            }
            
            $validated = $request->validate($rules);
        // Sanitize bio field to prevent XSS
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        // Build update array with only provided fields
        $updateData = [];
        
        if (isset($validated['firstName']) && isset($validated['lastName'])) {
            $updateData['name'] = trim($validated['firstName'] . ' ' . $validated['lastName']);
        }
        
        if (isset($validated['email'])) $updateData['email'] = $validated['email'];
        if (isset($validated['phone'])) $updateData['phone'] = $validated['phone'];
        if (isset($validated['birthdate'])) $updateData['date_of_birth'] = $validated['birthdate'];
        if (isset($validated['address'])) $updateData['address'] = $validated['address'];
        if (isset($validated['city'])) $updateData['city'] = $validated['city'];
        if (isset($validated['county'])) $updateData['county'] = $validated['county'];
        if (isset($validated['borough'])) $updateData['borough'] = $validated['borough'];
        if (isset($validated['state'])) $updateData['state'] = $validated['state'];
    // Accept either incoming key and normalize to users.zip_code
    if (isset($validated['zip'])) $updateData['zip_code'] = $validated['zip'];
    if (isset($validated['zip_code'])) $updateData['zip_code'] = $validated['zip_code'];
        if (isset($validated['ein'])) $updateData['ein'] = $validated['ein'];
        if (isset($validated['department'])) $updateData['department'] = $validated['department'];
        if (isset($validated['role'])) $updateData['role'] = $validated['role'];
        if (isset($validated['ssn'])) $updateData['ssn'] = $validated['ssn'];
        if (isset($validated['itin'])) $updateData['itin'] = $validated['itin'];
        
        // Update user table
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Handle role-specific profile data
        $isCaregiver = $user->user_type === 'caregiver' || $user->user_type === 'Caregiver';
        $isHousekeeper = strtolower($user->user_type ?? '') === 'housekeeper';

        if ($isHousekeeper) {
            // Ensure housekeeper record exists
            $housekeeper = $user->housekeeper;
            if (!$housekeeper) {
                $housekeeper = \App\Models\Housekeeper::create([
                    'user_id' => $user->id,
                    'availability_status' => 'available',
                ]);
                $user->refresh();
            }
            $hkData = [];
            if (isset($validated['experience'])) {
                $hkData['years_experience'] = (int) $validated['experience'];
            }
            if (isset($validated['bio'])) {
                $hkData['bio'] = $validated['bio'];
            }
            // Accept cleaningSpecialties (frontend) or specializations
            $specs = $validated['cleaningSpecialties'] ?? $validated['specializations'] ?? null;
            if ($specs !== null && is_array($specs)) {
                $hkData['specializations'] = array_values(array_filter(array_map('trim', $specs)));
            }
            if (isset($validated['hourly_rate'])) {
                $hkData['hourly_rate'] = (float) $validated['hourly_rate'];
            }
            if (isset($validated['hasOwnSupplies'])) {
                $hkData['has_own_supplies'] = (bool) $validated['hasOwnSupplies'];
            }
            if (!empty($hkData)) {
                $housekeeper->update($hkData);
            }
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user->fresh(),
                'caregiver' => [
                    'id' => $housekeeper->id,
                    'years_experience' => $housekeeper->fresh()->years_experience,
                    'bio' => $housekeeper->fresh()->bio,
                    'specializations' => $housekeeper->fresh()->specializations,
                ],
            ]);
        }

        if (!$isCaregiver) {
            // For other roles (admin, client, etc.), just return success
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);
        }

        // Ensure caregiver record exists (only for caregivers)
        if (!$user->caregiver) {
            \App\Models\Caregiver::create([
                'user_id' => $user->id,
                'first_name' => $validated['firstName'] ?? '',
                'last_name' => $validated['lastName'] ?? '',
                'gender' => 'female',
                'availability_status' => 'available'
            ]);
            $user->refresh();
        }

        // Handle training certificate upload
        if ($request->hasFile('trainingCertificate')) {
            \Log::info('Training certificate file detected in request', []);
            $file = $request->file('trainingCertificate');
            \Log::info('File details:', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'isValid' => $file->isValid()
            ]);
            
            // Validate file
            if (!$file->isValid()) {
                \Log::error('Invalid file uploaded');
                return response()->json([
                    'error' => 'Invalid file uploaded',
                    'message' => 'The uploaded file is not valid'
                ], 422);
            }
            
            // Delete old certificate if exists
            if ($user->caregiver && $user->caregiver->training_certificate) {
                Storage::disk('public')->delete($user->caregiver->training_certificate);
            }
            
            $path = $file->store('training_certificates', 'public');
            \Log::info('File stored at path:', ['path' => $path]);
            $validated['training_certificate'] = $path;
        } else {
            \Log::info('No training certificate file in request', [
                'hasFile' => $request->hasFile('trainingCertificate'),
                'request_files' => array_keys($request->allFiles())
            ]);
        }

        // Ensure caregiver record exists
        if (!$user->caregiver) {
            \App\Models\Caregiver::create([
                'user_id' => $user->id,
                'first_name' => $validated['firstName'] ?? '',
                'last_name' => $validated['lastName'] ?? '',
                'gender' => 'female',
                'availability_status' => 'available'
            ]);
            $user->refresh();
        }

        // Handle training center
        $caregiverData = [];
        
        // Check if custom training center checkbox is checked
        $isCustomTrainingCenter = isset($validated['customTrainingCenter']) && !empty(trim($validated['customTrainingCenter']));
        
        if ($isCustomTrainingCenter) {
            // Custom training center - clear the training_center_id
            $caregiverData['training_center_id'] = null;
            $caregiverData['has_training_center'] = false;
            $caregiverData['training_center_approval_status'] = null;
            \Log::info('Custom training center selected, clearing training_center_id');
        } else if (isset($validated['trainingCenter']) && !empty(trim($validated['trainingCenter']))) {
            // Find training center by name or email (Active + pending so selection saves)
            $trainingCenterValue = trim($validated['trainingCenter']);
            // Normalize "x x" (duplicated label) to "x" so lookup matches DB
            if (preg_match('/^(.+)\s+\1$/u', $trainingCenterValue, $m)) {
                $trainingCenterValue = trim($m[1]);
            }
            $trainingCenter = \App\Models\User::whereIn('user_type', ['training_center', 'training'])
                ->whereIn('status', ['Active', 'pending'])
                ->where(function ($q) use ($trainingCenterValue) {
                    $q->where('name', $trainingCenterValue)
                        ->orWhere('email', $trainingCenterValue);
                })
                ->first();

            if ($trainingCenter) {
                $caregiverData['training_center_id'] = $trainingCenter->id;
                $caregiverData['has_training_center'] = true;
                // Only set to pending when this is a new/changed training center; keep existing approved/rejected
                $existingId = $user->caregiver->training_center_id ?? null;
                if ((int) $existingId !== (int) $trainingCenter->id) {
                    $caregiverData['training_center_approval_status'] = 'pending';
                    \Log::info('Training center association requested (pending approval):', [
                        'caregiver_id' => $user->caregiver->id,
                        'training_center_id' => $trainingCenter->id,
                        'training_center_name' => $trainingCenter->name
                    ]);
                }
                // else: same center, leave training_center_approval_status unchanged
            } else {
                \Log::warning('Training center not found:', ['value' => $trainingCenterValue]);
            }
        } else if (isset($validated['trainingCenter']) && empty(trim($validated['trainingCenter']))) {
            // Training center was explicitly cleared (empty string sent)
            $caregiverData['training_center_id'] = null;
            $caregiverData['has_training_center'] = false;
            $caregiverData['training_center_approval_status'] = null;
            \Log::info('Training center cleared from caregiver');
        }
        // If trainingCenter is not in validated array, don't change it (user didn't modify this field)

        // Update caregiver record
        if (isset($validated['experience'])) $caregiverData['years_experience'] = $validated['experience'];
        if (isset($validated['bio'])) $caregiverData['bio'] = $validated['bio'];
        if (isset($validated['training_certificate'])) {
            $caregiverData['training_certificate'] = $validated['training_certificate'];
            \Log::info('Setting training_certificate in caregiver data:', ['path' => $validated['training_certificate']]);
        }
        
        // Add certification fields
        if (isset($validated['hasHHA'])) $caregiverData['has_hha'] = $validated['hasHHA'];
        if (isset($validated['hhaNumber'])) $caregiverData['hha_number'] = $validated['hhaNumber'];
        if (isset($validated['hasCNA'])) $caregiverData['has_cna'] = $validated['hasCNA'];
        if (isset($validated['cnaNumber'])) $caregiverData['cna_number'] = $validated['cnaNumber'];
        if (isset($validated['hasRN'])) $caregiverData['has_rn'] = $validated['hasRN'];
        if (isset($validated['rnNumber'])) $caregiverData['rn_number'] = $validated['rnNumber'];
        
        // Add preferred salary range
        if (isset($validated['preferredHourlyRateMin'])) $caregiverData['preferred_hourly_rate_min'] = $validated['preferredHourlyRateMin'];
        if (isset($validated['preferredHourlyRateMax'])) $caregiverData['preferred_hourly_rate_max'] = $validated['preferredHourlyRateMax'];
        
        if (!empty($caregiverData)) {
            \Log::info('Updating caregiver with data:', $caregiverData);
            $user->caregiver->update($caregiverData);
            \Log::info('Caregiver updated. Training certificate:', [
                'training_certificate' => $user->caregiver->fresh()->training_certificate
            ]);
        } else {
            \Log::info('No caregiver data to update', []);
        }

            // Refresh relationships
            $user->refresh();
            $caregiver = $user->caregiver ? $user->caregiver->fresh() : null;
            
            // Get training center name if caregiver has a training center (dedupe "x x" -> "x")
            $trainingCenterName = null;
            if ($caregiver && $caregiver->training_center_id) {
                $trainingCenter = \App\Models\User::find($caregiver->training_center_id);
                if ($trainingCenter) {
                    $trainingCenterName = trim($trainingCenter->name ?? $trainingCenter->email ?? '') ?: null;
                    if ($trainingCenterName && preg_match('/^(.+)\s+\1$/u', $trainingCenterName, $m)) {
                        $trainingCenterName = trim($m[1]);
                    }
                }
            }
            
            \Log::info('Returning response with caregiver data:', [
                'has_caregiver' => $caregiver !== null,
                'training_certificate' => $caregiver ? $caregiver->training_certificate : null,
                'training_center_name' => $trainingCenterName
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!', 
                'user' => $user->fresh(),
                'caregiver' => $caregiver ? [
                    'id' => $caregiver->id,
                    'years_experience' => $caregiver->years_experience,
                    'bio' => $caregiver->bio,
                    'training_certificate' => $caregiver->training_certificate,
                    'training_center_id' => $caregiver->training_center_id,
                    'training_center_name' => $trainingCenterName,
                    'training_center_approval_status' => $caregiver->training_center_approval_status
                ] : null
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProfile()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $caregiver = $user->caregiver;
        $client = $user->client;
        $housekeeper = $user->housekeeper;
        
        // Get training center name if caregiver has a training center (dedupe "x x" -> "x")
        $trainingCenterName = null;
        if ($caregiver && $caregiver->training_center_id) {
            $trainingCenter = \App\Models\User::find($caregiver->training_center_id);
            if ($trainingCenter) {
                $trainingCenterName = trim($trainingCenter->name ?? $trainingCenter->email ?? '') ?: null;
                if ($trainingCenterName && preg_match('/^(.+)\s+\1$/u', $trainingCenterName, $m)) {
                    $trainingCenterName = trim($m[1]);
                }
            }
        }
        
        $caregiverData = null;
        if ($caregiver) {
            $caregiverData = [
                'id' => $caregiver->id,
                'years_experience' => $caregiver->years_experience,
                'bio' => $caregiver->bio,
                'specializations' => $caregiver->specializations,
                'training_certificate' => $caregiver->training_certificate,
                'training_center_id' => $caregiver->training_center_id,
                'training_center_name' => $trainingCenterName,
                'training_center_approval_status' => $caregiver->training_center_approval_status
            ];
        }
        
        // Get housekeeper data if user is a housekeeper
        $housekeeperData = null;
        if ($housekeeper) {
            $housekeeperTrainingCenterName = null;
            if ($housekeeper->training_center_id) {
                $housekeeperTrainingCenter = \App\Models\User::find($housekeeper->training_center_id);
                if ($housekeeperTrainingCenter) {
                    $housekeeperTrainingCenterName = trim($housekeeperTrainingCenter->name ?? $housekeeperTrainingCenter->email ?? '') ?: null;
                    if ($housekeeperTrainingCenterName && preg_match('/^(.+)\s+\1$/u', $housekeeperTrainingCenterName, $m)) {
                        $housekeeperTrainingCenterName = trim($m[1]);
                    }
                }
            }
            
            $housekeeperData = [
                'id' => $housekeeper->id,
                'years_experience' => $housekeeper->years_experience,
                'bio' => $housekeeper->bio,
                'specializations' => $housekeeper->specializations,
                'training_certificate' => $housekeeper->training_certificate,
                'training_center_id' => $housekeeper->training_center_id,
                'training_center_name' => $housekeeperTrainingCenterName,
                'training_center_approval_status' => $housekeeper->training_center_approval_status ?? 'approved',
                'salary_min' => $housekeeper->salary_min,
                'salary_max' => $housekeeper->salary_max,
            ];
        }
        
        // Determine which data to return in the 'caregiver' field for compatibility
        // If user is a housekeeper, prioritize housekeeper data
        $isHousekeeper = strtolower($user->user_type ?? '') === 'housekeeper';
        $compatCaregiverData = $isHousekeeper ? ($housekeeperData ?? $caregiverData) : ($caregiverData ?? $housekeeperData);
        
        return response()->json([
            'user' => $user,
            'caregiver' => $compatCaregiverData, // Return appropriate data based on user type
            'housekeeper' => $housekeeperData,
            'client' => $client
        ]);
    }
}