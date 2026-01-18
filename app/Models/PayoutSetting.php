<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayoutSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'description' => $description
            ]
        );

        return $setting;
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'decimal':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
            case 'array':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray()
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        })->toArray();
    }

    /**
     * Get default payout frequency
     */
    public static function getDefaultPayoutFrequency()
    {
        return static::getValue('default_payout_frequency', 'weekly');
    }

    /**
     * Get default payout day
     */
    public static function getDefaultPayoutDay()
    {
        return static::getValue('default_payout_day', 5);
    }

    /**
     * Get minimum payout amount
     */
    public static function getMinimumPayoutAmount()
    {
        return static::getValue('minimum_payout_amount', 50.00);
    }

    /**
     * Get auto-approve threshold
     */
    public static function getAutoApproveThreshold()
    {
        return static::getValue('auto_approve_threshold', 500.00);
    }

    /**
     * Get 1099 threshold
     */
    public static function get1099Threshold()
    {
        return static::getValue('1099_threshold', 600.00);
    }

    /**
     * Check if W9 is required before payout
     */
    public static function isW9RequiredBeforePayout()
    {
        return static::getValue('require_w9_before_payout', true);
    }

    /**
     * Get company info for tax documents
     */
    public static function getCompanyInfo()
    {
        return [
            'name' => static::getValue('company_name', 'CAS Private Care LLC'),
            'ein' => static::getValue('company_ein', ''),
            'address' => static::getValue('company_address', [])
        ];
    }
}
