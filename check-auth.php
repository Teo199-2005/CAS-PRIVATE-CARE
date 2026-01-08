<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

echo "=== SESSION CHECK ===\n\n";

// Check if there are any active sessions
try {
    $sessions = DB::table('sessions')->get();
    echo "Total active sessions: " . $sessions->count() . "\n\n";
    
    if ($sessions->count() > 0) {
        echo "Recent sessions:\n";
        foreach ($sessions->take(3) as $session) {
            echo "  - Session ID: " . substr($session->id, 0, 20) . "...\n";
            echo "    User ID: " . ($session->user_id ?? 'Not logged in') . "\n";
            echo "    Last Activity: " . date('Y-m-d H:i:s', $session->last_activity) . "\n";
            
            // Try to decode payload
            if (isset($session->payload)) {
                $payload = base64_decode($session->payload);
                if (strpos($payload, 'login_admin') !== false) {
                    echo "    Type: Admin session\n";
                } elseif (strpos($payload, 'user_id') !== false) {
                    echo "    Type: User session\n";
                }
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking sessions: " . $e->getMessage() . "\n";
}

echo "\n=== ADMIN USERS ===\n";
$adminUsers = DB::table('users')->where('user_type', 'admin')->get();
echo "Total admin users: " . $adminUsers->count() . "\n\n";

foreach ($adminUsers as $admin) {
    echo "  - ID: " . $admin->id . "\n";
    echo "    Name: " . $admin->name . "\n";
    echo "    Email: " . $admin->email . "\n";
    echo "    Status: " . $admin->status . "\n";
    echo "    Role: " . ($admin->role ?? 'N/A') . "\n";
    echo "\n";
}

echo "\n=== MIDDLEWARE CHECK ===\n";
echo "The /api/admin/users route requires:\n";
echo "  1. 'web' middleware (session)\n";
echo "  2. 'auth' middleware (logged in)\n";
echo "  3. 'user.type:admin' middleware (user_type = admin)\n";
echo "\n";
echo "If tables show 'No data available', possible causes:\n";
echo "  1. Not logged in\n";
echo "  2. Logged in as wrong user type (client/caregiver instead of admin)\n";
echo "  3. Session expired\n";
echo "  4. JavaScript error preventing API call\n";
