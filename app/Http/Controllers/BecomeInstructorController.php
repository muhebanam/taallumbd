<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\InstructorApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BecomeInstructorController extends Controller
{
    public function index()
    {
        // 1. Calculate live statistics
        $stats = [
            'total_students' => bn_number(User::where('role', 'student')->count()) . ' জন',
            'active_students' => bn_number(max(20, User::has('enrollments')->count())) . ' জন', // Safe fallback if no activity exists
            'published_lessons' => bn_number(Lesson::count()) . ' টি',
            'active_courses' => bn_number(Course::where('status', 'published')->count()) . ' টি',
            'total_instructors' => bn_number(User::where('role', 'instructor')->count()) . ' জন',
            'support_label' => "২৪/৭",
        ];

        // 2. Load existing application if authenticated
        $existingApplication = null;
        if (auth()->check()) {
            $existingApplication = InstructorApplication::where('user_id', auth()->id())
                ->latest()
                ->first();
        }

        return Inertia::render('BecomeInstructor', [
            'stats' => $stats,
            'existingApplication' => $existingApplication,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'expertise' => 'required|string|max:255',
            'experience' => 'required|string|max:5000',
            'cv_link' => 'nullable|url|max:255',
        ]);

        // 1. Prevent duplicate pending applications
        $pending = InstructorApplication::where('email', $request->email)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return back()->with('error', 'এই ইমেইল দিয়ে ইতিমধ্যে একটি আবেদন জমা আছে। আমাদের টিম যাচাই করে আপনার সাথে যোগাযোগ করবে, ইনশাআল্লাহ।');
        }

        // 2. Prevent submitting if already approved
        $approved = InstructorApplication::where('email', $request->email)
            ->where('status', 'approved')
            ->first();

        if ($approved) {
            return back()->with('error', 'এই ইমেইলের আবেদন ইতিমধ্যে অনুমোদিত হয়েছে।');
        }

        // 3. Resolve user_id association
        $userId = null;
        if (auth()->check()) {
            $userId = auth()->id();
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        // 4. Create application
        InstructorApplication::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'expertise' => $data['expertise'],
            'experience' => $data['experience'],
            'cv_link' => $data['cv_link'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'আপনার আবেদনটি সফলভাবে জমা হয়েছে। আমাদের টিম যাচাই করে আপনার সাথে যোগাযোগ করবে, ইনশাআল্লাহ।');
    }
}
