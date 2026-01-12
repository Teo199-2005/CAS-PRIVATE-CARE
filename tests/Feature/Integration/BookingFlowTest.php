<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete booking creation flow
     */
    public function test_user_can_complete_booking_flow(): void
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
        
        // 2. Create booking
        $bookingData = [
            'client_id' => $user->id,
            'service_type' => 'companion_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'duration' => 8,
            'hourly_rate' => 25.00,
            'total_price' => 200.00,
            'status' => 'pending',
            'payment_status' => 'pending',
        ];
        
        $response = $this->postJson('/api/bookings', $bookingData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'booking' => [
                    'id',
                    'client_id',
                    'service_type',
                    'total_price',
                    'status',
                ]
            ]);
        
        $bookingId = $response->json('booking.id');
        
        // 3. Verify booking was created
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'client_id' => $user->id,
            'status' => 'pending',
        ]);
        
        // 4. View booking details
        $response = $this->getJson("/api/bookings/{$bookingId}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'booking' => [
                    'id' => $bookingId,
                    'client_id' => $user->id,
                ]
            ]);
    }
    
    /**
     * Test booking status progression
     */
    public function test_booking_status_progresses_correctly(): void
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'status' => 'pending',
        ]);
        
        $this->actingAs($user);
        
        // Confirm booking
        $response = $this->patchJson("/api/bookings/{$booking->id}/status", [
            'status' => 'confirmed',
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
        
        // Mark in progress
        $response = $this->patchJson("/api/bookings/{$booking->id}/status", [
            'status' => 'in_progress',
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'in_progress',
        ]);
        
        // Complete booking
        $response = $this->patchJson("/api/bookings/{$booking->id}/status", [
            'status' => 'completed',
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);
    }
    
    /**
     * Test booking cancellation flow
     */
    public function test_user_can_cancel_booking(): void
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'status' => 'confirmed',
        ]);
        
        $this->actingAs($user);
        
        $response = $this->deleteJson("/api/bookings/{$booking->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
    
    /**
     * Test booking validation
     */
    public function test_booking_requires_valid_data(): void
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $this->actingAs($user);
        
        // Missing required fields
        $response = $this->postJson('/api/bookings', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_type', 'start_date', 'start_time']);
        
        // Invalid service type
        $response = $this->postJson('/api/bookings', [
            'service_type' => 'invalid_service',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '09:00',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_type']);
        
        // Past date
        $response = $this->postJson('/api/bookings', [
            'service_type' => 'companion_care',
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'start_time' => '09:00',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }
    
    /**
     * Test only booking owner can access booking
     */
    public function test_only_booking_owner_can_access_booking(): void
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
