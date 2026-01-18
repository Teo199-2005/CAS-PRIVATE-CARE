<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $payoutDate;
    public $periodStart;
    public $periodEnd;
    public $hoursWorked;
    public $transactionId;
    public $payoutMethod;
    public $estimatedArrival;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        float $amount,
        string $payoutDate,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?float $hoursWorked = null,
        ?string $transactionId = null,
        ?string $payoutMethod = 'Direct Deposit',
        ?string $estimatedArrival = null
    ) {
        $this->user = $user;
        $this->amount = $amount;
        $this->payoutDate = $payoutDate;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        $this->hoursWorked = $hoursWorked;
        $this->transactionId = $transactionId;
        $this->payoutMethod = $payoutMethod;
        $this->estimatedArrival = $estimatedArrival ?? $this->calculateEstimatedArrival($payoutDate);
    }

    /**
     * Calculate estimated arrival date (typically 2-3 business days)
     */
    protected function calculateEstimatedArrival(string $payoutDate): string
    {
        $date = \Carbon\Carbon::parse($payoutDate);
        // Add 2-3 business days
        $businessDays = 0;
        while ($businessDays < 3) {
            $date->addDay();
            if (!$date->isWeekend()) {
                $businessDays++;
            }
        }
        return $date->format('F j, Y');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Sent - $' . number_format($this->amount, 2) . ' | CAS Private Care')
                    ->view('emails.payout-confirmation');
    }
}
