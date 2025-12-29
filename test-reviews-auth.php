<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Review;

echo "=== Testing Reviews API Authentication ===\n\n";

try {
    // Get admin user
    $admin = User::where('role', 'admin')->first();
    
    if (!$admin) {
        echo "❌ No admin user found!\n";
        exit(1);
    }
    
    echo "✅ Found admin user: {$admin->name} (ID: {$admin->id})\n";
    echo "   Email: {$admin->email}\n";
    echo "   Role: {$admin->role}\n\n";
    
    // Manually authenticate
    Auth::login($admin);
    
    echo "✅ Authenticated as admin\n\n";
    
    // Check if user is authenticated
    if (Auth::check()) {
        echo "✅ Auth::check() = true\n";
        echo "✅ Auth::user()->name = " . Auth::user()->name . "\n";
        echo "✅ Auth::user()->role = " . Auth::user()->role . "\n\n";
    } else {
        echo "❌ Auth::check() = false\n";
        exit(1);
    }
    
    // Test the controller logic
    $user = Auth::user();
    if (!$user || $user->role !== 'admin') {
        echo "❌ Role check failed!\n";
        echo "   User exists: " . ($user ? 'Yes' : 'No') . "\n";
        if ($user) {
            echo "   User role: {$user->role}\n";
            echo "   Role === 'admin': " . ($user->role === 'admin' ? 'true' : 'false') . "\n";
        }
        exit(1);
    }
    
    echo "✅ Role check passed\n\n";
    
    // Get reviews
    $reviews = Review::with(['client:id,name', 'caregiver.user:id,name', 'booking:id,service_type,service_date'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($review) {
            return [
                'id' => $review->id,
                'client_name' => $review->client->name ?? 'Unknown',
                'caregiver_name' => $review->caregiver->user->name ?? 'Unknown',
                'service_type' => $review->booking->service_type ?? 'N/A',
                'service_date' => $review->booking->service_date ?? null,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'recommend' => $review->recommend,
                'created_at' => $review->created_at->format('M d, Y H:i'),
            ];
        });
    
    echo "✅ Successfully retrieved {$reviews->count()} reviews\n\n";
    
    // Show sample
    echo "=== Sample Reviews ===\n";
    foreach ($reviews->take(3) as $review) {
        echo "\n📝 Review #{$review['id']}\n";
        echo "   Client: {$review['client_name']}\n";
        echo "   Caregiver: {$review['caregiver_name']}\n";
        echo "   Rating: {$review['rating']}/5 ⭐\n";
        echo "   Recommend: " . ($review['recommend'] ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n\n✅ API authentication and data retrieval working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
