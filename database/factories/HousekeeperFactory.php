<?php

namespace Database\Factories;

use App\Models\Housekeeper;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HousekeeperFactory extends Factory
{
    protected $model = Housekeeper::class;

    public function definition()
    {
        // Create or use a linked user
        return [
            'user_id' => User::factory(),
            'bio' => $this->faker->paragraph(),
            'years_experience' => $this->faker->numberBetween(0, 10),
            'hourly_rate' => $this->faker->randomFloat(2, 10, 60),
            'stripe_connect_id' => null,
            'stripe_charges_enabled' => false,
            'stripe_payouts_enabled' => false,
            'availability_status' => 'available',
        ];
    }
}
