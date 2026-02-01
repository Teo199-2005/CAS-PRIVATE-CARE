<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Policy Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the password requirements for the application.
    | These settings are used for validation during registration,
    | password reset, and password change operations.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Minimum Password Length
    |--------------------------------------------------------------------------
    |
    | The minimum number of characters required for passwords.
    | NIST SP 800-63B recommends at least 8 characters.
    | We use 8 as the minimum for usability while maintaining security.
    |
    */
    'min_length' => env('PASSWORD_MIN_LENGTH', 8),

    /*
    |--------------------------------------------------------------------------
    | Maximum Password Length
    |--------------------------------------------------------------------------
    |
    | The maximum number of characters allowed for passwords.
    | Set to 128 to allow passphrase-style passwords while preventing
    | denial-of-service via extremely long passwords.
    |
    */
    'max_length' => env('PASSWORD_MAX_LENGTH', 128),

    /*
    |--------------------------------------------------------------------------
    | Require Mixed Case
    |--------------------------------------------------------------------------
    |
    | Whether passwords must contain both uppercase and lowercase letters.
    | Recommended for stronger passwords.
    |
    */
    'require_mixed_case' => env('PASSWORD_REQUIRE_MIXED_CASE', true),

    /*
    |--------------------------------------------------------------------------
    | Require Numbers
    |--------------------------------------------------------------------------
    |
    | Whether passwords must contain at least one numeric digit.
    |
    */
    'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),

    /*
    |--------------------------------------------------------------------------
    | Require Symbols
    |--------------------------------------------------------------------------
    |
    | Whether passwords must contain at least one special character.
    | Special characters: !@#$%^&*()_+-=[]{}|;':",.<>?/`~
    |
    */
    'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', false),

    /*
    |--------------------------------------------------------------------------
    | Uncompromised Password Check
    |--------------------------------------------------------------------------
    |
    | Whether to check passwords against the Have I Been Pwned database.
    | This helps prevent users from using passwords that have been
    | exposed in data breaches.
    |
    */
    'check_uncompromised' => env('PASSWORD_CHECK_UNCOMPROMISED', true),

    /*
    |--------------------------------------------------------------------------
    | Uncompromised Threshold
    |--------------------------------------------------------------------------
    |
    | The number of times a password must appear in data breaches
    | before being rejected. A higher number is more lenient.
    |
    */
    'uncompromised_threshold' => env('PASSWORD_UNCOMPROMISED_THRESHOLD', 3),

    /*
    |--------------------------------------------------------------------------
    | Password History
    |--------------------------------------------------------------------------
    |
    | The number of previous passwords to remember to prevent reuse.
    | Set to 0 to disable password history checking.
    |
    */
    'history_count' => env('PASSWORD_HISTORY_COUNT', 5),

    /*
    |--------------------------------------------------------------------------
    | Password Expiry Days
    |--------------------------------------------------------------------------
    |
    | The number of days before a password expires and must be changed.
    | Set to 0 to disable password expiration.
    | Note: NIST no longer recommends mandatory password rotation.
    |
    */
    'expiry_days' => env('PASSWORD_EXPIRY_DAYS', 0),

    /*
    |--------------------------------------------------------------------------
    | Common Passwords List
    |--------------------------------------------------------------------------
    |
    | Additional common passwords to block beyond the HIBP check.
    | These are checked case-insensitively.
    |
    */
    'blocked_passwords' => [
        'password',
        'password1',
        'password123',
        '12345678',
        '123456789',
        'qwerty123',
        'letmein',
        'welcome',
        'admin123',
        'iloveyou',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Strength Indicator
    |--------------------------------------------------------------------------
    |
    | Configuration for the frontend password strength meter.
    |
    */
    'strength_meter' => [
        'enabled' => true,
        'levels' => [
            'weak' => ['color' => '#ef4444', 'label' => 'Weak'],
            'fair' => ['color' => '#f59e0b', 'label' => 'Fair'],
            'good' => ['color' => '#10b981', 'label' => 'Good'],
            'strong' => ['color' => '#059669', 'label' => 'Strong'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User-Friendly Requirements Message
    |--------------------------------------------------------------------------
    |
    | A clear message explaining password requirements to users.
    |
    */
    'requirements_message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.',

    /*
    |--------------------------------------------------------------------------
    | Progressive Account Lockout
    |--------------------------------------------------------------------------
    |
    | Configuration for progressive lockout on failed login attempts.
    | Lockout duration increases with each subsequent failed attempt.
    |
    */
    'lockout' => [
        'enabled' => env('ACCOUNT_LOCKOUT_ENABLED', true),
        
        // Number of failed attempts before lockout kicks in
        'max_attempts' => env('ACCOUNT_LOCKOUT_MAX_ATTEMPTS', 5),
        
        // Progressive lockout durations in minutes
        // 1st lockout: 15 min, 2nd: 60 min, 3rd+: 1440 min (24 hours)
        'durations' => [
            1 => 15,      // 15 minutes
            2 => 60,      // 1 hour
            3 => 1440,    // 24 hours
        ],
        
        // Clear lockout history after this many days of successful logins
        'clear_after_days' => env('ACCOUNT_LOCKOUT_CLEAR_DAYS', 30),
        
        // Whether to notify user via email when account is locked
        'notify_user' => env('ACCOUNT_LOCKOUT_NOTIFY', true),
        
        // Whether to notify admin of suspicious lockouts
        'notify_admin' => env('ACCOUNT_LOCKOUT_NOTIFY_ADMIN', true),
        
        // IP-based rate limiting (per minute)
        'ip_rate_limit' => env('ACCOUNT_LOCKOUT_IP_RATE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Additional session security settings for authentication.
    |
    */
    'session' => [
        // Regenerate session ID on login
        'regenerate_on_login' => true,
        
        // Invalidate all other sessions on password change
        'invalidate_on_password_change' => true,
        
        // Maximum concurrent sessions per user (0 = unlimited)
        'max_concurrent' => env('MAX_CONCURRENT_SESSIONS', 5),
        
        // Require re-authentication for sensitive operations (minutes)
        'sudo_mode_timeout' => env('SUDO_MODE_TIMEOUT', 15),
    ],
];
