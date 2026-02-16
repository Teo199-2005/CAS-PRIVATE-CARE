<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Booking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create Admin User (demo)
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'user_type' => 'admin'
            ]
        );

        // CAS Private Care primary admin (casprivatecare@gmail.com)
        $this->call(CasAdminSeeder::class);

        // Create Marketing User
        $marketing = User::firstOrCreate(
            ['email' => 'marketing@demo.com'],
            [
                'name' => 'Marketing Staff',
                'password' => Hash::make('password123'),
                'user_type' => 'marketing'
            ]
        );

        // Create Training User
        $training = User::firstOrCreate(
            ['email' => 'training@demo.com'],
            [
                'name' => 'Training Center',
                'password' => Hash::make('password123'),
                'user_type' => 'training'
            ]
        );

        // Create Sample Clients
        $client1 = User::firstOrCreate(
            ['email' => 'client@demo.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
                'user_type' => 'client'
            ]
        );

        Client::firstOrCreate(
            ['user_id' => $client1->id],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '1950-05-15',
                'mobility_level' => 'assisted',
                'medical_conditions' => ['Diabetes', 'Hypertension'],
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_phone' => '(555) 123-4567',
                'emergency_contact_relationship' => 'Daughter',
                'verified' => true
            ]
        );

        $client2 = User::firstOrCreate(
            ['email' => 'sarah.williams@example.com'],
            [
                'name' => 'Sarah Williams',
                'password' => Hash::make('password'),
                'user_type' => 'client'
            ]
        );

        Client::firstOrCreate(
            ['user_id' => $client2->id],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'date_of_birth' => '1945-08-22',
                'mobility_level' => 'independent',
                'medical_conditions' => ['Arthritis'],
                'emergency_contact_name' => 'Michael Williams',
                'emergency_contact_phone' => '(555) 987-6543',
                'emergency_contact_relationship' => 'Son',
            'verified' => true
        ]);

        // Create Sample Caregivers
        $caregiver1 = User::firstOrCreate(
            ['email' => 'caregiver@demo.com'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password123'),
                'user_type' => 'caregiver'
            ]
        );

        Caregiver::firstOrCreate(
            ['user_id' => $caregiver1->id],
            [
                'gender' => 'female',
                'skills' => ['Medication Management', 'Personal Hygiene', 'Meal Preparation'],
                'specializations' => ['Elderly Care', 'Dementia Care'],
                'years_experience' => 8,
                'hourly_rate' => 30.00,
                'license_number' => 'CNA123456',
                'certifications' => ['CPR', 'First Aid', 'CNA'],
                'bio' => 'Compassionate caregiver with 8 years of experience in elderly care.',
                'background_check_completed' => true,
                'available_for_transport' => true,
                'availability_status' => 'available',
                'rating' => 4.9,
                'total_reviews' => 24
            ]
        );

        $caregiver2 = User::firstOrCreate(
            ['email' => 'robert.chen@casprivatecare.com'],
            [
                'name' => 'Robert Chen',
                'password' => Hash::make('password'),
                'user_type' => 'caregiver'
            ]
        );

        Caregiver::firstOrCreate(
            ['user_id' => $caregiver2->id],
            [
                'gender' => 'male',
                'skills' => ['Physical Therapy', 'Mobility Assistance', 'Exercise Programs'],
                'specializations' => ['Physical Therapy', 'Rehabilitation'],
                'years_experience' => 10,
                'hourly_rate' => 40.00,
                'license_number' => 'PT789012',
                'certifications' => ['Licensed PT', 'CPR'],
                'bio' => 'Licensed physical therapist specializing in rehabilitation.',
                'background_check_completed' => true,
                'available_for_transport' => false,
                'availability_status' => 'available',
                'rating' => 4.8,
                'total_reviews' => 31
            ]
        );

        // Create Sample Bookings
        if (Booking::count() === 0) {
            Booking::create([
                'client_id' => $client1->id,
                'service_type' => 'Elderly Care',
                'duty_type' => '3 Caregivers - 8 Hours Duty',
                'borough' => 'Manhattan',
                'service_date' => now()->addDays(3),
                'start_time' => '09:00:00',
                'duration_days' => 15,
                'gender_preference' => 'no_preference',
                'specific_skills' => ['Medication Management', 'Personal Hygiene'],
                'client_age' => 74,
                'mobility_level' => 'assisted',
                'medical_conditions' => ['Diabetes', 'Hypertension'],
                'transportation_needed' => false,
                'street_address' => '123 Main Street',
                'apartment_unit' => 'Apt 4B',
                'special_instructions' => 'Client prefers morning appointments',
                'status' => 'approved'
            ]);

            Booking::create([
                'client_id' => $client2->id,
                'service_type' => 'Caregiver',
                'duty_type' => '2 Caregivers - 12 Hours Duty',
                'borough' => 'Brooklyn',
                'service_date' => now()->addDays(7),
                'start_time' => '08:00:00',
                'duration_days' => 30,
                'gender_preference' => 'female',
                'specific_skills' => ['Meal Preparation', 'Light Housekeeping'],
                'client_age' => 79,
                'mobility_level' => 'independent',
                'medical_conditions' => ['Arthritis'],
                'transportation_needed' => true,
                'street_address' => '456 Oak Avenue',
                'apartment_unit' => null,
                'special_instructions' => 'Client has a small dog',
                'status' => 'approved'
            ]);
        }
    }
}
