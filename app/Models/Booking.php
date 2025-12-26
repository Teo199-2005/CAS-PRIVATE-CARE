<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_type',
        'duty_type',
        'borough',
        'city',
        'county',
        'service_date',
        'start_time',
        'duration_days',
        'hourly_rate',
        'total_budget',
        'payment_method',
        'gender_preference',
        'language_preference',
        'background_check_level',
        'specific_skills',
        'client_age',
        'mobility_level',
        'medical_conditions',
        'transportation_needed',
        'recurring_service',
        'recurring_schedule',
        'urgency_level',
        'street_address',
        'apartment_unit',
        'special_instructions',
        'status',
        'assignment_status',
        'submitted_at',
        'referral_code_id',
        'referral_discount_applied'
    ];

    protected $casts = [
        'service_date' => 'date',
        'start_time' => 'datetime:H:i',
        'transportation_needed' => 'boolean',
        'recurring_service' => 'boolean',
        'specific_skills' => 'array',
        'medical_conditions' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function assignments()
    {
        return $this->hasMany(BookingAssignment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function assignedCaregiver()
    {
        return $this->hasOneThrough(
            Caregiver::class,
            BookingAssignment::class,
            'booking_id',
            'id',
            'id',
            'caregiver_id'
        )->where('booking_assignments.status', '!=', 'cancelled');
    }

    /**
     * Get the number of caregivers needed for this booking
     * Rule: 1 caregiver per 15 days, minimum 1
     */
    public function getCaregiversNeededAttribute(): int
    {
        return max(1, (int) ceil($this->duration_days / 15));
    }
    
    /**
     * Update assignment status based on current assignments
     */
    public function updateAssignmentStatus()
    {
        $totalRequired = $this->caregivers_needed;
        $assignedCount = $this->assignments()->where('status', 'assigned')->count();
        
        if ($assignedCount == 0) {
            $this->assignment_status = 'unassigned';
        } elseif ($assignedCount < $totalRequired) {
            $this->assignment_status = 'partial';
        } else {
            $this->assignment_status = 'assigned';
        }
        
        $this->save();
    }

    /**
     * Get assignment progress as fraction string (e.g., "1 / 2")
     */
    public function getAssignmentProgressAttribute()
    {
        $totalRequired = $this->caregivers_needed;
        $assignedCount = $this->assignments()->where('status', 'assigned')->count();
        return "{$assignedCount} / {$totalRequired}";
    }
}