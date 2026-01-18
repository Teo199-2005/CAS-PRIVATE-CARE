<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;

class AssignmentNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $contractor;
    public $assignmentDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, User $contractor, array $assignmentDetails = [])
    {
        $this->booking = $booking;
        $this->contractor = $contractor;
        $this->assignmentDetails = $assignmentDetails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Assignment: ' . $this->booking->service_type . ' - ' . \Carbon\Carbon::parse($this->booking->service_date)->format('M j, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Get client info
        $client = User::find($this->booking->client_id);
        
        return new Content(
            view: 'emails.assignment-notification',
            with: [
                'booking' => $this->booking,
                'contractor' => $this->contractor,
                'client' => $client,
                'assignmentDetails' => $this->assignmentDetails,
                'hourlyRate' => $this->assignmentDetails['hourly_rate'] ?? $this->booking->hourly_rate ?? 0,
                'estimatedEarnings' => $this->calculateEstimatedEarnings(),
            ],
        );
    }

    /**
     * Calculate estimated earnings
     */
    protected function calculateEstimatedEarnings()
    {
        $rate = $this->assignmentDetails['hourly_rate'] ?? $this->booking->hourly_rate ?? 0;
        $hours = $this->booking->hours_per_day ?? 8;
        $days = $this->booking->duration_days ?? 1;
        
        return $rate * $hours * $days;
    }
}
