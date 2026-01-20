<?php

namespace Database\Factories;

use App\Models\BookingAssignment;
use App\Models\Booking;
use App\Models\Caregiver;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingAssignmentFactory extends Factory
{
    protected $model = BookingAssignment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'caregiver_id' => Caregiver::factory(),
            'status' => $this->faker->randomElement(['assigned', 'in_progress', 'completed', 'cancelled']),
            'assigned_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'assigned_hourly_rate' => $this->faker->randomFloat(2, 25, 45),
        ];
    }
}
