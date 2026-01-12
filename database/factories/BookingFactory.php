<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+30 days');
        $endDate = $this->faker->dateTimeBetween($startDate, '+60 days');

        return [
            'client_id' => User::factory()->create(['user_type' => 'client'])->id,
            'service_type' => $this->faker->randomElement(['home_care', 'companion_care', 'personal_care', 'respite_care']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'hourly_rate' => $this->faker->randomFloat(2, 25, 50),
            'hours_per_day' => $this->faker->numberBetween(4, 12),
            'status' => $this->faker->randomElement(['pending', 'approved', 'in_progress', 'completed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'processing', 'paid', 'failed']),
            'address' => $this->faker->streetAddress(),
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => $this->faker->randomElement(['10001', '10002', '10003', '11201']),
            'special_instructions' => $this->faker->optional()->sentence(),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'payment_status' => 'pending',
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
                'stripe_payment_intent_id' => 'pi_test_' . $this->faker->uuid(),
            ];
        });
    }
}
