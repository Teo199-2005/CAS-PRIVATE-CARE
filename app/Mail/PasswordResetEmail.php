<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $email, string $token, string $userName = 'User')
    {
        $this->userName = $userName;
        $this->resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($email));
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Your Password - CAS Private Care')
                    ->view('emails.password-reset');
    }
}


