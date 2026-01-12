<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\User;
use App\Models\BookingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function booking_belongs_to_client()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $booking = Booking::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(User::class, $booking->client);
        $this->assertEquals($client->id, $booking->client->id);
    }

    /** @test */
    public function booking_has_assignments()
    {
        $booking = Booking::factory()->create();
        
        BookingAssignment::factory()->count(2)->create([
            'booking_id' => $booking->id
        ]);

        $this->assertCount(2, $booking->assignments);
    }

    /** @test */
    public function booking_status_defaults_to_pending()
    {
        $booking = Booking::factory()->create();
        
        $this->assertEquals('pending', $booking->status);
    }

    /** @test */
    public function booking_payment_status_defaults_to_pending()
    {
        $booking = Booking::factory()->create();
        
        $this->assertEquals('pending', $booking->payment_status);
    }

    /** @test */
    public function booking_calculates_duration_in_days()
    {
        $booking = Booking::factory()->create([
            'start_date' => '2026-01-15',
            'end_date' => '2026-01-20'
        ]);

        $duration = \Carbon\Carbon::parse($booking->start_date)
            ->diffInDays(\Carbon\Carbon::parse($booking->end_date));

        $this->assertEquals(5, $duration);
    }

    /** @test */
    public function booking_stores_hourly_rate_as_decimal()
    {
        $booking = Booking::factory()->create([
            'hourly_rate' => 30.50
        ]);

        $this->assertEquals(30.50, $booking->hourly_rate);
        $this->assertIsFloat($booking->hourly_rate);
    }

    /** @test */
    public function booking_has_service_type()
    {
        $booking = Booking::factory()->create([
            'service_type' => 'home_care'
        ]);

        $this->assertEquals('home_care', $booking->service_type);
    }
}
