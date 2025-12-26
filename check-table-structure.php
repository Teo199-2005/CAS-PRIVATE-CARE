<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Get table structure
echo "=== time_trackings Table Structure ===\n";
$columns = DB::select("SHOW COLUMNS FROM time_trackings");
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})\n";
}
