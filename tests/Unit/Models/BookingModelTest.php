<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function booking_belongs_to_client(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create(['client_id' => $client->id]);

        $this->assertEquals($client->id, $booking->client_id);
    }

    #[Test]
    public function booking_has_fillable_attributes(): void
    {
        $booking = new Booking();
        
        $this->assertContains('client_id', $booking->getFillable());
        $this->assertContains('status', $booking->getFillable());
        $this->assertContains('service_type', $booking->getFillable());
    }

    #[Test]
    public function booking_status_can_be_pending(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending'
        ]);

        $this->assertEquals('pending', $booking->status);
    }

    #[Test]
    public function booking_status_can_be_approved(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => 'approved'
        ]);

        $this->assertEquals('approved', $booking->status);
    }

    #[Test]
    public function booking_status_can_be_completed(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => 'completed'
        ]);

        $this->assertEquals('completed', $booking->status);
    }

    #[Test]
    public function booking_has_service_type(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'service_type' => 'home_care'
        ]);

        $this->assertEquals('home_care', $booking->service_type);
    }
}
