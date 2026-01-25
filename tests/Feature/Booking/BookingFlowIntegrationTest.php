<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Models\Housekeeper;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $clientUser;
    protected Client $client;
    protected Service $service;
    protected Housekeeper $housekeeper;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->clientUser = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now()
        ]);
        
        $this->client = Client::factory()->create([
            'user_id' => $this->clientUser->id
        ]);
        
        $this->service = Service::factory()->create([
            'name' => 'Home Care',
            'price' => 100.00,
            'is_active' => true
        ]);
        
        $this->housekeeper = Housekeeper::factory()->create([
            'status' => 'active',
            'is_available' => true
        ]);
    }

    /** @test */
    public function client_can_view_available_services()
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price']
                ]
            ]);
    }

    /** @test */
    public function client_can_view_available_caregivers()
    {
        $response = $this->getJson('/api/housekeepers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'rating']
                ]
            ]);
    }

    /** @test */
    public function client_can_create_booking()
    {
        Queue::fake();

        $bookingData = [
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'address' => '123 Test Street',
            'notes' => 'Test booking notes'
        ];

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'status', 'total_amount']
            ]);

        $this->assertDatabaseHas('bookings', [
            'client_id' => $this->client->id,
            'service_id' => $this->service->id
        ]);
    }

    /** @test */
    public function client_cannot_book_in_past()
    {
        $bookingData = [
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id,
            'date' => now()->subDays(1)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'address' => '123 Test Street'
        ];

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422);
    }

    /** @test */
    public function client_can_view_their_bookings()
    {
        Booking::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id
        ]);

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson('/api/client/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function client_can_cancel_pending_booking()
    {
        $booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function client_cannot_cancel_completed_booking()
    {
        $booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(422);
    }

    /** @test */
    public function caregiver_can_accept_booking()
    {
        $caregiverUser = User::factory()->create(['role' => 'caregiver']);
        $this->housekeeper->update(['user_id' => $caregiverUser->id]);

        $booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($caregiverUser, 'sanctum')
            ->postJson("/api/caregiver/bookings/{$booking->id}/accept");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'accepted'
        ]);
    }

    /** @test */
    public function booking_requires_authentication()
    {
        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(401);
    }

    /** @test */
    public function booking_validation_works()
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_id', 'date']);
    }

    /** @test */
    public function client_cannot_access_other_clients_bookings()
    {
        $otherClient = Client::factory()->create();
        $otherBooking = Booking::factory()->create([
            'client_id' => $otherClient->id,
            'service_id' => $this->service->id,
            'housekeeper_id' => $this->housekeeper->id
        ]);

        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson("/api/bookings/{$otherBooking->id}");

        $response->assertStatus(403);
    }
}
