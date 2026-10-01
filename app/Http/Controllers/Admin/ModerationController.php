<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Central admin moderation: approve/reject member-submitted courses, articles, fatawa. */
class ModerationController extends Controller
{
    public function courses(Request $request)
    {
        return Inertia::render('Admin/Courses', [
            'courses' => Course::with('instructor:id,name')->withCount('enrollments')
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function updateCourseStatus(Request $request, Course $course)
    {
        $course->update($request->validate(['status' => 'required|in:draft,pending,published,rejected']));
        return back()->with('success', 'কোর্স স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function articles(Request $request)
    {
        return Inertia::render('Admin/Articles', [
            'articles' => Article::with(['author:id,name', 'category:id,name'])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function updateArticleStatus(Request $request, Article $article)
    {
        $data = $request->validate(['status' => 'required|in:draft,pending,published,rejected']);
        $article->update([...$data, 'published_at' => $data['status'] === 'published' ? now() : $article->published_at]);
        return back()->with('success', 'প্রবন্ধ স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function fatawa(Request $request)
    {
        return Inertia::render('Admin/Fatawa', [
            'fatawa' => Fatwa::with([
                'category:id,name',
                'mufti:id,name',
                'teacher.user:id,name',
                'assignedScholar.user:id,name',
                'relatedCourse:id,title'
            ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
            'scholars' => \App\Models\Teacher::with('user:id,name')->get(['id', 'user_id', 'title_prefix', 'designation']),
            'courses' => \App\Models\Course::where('status', 'published')->get(['id', 'title']),
        ]);
    }

    /** Only admin or instructor (scholar) may answer — enforced in routes middleware too. */
    public function answerFatwa(Request $request, Fatwa $fatwa)
    {
        $data = $request->validate([
            'answer_body' => 'nullable|string',
            'status' => 'required|in:pending,answered,published,rejected',
            'assigned_scholar_id' => 'nullable|exists:teachers,id',
            'related_course_id' => 'nullable|exists:courses,id',
            'references' => 'nullable|string',
        ]);

        $updates = [
            'status' => $data['status'],
            'assigned_scholar_id' => $data['assigned_scholar_id'] ?? $fatwa->assigned_scholar_id,
            'related_course_id' => $data['related_course_id'] ?? $fatwa->related_course_id,
            'references' => $data['references'] ?? $fatwa->references,
        ];

        if (!empty($data['answer_body'])) {
            $updates['answer_body'] = $data['answer_body'];
            $updates['answered_by'] = $request->user()->id;
            $updates['answered_at'] = now();
        }

        if ($data['status'] === 'published') {
            $updates['published_at'] = $fatwa->published_at ?? now();
        }

        $fatwa->update($updates);

        return back()->with('success', 'ফাতওয়া ও গবেষণা ডাটা সফলভাবে সংরক্ষিত হয়েছে।');
    }

    public function orders()
    {
        return Inertia::render('Admin/Orders', [
            'orders' => Order::with(['user:id,name,email', 'course:id,title', 'payments'])->latest()->paginate(20),
        ]);
    }

    public function enrollments()
    {
        return Inertia::render('Admin/Enrollments', [
            'enrollments' => Enrollment::with(['user:id,name,email', 'course:id,title'])->latest()->paginate(20),
        ]);
    }

    public function contactMessages()
    {
        return Inertia::render('Admin/ContactMessages', [
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }

    public function markMessageRead(ContactMessage $message)
    {
        $message->update(['status' => 'read']);
        return back();
    }
}
