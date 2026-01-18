<?php

/**
 * Debug SMTP Connection
 */

echo "===========================================\n";
echo "   SMTP CONNECTION DEBUG\n";
echo "===========================================\n\n";

// Test 1: Basic socket connection
echo "1️⃣  Testing TCP connection to smtp-relay.brevo.com:587...\n";
$fp = @fsockopen('smtp-relay.brevo.com', 587, $errno, $errstr, 10);
if (!$fp) {
    echo "   ❌ Connection FAILED: $errstr ($errno)\n\n";
} else {
    echo "   ✅ TCP Connection OK\n";
    $response = fgets($fp, 1024);
    echo "   Server response: $response\n";
    fclose($fp);
}

// Test 2: Check PHP mail extensions
echo "2️⃣  Checking PHP extensions...\n";
echo "   openssl: " . (extension_loaded('openssl') ? '✅ loaded' : '❌ not loaded') . "\n";
echo "   curl: " . (extension_loaded('curl') ? '✅ loaded' : '❌ not loaded') . "\n";
echo "   sockets: " . (extension_loaded('sockets') ? '✅ loaded' : '❌ not loaded') . "\n\n";

// Test 3: Check SSL/TLS
echo "3️⃣  Testing SSL/TLS...\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);
$fp = @stream_socket_client('tcp://smtp-relay.brevo.com:587', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
if (!$fp) {
    echo "   ❌ Stream connection FAILED: $errstr ($errno)\n\n";
} else {
    echo "   ✅ Stream connection OK\n";
    fclose($fp);
}

// Now test with Laravel
echo "\n4️⃣  Testing Laravel mail with verbose output...\n";

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Enable more verbose error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $transport = app('mailer')->getSymfonyTransport();
    echo "   Transport type: " . get_class($transport) . "\n";
    
    // Try sending with exception details
    Mail::raw('Debug test email - ' . now()->toString(), function ($message) {
        $message->to('teofiloharry69@gmail.com')
                ->subject('Debug Test - ' . date('H:i:s'));
    });
    
    echo "   ✅ Mail::raw() executed without exception\n";
    
} catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
    echo "   ❌ Transport Exception: " . $e->getMessage() . "\n";
    echo "   Debug: " . $e->getDebug() . "\n";
} catch (\Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n===========================================\n";
echo "Check storage/logs/laravel.log for more details\n";
echo "===========================================\n";
