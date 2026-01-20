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
    }

    #[Test]
    public function booking_status_can_be_pending(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create(['client_id' => $client->id, 'status' => 'pending']);
        $this->assertEquals('pending', $booking->status);
    }
}