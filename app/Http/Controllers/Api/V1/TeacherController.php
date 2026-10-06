<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeacherResource;
use App\Models\Teacher;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * List active scholars and teachers with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::active()->with(['user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('headline', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        if ($request->has('consultation_enabled')) {
            $query->where('consultation_enabled', $request->boolean('consultation_enabled'));
        }

        $perPage = min(50, max(5, (int) $request->input('per_page', 15)));
        $teachers = $query->orderBy('sort_order', 'asc')->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TeacherResource::collection($teachers),
            'meta' => [
                'current_page' => $teachers->currentPage(),
                'last_page' => $teachers->lastPage(),
                'per_page' => $teachers->perPage(),
                'total' => $teachers->total(),
            ],
        ]);
    }

    /**
     * Get detailed scholar profile.
     */
    public function show(Request $request, Teacher $teacher): JsonResponse
    {
        if ($teacher->status !== 'active' && (! $request->user() || ! $request->user()->isAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'শিক্ষকের প্রোফাইলটি সক্রিয় নয়।',
            ], 404);
        }

        $teacher->load([
            'courses' => fn ($q) => $q->where('status', 'published')->withCount('lessons'),
            'followers',
        ]);

        if ($request->user()) {
            app(EventTracker::class)->trackScholarProfileViewed($request->user(), $teacher->id);
        }

        return response()->json([
            'success' => true,
            'teacher' => new TeacherResource($teacher),
        ]);
    }

    /**
     * Toggle follow/unfollow scholar.
     */
    public function toggleFollow(Request $request, Teacher $teacher): JsonResponse
    {
        $user = $request->user();

        if ($user->id === $teacher->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'আপনি নিজেকে অনুসরণ করতে পারবেন না।',
            ], 400);
        }

        $isFollowing = $teacher->followers()->where('user_id', $user->id)->exists();

        if ($isFollowing) {
            $teacher->followers()->detach($user->id);
            $message = 'শিক্ষককে আনফলো করা হয়েছে।';
            $following = false;
        } else {
            $teacher->followers()->attach($user->id);
            $message = 'শিক্ষককে সফলভাবে অনুসরণ করছেন।';
            $following = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_following' => $following,
            'followers_count' => $teacher->followers()->count(),
        ]);
    }
}
