<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = [
            [
                'client_id' => 2, // John Doe
                'service_type' => 'Caregiver',
                'duty_type' => '8 Hours per Day',
                'borough' => 'Manhattan',
                'service_date' => '2025-12-22',
                'start_time' => '08:00:00',
                'duration_days' => 15,
                'hourly_rate' => 25.00,
                'payment_method' => 'credit_card',
                'gender_preference' => 'no_preference',
                'language_preference' => 'english',
                'client_age' => 75,
                'mobility_level' => 'independent',
                'urgency_level' => 'scheduled',
                'street_address' => '123 Main Street',
                'apartment_unit' => 'Apt 4B',
                'status' => 'pending'
            ],
            [
                'client_id' => 2, // John Doe
                'service_type' => 'Caregiver',
                'duty_type' => '12 Hours per Day',
                'borough' => 'Brooklyn',
                'service_date' => '2025-12-23',
                'start_time' => '08:00:00',
                'duration_days' => 30,
                'hourly_rate' => 28.00,
                'payment_method' => 'insurance',
                'gender_preference' => 'female',
                'language_preference' => 'english',
                'client_age' => 82,
                'mobility_level' => 'assisted',
                'urgency_level' => 'today',
                'street_address' => '456 Oak Avenue',
                'status' => 'pending'
            ],
            [
                'client_id' => 2, // John Doe
                'service_type' => 'Elderly Care',
                'duty_type' => '24 Hours per Day',
                'borough' => 'Manhattan',
                'service_date' => '2026-01-10',
                'start_time' => '08:00:00',
                'duration_days' => 90,
                'hourly_rate' => 30.00,
                'payment_method' => 'credit_card',
                'gender_preference' => 'no_preference',
                'language_preference' => 'english',
                'client_age' => 68,
                'mobility_level' => 'independent',
                'urgency_level' => 'scheduled',
                'street_address' => '789 Pine Street',
                'status' => 'approved'
            ],
            [
                'client_id' => 2, // John Doe
                'service_type' => 'Childcare',
                'duty_type' => '8 Hours per Day',
                'borough' => 'Queens',
                'service_date' => '2025-12-30',
                'start_time' => '08:00:00',
                'duration_days' => 60,
                'hourly_rate' => 32.00,
                'payment_method' => 'insurance',
                'gender_preference' => 'female',
                'language_preference' => 'english',
                'client_age' => 79,
                'mobility_level' => 'wheelchair',
                'urgency_level' => 'asap',
                'street_address' => '321 Broadway',
                'apartment_unit' => 'Suite 12A',
                'status' => 'pending'
            ],
            [
                'client_id' => 2, // John Doe
                'service_type' => 'Personal Care',
                'duty_type' => '8 Hours per Day',
                'borough' => 'Bronx',
                'service_date' => '2024-12-15',
                'start_time' => '09:00:00',
                'duration_days' => 15,
                'hourly_rate' => 25.00,
                'payment_method' => 'credit_card',
                'gender_preference' => 'no_preference',
                'language_preference' => 'english',
                'client_age' => 70,
                'mobility_level' => 'independent',
                'urgency_level' => 'scheduled',
                'street_address' => '555 Park Avenue',
                'status' => 'completed'
            ],
            [
                'client_id' => 3, // Sarah Williams
                'service_type' => 'Personal Care',
                'duty_type' => '12 Hours per Day',
                'borough' => 'Manhattan',
                'service_date' => '2025-12-24',
                'start_time' => '08:00:00',
                'duration_days' => 30,
                'hourly_rate' => 28.00,
                'payment_method' => 'insurance',
                'gender_preference' => 'female',
                'language_preference' => 'english',
                'client_age' => 65,
                'mobility_level' => 'assisted',
                'urgency_level' => 'scheduled',
                'street_address' => '777 Fifth Avenue',
                'status' => 'approved'
            ]
        ];

        foreach ($bookings as $index => $booking) {
            $createdBooking = Booking::create($booking);
            
            // Create assignments for approved bookings
            if ($booking['status'] === 'approved') {
                // Assign the first confirmed booking to caregiver ID 2 (Demo Caregiver)
                // and others to the first available caregiver
                if ($index === 2) { // The Elderly Care booking
                    $caregiver = Caregiver::find(2); // Demo Caregiver
                } else {
                    $caregiver = Caregiver::first(); // First available caregiver
                }
                
                if ($caregiver) {
                    BookingAssignment::create([
                        'booking_id' => $createdBooking->id,
                        'caregiver_id' => $caregiver->id,
                        'assigned_at' => now(),
                        'status' => 'assigned'
                    ]);
                }
            }
        }
    }
}