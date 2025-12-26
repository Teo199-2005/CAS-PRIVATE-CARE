<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            [
                'name' => 'Demo Client',
                'email' => 'client@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'client',
            ],
            [
                'name' => 'Demo Caregiver',
                'email' => 'caregiver@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'caregiver',
            ],
            [
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Demo Marketing',
                'email' => 'marketing@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'marketing',
            ],
            [
                'name' => 'Demo Training',
                'email' => 'training@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'training',
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}