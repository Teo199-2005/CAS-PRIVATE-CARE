<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutPendingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $hoursWorked;
    public $periodStart;
    public $periodEnd;
    public $scheduledDate;
    public $pendingCount;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        float $amount,
        ?float $hoursWorked = null,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?string $scheduledDate = null,
        ?int $pendingCount = 1
    ) {
        $this->user = $user;
        $this->amount = $amount;
        $this->hoursWorked = $hoursWorked;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        $this->scheduledDate = $scheduledDate;
        $this->pendingCount = $pendingCount;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Upcoming Payout - $' . number_format($this->amount, 2) . ' | CAS Private Care')
                    ->view('emails.payout-pending');
    }
}
