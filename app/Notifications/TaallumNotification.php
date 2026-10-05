<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use App\Notifications\Channels\NullSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaallumNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?string $url = null,
        public array $context = [],
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        $channels = [];

        if ($notifiable->notificationChannelEnabled($this->type, 'database')) {
            $channels[] = 'database';
        }
        if ($notifiable->notificationChannelEnabled($this->type, 'mail') && $notifiable->email) {
            $channels[] = 'mail';
        }
        if ($notifiable->notificationChannelEnabled($this->type, 'fcm')) {
            $channels[] = FcmChannel::class;
        }
        if ($notifiable->notificationChannelEnabled($this->type, 'sms') && $notifiable->phone) {
            $channels[] = NullSmsChannel::class;
        }

        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'context' => $this->context,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title.' | Taallum BD')
            ->markdown('mail.notifications.taallum', [
                'user' => $notifiable,
                'title' => $this->title,
                'message' => $this->message,
                'url' => $this->url,
                'context' => $this->context,
            ]);
    }
}
