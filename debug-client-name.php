<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Creating Client Record for PAET ===\n";

// Check if client record already exists
$existing = DB::table('clients')->where('user_id', 31)->first();
if ($existing) {
    echo "Client record already exists! ID: " . $existing->id . "\n";
} else {
    $clientId = DB::table('clients')->insertGetId([
        'user_id' => 31,
        'first_name' => 'TEOFILO HARRY',
        'last_name' => 'PAET',
        'date_of_birth' => '1970-01-01',
        'mobility_level' => 'independent',
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_phone' => '555-0000',
        'emergency_contact_relationship' => 'Family',
        'verified' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Created client record for PAET. Client ID: " . $clientId . "\n";
    
    // Update session 45 to have the correct client_id
    DB::table('time_trackings')->where('id', 45)->update(['client_id' => $clientId]);
    echo "Updated time tracking session 45 with client_id: " . $clientId . "\n";
}
