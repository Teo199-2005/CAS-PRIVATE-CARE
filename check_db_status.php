<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DATABASE STATUS CHECK ===" . PHP_EOL . PHP_EOL;

// Check connection
try {
    DB::connection()->getPdo();
    echo "✓ Database connection: OK" . PHP_EOL;
    echo "  Database: " . DB::connection()->getDatabaseName() . PHP_EOL . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ Database connection FAILED: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Check all tables and their row counts
echo "=== TABLE ROW COUNTS ===" . PHP_EOL;
$tables = DB::select('SHOW TABLES');
$dbName = DB::connection()->getDatabaseName();
$key = "Tables_in_" . $dbName;

foreach ($tables as $table) {
    $tableName = $table->$key;
    try {
        $count = DB::table($tableName)->count();
        $status = $count > 0 ? "✓" : "○";
        echo sprintf("  %s %-40s %d rows" . PHP_EOL, $status, $tableName, $count);
    } catch (\Exception $e) {
        echo sprintf("  ✗ %-40s ERROR" . PHP_EOL, $tableName);
    }
}

echo PHP_EOL . "=== MIGRATION HISTORY ===" . PHP_EOL;
$migrations = DB::table('migrations')->orderBy('id', 'desc')->limit(5)->get();
foreach ($migrations as $m) {
    echo "  Batch {$m->batch}: {$m->migration}" . PHP_EOL;
}
