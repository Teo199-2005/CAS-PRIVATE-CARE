<?php

namespace Database\Factories;

use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeTrackingFactory extends Factory
{
    protected $model = TimeTracking::class;

    public function definition(): array
    {
        $clockIn = $this->faker->dateTimeBetween('-7 days', 'now');
        $clockOut = (clone $clockIn)->modify('+8 hours');
        $hoursWorked = 8.00;
        
        // Create a proper client record
        $clientUser = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $clientUser->id]);
        
        return [
            'caregiver_id' => null,
            'housekeeper_id' => null,
            'provider_type' => 'caregiver',
            'client_id' => $client->id,
            'booking_id' => null,
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'hours_worked' => $hoursWorked,
            'actual_minutes_worked' => $hoursWorked * 60,
            'scheduled_minutes' => $hoursWorked * 60,
            'late_minutes' => 0,
            'is_late' => false,
            'minutes_difference' => 0,
            'caregiver_earnings' => $hoursWorked * 28.00,
            'agency_commission' => $hoursWorked * 10.50,
            'total_client_charge' => $hoursWorked * 40.00,
            'marketing_partner_id' => null,
            'marketing_partner_commission' => 0,
            'marketing_commission_paid_at' => null,
            'marketing_paid' => false,
            'training_center_user_id' => null,
            'training_center_commission' => $hoursWorked * 0.50,
            'training_commission_paid_at' => null,
            'training_paid' => false,
            'payment_status' => 'pending',
            'paid_at' => null,
            'status' => 'completed',
            'work_date' => $clockIn->format('Y-m-d'),
            'location' => 'New York, NY',
        ];
    }

    public function forCaregiver($caregiverId)
    {
        return $this->state(function (array $attributes) use ($caregiverId) {
            return [
                'caregiver_id' => $caregiverId,
                'provider_type' => 'caregiver',
            ];
        });
    }

    public function forHousekeeper($housekeeperId)
    {
        return $this->state(function (array $attributes) use ($housekeeperId) {
            return [
                'housekeeper_id' => $housekeeperId,
                'provider_type' => 'housekeeper',
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
                'paid_at' => now(),
                'stripe_transfer_id' => 'tr_test_' . $this->faker->uuid(),
            ];
        });
    }
}
