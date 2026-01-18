<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WeeklyEarningsSummaryEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contractor;
    public $summaryData;

    /**
     * Create a new message instance.
     */
    public function __construct(User $contractor, array $summaryData)
    {
        $this->contractor = $contractor;
        $this->summaryData = $summaryData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $weekOf = $this->summaryData['week_start'] ?? now()->startOfWeek()->format('M j');
        return new Envelope(
            subject: "Your Weekly Earnings Summary - Week of {$weekOf}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-earnings-summary',
            with: [
                'contractor' => $this->contractor,
                'summary' => $this->summaryData,
            ],
        );
    }
}
