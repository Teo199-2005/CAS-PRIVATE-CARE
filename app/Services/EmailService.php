<?php

namespace App\Services;

use App\Mail\EmailVerification;
use App\Mail\PasswordResetEmail;
use App\Mail\BookingApprovedEmail;
use App\Mail\ContractorApplicationApprovedEmail;
use App\Mail\ContractorApplicationRejectedEmail;
use App\Mail\AnnouncementEmail;
use App\Mail\WelcomeEmail;
use App\Mail\PayoutConfirmationEmail;
use App\Mail\PayoutPendingEmail;
use App\Mail\PayoutFailedEmail;
use App\Mail\OTPVerificationEmail;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EmailService
{
    /**
     * Send email verification email
     */
    public static function sendVerificationEmail(User $user): void
    {
        try {
            // Create verification token
            $token = Str::random(64);
            
            // Store token in database (using password_reset_tokens table or create email_verifications table)
            DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => hash('sha256', $token),
                    'created_at' => now()
                ]
            );
            
            Mail::to($user->email)->send(new EmailVerification($user, $token));
            
            Log::info("Verification email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send verification email to {$user->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail(User $user, string $token): void
    {
        try {
            Mail::to($user->email)->send(new PasswordResetEmail($user->email, $token, $user->name));
            
            Log::info("Password reset email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$user->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send booking approved email
     */
    public static function sendBookingApprovedEmail(Booking $booking): void
    {
        try {
            // Load the client relationship (which is a User model)
            if (!$booking->relationLoaded('client')) {
                $booking->load('client');
            }
            $client = $booking->client;
            if ($client && $client->email) {
                Mail::to($client->email)->send(new BookingApprovedEmail($booking));
                Log::info("Booking approved email sent to {$client->email} for booking #{$booking->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send booking approved email: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the approval process
        }
    }

    /**
     * Send contractor application approved email
     */
    public static function sendContractorApprovedEmail(User $user): void
    {
        try {
            Mail::to($user->email)->send(new ContractorApplicationApprovedEmail($user));
            Log::info("Contractor approved email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send contractor approved email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the approval process
        }
    }

    /**
     * Send contractor application rejected email
     */
    public static function sendContractorRejectedEmail(User $user, string $reason = null): void
    {
        try {
            Mail::to($user->email)->send(new ContractorApplicationRejectedEmail($user, $reason));
            Log::info("Contractor rejected email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send contractor rejected email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the rejection process
        }
    }

    /**
     * Send announcement email
     */
    public static function sendAnnouncementEmail(string $email, string $title, string $message, string $type = 'info'): void
    {
        try {
            Mail::to($email)->send(new AnnouncementEmail($title, $message, $type));
            Log::info("Announcement email sent to {$email}");
        } catch (\Exception $e) {
            Log::error("Failed to send announcement email to {$email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the announcement process
        }
    }

    /**
     * Send welcome email
     */
    public static function sendWelcomeEmail(User $user): void
    {
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
            Log::info("Welcome email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::warning("Failed to send welcome email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break registration
        }
    }

    /**
     * Send OTP email for verification
     */
    public static function sendOTPEmail(User $user, string $otp): void
    {
        try {
            Mail::to($user->email)->send(new OTPVerificationEmail($user, $otp));
            Log::info("OTP email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send payout confirmation email
     */
    public static function sendPayoutConfirmationEmail(
        User $user,
        float $amount,
        string $payoutDate,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?float $hoursWorked = null,
        ?string $transactionId = null,
        ?string $payoutMethod = 'Direct Deposit'
    ): void {
        try {
            Mail::to($user->email)->send(new PayoutConfirmationEmail(
                $user,
                $amount,
                $payoutDate,
                $periodStart,
                $periodEnd,
                $hoursWorked,
                $transactionId,
                $payoutMethod
            ));
            
            Log::info("Payout confirmation email sent to {$user->email}", [
                'amount' => $amount,
                'transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payout confirmation email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the payout process
        }
    }

    /**
     * Send payout pending/upcoming email
     */
    public static function sendPayoutPendingEmail(
        User $user,
        float $amount,
        ?float $hoursWorked = null,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?string $scheduledDate = null,
        ?int $pendingCount = 1
    ): void {
        try {
            Mail::to($user->email)->send(new PayoutPendingEmail(
                $user,
                $amount,
                $hoursWorked,
                $periodStart,
                $periodEnd,
                $scheduledDate,
                $pendingCount
            ));
            
            Log::info("Payout pending email sent to {$user->email}", [
                'amount' => $amount,
                'scheduled_date' => $scheduledDate
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payout pending email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the process
        }
    }

    /**
     * Send payout failed email
     */
    public static function sendPayoutFailedEmail(
        User $user,
        float $amount,
        ?string $reason = null,
        ?string $actionRequired = null
    ): void {
        try {
            Mail::to($user->email)->send(new PayoutFailedEmail(
                $user,
                $amount,
                $reason,
                $actionRequired
            ));
            
            Log::info("Payout failed email sent to {$user->email}", [
                'amount' => $amount,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payout failed email to {$user->email}: " . $e->getMessage());
            // Don't throw - email failure shouldn't break the process
        }
    }
}