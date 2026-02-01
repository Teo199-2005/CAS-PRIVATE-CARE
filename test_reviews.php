<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

try {
    $reviews = Review::all();
    echo "Reviews count: " . count($reviews) . PHP_EOL;
    echo "Reviews API test: SUCCESS" . PHP_EOL;
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
