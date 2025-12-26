<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\BookingAssignment;
use App\Models\Caregiver;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $id = 25;
    $caregiver = Caregiver::find($id);
    
    if (!$caregiver) {
        echo "Caregiver not found\n";
        exit;
    }
    
    echo "Testing caregiver stats for ID: $id\n";
    echo "Current time: " . now() . "\n";
    echo "Today: " . today() . "\n\n";
    
    // Get active assignments for this caregiver
    $activeAssignments = BookingAssignment::with(['booking.client'])
        ->where('caregiver_id', $id)
        ->where('status', 'assigned')
        ->where(function($query) {
            $query->where('start_time', '<=', now())
                  ->where('end_time', '>=', now())
                  ->orWhere(function($q) {
                      // Also include assignments for today regardless of time
                      $q->whereDate('start_time', '<=', today())
                        ->whereDate('end_time', '>=', today());
                  });
        })
        ->get();
    
    echo "Found " . $activeAssignments->count() . " active assignments\n";
    
    if ($activeAssignments->count() > 0) {
        foreach ($activeAssignments as $assignment) {
            echo "Assignment ID: " . $assignment->id . "\n";
            echo "Start time: " . $assignment->start_time . "\n";
            echo "End time: " . $assignment->end_time . "\n";
            echo "Status: " . $assignment->status . "\n";
            echo "Client name: " . $assignment->booking->client->name . "\n";
            echo "---\n";
        }
    } else {
        echo "No active assignments found\n";
        
        // Debug: show all assignments for this caregiver
        $allAssignments = BookingAssignment::where('caregiver_id', $id)->get();
        echo "\nAll assignments for caregiver $id:\n";
        foreach ($allAssignments as $assignment) {
            echo "ID: {$assignment->id}, Status: {$assignment->status}, Start: {$assignment->start_time}, End: {$assignment->end_time}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}