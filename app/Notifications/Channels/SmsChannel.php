<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

interface SmsChannel
{
    public function send($notifiable, Notification $notification): void;
}
