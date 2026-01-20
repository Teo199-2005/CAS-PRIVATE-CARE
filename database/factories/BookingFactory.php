<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        // client_id references users table directly (not clients table)
        // Use lazy factory reference to only create user when client_id is not provided
        return [
            'client_id' => User::factory()->state(['user_type' => 'client']),
            'service_type' => $this->faker->randomElement(['home_care', 'companion_care', 'personal_care', 'respite_care']),
            'duty_type' => $this->faker->randomElement(['live_in', 'hourly', 'overnight']),
            'caregivers_needed' => $this->faker->numberBetween(1, 3),
            'borough' => $this->faker->randomElement(['Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island']),
            'city' => 'New York',
            'county' => 'New York',
            'zipcode' => $this->faker->randomElement(['10001', '10002', '10003', '11201']),
            'service_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i:s'),
            'duration_days' => $this->faker->numberBetween(1, 30),
            'hourly_rate' => $this->faker->randomFloat(2, 25, 50),
            'gender_preference' => $this->faker->randomElement(['male', 'female', 'no_preference']),
            'street_address' => $this->faker->streetAddress(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'payment_status' => $this->faker->randomElement(['pending', 'processing', 'paid', 'failed']),
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

    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
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
