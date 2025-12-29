<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnnouncementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $message, string $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Announcement: ' . $this->title . ' - CAS Private Care';
        
        return $this->subject($subject)
                    ->view('emails.announcement');
    }
}


