<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingApprovedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $clientName;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        // Load the client relationship (which is a User model) if not already loaded
        if (!$booking->relationLoaded('client')) {
            $booking->load('client');
        }
        $this->clientName = $booking->client ? $booking->client->name : 'Client';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Booking Approved - CAS Private Care')
                    ->view('emails.booking-approved');
    }
}

