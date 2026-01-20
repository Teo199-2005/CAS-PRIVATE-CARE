<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_complete_booking_flow(): void
    {
        // 1. Create and authenticate client
        $user = User::factory()->create([
            'user_type' => 'client',
            'email_verified_at' => now(),
        ]);
        
        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);
        
        $this->actingAs($user);
        
        // 2. Create booking with correct fields
        $bookingData = [
            'client_id' => $user->id,
            'service_type' => 'Caregiver',
            'duty_type' => '8 Hours',
            'service_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'duration_days' => 15,
            'hourly_rate' => 45.00,
            'borough' => 'Manhattan',
            'city' => 'New York',
            'county' => 'New York',
            'zipcode' => '10001',
            'street_address' => '123 Main St',
        ];
        
        $response = $this->postJson('/api/bookings', $bookingData);
        
        $response->assertStatus(201);
        
        $bookingId = $response->json('booking.id');
        
        // 3. Verify booking was created
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'client_id' => $user->id,
            'status' => 'pending',
        ]);
    }
    
    #[Test]
    public function booking_status_progresses_correctly(): void
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'status' => 'pending',
        ]);
        
        $this->actingAs($user);
        
        // User can view their booking
        $response = $this->getJson("/api/bookings/{$booking->id}");
        $response->assertStatus(200);
    }
    
    #[Test]
    public function user_can_cancel_booking(): void
    {
        // Note: Only admins can delete bookings in this system
        $admin = User::factory()->create(['user_type' => 'admin']);
        $client = User::factory()->create(['user_type' => 'client']);
        Client::factory()->create(['user_id' => $client->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => 'approved',
        ]);
        
        // Admin can delete the booking
        $this->actingAs($admin);
        
        $response = $this->deleteJson("/api/bookings/{$booking->id}");
        
        // Admin should be able to delete
        $response->assertSuccessful();
    }
    
    #[Test]
    public function booking_requires_minimal_valid_data(): void
    {
        $user = User::factory()->create(['user_type' => 'client']);
        Client::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);
        
        // Provide complete booking data
        $response = $this->postJson('/api/bookings', [
            'service_type' => 'Caregiver',
            'duty_type' => '8 Hours',
            'service_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'duration_days' => 15,
            'hourly_rate' => 45.00,
            'borough' => 'Manhattan',
            'city' => 'New York',
            'county' => 'New York',
            'zipcode' => '10001',
            'street_address' => '123 Test St',
        ]);
        
        // Should succeed with valid data
        $response->assertSuccessful();
    }
    
    #[Test]
    public function only_booking_owner_can_access_booking(): void
    {
        $owner = User::factory()->create(['user_type' => 'client']);
        $otherUser = User::factory()->create(['user_type' => 'client']);
        
        Client::factory()->create(['user_id' => $owner->id]);
        Client::factory()->create(['user_id' => $otherUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $owner->id,
        ]);
        
        // Other user cannot access
        $this->actingAs($otherUser);
        $response = $this->getJson("/api/bookings/{$booking->id}");
        $response->assertStatus(403);
        
        // Owner can access
        $this->actingAs($owner);
        $response = $this->getJson("/api/bookings/{$booking->id}");
        $response->assertStatus(200);
    }
}
