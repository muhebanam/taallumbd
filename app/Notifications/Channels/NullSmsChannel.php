<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class NullSmsChannel implements SmsChannel
{
    public function send($notifiable, Notification $notification): void
    {
        // Provider integration is intentionally deferred.
    }
}
