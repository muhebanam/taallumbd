<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveClass;
use App\Services\ScholarSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorScholarSessionController extends Controller
{
    public function __construct(
        protected ScholarSessionService $sessionService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isInstructor() || $user->isAdmin(), 403);

        $sessions = LiveClass::with(['course:id,title'])
            ->where('instructor_id', $user->id)
            ->withCount('registrations')
            ->latest('start_time')
            ->paginate(15);

        return Inertia::render('Instructor/ScholarSessions/Index', [
            'sessions' => $sessions,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isInstructor() || $user->isAdmin(), 403);

        $courses = Course::where('instructor_id', $user->id)->get(['id', 'title']);

        return Inertia::render('Instructor/ScholarSessions/Create', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isInstructor() || $user->isAdmin(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'session_type' => 'required|in:live_class,webinar,consultation',
            'course_id' => 'nullable|exists:courses,id',
            'platform' => 'required|in:zoom,jitsi,youtube_live,google_meet,other',
            'meeting_url' => 'required|url|max:500',
            'start_time' => 'required|date|after:now',
            'duration' => 'required|integer|min:15|max:300',
            'fee' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1|max:1000',
            'description' => 'nullable|string|max:2000',
        ]);

        $session = $this->sessionService->createSession($user, $validated);

        return redirect()->route('instructor.sessions.index')
            ->with('success', 'মাশাআল্লাহ! স্কলার সেশন সফলভাবে শিডিউল করা হয়েছে।');
    }

    public function update(Request $request, LiveClass $session)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $session->instructor_id === $user->id, 403);

        $validated = $request->validate([
            'status' => 'nullable|in:scheduled,live,completed,cancelled',
            'recording_url' => 'nullable|url|max:500',
            'meeting_url' => 'nullable|url|max:500',
        ]);

        $session->update($validated);

        return back()->with('success', 'সেশন তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    public function sendReminders(Request $request, LiveClass $session)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $session->instructor_id === $user->id, 403);

        $count = $this->sessionService->sendReminders($session);

        return back()->with('success', "মাশাআল্লাহ! {$count} জন নিবন্ধিত শিক্ষার্থীকে রিমাইন্ডার পাঠানো হয়েছে।");
    }
}
