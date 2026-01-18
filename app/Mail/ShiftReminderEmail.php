<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;

class ShiftReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $contractor;
    public $hoursUntilShift;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, User $contractor, int $hoursUntilShift = 24)
    {
        $this->booking = $booking;
        $this->contractor = $contractor;
        $this->hoursUntilShift = $hoursUntilShift;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $timeLabel = $this->hoursUntilShift <= 24 ? 'Tomorrow' : "In {$this->hoursUntilShift} Hours";
        return new Envelope(
            subject: "Shift Reminder: {$timeLabel} - " . \Carbon\Carbon::parse($this->booking->service_date)->format('M j, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $client = User::find($this->booking->client_id);
        
        return new Content(
            view: 'emails.shift-reminder',
            with: [
                'booking' => $this->booking,
                'contractor' => $this->contractor,
                'client' => $client,
                'hoursUntilShift' => $this->hoursUntilShift,
            ],
        );
    }
}
