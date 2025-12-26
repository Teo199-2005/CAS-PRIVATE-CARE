<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Time Trackings ===\n";
$trackings = DB::table('time_trackings')->get();
echo "Total records: " . count($trackings) . "\n\n";

foreach ($trackings as $t) {
    echo "ID: {$t->id} | Caregiver ID: {$t->caregiver_id} | Client ID: " . ($t->client_id ?? 'null');
    echo " | Hours: " . ($t->total_hours ?? 'null') . "\n";
}

echo "\n=== Caregivers ===\n";
$caregivers = DB::table('caregivers')->select('id', 'user_id')->get();
foreach ($caregivers as $c) {
    echo "Caregiver ID: {$c->id} | User ID: {$c->user_id}\n";
}

echo "\n=== Demo Caregiver User ===\n";
$user = DB::table('users')->where('user_type', 'caregiver')->first();
if ($user) {
    echo "User ID: {$user->id} | Name: {$user->name}\n";
    $caregiver = DB::table('caregivers')->where('user_id', $user->id)->first();
    if ($caregiver) {
        echo "Caregiver ID: {$caregiver->id}\n";
    }
}
