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
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin'
        ]);

        // Create Marketing User
        $marketing = User::create([
            'name' => 'Marketing Staff',
            'email' => 'marketing@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'marketing'
        ]);

        // Create Training User
        $training = User::create([
            'name' => 'Training Center',
            'email' => 'training@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'training'
        ]);

        // Create Sample Clients
        $client1 = User::create([
            'name' => 'John Doe',
            'email' => 'client@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'client'
        ]);

        Client::create([
            'user_id' => $client1->id,
            'date_of_birth' => '1950-05-15',
            'mobility_level' => 'assisted',
            'medical_conditions' => ['Diabetes', 'Hypertension'],
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '(555) 123-4567',
            'emergency_contact_relationship' => 'Daughter',
            'verified' => true
        ]);

        $client2 = User::create([
            'name' => 'Sarah Williams',
            'email' => 'sarah.williams@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'client'
        ]);

        Client::create([
            'user_id' => $client2->id,
            'date_of_birth' => '1945-08-22',
            'mobility_level' => 'independent',
            'medical_conditions' => ['Arthritis'],
            'emergency_contact_name' => 'Michael Williams',
            'emergency_contact_phone' => '(555) 987-6543',
            'emergency_contact_relationship' => 'Son',
            'verified' => true
        ]);

        // Create Sample Caregivers
        $caregiver1 = User::create([
            'name' => 'Maria Santos',
            'email' => 'caregiver@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'caregiver'
        ]);

        Caregiver::create([
            'user_id' => $caregiver1->id,
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
        ]);

        $caregiver2 = User::create([
            'name' => 'Robert Chen',
            'email' => 'robert.chen@casprivatecare.com',
            'password' => Hash::make('password'),
            'user_type' => 'caregiver'
        ]);

        Caregiver::create([
            'user_id' => $caregiver2->id,
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
        ]);

        // Create Sample Bookings
        Booking::create([
            'client_id' => $client1->id,
            'service_type' => 'Elderly Care',
            'duty_type' => '3 Caregivers - 8 Hours Duty',
            'borough' => 'Manhattan',
            'service_date' => now()->addDays(3),
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
            'service_type' => 'Personal Care',
            'duty_type' => '2 Caregivers - 12 Hours Duty',
            'borough' => 'Brooklyn',
            'service_date' => now()->addDays(7),
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
