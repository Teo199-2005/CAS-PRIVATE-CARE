<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\User;
use App\Mail\MarketingCampaignEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminEmailController extends Controller
{
    /**
     * Get all email campaigns with stats
     */
    public function getCampaigns(Request $request)
    {
        $campaigns = EmailCampaign::with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Get a single campaign by ID
     */
    public function getCampaign($id)
    {
        $campaign = EmailCampaign::with(['createdBy', 'emailLogs'])->find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
            'stats' => [
                'sent' => $campaign->emails_sent,
                'opened' => $campaign->emails_opened,
                'clicked' => $campaign->emails_clicked,
                'open_rate' => $campaign->emails_sent > 0 ? round(($campaign->emails_opened / $campaign->emails_sent) * 100, 2) : 0,
                'click_rate' => $campaign->emails_sent > 0 ? round(($campaign->emails_clicked / $campaign->emails_sent) * 100, 2) : 0
            ]
        ]);
    }

    /**
     * Get clients with filtering options
     */
    public function getClients(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $search = $request->input('search', '');

        $query = User::where('role', 'client');

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply filters
        switch ($filter) {
            case 'never_booked':
                $query->whereDoesntHave('bookings');
                break;

            case 'inactive_30_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                });
                break;

            case 'inactive_60_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(60));
                });
                break;

            case 'inactive_90_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(90));
                });
                break;

            case 'active_clients':
                $query->whereHas('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                });
                break;

            case 'repeat_clients':
                $query->has('bookings', '>=', 2);
                break;

            case 'vip_clients':
                $query->has('bookings', '>=', 5);
                break;

            case 'all':
            default:
                // No additional filter
                break;
        }

        $clients = $query->select('id', 'name', 'email', 'created_at')
            ->withCount('bookings')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'clients' => $clients,
            'total' => $clients->count()
        ]);
    }

    /**
     * Get filter options for client selection
     */
    public function getFilterOptions()
    {
        $filters = [
            ['value' => 'all', 'label' => 'All Clients', 'description' => 'Send to all registered clients'],
            ['value' => 'never_booked', 'label' => 'Never Booked', 'description' => 'Clients who registered but never made a booking'],
            ['value' => 'inactive_30_days', 'label' => 'Inactive 30+ Days', 'description' => 'No bookings in the last 30 days'],
            ['value' => 'inactive_60_days', 'label' => 'Inactive 60+ Days', 'description' => 'No bookings in the last 60 days'],
            ['value' => 'inactive_90_days', 'label' => 'Inactive 90+ Days', 'description' => 'No bookings in the last 90 days'],
            ['value' => 'active_clients', 'label' => 'Active Clients', 'description' => 'Made a booking in the last 30 days'],
            ['value' => 'repeat_clients', 'label' => 'Repeat Clients', 'description' => 'Made 2 or more bookings'],
            ['value' => 'vip_clients', 'label' => 'VIP Clients', 'description' => 'Made 5 or more bookings'],
        ];

        return response()->json([
            'success' => true,
            'filters' => $filters
        ]);
    }

    /**
     * Create a new email campaign
     */
    public function createCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|string',
            'type' => 'required|in:marketing,promotional,newsletter,announcement',
        ]);

        $campaign = EmailCampaign::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'preview_text' => $request->preview_text ?? null,
            'type' => $request->type,
            'target_audience' => $request->target_audience,
            'filters' => $request->filters ?? [],
            'scheduled_at' => $request->scheduled_at ?? null,
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully',
            'campaign' => $campaign
        ]);
    }

    /**
     * Update an existing campaign
     */
    public function updateCampaign(Request $request, $id)
    {
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json(['success' => false, 'message' => 'Cannot edit a sent campaign'], 400);
        }

        $campaign->update($request->only([
            'name', 'subject', 'content', 'preview_text', 
            'type', 'target_audience', 'filters', 'scheduled_at'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign
        ]);
    }

    /**
     * Delete a campaign
     */
    public function deleteCampaign($id)
    {
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        if ($campaign->status === 'sending') {
            return response()->json(['success' => false, 'message' => 'Cannot delete a campaign that is currently sending'], 400);
        }

        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully'
        ]);
    }

    /**
     * Send a campaign to selected recipients
     */
    public function sendCampaign(Request $request, $id)
    {
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json(['success' => false, 'message' => 'Campaign has already been sent'], 400);
        }

        // Get recipients based on filter or specific client IDs
        $clientIds = $request->input('client_ids', []);
        
        if (empty($clientIds)) {
            // Use campaign's target_audience filter
            $clients = $this->getClientsByFilter($campaign->target_audience);
        } else {
            $clients = User::whereIn('id', $clientIds)->where('role', 'client')->get();
        }

        if ($clients->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No recipients found'], 400);
        }

        // Update campaign status
        $campaign->update(['status' => 'sending']);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($clients as $client) {
            try {
                // Create email log
                $emailLog = EmailLog::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $client->id,
                    'subject' => $campaign->subject,
                    'email_type' => 'marketing',
                    'status' => 'pending'
                ]);

                // Process content with personalization
                $processedContent = $this->processContent($campaign->content, $client);

                // Send email - constructor signature: EmailCampaign, User, trackingId, customContent
                Mail::to($client->email)->send(new MarketingCampaignEmail(
                    $campaign,
                    $client,
                    $emailLog->tracking_id,
                    $processedContent
                ));

                $emailLog->update(['status' => 'sent', 'sent_at' => now()]);
                $sentCount++;

            } catch (\Exception $e) {
                if (isset($emailLog)) {
                    $emailLog->update([
                        'status' => 'failed',
                        'metadata' => ['error' => $e->getMessage()]
                    ]);
                }
                $failedCount++;
                \Log::error("Failed to send campaign email to {$client->email}: " . $e->getMessage());
            }
        }

        // Update campaign stats
        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'emails_sent' => $sentCount,
            'emails_failed' => $failedCount,
            'total_recipients' => $clients->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Campaign sent successfully. {$sentCount} emails sent, {$failedCount} failed.",
            'stats' => [
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => $clients->count()
            ]
        ]);
    }

    /**
     * Preview a campaign email
     */
    public function previewCampaign(Request $request)
    {
        $content = $request->input('content', '');
        $subject = $request->input('subject', 'Preview Email');

        // Use current user for preview
        $user = auth()->user();
        $processedContent = $this->processContent($content, $user);

        return response()->json([
            'success' => true,
            'subject' => $subject,
            'content' => $processedContent,
            'preview_user' => [
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * Send a test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string'
        ]);

        try {
            $testUser = (object) [
                'name' => 'Test User',
                'email' => $request->email
            ];

            $processedContent = $this->processContent($request->content, $testUser);

            // Create a temporary campaign object
            $campaign = new EmailCampaign([
                'subject' => $request->subject,
                'content' => $processedContent,
                'preview_text' => $request->preview_text ?? ''
            ]);

            Mail::to($request->email)->send(new MarketingCampaignEmail(
                $campaign,
                $testUser,
                $processedContent,
                'test-' . uniqid()
            ));

            return response()->json([
                'success' => true,
                'message' => "Test email sent to {$request->email}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track email open
     */
    public function trackOpen($trackingToken)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingToken)->first();

        if ($emailLog) {
            $emailLog->markAsOpened();

            // Update campaign open count
            $campaign = $emailLog->campaign;
            if ($campaign) {
                $campaign->increment('emails_opened');
            }
        }

        // Return a 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel, 200)->header('Content-Type', 'image/gif');
    }

    /**
     * Track email click
     */
    public function trackClick(Request $request, $trackingToken)
    {
        $url = $request->input('url', url('/'));
        
        $emailLog = EmailLog::where('tracking_id', $trackingToken)->first();

        if ($emailLog) {
            $emailLog->markAsClicked($url);

            // Update campaign click count
            $campaign = $emailLog->campaign;
            if ($campaign) {
                $campaign->increment('emails_clicked');
            }
        }

        return redirect($url);
    }

    /**
     * Get campaign analytics
     */
    public function getCampaignAnalytics($id)
    {
        $campaign = EmailCampaign::with(['emailLogs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        $logs = $campaign->emailLogs;

        $analytics = [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'sent_at' => $campaign->sent_at,
            'totals' => [
                'sent' => $logs->where('status', 'sent')->count(),
                'opened' => $logs->whereNotNull('opened_at')->count(),
                'clicked' => $logs->whereNotNull('clicked_at')->count(),
                'failed' => $logs->where('status', 'failed')->count(),
            ],
            'rates' => [
                'open_rate' => $campaign->open_rate,
                'click_rate' => $campaign->click_rate,
            ],
            'recent_opens' => $logs->whereNotNull('opened_at')
                ->sortByDesc('opened_at')
                ->take(10)
                ->map(function ($log) {
                    return [
                        'email' => $log->email,
                        'opened_at' => $log->opened_at
                    ];
                })->values(),
            'recent_clicks' => $logs->whereNotNull('clicked_at')
                ->sortByDesc('clicked_at')
                ->take(10)
                ->map(function ($log) {
                    return [
                        'email' => $log->email,
                        'clicked_at' => $log->clicked_at,
                        'clicked_links' => $log->clicked_links
                    ];
                })->values(),
        ];

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * Get overall email marketing dashboard stats
     */
    public function getDashboardStats()
    {
        $totalCampaigns = EmailCampaign::count();
        $totalSent = EmailLog::where('status', 'sent')->count();
        $totalOpened = EmailLog::whereNotNull('opened_at')->count();
        $totalClicked = EmailLog::whereNotNull('clicked_at')->count();

        $recentCampaigns = EmailCampaign::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'name', 'status', 'emails_sent', 'emails_opened', 'emails_clicked', 'sent_at']);

        return response()->json([
            'success' => true,
            'stats' => [
                'total_campaigns' => $totalCampaigns,
                'total_emails_sent' => $totalSent,
                'total_opens' => $totalOpened,
                'total_clicks' => $totalClicked,
                'average_open_rate' => $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0,
                'average_click_rate' => $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0,
            ],
            'recent_campaigns' => $recentCampaigns
        ]);
    }

    /**
     * Process content with personalization tokens
     */
    private function processContent($content, $user)
    {
        $userName = $user->name ?? 'Valued Client';
        $firstName = explode(' ', $userName)[0];
        
        // Support both technical tokens and friendly display names
        $tokens = [
            // Technical tokens (legacy support)
            '{{name}}' => $userName,
            '{{first_name}}' => $firstName,
            '{{email}}' => $user->email ?? '',
            '{{current_year}}' => date('Y'),
            '{{company_name}}' => 'CAS Private Care',
            
            // Friendly display names (from updated UI)
            '[Client Name]' => $userName,
            '[First Name]' => $firstName,
            '[Client Email]' => $user->email ?? '',
            '[CAS Private Care]' => 'CAS Private Care',
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }

    /**
     * Get clients by filter name
     */
    private function getClientsByFilter($filter)
    {
        $query = User::where('role', 'client');

        switch ($filter) {
            case 'never_booked':
                $query->whereDoesntHave('bookings');
                break;
            case 'inactive_30_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                });
                break;
            case 'inactive_60_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(60));
                });
                break;
            case 'inactive_90_days':
                $query->whereDoesntHave('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(90));
                });
                break;
            case 'active_clients':
                $query->whereHas('bookings', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                });
                break;
            case 'repeat_clients':
                $query->has('bookings', '>=', 2);
                break;
            case 'vip_clients':
                $query->has('bookings', '>=', 5);
                break;
        }

        return $query->get();
    }
}
