<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailCampaign;
use App\Models\User;

class MarketingCampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $user;
    public $trackingId;
    public $customContent;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, User $user, string $trackingId, ?string $customContent = null)
    {
        $this->campaign = $campaign;
        $this->user = $user;
        $this->trackingId = $trackingId;
        $this->customContent = $customContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replacePlaceholders($this->campaign->subject),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing-campaign',
            with: [
                'campaign' => $this->campaign,
                'user' => $this->user,
                'content' => $this->replacePlaceholders($this->customContent ?? $this->campaign->content),
                'trackingId' => $this->trackingId,
                'trackingPixelUrl' => url("/email/track/open/{$this->trackingId}"),
                'clickTrackUrl' => url("/email/track/click/{$this->trackingId}"),
            ],
        );
    }

    /**
     * Replace placeholders in content
     */
    protected function replacePlaceholders($content)
    {
        $replacements = [
            '{{name}}' => $this->user->name ?? 'Valued Customer',
            '{{first_name}}' => explode(' ', $this->user->name ?? '')[0] ?? 'There',
            '{{email}}' => $this->user->email ?? '',
            '{{date}}' => now()->format('F j, Y'),
            '{{year}}' => date('Y'),
            '{{company}}' => 'CAS Private Care',
            '{{login_url}}' => url('/login'),
            '{{booking_url}}' => url('/book'),
            '{{unsubscribe_url}}' => url("/email/unsubscribe/{$this->trackingId}"),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
