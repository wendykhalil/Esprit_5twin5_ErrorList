<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class NotificationHelper
{
    /**
     * Get the count of unread notifications for the authenticated user.
     */
    public static function unreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Auth::user()->unreadNotifications->count();
    }

    /**
     * Get all unread notifications for the authenticated user.
     */
    public static function unreadNotifications()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()->unreadNotifications()->latest()->get();
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead($notificationId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $notification = Auth::user()->notifications()->find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        Auth::user()->unreadNotifications->markAsRead();
        return true;
    }
}
