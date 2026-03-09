<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Staff scheduling limits
    |--------------------------------------------------------------------------
    | Maximum hours per week and per shift for caregivers and housekeepers.
    */
    'max_hours_per_week' => (int) env('SCHEDULING_MAX_HOURS_PER_WEEK', 40),
    'max_hours_per_shift' => (int) env('SCHEDULING_MAX_HOURS_PER_SHIFT', 12),

];
