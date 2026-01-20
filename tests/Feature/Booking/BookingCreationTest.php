<?php

namespace Tests\Feature\Booking;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    private $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientUser = User::factory()->create(['user_type' => 'client', 'status' => 'Active']);
        Client::factory()->create(['user_id' => $this->clientUser->id]);
    }

    #[Test]
    public function authenticated_client_can_create_booking(): void
    {
        $this->actingAs($this->clientUser);
        $response = $this->postJson('/api/bookings', [
            'service_type' => 'Caregiver',
            'duty_type' => '8 Hours',
            'service_date' => now()->addDays(1)->format('Y-m-d'),
            'duration_days' => 15,
            'hourly_rate' => 45.00,
            'borough' => 'Manhattan',
            'city' => 'New York',
            'county' => 'New York',
            'zipcode' => '10001',
            'street_address' => '123 Main St'
        ]);
        $response->assertStatus(201);
    }

    #[Test]
    public function guest_cannot_create_booking(): void
    {
        $response = $this->postJson('/api/bookings', ['service_type' => 'Caregiver']);
        $response->assertStatus(401);
    }

    #[Test]
    public function client_can_view_their_bookings(): void
    {
        $this->actingAs($this->clientUser);
        Booking::factory()->create(['client_id' => $this->clientUser->id]);
        $response = $this->getJson('/api/bookings');
        $response->assertStatus(200);
    }
}