<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Models\CaregiverPayrollProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaregiverPayrollProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver not found'], 404);
        }

        $profile = CaregiverPayrollProfile::firstOrNew(['caregiver_id' => $caregiver->id]);

        return response()->json([
            'success' => true,
            'profile' => $this->sanitizeForClient($profile),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver not found'], 404);
        }

        $validated = $request->validate([
            'legal_first_name' => 'nullable|string|max:100',
            'legal_middle_name' => 'nullable|string|max:100',
            'legal_last_name' => 'nullable|string|max:100',
            'ssn' => 'nullable|string|regex:/^\d{9}$/',
            'date_of_birth' => 'nullable|date',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'region' => 'nullable|string|max:64',
            'postal_code' => 'nullable|string|max:32',
            'country' => 'nullable|string|size:2',
            'payroll_email' => 'nullable|email|max:255',
            'payroll_phone' => 'nullable|string|max:32',
            'bank_routing_number' => 'nullable|string|regex:/^\d{9}$/',
            'bank_account_number' => 'nullable|string|max:17',
            'bank_account_type' => 'nullable|in:checking,savings',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:32',
            'emergency_contact_relationship' => 'nullable|string|max:64',
            'mark_complete' => 'sometimes|boolean',
        ]);

        $profile = CaregiverPayrollProfile::firstOrNew(['caregiver_id' => $caregiver->id]);

        foreach ([
            'legal_first_name', 'legal_middle_name', 'legal_last_name',
            'date_of_birth', 'address_line1', 'address_line2', 'city', 'region', 'postal_code',
            'payroll_email', 'payroll_phone',
            'bank_account_type',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        ] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $profile->{$field} = $validated[$field];
            }
        }

        if (!empty($validated['country'])) {
            $profile->country = strtoupper($validated['country']);
        }

        if (!empty($validated['ssn'])) {
            $profile->ssn_encrypted = $validated['ssn'];
            $profile->ssn_last_four = substr($validated['ssn'], -4);
        }

        if (!empty($validated['bank_routing_number'])) {
            $profile->bank_routing_number_encrypted = $validated['bank_routing_number'];
        }

        if (!empty($validated['bank_account_number'])) {
            $profile->bank_account_number_encrypted = $validated['bank_account_number'];
        }

        if (!empty($validated['mark_complete'])) {
            $required = [
                $profile->legal_first_name,
                $profile->legal_last_name,
                $profile->date_of_birth,
                $profile->address_line1,
                $profile->city,
                $profile->region,
                $profile->postal_code,
                $profile->ssn_last_four,
                $profile->bank_routing_number_encrypted,
                $profile->bank_account_number_encrypted,
                $profile->bank_account_type,
                $profile->emergency_contact_name,
                $profile->emergency_contact_phone,
            ];
            if (in_array(null, $required, true) || in_array('', $required, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Complete all required payroll fields before marking complete.',
                ], 422);
            }
            $profile->profile_completed_at = now();
        }

        $profile->caregiver_id = $caregiver->id;
        $profile->save();

        return response()->json([
            'success' => true,
            'profile' => $this->sanitizeForClient($profile->fresh()),
        ]);
    }

    public function adminShow(int $userId)
    {
        $subject = User::find($userId);
        if (!$subject || $subject->user_type !== 'caregiver') {
            return response()->json(['error' => 'Caregiver user not found'], 404);
        }

        $caregiver = Caregiver::where('user_id', $subject->id)->first();
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $profile = CaregiverPayrollProfile::firstOrNew(['caregiver_id' => $caregiver->id]);

        return response()->json([
            'success' => true,
            'profile' => $this->sanitizeForAdmin($profile),
        ]);
    }

    public function adminUpdate(Request $request, int $userId)
    {
        $subject = User::find($userId);
        if (!$subject || $subject->user_type !== 'caregiver') {
            return response()->json(['error' => 'Caregiver user not found'], 404);
        }

        $caregiver = Caregiver::where('user_id', $subject->id)->first();
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $validated = $request->validate([
            'legal_first_name' => 'nullable|string|max:100',
            'legal_middle_name' => 'nullable|string|max:100',
            'legal_last_name' => 'nullable|string|max:100',
            'ssn' => 'nullable|string|regex:/^\d{9}$/',
            'date_of_birth' => 'nullable|date',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'region' => 'nullable|string|max:64',
            'postal_code' => 'nullable|string|max:32',
            'country' => 'nullable|string|size:2',
            'payroll_email' => 'nullable|email|max:255',
            'payroll_phone' => 'nullable|string|max:32',
            'bank_routing_number' => 'nullable|string|regex:/^\d{9}$/',
            'bank_account_number' => 'nullable|string|max:17',
            'bank_account_type' => 'nullable|in:checking,savings',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:32',
            'emergency_contact_relationship' => 'nullable|string|max:64',
            'mark_complete' => 'sometimes|boolean',
            'verified_by_admin' => 'sometimes|boolean',
        ]);

        $profile = CaregiverPayrollProfile::firstOrNew(['caregiver_id' => $caregiver->id]);

        foreach ([
            'legal_first_name', 'legal_middle_name', 'legal_last_name',
            'date_of_birth', 'address_line1', 'address_line2', 'city', 'region', 'postal_code',
            'payroll_email', 'payroll_phone',
            'bank_account_type',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        ] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $profile->{$field} = $validated[$field];
            }
        }

        if (!empty($validated['country'])) {
            $profile->country = strtoupper($validated['country']);
        }

        if (!empty($validated['ssn'])) {
            $profile->ssn_encrypted = $validated['ssn'];
            $profile->ssn_last_four = substr($validated['ssn'], -4);
        }

        if (!empty($validated['bank_routing_number'])) {
            $profile->bank_routing_number_encrypted = $validated['bank_routing_number'];
        }

        if (!empty($validated['bank_account_number'])) {
            $profile->bank_account_number_encrypted = $validated['bank_account_number'];
        }

        if (!empty($validated['mark_complete'])) {
            $profile->profile_completed_at = now();
        }

        if (array_key_exists('verified_by_admin', $validated) && $validated['verified_by_admin']) {
            $profile->verified_by_admin_at = now();
            $profile->verified_by_admin_user_id = Auth::id();
        }

        $profile->caregiver_id = $caregiver->id;
        $profile->save();

        return response()->json([
            'success' => true,
            'profile' => $this->sanitizeForAdmin($profile->fresh()),
        ]);
    }

    private function sanitizeForClient(CaregiverPayrollProfile $profile): array
    {
        return $this->serializeProfile($profile, false);
    }

    private function sanitizeForAdmin(CaregiverPayrollProfile $profile): array
    {
        return $this->serializeProfile($profile, true);
    }

    private function serializeProfile(CaregiverPayrollProfile $profile, bool $admin): array
    {
        $routing = $profile->bank_routing_number_encrypted;
        $account = $profile->bank_account_number_encrypted;

        $digits = $routing ? preg_replace('/\D/', '', $routing) : '';
        $routingMasked = (strlen($digits) >= 4) ? '••••'.substr($digits, -4) : null;

        $acctDigits = $account ? preg_replace('/\D/', '', $account) : '';
        $accountMasked = strlen($acctDigits) >= 4 ? '••••'.substr($acctDigits, -4) : null;

        $ssnDisplay = $profile->ssn_last_four ? '***-**-'.$profile->ssn_last_four : null;

        $base = [
            'legal_first_name' => $profile->legal_first_name,
            'legal_middle_name' => $profile->legal_middle_name,
            'legal_last_name' => $profile->legal_last_name,
            'ssn_masked' => $ssnDisplay,
            'date_of_birth' => $profile->date_of_birth?->format('Y-m-d'),
            'address_line1' => $profile->address_line1,
            'address_line2' => $profile->address_line2,
            'city' => $profile->city,
            'region' => $profile->region,
            'postal_code' => $profile->postal_code,
            'country' => $profile->country,
            'payroll_email' => $profile->payroll_email,
            'payroll_phone' => $profile->payroll_phone,
            'bank_routing_masked' => $routingMasked,
            'bank_account_masked' => $accountMasked,
            'bank_account_type' => $profile->bank_account_type,
            'emergency_contact_name' => $profile->emergency_contact_name,
            'emergency_contact_phone' => $profile->emergency_contact_phone,
            'emergency_contact_relationship' => $profile->emergency_contact_relationship,
            'profile_completed_at' => $profile->profile_completed_at?->toIso8601String(),
            'verified_by_admin_at' => $profile->verified_by_admin_at?->toIso8601String(),
        ];

        if ($admin) {
            $base['caregiver_id'] = $profile->caregiver_id;
            $base['verified_by_admin_user_id'] = $profile->verified_by_admin_user_id;
        }

        return $base;
    }
}
