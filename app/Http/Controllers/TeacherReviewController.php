<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Review;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherReviewController extends Controller
{
    public function store(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:5|max:1000',
            'course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('instructor_id', $teacher->user_id)->where('status', 'published')),
            ],
        ]);

        $user = auth()->user();
        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : null;

        if ($courseId) {
            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->whereIn('status', ['active', 'completed'])
                ->exists();

            if (!$isEnrolled) {
                return back()->with('error', 'নির্দিষ্ট কোর্সের রিভিউ দেওয়ার জন্য আপনাকে কোর্সটিতে ভর্তি থাকতে হবে।');
            }

            $exists = Review::where('teacher_id', $teacher->id)
                ->where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->exists();

            if ($exists) {
                return back()->with('error', 'আপনি ইতিমধ্যে এই কোর্সের জন্য রিভিউ প্রদান করেছেন।');
            }
        } else {
            // General teacher review
            $exists = Review::where('teacher_id', $teacher->id)
                ->where('user_id', $user->id)
                ->whereNull('course_id')
                ->exists();

            if ($exists) {
                return back()->with('error', 'আপনি ইতিমধ্যে এই শিক্ষকের জন্য সাধারণ মূল্যায়ন প্রদান করেছেন।');
            }
        }

        Review::create([
            'teacher_id' => $teacher->id,
            'user_id' => $user->id,
            'course_id' => $courseId,
            'rating' => $data['rating'],
            'comment' => $data['review'] ?? null,
            'status' => 'pending', // Pending admin moderation
        ]);

        return back()->with('success', 'আপনার রিভিউটি সফলভাবে জমা হয়েছে। অ্যাডমিন অনুমোদনের পর এটি প্রকাশিত হবে।');
    }
}
