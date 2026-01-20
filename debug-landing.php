<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/', 'GET');

// Capture any errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "Error [$errno]: $errstr in $errfile:$errline\n";
});

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() !== 200) {
        // Check if it's a debug response
        $content = $response->getContent();
        if (strpos($content, 'Whoops') !== false) {
            echo "Debug error page detected\n";
        }
        // Try to find error message
        if (preg_match('/error-message.*?>(.*?)<\/p/s', $content, $matches)) {
            echo "Error: " . strip_tags($matches[1]) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
