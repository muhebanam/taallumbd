<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorApplicationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/InstructorApplications', [
            'applications' => InstructorApplication::with(['user:id,name,email,role', 'reviewer:id,name'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function approve(Request $request, InstructorApplication $application)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        if ($application->user_id) {
            $user = User::find($application->user_id);
            if ($user) {
                $user->update(['role' => 'instructor']);
            }

            return back()->with('success', 'আবেদনটি অনুমোদিত হয়েছে এবং ব্যবহারকারীকে শিক্ষক হিসেবে উন্নীত করা হয়েছে।');
        }

        return back()->with('success', 'আবেদনটি অনুমোদিত হয়েছে। তবে এই আবেদনকারীর কোনো অ্যাকাউন্ট নেই, অনুগ্রহ করে ম্যানুয়ালি যোগাযোগ করুন।');
    }

    public function reject(Request $request, InstructorApplication $application)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'আবেদনটি প্রত্যাখ্যান করা হয়েছে।');
    }
}
