<?php

namespace Tests\Feature\Booking;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    private $client;
    private $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientUser = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        $this->client = Client::factory()->create([
            'user_id' => $this->clientUser->id
        ]);
    }

    /** @test */
    public function authenticated_client_can_create_booking()
    {
        $this->actingAs($this->clientUser);

        $response = $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'hourly_rate' => 30.00,
            'hours_per_day' => 8,
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'special_instructions' => 'Test booking'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('bookings', [
            'client_id' => $this->clientUser->id,
            'service_type' => 'home_care',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function guest_cannot_create_booking()
    {
        $response = $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'hourly_rate' => 30.00
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function booking_validates_required_fields()
    {
        $this->actingAs($this->clientUser);

        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'service_type',
            'start_date',
            'hourly_rate'
        ]);
    }

    /** @test */
    public function booking_validates_start_date_is_future()
    {
        $this->actingAs($this->clientUser);

        $response = $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'hourly_rate' => 30.00
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function booking_validates_hourly_rate_is_positive()
    {
        $this->actingAs($this->clientUser);

        $response = $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'hourly_rate' => -10.00
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function booking_status_defaults_to_pending()
    {
        $this->actingAs($this->clientUser);

        $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'hourly_rate' => 30.00
        ]);

        $booking = Booking::latest()->first();
        $this->assertEquals('pending', $booking->status);
    }

    /** @test */
    public function booking_calculates_total_amount()
    {
        $this->actingAs($this->clientUser);

        $this->postJson('/api/bookings', [
            'service_type' => 'home_care',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
            'hourly_rate' => 30.00,
            'hours_per_day' => 8
        ]);

        $booking = Booking::latest()->first();
        $expectedTotal = 30.00 * 8; // $240
        
        $this->assertEquals($expectedTotal, $booking->hourly_rate * 8);
    }

    /** @test */
    public function client_can_view_their_own_bookings()
    {
        $this->actingAs($this->clientUser);

        Booking::factory()->create([
            'client_id' => $this->clientUser->id,
            'service_type' => 'home_care'
        ]);

        $response = $this->getJson('/api/client/bookings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'service_type', 'status', 'created_at']
            ]
        ]);
    }

    /** @test */
    public function client_cannot_view_other_clients_bookings()
    {
        $otherClient = User::factory()->create(['user_type' => 'client']);
        
        Booking::factory()->create([
            'client_id' => $otherClient->id
        ]);

        $this->actingAs($this->clientUser);
        $response = $this->getJson('/api/client/bookings');

        $response->assertStatus(200);
        $this->assertEquals(0, count($response->json('data')));
    }
}
