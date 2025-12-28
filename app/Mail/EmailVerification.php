<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->verificationUrl = url('/verify-email/' . $token);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verify Your Email Address - CAS Private Care')
                    ->view('emails.verification');
    }
}

