<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'parent_booking_id',
        'service_type',
        'duty_type',
        'borough',
        'city',
        'county',
        'service_date',
        'start_time',
        'duration_days',
        'hourly_rate',
        'hours_per_day',
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
        'recurring_count',
        'recurring_status',
        'recurring_failed_attempts',
        'last_recurring_charge_date',
        'day_schedules',
        'urgency_level',
        'street_address',
        'apartment_unit',
        'special_instructions',
        'status',
        'assignment_status',
        'assigned_hourly_rate',
        'assigned_caregiver_id',
        'submitted_at',
        'referral_code_id',
        'referral_discount_applied',
        'payment_status',
        'stripe_payment_intent_id',
        'payment_intent_id',
        'payment_date',
        'stripe_subscription_id',
        'payment_type',
        'auto_pay_enabled',
        'next_payment_date',
        'stripe_price_id'
    ];

    protected $casts = [
        'service_date' => 'date',
        'start_time' => 'datetime:H:i',
        'transportation_needed' => 'boolean',
        'recurring_service' => 'boolean',
        'auto_pay_enabled' => 'boolean',
        'specific_skills' => 'array',
        'medical_conditions' => 'array',
        'day_schedules' => 'array',
        'last_recurring_charge_date' => 'datetime',
        'next_payment_date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the parent booking (if this is a recurring booking)
     */
    public function parentBooking()
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    /**
     * Get child bookings (recurring bookings created from this one)
     */
    public function childBookings()
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }

    /**
     * Get the original/root booking in a recurring chain
     */
    public function getRootBookingAttribute()
    {
        $booking = $this;
        while ($booking->parent_booking_id) {
            $booking = $booking->parentBooking;
        }
        return $booking;
    }

    /**
     * Check if this is a recurring booking (has parent)
     */
    public function getIsRecurringChildAttribute()
    {
        return !is_null($this->parent_booking_id);
    }

    /**
     * Get booking end date
     */
    public function getEndDateAttribute()
    {
        return $this->service_date->addDays($this->duration_days ?? 15);
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