<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Review;
use App\Models\User;

echo "=== Testing Reviews API ===\n\n";

try {
    // Check total reviews in database
    $totalReviews = Review::count();
    echo "Total reviews in database: {$totalReviews}\n\n";
    
    if ($totalReviews === 0) {
        echo "❌ No reviews found in database!\n";
        exit(1);
    }
    
    // Get all reviews with relationships
    $reviews = Review::with(['client:id,name', 'caregiver.user:id,name', 'booking:id,service_type,service_date'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "=== All Reviews ===\n";
    foreach ($reviews as $review) {
        echo "\nReview ID: {$review->id}\n";
        echo "Client: " . ($review->client->name ?? 'Unknown') . "\n";
        echo "Caregiver: " . ($review->caregiver->user->name ?? 'Unknown') . "\n";
        echo "Service: " . ($review->booking->service_type ?? 'N/A') . "\n";
        echo "Rating: {$review->rating}/5 ⭐\n";
        echo "Recommend: " . ($review->recommend ? 'Yes ✓' : 'No ✗') . "\n";
        echo "Comment: " . ($review->comment ?? 'No comment') . "\n";
        echo "Date: " . $review->created_at->format('M d, Y H:i') . "\n";
        echo "---\n";
    }
    
    // Format for API response
    echo "\n=== API Format ===\n";
    $apiReviews = $reviews->map(function($review) {
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
    
    echo json_encode([
        'success' => true,
        'reviews' => $apiReviews,
        'total' => $totalReviews,
    ], JSON_PRETTY_PRINT) . "\n";
    
    echo "\n✅ Reviews data is ready for API!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
