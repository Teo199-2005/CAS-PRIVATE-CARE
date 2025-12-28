<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $requiresApproval;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->requiresApproval = in_array($user->user_type, ['caregiver', 'marketing', 'training_center']);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to CAS Private Care!')
                    ->view('emails.welcome');
    }
}

