<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(App\Http\Controllers\LandingController::class);
$stats = $controller->getLandingStats();
$html = view('landing', ['stats' => $stats, 'landing' => []])->render();

if (preg_match('/<p id="hero-description"[^>]*>([^<]+)<\/p>/', $html, $m)) {
    echo $m[1];
}
