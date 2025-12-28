<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Booking;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public static function create(int $userId, string $title, string $message, string $type = 'System', string $priority = 'normal'): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'read' => false,
        ]);
    }

    /**
     * Notify client when their booking is approved
     */
    public static function notifyBookingApproved(Booking $booking): void
    {
        if (!$booking->client_id) return;
        
        self::create(
            $booking->client_id,
            'Booking Approved',
            "Your booking for {$booking->service_type} starting on " . 
            \Carbon\Carbon::parse($booking->service_date)->format('M d, Y') . " has been approved.",
            'Appointments',
            'high'
        );
    }

    /**
     * Notify client when their booking is rejected
     */
    public static function notifyBookingRejected(Booking $booking, string $reason = null): void
    {
        if (!$booking->client_id) return;
        
        $message = "Your booking for {$booking->service_type} has been rejected.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }
        
        self::create(
            $booking->client_id,
            'Booking Rejected',
            $message,
            'Appointments',
            'high'
        );
    }

    /**
     * Notify client when a caregiver is assigned to their booking
     */
    public static function notifyCaregiverAssigned(Booking $booking, User $caregiver): void
    {
        if (!$booking->client_id) return;
        
        self::create(
            $booking->client_id,
            'Caregiver Assigned',
            "{$caregiver->name} has been assigned to your booking for {$booking->service_type}.",
            'Caregivers',
            'high'
        );
    }

    /**
     * Notify caregiver when they are assigned to a booking
     */
    public static function notifyCaregiverOfAssignment(Booking $booking, int $caregiverUserId): void
    {
        $client = User::find($booking->client_id);
        $clientName = $client ? $client->name : 'a client';
        
        self::create(
            $caregiverUserId,
            'New Assignment',
            "You have been assigned to {$clientName}'s booking for {$booking->service_type} starting on " .
            \Carbon\Carbon::parse($booking->service_date)->format('M d, Y') . ".",
            'Appointments',
            'high'
        );
    }

    /**
     * Notify caregiver when they are unassigned from a booking
     */
    public static function notifyCaregiverOfUnassignment(Booking $booking, int $caregiverUserId): void
    {
        $client = User::find($booking->client_id);
        $clientName = $client ? $client->name : 'a client';
        
        self::create(
            $caregiverUserId,
            'Assignment Cancelled',
            "Your assignment to {$clientName}'s booking for {$booking->service_type} has been cancelled.",
            'Appointments',
            'normal'
        );
    }

    /**
     * Notify both parties when booking is completed
     */
    public static function notifyBookingCompleted(Booking $booking): void
    {
        // Notify client
        if ($booking->client_id) {
            self::create(
                $booking->client_id,
                'Service Completed',
                "Your {$booking->service_type} service has been completed. Thank you for using CAS Private Care!",
                'Appointments',
                'normal'
            );
        }
        
        // Notify assigned caregivers
        $booking->load('assignments.caregiver.user');
        foreach ($booking->assignments as $assignment) {
            if ($assignment->caregiver && $assignment->caregiver->user) {
                self::create(
                    $assignment->caregiver->user->id,
                    'Service Completed',
                    "Your service for booking #{$booking->id} has been marked as completed.",
                    'Appointments',
                    'normal'
                );
            }
        }
    }

    /**
     * Notify client about payment
     */
    public static function notifyPaymentReceived(int $clientId, float $amount, string $bookingService): void
    {
        self::create(
            $clientId,
            'Payment Confirmed',
            "Your payment of $" . number_format($amount, 2) . " for {$bookingService} has been received.",
            'Payments',
            'normal'
        );
    }

    /**
     * Notify caregiver about payout
     */
    public static function notifyPayoutSent(int $caregiverUserId, float $amount): void
    {
        self::create(
            $caregiverUserId,
            'Payout Sent',
            "A payout of $" . number_format($amount, 2) . " has been initiated to your account.",
            'Payments',
            'normal'
        );
    }

    /**
     * Notify user of account creation
     */
    public static function notifyAccountCreated(User $user): void
    {
        self::create(
            $user->id,
            'Welcome to CAS Private Care!',
            "Your account has been created successfully. " . 
            ($user->user_type === 'caregiver' 
                ? "Your application is pending approval. You will be notified once approved."
                : "You can now book caregiving services."),
            'System',
            'normal'
        );
    }

    /**
     * Notify contractor that their account has been approved
     */
    public static function notifyAccountApproved(User $user): void
    {
        self::create(
            $user->id,
            'Account Approved!',
            "Your contractor application has been approved! You can now login and start using the platform.",
            'System',
            'high'
        );
    }

    /**
     * Notify contractor that their account has been rejected
     */
    public static function notifyAccountRejected(User $user, string $reason = null): void
    {
        $message = "Your contractor application has been rejected.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }
        
        self::create(
            $user->id,
            'Application Rejected',
            $message,
            'System',
            'high'
        );
    }
}

