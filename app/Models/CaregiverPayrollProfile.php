<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverPayrollProfile extends Model
{
    protected $fillable = [
        'caregiver_id',
        'legal_first_name',
        'legal_middle_name',
        'legal_last_name',
        'ssn_encrypted',
        'ssn_last_four',
        'date_of_birth',
        'address_line1',
        'address_line2',
        'city',
        'region',
        'postal_code',
        'country',
        'payroll_email',
        'payroll_phone',
        'bank_routing_number_encrypted',
        'bank_account_number_encrypted',
        'bank_account_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'profile_completed_at',
        'verified_by_admin_at',
        'verified_by_admin_user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'profile_completed_at' => 'datetime',
        'verified_by_admin_at' => 'datetime',
        'ssn_encrypted' => 'encrypted',
        'bank_routing_number_encrypted' => 'encrypted',
        'bank_account_number_encrypted' => 'encrypted',
    ];

    protected $hidden = [
        'ssn_encrypted',
        'bank_routing_number_encrypted',
        'bank_account_number_encrypted',
    ];

    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function isComplete(): bool
    {
        return $this->profile_completed_at !== null;
    }
}
