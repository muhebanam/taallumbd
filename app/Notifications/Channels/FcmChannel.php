<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

/**
 * Phase 17 placeholder. Device token registration and provider delivery can be
 * added here without changing notification classes or recipient contracts.
 */
class FcmChannel
{
    public function send($notifiable, Notification $notification): void
    {
        // Intentionally empty until an FCM provider is configured.
    }
}
