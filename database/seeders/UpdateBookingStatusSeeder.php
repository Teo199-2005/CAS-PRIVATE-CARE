<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;

class UpdateBookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Update existing bookings to use new status structure
        $bookings = Booking::all();
        
        foreach ($bookings as $booking) {
            // Convert old status to new status structure
            $newStatus = 'pending';
            $assignmentStatus = 'unassigned';
            
            switch ($booking->status) {
                case 'confirmed':
                case 'completed':
                    $newStatus = 'approved';
                    break;
                case 'cancelled':
                    $newStatus = 'rejected';
                    break;
                case 'pending':
                default:
                    $newStatus = 'pending';
                    break;
            }
            
            // Determine assignment status based on existing assignments
            $assignmentCount = $booking->assignments()->where('status', 'assigned')->count();
            $requiredCaregivers = max(1, ceil($booking->duration_days / 15));
            
            if ($assignmentCount == 0) {
                $assignmentStatus = 'unassigned';
            } elseif ($assignmentCount < $requiredCaregivers) {
                $assignmentStatus = 'partial';
            } else {
                $assignmentStatus = 'assigned';
            }
            
            $booking->update([
                'status' => $newStatus,
                'assignment_status' => $assignmentStatus
            ]);
        }
        
        $this->command->info('Updated ' . $bookings->count() . ' bookings with new status structure.');
    }
}