<?php

namespace App\Http\Controllers;

use App\Models\LiveClass;
use App\Models\ScholarSessionRegistration;
use App\Services\ScholarSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScholarSessionController extends Controller
{
    public function __construct(
        protected ScholarSessionService $sessionService
    ) {}

    /**
     * Public listing of scholar sessions, webinars, and consultations.
     */
    public function index(Request $request)
    {
        $type = $request->query('type'); // live_class, webinar, consultation
        $status = $request->query('status', 'scheduled'); // scheduled, completed (recordings)

        $query = LiveClass::with(['instructor:id,name,role', 'course:id,title,slug'])
            ->when($type, fn ($q) => $q->where('session_type', $type))
            ->when($status === 'completed', fn ($q) => $q->where('status', 'completed')->whereNotNull('recording_url'))
            ->when($status !== 'completed', fn ($q) => $q->whereIn('status', ['scheduled', 'live']));

        $sessions = $query->orderBy('start_time')->paginate(12)->withQueryString();

        $user = $request->user();
        $registeredSessionIds = $user
            ? ScholarSessionRegistration::where('user_id', $user->id)->where('status', 'registered')->pluck('live_class_id')->toArray()
            : [];

        return Inertia::render('Community/ScholarSessions/Index', [
            'sessions' => $sessions,
            'registeredSessionIds' => $registeredSessionIds,
            'filters' => [
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show session detail.
     */
    public function show(Request $request, LiveClass $session)
    {
        $session->load(['instructor:id,name,role,avatar', 'course:id,title,slug']);

        $user = $request->user();
        $isRegistered = $session->isRegisteredBy($user);
        $canAccessMeeting = $session->canAccessMeeting($user);

        // Hide meeting link if user is not authorized
        $meetingUrl = $canAccessMeeting ? $session->meeting_url : null;

        $registrationsCount = $session->registrations()->count();

        return Inertia::render('Community/ScholarSessions/Show', [
            'session' => array_merge($session->toArray(), [
                'meeting_url' => $meetingUrl,
            ]),
            'isRegistered' => $isRegistered,
            'canAccessMeeting' => $canAccessMeeting,
            'registrationsCount' => $registrationsCount,
        ]);
    }

    /**
     * Register for a free session or initiate booking for a paid consultation/webinar.
     */
    public function register(Request $request, LiveClass $session)
    {
        $user = $request->user();
        abort_if(! $user, 401);
        abort_if($user->is_banned, 403, 'আপনার অ্যাকাউন্টটি স্থগিত রয়েছে।');

        if ($session->isRegisteredBy($user)) {
            return back()->with('info', 'আপনি ইতিমধ্যে এই সেশনে নিবন্ধিত আছেন।');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($session->isFree()) {
            $this->sessionService->registerFree($user, $session, $validated['notes'] ?? null);

            return back()->with('success', 'আলহামদুলিল্লাহ! আপনার নিবন্ধন সম্পন্ন হয়েছে। সেশনের সময় লিংক উন্মুক্ত হবে।');
        }

        // Paid session or consultation
        $order = $this->sessionService->createBookingOrder($user, $session, $validated['notes'] ?? null);

        // Redirect to invoice/checkout
        return redirect()->route('invoice.show', $order->id)
            ->with('success', 'সেশন বুকিং তৈরি হয়েছে। অনুগ্রহ করে পেমেন্ট সম্পন্ন করুন।');
    }

    /**
     * Student marks attendance / check-in.
     */
    public function checkIn(Request $request, LiveClass $session)
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $registration = ScholarSessionRegistration::where('live_class_id', $session->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $registration->update([
            'status' => 'attended',
            'attended_at' => now(),
        ]);

        return back()->with('success', 'উপস্থিতি সফলভাবে রেকর্ড করা হয়েছে।');
    }
}
