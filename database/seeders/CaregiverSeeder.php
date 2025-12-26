<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CaregiverSeeder extends Seeder
{
    public function run(): void
    {
        $caregivers = [
            ['name' => 'Jennifer Martinez', 'email' => 'jennifer.martinez@example.com', 'gender' => 'female', 'borough' => 'Manhattan'],
            ['name' => 'Michael Johnson', 'email' => 'michael.johnson@example.com', 'gender' => 'male', 'borough' => 'Brooklyn'],
            ['name' => 'Sarah Wilson', 'email' => 'sarah.wilson@example.com', 'gender' => 'female', 'borough' => 'Queens'],
            ['name' => 'David Brown', 'email' => 'david.brown@example.com', 'gender' => 'male', 'borough' => 'Bronx'],
            ['name' => 'Lisa Anderson', 'email' => 'lisa.anderson@example.com', 'gender' => 'female', 'borough' => 'Manhattan'],
            ['name' => 'James Wilson', 'email' => 'james.wilson@example.com', 'gender' => 'male', 'borough' => 'Brooklyn'],
            ['name' => 'Patricia Garcia', 'email' => 'patricia.garcia@example.com', 'gender' => 'female', 'borough' => 'Queens'],
            ['name' => 'Christopher Lee', 'email' => 'christopher.lee@example.com', 'gender' => 'male', 'borough' => 'Manhattan'],
            ['name' => 'Nancy Rodriguez', 'email' => 'nancy.rodriguez@example.com', 'gender' => 'female', 'borough' => 'Bronx'],
            ['name' => 'Daniel Taylor', 'email' => 'daniel.taylor@example.com', 'gender' => 'male', 'borough' => 'Brooklyn'],
            ['name' => 'Karen Thomas', 'email' => 'karen.thomas@example.com', 'gender' => 'female', 'borough' => 'Queens'],
            ['name' => 'Matthew Moore', 'email' => 'matthew.moore@example.com', 'gender' => 'male', 'borough' => 'Manhattan'],
            ['name' => 'Betty Jackson', 'email' => 'betty.jackson@example.com', 'gender' => 'female', 'borough' => 'Brooklyn'],
            ['name' => 'Anthony White', 'email' => 'anthony.white@example.com', 'gender' => 'male', 'borough' => 'Queens'],
            ['name' => 'Sandra Harris', 'email' => 'sandra.harris@example.com', 'gender' => 'female', 'borough' => 'Bronx'],
            ['name' => 'Mark Martin', 'email' => 'mark.martin@example.com', 'gender' => 'male', 'borough' => 'Manhattan'],
            ['name' => 'Donna Thompson', 'email' => 'donna.thompson@example.com', 'gender' => 'female', 'borough' => 'Brooklyn'],
            ['name' => 'Paul Martinez', 'email' => 'paul.martinez@example.com', 'gender' => 'male', 'borough' => 'Queens'],
            ['name' => 'Carol Robinson', 'email' => 'carol.robinson@example.com', 'gender' => 'female', 'borough' => 'Manhattan'],
            ['name' => 'Steven Clark', 'email' => 'steven.clark@example.com', 'gender' => 'male', 'borough' => 'Brooklyn'],
        ];

        $skills = [
            ['Medication Management', 'Personal Hygiene', 'Meal Preparation'],
            ['Physical Therapy', 'Mobility Assistance', 'Exercise Programs'],
            ['Dementia Care', 'Alzheimer Support', 'Memory Care'],
            ['Wound Care', 'Vital Signs Monitoring', 'Medical Equipment'],
        ];

        $specializations = [
            ['Elderly Care', 'Dementia Care'],
            ['Physical Therapy', 'Rehabilitation'],
            ['Palliative Care', 'Hospice Care'],
            ['Post-Surgery Care', 'Chronic Illness'],
        ];

        foreach ($caregivers as $index => $caregiver) {
            // Skip if email already exists
            if (DB::table('users')->where('email', $caregiver['email'])->exists()) {
                continue;
            }
            
            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $caregiver['name'],
                'email' => $caregiver['email'],
                'password' => Hash::make('password123'),
                'user_type' => 'caregiver',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create caregiver profile
            DB::table('caregivers')->insert([
                'user_id' => $userId,
                'gender' => $caregiver['gender'],
                'skills' => json_encode($skills[$index % 4]),
                'specializations' => json_encode($specializations[$index % 4]),
                'years_experience' => rand(3, 15),
                'hourly_rate' => rand(25, 50),
                'license_number' => 'LIC' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'certifications' => json_encode(['CPR', 'First Aid', 'CNA']),
                'bio' => 'Experienced caregiver dedicated to providing compassionate care.',
                'background_check_completed' => 1,
                'available_for_transport' => rand(0, 1),
                'availability_status' => 'available',
                'rating' => number_format(rand(42, 50) / 10, 2),
                'total_reviews' => rand(10, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
