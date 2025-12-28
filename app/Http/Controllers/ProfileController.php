<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
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
            'zip_code' => ['required','string','regex:/^\\d{5}(-\\d{4})?$/'],
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
            'new_password' => 'nullable|string|min:8|confirmed',
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

        // Handle password change
        if ($validated['current_password'] && $validated['new_password']) {
            if (Hash::check($validated['current_password'], $user->password)) {
                $user->update(['password' => Hash::make($validated['new_password'])]);
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
                'borough' => 'nullable|string',
                'state' => 'nullable|string',
                'zip' => ['nullable','string','regex:/^\\d{5}(-\\d{4})?$/'],
                'ein' => 'nullable|string',
                'experience' => 'nullable|integer',
                'trainingCenter' => 'nullable|string',
                'customTrainingCenter' => 'nullable|string',
                'bio' => 'nullable|string|max:1000',
                'department' => 'nullable|string',
                'role' => 'nullable|string',
                'ssn' => ['nullable', new \App\Rules\ValidSSN, 'max:11'],
                'itin' => ['nullable', new \App\Rules\ValidITIN, 'max:11']
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
        if (isset($validated['borough'])) $updateData['borough'] = $validated['borough'];
        if (isset($validated['state'])) $updateData['state'] = $validated['state'];
        if (isset($validated['zip'])) $updateData['zip_code'] = $validated['zip'];
        if (isset($validated['ein'])) $updateData['ein'] = $validated['ein'];
        if (isset($validated['department'])) $updateData['department'] = $validated['department'];
        if (isset($validated['role'])) $updateData['role'] = $validated['role'];
        if (isset($validated['ssn'])) $updateData['ssn'] = $validated['ssn'];
        if (isset($validated['itin'])) $updateData['itin'] = $validated['itin'];
        
        // Update user table
        if (!empty($updateData)) {
            $user->update($updateData);
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
            // Find training center by name
            $trainingCenterName = trim($validated['trainingCenter']);
            $trainingCenter = \App\Models\User::whereIn('user_type', ['training_center', 'training'])
                ->where('name', $trainingCenterName)
                ->where('status', 'Active')
                ->first();
            
            if ($trainingCenter) {
                $caregiverData['training_center_id'] = $trainingCenter->id;
                $caregiverData['has_training_center'] = true;
                // Set approval status to pending - requires training center approval
                $caregiverData['training_center_approval_status'] = 'pending';
                \Log::info('Training center association requested (pending approval):', [
                    'caregiver_id' => $user->caregiver->id,
                    'training_center_id' => $trainingCenter->id,
                    'training_center_name' => $trainingCenter->name
                ]);
            } else {
                \Log::warning('Training center not found:', ['name' => $trainingCenterName]);
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
            
            // Get training center name if caregiver has a training center
            $trainingCenterName = null;
            if ($caregiver && $caregiver->training_center_id) {
                $trainingCenter = \App\Models\User::find($caregiver->training_center_id);
                if ($trainingCenter) {
                    $trainingCenterName = $trainingCenter->name;
                }
            }
            
            \Log::info('Returning response with caregiver data:', [
                'has_caregiver' => $caregiver !== null,
                'training_certificate' => $caregiver ? $caregiver->training_certificate : null,
                'training_center_name' => $trainingCenterName
            ]);
            
            return response()->json([
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
        
        // Get training center name if caregiver has a training center
        $trainingCenterName = null;
        if ($caregiver && $caregiver->training_center_id) {
            $trainingCenter = \App\Models\User::find($caregiver->training_center_id);
            if ($trainingCenter) {
                $trainingCenterName = $trainingCenter->name;
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
        
        return response()->json([
            'user' => $user,
            'caregiver' => $caregiverData,
            'client' => $client
        ]);
    }
}