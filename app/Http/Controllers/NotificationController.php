<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get all unread notifications for the authenticated user.
     * Authorization: User can only see their own notifications
     */
    public function unread(Request $request): JsonResponse
    {
        // Only return notifications for the authenticated user
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'count' => count($notifications),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get unread notification count.
     * Authorization: User can only see their own count
     */
    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     * Authorization: User can only mark their own notifications as read
     */
    public function markAsRead(Request $request, $notificationId): JsonResponse
    {
        $notification = $request->user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     * Authorization: User can only mark their own notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'count' => 0,
        ]);
    }

    /**
     * Get all notifications (read and unread).
     * Authorization: User can only see their own notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * View a specific notification.
     * Authorization: User can only view their own notifications
     */
    public function show(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->find($notificationId);

        if (!$notification) {
            abort(404);
        }

        $notification->markAsRead();

        return view('notifications.show', compact('notification'));
    }
}
