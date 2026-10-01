<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\LessonBookmark;
use App\Models\LessonComment;
use Illuminate\Http\Request;

class LessonInteractionController extends Controller
{
    /**
     * Get or fetch personal note for a lesson.
     */
    public function getNote(Request $request, Lesson $lesson)
    {
        $note = LessonNote::where('user_id', $request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return response()->json([
            'note' => $note ? $note->note : '',
            'updated_at' => $note?->updated_at?->diffForHumans(),
        ]);
    }

    /**
     * Save / update note for a lesson.
     */
    public function saveNote(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $note = LessonNote::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['note' => $validated['note']]
        );

        return response()->json([
            'success' => true,
            'message' => 'নোট সফলভাবে সংরক্ষিত হয়েছে।',
            'updated_at' => $note->updated_at->diffForHumans(),
        ]);
    }

    /**
     * Save a timestamp bookmark for video.
     */
    public function saveBookmark(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'timestamp_seconds' => 'required|integer|min:0',
            'title' => 'nullable|string|max:255',
        ]);

        $bookmark = LessonBookmark::create([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => $validated['timestamp_seconds'],
            'title' => $validated['title'] ?: 'বুকমার্ক (' . gmdate("i:s", $validated['timestamp_seconds']) . ')',
        ]);

        return response()->json([
            'success' => true,
            'bookmark' => $bookmark,
            'message' => 'বুকমার্ক যুক্ত হয়েছে।',
        ]);
    }

    /**
     * Delete a bookmark.
     */
    public function deleteBookmark(Request $request, LessonBookmark $bookmark)
    {
        abort_unless($bookmark->user_id === $request->user()->id, 403);
        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'বুকমার্ক মুছে ফেলা হয়েছে।',
        ]);
    }

    /**
     * Get comments and discussions for a lesson.
     */
    public function getComments(Request $request, Lesson $lesson)
    {
        $comments = LessonComment::where('lesson_id', $lesson->id)
            ->whereNull('parent_id')
            ->with(['user:id,name,role,avatar', 'replies'])
            ->latest()
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * Post a new comment or reply.
     */
    public function postComment(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:lesson_comments,id',
        ]);

        $comment = LessonComment::create([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ]);

        $comment->load('user:id,name,role,avatar');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'মন্তব্য বা প্রশ্ন সফলভাবে পোস্ট হয়েছে।',
        ]);
    }
}
