<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutFailedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $reason;
    public $actionRequired;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        float $amount,
        ?string $reason = null,
        ?string $actionRequired = null
    ) {
        $this->user = $user;
        $this->amount = $amount;
        $this->reason = $reason ?? 'We encountered an issue processing your payment.';
        $this->actionRequired = $actionRequired ?? 'Please verify your bank account details are correct and try again.';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Issue - Action Required | CAS Private Care')
                    ->view('emails.payout-failed');
    }
}
