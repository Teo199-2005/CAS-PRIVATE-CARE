<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'check_date',
        'checks_performed',
        'check_results',
        'overall_compliant',
        'w9_compliant',
        'bank_compliant',
        'background_check_compliant',
        'certification_compliant',
        'work_pattern_compliant',
        'average_weekly_hours',
        'unique_clients_count',
        'compliance_notes',
        'check_type',
        'checked_by'
    ];

    protected $casts = [
        'check_date' => 'date',
        'checks_performed' => 'array',
        'check_results' => 'array',
        'overall_compliant' => 'boolean',
        'w9_compliant' => 'boolean',
        'bank_compliant' => 'boolean',
        'background_check_compliant' => 'boolean',
        'certification_compliant' => 'boolean',
        'work_pattern_compliant' => 'boolean',
        'average_weekly_hours' => 'decimal:2'
    ];

    /**
     * Get the user this check belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who performed the check
     */
    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Check if this was an automated check
     */
    public function isAutomated(): bool
    {
        return $this->check_type === 'automated';
    }

    /**
     * Get non-compliant items
     */
    public function getNonCompliantItems(): array
    {
        $items = [];

        if (!$this->w9_compliant) $items[] = 'W9 form not submitted or verified';
        if (!$this->bank_compliant) $items[] = 'Bank account not connected';
        if (!$this->background_check_compliant) $items[] = 'Background check not completed';
        if (!$this->certification_compliant) $items[] = 'Required certifications missing';
        if (!$this->work_pattern_compliant) $items[] = 'Work pattern may indicate employee relationship';

        return $items;
    }

    /**
     * Get compliance percentage
     */
    public function getCompliancePercentageAttribute(): int
    {
        $checks = [
            $this->w9_compliant,
            $this->bank_compliant,
            $this->background_check_compliant,
            $this->certification_compliant,
            $this->work_pattern_compliant
        ];

        $passed = count(array_filter($checks));
        return (int) round(($passed / count($checks)) * 100);
    }

    /**
     * Scope for compliant users
     */
    public function scopeCompliant($query)
    {
        return $query->where('overall_compliant', true);
    }

    /**
     * Scope for non-compliant users
     */
    public function scopeNonCompliant($query)
    {
        return $query->where('overall_compliant', false);
    }

    /**
     * Scope for latest check per user
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('check_date', 'desc');
    }

    /**
     * Get the latest compliance check for a user
     */
    public static function getLatestForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('check_date', 'desc')
            ->first();
    }
}
