<?php

// Direct API test - bypasses Laravel routing for debugging
header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate the BookingController::index() logic for client_id = 4
$bookings = \App\Models\Booking::where('client_id', 4)
    ->with(['assignments.caregiver', 'referralCode'])
    ->orderBy('created_at', 'desc')
    ->get();

$result = $bookings->map(function($b) {
    return [
        'id' => $b->id,
        'service_type' => $b->service_type,
        'duty_type' => $b->duty_type,
        'status' => $b->status,
        'assignment_status' => $b->assignment_status,
        'payment_status' => $b->payment_status,
        'payment_intent_id' => $b->stripe_payment_intent_id,
        'payment_date' => $b->payment_date ? (is_string($b->payment_date) ? $b->payment_date : $b->payment_date->toIso8601String()) : null,
        'service_date' => $b->service_date ? (is_string($b->service_date) ? $b->service_date : $b->service_date->toIso8601String()) : null,
        'location' => $b->city . ', ' . $b->borough,
        'hourly_rate' => $b->hourly_rate,
        'duration_days' => $b->duration_days,
    ];
});

echo json_encode([
    'success' => true,
    'bookings' => $result,
    'debug' => [
        'booking_12_payment_status' => \App\Models\Booking::find(12)->payment_status ?? 'not found',
        'timestamp' => now()->toDateTimeString()
    ]
], JSON_PRETTY_PRINT);
