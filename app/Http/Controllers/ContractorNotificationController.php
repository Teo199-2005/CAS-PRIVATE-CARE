<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ContractorNotificationService;

class ContractorNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(ContractorNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get contractor's notification settings
     */
    public function getSettings(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['caregiver', 'housekeeper'])) {
            return response()->json(['success' => false, 'message' => 'Only contractors can access this'], 403);
        }

        $settings = $this->notificationService->getNotificationSettings($user->id);

        return response()->json([
            'success' => true,
            'settings' => [
                'assignment_notifications' => (bool) $settings->assignment_notifications,
                'shift_reminders' => (bool) $settings->shift_reminders,
                'cancellation_alerts' => (bool) $settings->cancellation_alerts,
                'weekly_earnings' => (bool) $settings->weekly_earnings,
                'payout_notifications' => (bool) $settings->payout_notifications,
            ]
        ]);
    }

    /**
     * Update contractor's notification settings
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['caregiver', 'housekeeper'])) {
            return response()->json(['success' => false, 'message' => 'Only contractors can access this'], 403);
        }

        $validated = $request->validate([
            'assignment_notifications' => 'sometimes|boolean',
            'shift_reminders' => 'sometimes|boolean',
            'cancellation_alerts' => 'sometimes|boolean',
            'weekly_earnings' => 'sometimes|boolean',
            'payout_notifications' => 'sometimes|boolean',
        ]);

        $settings = $this->notificationService->updateNotificationSettings($user->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'settings' => $settings
        ]);
    }
}
