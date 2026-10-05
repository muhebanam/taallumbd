<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20)->through(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'নোটিফিকেশন',
                'message' => $notification->data['message'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'type' => $notification->data['type'] ?? null,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at?->diffForHumans(),
            ]),
        ]);
    }

    public function read(Request $request, string $notification)
    {
        $request->user()->notifications()->whereKey($notification)->update(['read_at' => now()]);

        return back();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    public function preferences(Request $request)
    {
        $types = collect((new \ReflectionClass(NotificationDispatcher::class))->getConstants())
            ->mapWithKeys(fn ($value, $key) => [$value => str_replace('_', ' ', ucfirst($value))]);

        $preferences = $request->user()->notificationPreferences()
            ->get(['notification_type', 'channel', 'enabled'])
            ->groupBy('notification_type')
            ->map(fn ($rows) => $rows->pluck('enabled', 'channel'));

        return Inertia::render('Profile/Notifications', [
            'types' => $types,
            'channels' => ['database' => 'ওয়েব নোটিফিকেশন', 'mail' => 'ইমেইল'],
            'preferences' => $preferences,
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*' => ['array'],
            'preferences.*.*' => ['boolean'],
        ]);

        foreach ($data['preferences'] as $type => $channels) {
            foreach ($channels as $channel => $enabled) {
                NotificationPreference::updateOrCreate(
                    ['user_id' => $request->user()->id, 'notification_type' => $type, 'channel' => $channel],
                    ['enabled' => $enabled],
                );
            }
        }

        return back()->with('success', 'নোটিফিকেশন পছন্দ সংরক্ষণ করা হয়েছে।');
    }
}
