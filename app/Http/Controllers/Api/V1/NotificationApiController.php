<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * List user notifications with unread count.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = min(50, max(5, (int) $request->input('per_page', 20)));
        $notifications = $user->notifications()->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'নোটিফিকেশন পাওয়া যায়নি।',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'সকল নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।',
            'unread_count' => 0,
        ]);
    }

    /**
     * Register FCM / APNs mobile push device token.
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'required|string|in:android,ios,web',
        ]);

        $user = $request->user();

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'মোবাইল ডিভাইস টোকেন সফলভাবে নিবন্ধিত হয়েছে।',
        ]);
    }
}
