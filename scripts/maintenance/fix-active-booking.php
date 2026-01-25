<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Get current date and set assignment period
    $now = Carbon::now();
    $startTime = $now->copy()->setTime(9, 0, 0); // 9:00 AM today
    $endTime = $now->copy()->setTime(17, 0, 0); // 5:00 PM today
    
    // Check if demo caregiver has an assignment, if not create one
    $existingAssignment = DB::table('booking_assignments')
        ->where('caregiver_id', 25) // Demo caregiver ID
        ->first();
    
    if ($existingAssignment) {
        // Update existing assignment
        $updated = DB::table('booking_assignments')
            ->where('caregiver_id', 25)
            ->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'assigned',
                'updated_at' => $now
            ]);
        echo "✅ Updated existing assignment for demo caregiver\n";
    } else {
        // Create new assignment for demo caregiver
        $bookingId = DB::table('bookings')->first()->id; // Get first available booking
        
        DB::table('booking_assignments')->insert([
            'booking_id' => $bookingId,
            'caregiver_id' => 25,
            'assigned_at' => $now,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'assigned',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        echo "✅ Created new assignment for demo caregiver\n";
    }
    
    echo "📅 Assignment time: {$startTime->format('M d, Y g:i A')} - {$endTime->format('g:i A')}\n";
    
    // Show the assignment details
    $assignment = DB::table('booking_assignments')
        ->join('bookings', 'booking_assignments.booking_id', '=', 'bookings.id')
        ->join('clients', 'bookings.client_id', '=', 'clients.id')
        ->join('users', 'clients.user_id', '=', 'users.id')
        ->where('booking_assignments.caregiver_id', 25)
        ->select('booking_assignments.*', 'bookings.hourly_rate', 'users.name as client_name')
        ->first();
        
    if ($assignment) {
        echo "👤 Client: {$assignment->client_name}\n";
        echo "💰 Rate: \${$assignment->hourly_rate}/hour\n";
        echo "📍 Status: {$assignment->status}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}