<?php

namespace Database\Factories;

use App\Models\Caregiver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaregiverFactory extends Factory
{
    protected $model = Caregiver::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'years_experience' => $this->faker->numberBetween(1, 20),
            'availability_status' => $this->faker->randomElement(['available', 'busy', 'unavailable']),
            'bio' => $this->faker->paragraph(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'preferred_hourly_rate_min' => 25.00,
            'preferred_hourly_rate_max' => 40.00,
            'has_hha' => $this->faker->boolean(),
            'has_cna' => $this->faker->boolean(),
            'has_rn' => $this->faker->boolean(),
        ];
    }
}
