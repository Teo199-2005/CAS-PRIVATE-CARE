<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CasAdminSeeder extends Seeder
{
    /**
     * Seed the CAS Private Care admin user.
     * Email: casprivatecare@gmail.com
     * Password: CastielCalli@2701CastielCalli@2701
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'casprivatecare@gmail.com'],
            [
                'name' => 'CAS Admin',
                'password' => Hash::make('CastielCalli@2701CastielCalli@2701'),
                'user_type' => 'admin',
                'role' => 'Super Admin',
                'department' => 'System Administration',
                'email_verified_at' => now(),
            ]
        );
    }
}
