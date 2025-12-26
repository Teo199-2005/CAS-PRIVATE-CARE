<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing notifications
        Notification::truncate();
        
        // Client notifications (user_id = 1)
        $clientNotifications = [
            [
                'user_id' => 1,
                'title' => 'Upcoming Appointment',
                'message' => 'Your appointment with Maria Santos is tomorrow at 2:00 PM',
                'type' => 'Appointments',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'user_id' => 1,
                'title' => 'Payment Confirmed',
                'message' => 'Your payment of $120.00 has been processed',
                'type' => 'Payments',
                'priority' => 'normal',
                'read' => false,
                'created_at' => Carbon::now()->subDay()
            ],
            [
                'user_id' => 1,
                'title' => 'New Caregiver Available',
                'message' => 'Jennifer Wilson is now available in your area',
                'type' => 'Caregivers',
                'priority' => 'normal',
                'read' => false,
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => 1,
                'title' => 'Booking Confirmed',
                'message' => 'Your booking with Ana Rodriguez has been confirmed for Dec 21',
                'type' => 'Appointments',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subDays(3)
            ],
            [
                'user_id' => 1,
                'title' => 'Service Reminder',
                'message' => 'You have a service scheduled with Robert Chen today at 10:00 AM',
                'type' => 'Appointments',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHours(5)
            ],
            [
                'user_id' => 1,
                'title' => 'Payment Pending',
                'message' => 'Payment of $85.00 is pending for your recent service',
                'type' => 'Payments',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subWeek()
            ],
            [
                'user_id' => 1,
                'title' => 'Profile Updated',
                'message' => 'Your profile information has been successfully updated',
                'type' => 'System',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subWeeks(2)
            ],
            [
                'user_id' => 1,
                'title' => 'New Message',
                'message' => 'You have a new message from Emma Wilson',
                'type' => 'Caregivers',
                'priority' => 'normal',
                'read' => false,
                'created_at' => Carbon::now()->subHours(3)
            ]
        ];
        
        // Caregiver notifications (user_id = 2)
        $caregiverNotifications = [
            [
                'user_id' => 2,
                'title' => 'New Client Assignment',
                'message' => 'You have been assigned to John Doe for elderly care services',
                'type' => 'Clients',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHour()
            ],
            [
                'user_id' => 2,
                'title' => 'Weekly Earnings Report',
                'message' => 'Your weekly earnings report is ready. You earned $1,240 this week.',
                'type' => 'Payments',
                'priority' => 'normal',
                'read' => false,
                'created_at' => Carbon::now()->subHours(6)
            ],
            [
                'user_id' => 2,
                'title' => 'Appointment Reminder',
                'message' => 'Reminder: You have an appointment with Sarah Johnson at 3:00 PM today',
                'type' => 'Appointments',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHours(4)
            ],
            [
                'user_id' => 2,
                'title' => 'Training Certificate Expiring',
                'message' => 'Your CPR certification expires in 30 days. Please renew it.',
                'type' => 'System',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'user_id' => 2,
                'title' => 'Payment Received',
                'message' => 'Payment of $320.00 has been deposited to your account',
                'type' => 'Payments',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => 2,
                'title' => 'Client Review Received',
                'message' => 'Emma Wilson left you a 5-star review: "Excellent care and very professional"',
                'type' => 'Clients',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subDays(3)
            ]
        ];
        
        // Admin notifications (user_id = 3)
        $adminNotifications = [
            [
                'user_id' => 3,
                'title' => 'New Caregiver Application',
                'message' => 'Jennifer Martinez has submitted a caregiver application for review',
                'type' => 'System',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subMinutes(30)
            ],
            [
                'user_id' => 3,
                'title' => 'System Maintenance Required',
                'message' => 'Scheduled maintenance window is approaching on Dec 20, 2024',
                'type' => 'System',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'user_id' => 3,
                'title' => 'Payment Dispute',
                'message' => 'Client John Doe has disputed a payment of $150.00. Requires admin review.',
                'type' => 'Payments',
                'priority' => 'high',
                'read' => false,
                'created_at' => Carbon::now()->subHours(8)
            ],
            [
                'user_id' => 3,
                'title' => 'Monthly Revenue Report',
                'message' => 'December revenue report is ready. Total revenue: $45,250',
                'type' => 'System',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subDay()
            ],
            [
                'user_id' => 3,
                'title' => 'High Booking Volume Alert',
                'message' => 'Booking volume is 25% higher than usual. Consider adding more caregivers.',
                'type' => 'System',
                'priority' => 'normal',
                'read' => false,
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => 3,
                'title' => 'Caregiver Verification Complete',
                'message' => 'Maria Santos has completed background verification successfully',
                'type' => 'System',
                'priority' => 'normal',
                'read' => true,
                'created_at' => Carbon::now()->subDays(3)
            ]
        ];
        
        // Insert all notifications
        foreach (array_merge($clientNotifications, $caregiverNotifications, $adminNotifications) as $notification) {
            Notification::create($notification);
        }
        
        $this->command->info('Created ' . count(array_merge($clientNotifications, $caregiverNotifications, $adminNotifications)) . ' notifications');
    }
}
