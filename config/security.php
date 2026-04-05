<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin email OTP (two-factor) gate
    |--------------------------------------------------------------------------
    |
    | When true, admin and admin-staff users must verify a one-time code before
    | dashboard routes and the admin API (v1) group. When false, the 2FA
    | middleware is a no-op for those users.
    |
    */

    'admin_two_factor_enabled' => filter_var(
        env('ADMIN_2FA_ENABLED', false),
        FILTER_VALIDATE_BOOLEAN
    ),

];
