<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeTracking;
use App\Models\Caregiver;
use App\Models\Client;
use Carbon\Carbon;

class TimeTrackingSeeder extends Seeder
{
    public function run(): void
    {
        // Get caregivers
        $caregivers = Caregiver::all();
        $clients = Client::all();

        if ($caregivers->isEmpty()) {
            $this->command->info('No caregivers found. Please run CaregiverSeeder first.');
            return;
        }

        // Clear existing time tracking data
        TimeTracking::truncate();

        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek();

        // Create sample data for the current week
        foreach ($caregivers->take(4) as $index => $caregiver) {
            $sampleData = $this->getSampleDataForCaregiver($index);
            
            // Create entries for the past week
            for ($day = 0; $day < 7; $day++) {
                $workDate = $startOfWeek->copy()->addDays($day);
                
                // Skip some days to create realistic patterns
                if ($this->shouldSkipDay($index, $day)) {
                    continue;
                }

                $clockIn = $workDate->copy()->setTime(9, 0)->addMinutes(rand(-30, 30));
                $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(-30, 30));
                
                // For today, some caregivers might still be clocked in
                if ($workDate->isToday() && $sampleData['currently_active']) {
                    TimeTracking::create([
                        'caregiver_id' => $caregiver->id,
                        'client_id' => $clients->random()->id ?? null,
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => null,
                        'hours_worked' => null,
                        'location' => 'Client Home',
                        'work_date' => $workDate->toDateString(),
                        'status' => 'active'
                    ]);
                } else {
                    $hoursWorked = $clockOut->diffInHours($clockIn, true);
                    
                    TimeTracking::create([
                        'caregiver_id' => $caregiver->id,
                        'client_id' => $clients->random()->id ?? null,
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'hours_worked' => $hoursWorked,
                        'location' => 'Client Home',
                        'work_date' => $workDate->toDateString(),
                        'status' => 'completed'
                    ]);
                }
            }
        }

        $this->command->info('Time tracking data seeded successfully!');
    }

    private function getSampleDataForCaregiver($index)
    {
        $sampleData = [
            0 => ['name' => 'Maria Santos', 'currently_active' => true],
            1 => ['name' => 'John Smith', 'currently_active' => false],
            2 => ['name' => 'Sarah Johnson', 'currently_active' => true],
            3 => ['name' => 'Robert Wilson', 'currently_active' => false],
        ];

        return $sampleData[$index] ?? ['name' => 'Unknown', 'currently_active' => false];
    }

    private function shouldSkipDay($caregiverIndex, $day)
    {
        // Create realistic work patterns - skip some days
        $patterns = [
            0 => [1, 6], // Maria: Skip Tuesday and Sunday
            1 => [2, 3], // John: Skip Wednesday and Thursday  
            2 => [0, 6], // Sarah: Skip Monday and Sunday
            3 => [1, 5], // Robert: Skip Tuesday and Saturday
        ];

        return in_array($day, $patterns[$caregiverIndex] ?? []);
    }
}