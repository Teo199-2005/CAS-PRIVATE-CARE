<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', 1);
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'read' => $notification->read,
                    'time' => $this->formatTime($notification->created_at),
                    'icon' => $this->getIcon($notification->type),
                    'color' => $this->getColor($notification->type),
                    'typeColor' => $this->getTypeColor($notification->type)
                ];
            });

        $unreadCount = Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', 1);
        Notification::where('user_id', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function delete($id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function deleteAll(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', 1);
        Notification::where('user_id', $userId)->delete();

        return response()->json(['success' => true]);
    }

    private function formatTime($timestamp)
    {
        $diff = (int) abs(now()->diffInMinutes($timestamp));
        
        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' ' . ($diff == 1 ? 'minute' : 'minutes') . ' ago';
        } elseif ($diff < 1440) {
            $hours = (int) floor($diff / 60);
            return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
        } elseif ($diff < 10080) {
            $days = (int) floor($diff / 1440);
            return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
        } else {
            $weeks = (int) floor($diff / 10080);
            return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') . ' ago';
        }
    }

    private function getIcon($type)
    {
        $icons = [
            'Appointments' => 'mdi-calendar',
            'Payments' => 'mdi-currency-usd',
            'Clients' => 'mdi-account-multiple',
            'Caregivers' => 'mdi-account-heart',
            'System' => 'mdi-information'
        ];
        return $icons[$type] ?? 'mdi-bell';
    }

    private function getColor($type)
    {
        $colors = [
            'Appointments' => 'warning',
            'Payments' => 'success',
            'Clients' => 'info',
            'Caregivers' => 'info',
            'System' => 'primary'
        ];
        return $colors[$type] ?? 'grey';
    }

    private function getTypeColor($type)
    {
        $colors = [
            'Appointments' => 'warning',
            'Payments' => 'success',
            'Clients' => 'primary',
            'Caregivers' => 'deep-purple',
            'System' => 'info'
        ];
        return $colors[$type] ?? 'grey';
    }
}
