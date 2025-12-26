<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MarketingUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Marketing Staff',
            'email' => 'marketing@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'marketing',
            'email_verified_at' => now(),
        ]);
    }
}