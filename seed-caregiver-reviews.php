<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Caregiver;
use App\Models\Review;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Seeding Caregiver Reviews ===\n\n";

try {
    // Get all caregivers
    $caregivers = Caregiver::with('user')->get();
    
    if ($caregivers->isEmpty()) {
        echo "❌ No caregivers found in database.\n";
        exit(1);
    }
    
    echo "Found " . $caregivers->count() . " caregivers\n\n";
    
    // Get all clients
    $clients = User::where('role', 'client')->get();
    
    if ($clients->isEmpty()) {
        echo "❌ No clients found in database.\n";
        exit(1);
    }
    
    echo "Found " . $clients->count() . " clients\n\n";
    
    // Sample review comments
    $excellentComments = [
        "Outstanding care! Very professional and attentive to all needs.",
        "Exceptional service. My loved one felt comfortable and well cared for.",
        "Highly skilled and compassionate. I couldn't ask for better care.",
        "Goes above and beyond. Truly dedicated to providing excellent service.",
        "Professional, punctual, and caring. Highly recommend!",
    ];
    
    $goodComments = [
        "Great service overall. Very reliable and trustworthy.",
        "Good experience. Professional and respectful at all times.",
        "Solid care provided. My family member was very satisfied.",
        "Reliable and competent. Would use their services again.",
        "Pleasant experience. Good communication and care.",
    ];
    
    $averageComments = [
        "Decent service. Met basic expectations.",
        "Satisfactory care. Nothing exceptional but reliable.",
        "Good enough for our needs. Professional demeanor.",
        "Average service. Did the job adequately.",
    ];
    
    $reviewsCreated = 0;
    
    // Create reviews for each caregiver
    foreach ($caregivers as $caregiver) {
        // Random number of reviews per caregiver (3-8 reviews)
        $numReviews = rand(3, 8);
        
        echo "Creating {$numReviews} reviews for {$caregiver->user->name}...\n";
        
        for ($i = 0; $i < $numReviews; $i++) {
            // Random client
            $client = $clients->random();
            
            // Check if this client has a completed booking
            $booking = Booking::where('client_id', $client->id)
                ->where('status', 'completed')
                ->first();
            
            // If no completed booking exists, create one
            if (!$booking) {
                $booking = Booking::create([
                    'client_id' => $client->id,
                    'service_type' => 'Elderly Care',
                    'service_date' => now()->subDays(rand(1, 60))->format('Y-m-d'),
                    'start_time' => '09:00:00',
                    'duration_days' => rand(1, 5),
                    'duty_type' => 'Live-out',
                    'borough' => 'Manhattan',
                    'city' => 'New York',
                    'county' => 'New York County',
                    'street_address' => '123 Main St',
                    'status' => 'completed',
                    'assignment_status' => 'assigned',
                    'hourly_rate' => rand(25, 35),
                    'total_budget' => rand(200, 500),
                    'payment_method' => 'credit_card',
                    'gender_preference' => 'no_preference',
                    'background_check_level' => 'standard',
                    'client_age' => rand(65, 90),
                    'mobility_level' => 'assisted',
                    'transportation_needed' => false,
                    'recurring_service' => false,
                    'urgency_level' => 'scheduled',
                    'submitted_at' => now()->subDays(rand(1, 60)),
                ]);
                
                // Create assignment
                DB::table('booking_assignments')->insert([
                    'booking_id' => $booking->id,
                    'caregiver_id' => $caregiver->id,
                    'status' => 'completed',
                    'assigned_at' => now()->subDays(rand(1, 60)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Check if review already exists
            $existingReview = Review::where('booking_id', $booking->id)
                ->where('caregiver_id', $caregiver->id)
                ->where('client_id', $client->id)
                ->first();
            
            if ($existingReview) {
                continue; // Skip if review already exists
            }
            
            // Generate rating (weighted towards higher ratings)
            $rand = rand(1, 100);
            if ($rand <= 60) {
                // 60% chance of 5 stars
                $rating = 5;
                $comments = $excellentComments;
                $recommend = true;
            } elseif ($rand <= 85) {
                // 25% chance of 4 stars
                $rating = 4;
                $comments = $goodComments;
                $recommend = true;
            } elseif ($rand <= 95) {
                // 10% chance of 3 stars
                $rating = 3;
                $comments = $averageComments;
                $recommend = rand(0, 1) === 1;
            } else {
                // 5% chance of 2 stars
                $rating = 2;
                $comments = $averageComments;
                $recommend = false;
            }
            
            // Create review
            $review = Review::create([
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'caregiver_id' => $caregiver->id,
                'rating' => $rating,
                'comment' => $comments[array_rand($comments)],
                'recommend' => $recommend,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(1, 60)),
            ]);
            
            $reviewsCreated++;
        }
        
        // Update caregiver rating and total reviews
        $avgRating = Review::where('caregiver_id', $caregiver->id)->avg('rating');
        $totalReviews = Review::where('caregiver_id', $caregiver->id)->count();
        
        $caregiver->update([
            'rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
        ]);
        
        echo "✅ {$caregiver->user->name}: {$totalReviews} reviews, Avg Rating: " . round($avgRating, 2) . "\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "✅ Created {$reviewsCreated} new reviews\n";
    echo "✅ Updated ratings for " . $caregivers->count() . " caregivers\n";
    
    // Display caregivers with their ratings
    echo "\n=== Caregivers Ratings ===\n";
    $caregivers = Caregiver::with('user')->orderBy('rating', 'desc')->get();
    foreach ($caregivers as $caregiver) {
        $stars = str_repeat('⭐', round($caregiver->rating ?? 0));
        echo "{$caregiver->user->name}: {$stars} " . ($caregiver->rating ?? 0) . "/5 ({$caregiver->total_reviews} reviews)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Review seeding completed successfully!\n";
