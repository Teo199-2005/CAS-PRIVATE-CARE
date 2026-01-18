<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;

class BookingCancellationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $contractor;
    public $cancellationReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, User $contractor, ?string $cancellationReason = null)
    {
        $this->booking = $booking;
        $this->contractor = $contractor;
        $this->cancellationReason = $cancellationReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Cancelled - ' . \Carbon\Carbon::parse($this->booking->service_date)->format('M j, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-cancellation',
            with: [
                'booking' => $this->booking,
                'contractor' => $this->contractor,
                'reason' => $this->cancellationReason,
            ],
        );
    }
}
