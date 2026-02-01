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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Admin User Management Controller
 * 
 * Extracted from AdminController for better maintainability.
 * Handles all user CRUD operations for the admin dashboard.
 * 
 * Phase 2 Refactoring: Controller Extraction
 */
class AdminUserController extends Controller
{
    /**
     * Get all users with their related data
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Filter by user_type if provided
            if ($request->has('user_type') && $request->user_type !== 'all') {
                $query->where('user_type', $request->user_type);
            }

            // Search by name or email
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
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
            'hourly_rate' => 'nullable|numeric|min:0|max:500',
            'has_own_supplies' => 'nullable|boolean',
            'available_for_transport' => 'nullable|boolean',
            'skills' => 'nullable|array',
            'specializations' => 'nullable|array',
        ]);

        // Sanitize bio field
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        // Generate secure random password
        $temporaryPassword = Str::random(16);

        $user = DB::transaction(function () use ($validated, $temporaryPassword) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'user_type' => $validated['user_type'],
                'status' => $validated['status'] ?? 'Active'
            ];

            // Add optional fields
            foreach (['phone', 'date_of_birth', 'address', 'state', 'county', 'city', 'borough', 'zip_code', 'ssn', 'itin'] as $field) {
                if (isset($validated[$field])) {
                    $userData[$field] = $validated[$field];
                }
            }

            $user = User::create($userData);

            // Create role-specific record
            $this->createRoleRecord($user, $validated);

            return $user;
        });

        Log::info('User created by admin', ['user_id' => $user->id, 'user_type' => $validated['user_type']]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Update an existing user
     */
    public function update(Request $request, $id)
    {
        $id = (int) $id;
        $user = User::findOrFail($id);

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
            'ssn' => ['sometimes', 'nullable', new ValidSSN, 'max:11'],
            'itin' => ['sometimes', 'nullable', new ValidITIN, 'max:11'],
            'status' => 'sometimes|nullable|in:Active,Inactive,Suspended',
        ]);

        $user->update($validated);

        Log::info('User updated by admin', ['user_id' => $id]);

        return response()->json(['success' => true, 'user' => $user->fresh()]);
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended'
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => $validated['status']]);

        Log::info('User status updated', ['user_id' => $id, 'status' => $validated['status']]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        $id = (int) $id;
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        DB::transaction(function () use ($user) {
            // Delete related records
            if ($user->user_type === 'caregiver') {
                Caregiver::where('user_id', $user->id)->delete();
            } elseif ($user->user_type === 'housekeeper') {
                Housekeeper::where('user_id', $user->id)->delete();
            } elseif ($user->user_type === 'client') {
                Client::where('user_id', $user->id)->delete();
            }

            $user->delete();
        });

        Log::info('User deleted by admin', ['user_id' => $id]);

        return response()->json(['success' => true]);
    }

    /**
     * Create role-specific record for new user
     */
    protected function createRoleRecord(User $user, array $validated): void
    {
        switch ($validated['user_type']) {
            case 'client':
                Client::create([
                    'user_id' => $user->id,
                    'first_name' => $validated['firstName'] ?? null,
                    'last_name' => $validated['lastName'] ?? null,
                ]);
                break;

            case 'caregiver':
                Caregiver::create([
                    'user_id' => $user->id,
                    'gender' => 'female',
                    'availability_status' => 'available',
                    'first_name' => $validated['firstName'] ?? null,
                    'last_name' => $validated['lastName'] ?? null,
                    'years_experience' => $validated['years_experience'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ]);
                break;

            case 'housekeeper':
                Housekeeper::create([
                    'user_id' => $user->id,
                    'gender' => 'female',
                    'availability_status' => 'available',
                    'years_experience' => $validated['years_experience'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'hourly_rate' => $validated['hourly_rate'] ?? null,
                    'has_own_supplies' => $validated['has_own_supplies'] ?? false,
                    'available_for_transport' => $validated['available_for_transport'] ?? false,
                    'skills' => $validated['skills'] ?? null,
                    'specializations' => $validated['specializations'] ?? null,
                ]);
                break;
        }
    }
}
