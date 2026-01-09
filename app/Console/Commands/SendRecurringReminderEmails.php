<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendRecurringReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-recurring-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming recurring contract renewals (5, 4, 3, 2, 1 days before)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for upcoming recurring contract renewals...');

        $emailsSent = 0;
        $notificationsCreated = 0;

        // Get all active recurring bookings with auto-pay enabled
        $bookings = Booking::where('recurring_service', true)
            ->where('auto_pay_enabled', true)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['approved', 'completed'])
            ->whereNotNull('service_date')
            ->get();

        foreach ($bookings as $booking) {
            try {
                // Calculate the end date (service_date + duration_days)
                $endDate = Carbon::parse($booking->service_date)->addDays($booking->duration_days);
                
                // Calculate days until renewal
                $daysUntilRenewal = Carbon::now()->startOfDay()->diffInDays($endDate, false);
                
                // Only send reminders at exactly 5, 4, 3, 2, or 1 days before
                if (!in_array($daysUntilRenewal, [5, 4, 3, 2, 1])) {
                    continue;
                }

                // Check if we already sent a reminder today for this booking
                $alreadySent = Notification::where('user_id', $booking->client_id)
                    ->where('title', 'LIKE', "%Contract Renews in {$daysUntilRenewal} Day%")
                    ->where('message', 'LIKE', "%#" . $booking->id . "%")
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if ($alreadySent) {
                    $this->info("Reminder already sent for booking #{$booking->id} ({$daysUntilRenewal} days)");
                    continue;
                }

                $client = $booking->client;
                if (!$client) {
                    $this->warn("Client not found for booking #{$booking->id}");
                    continue;
                }

                // Calculate renewal details
                $hours = $this->extractHours($booking->duty_type);
                $rate = $booking->hourly_rate ?? 45;
                $days = $booking->duration_days ?? 15;
                $amount = $hours * $days * $rate;
                $renewalDate = $endDate->format('F j, Y');

                // Send email
                try {
                    Mail::send('emails.recurring-reminder', [
                        'client_name' => $client->name,
                        'days_until_renewal' => $daysUntilRenewal,
                        'renewal_date' => $renewalDate,
                        'booking_id' => $booking->id,
                        'service_type' => $booking->service_type,
                        'duration_days' => $days,
                        'amount' => number_format($amount, 2),
                        'hours_per_day' => $hours,
                        'dashboard_url' => url('/client-dashboard')
                    ], function ($message) use ($client, $daysUntilRenewal) {
                        $message->to($client->email)
                            ->subject("Reminder: Your Contract Renews in {$daysUntilRenewal} Day" . ($daysUntilRenewal > 1 ? 's' : ''));
                    });

                    $emailsSent++;
                    $this->info("✓ Email sent to {$client->email} for booking #{$booking->id} ({$daysUntilRenewal} days)");
                } catch (\Exception $e) {
                    $this->error("Failed to send email to {$client->email}: {$e->getMessage()}");
                    Log::error('Failed to send recurring reminder email', [
                        'booking_id' => $booking->id,
                        'client_email' => $client->email,
                        'error' => $e->getMessage()
                    ]);
                }

                // Create in-app notification
                Notification::create([
                    'user_id' => $booking->client_id,
                    'type' => 'Payments',
                    'priority' => $daysUntilRenewal <= 2 ? 'high' : 'normal',
                    'title' => "Contract Renews in {$daysUntilRenewal} Day" . ($daysUntilRenewal > 1 ? 's' : ''),
                    'message' => "Your {$booking->service_type} contract (booking #{$booking->id}) will automatically renew on {$renewalDate}. Your card will be charged \${$amount}. To cancel auto-renewal, visit your Payment Information page.",
                    'read' => false
                ]);

                $notificationsCreated++;
                
                Log::info('Recurring reminder sent', [
                    'booking_id' => $booking->id,
                    'client_id' => $booking->client_id,
                    'days_until_renewal' => $daysUntilRenewal,
                    'amount' => $amount
                ]);

            } catch (\Exception $e) {
                $this->error("Error processing booking #{$booking->id}: {$e->getMessage()}");
                Log::error('Error in recurring reminder command', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\n✓ Recurring reminder check complete");
        $this->info("📧 Emails sent: {$emailsSent}");
        $this->info("🔔 Notifications created: {$notificationsCreated}");

        return 0;
    }

    private function extractHours($dutyType)
    {
        if (preg_match('/(\d+)/', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8;
    }
}
