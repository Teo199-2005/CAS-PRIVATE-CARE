<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Modify the status column from enum to varchar to support all status values
    DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN status VARCHAR(50) DEFAULT 'draft'");
    echo "Status column updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
