<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminTeacherReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::whereNotNull('teacher_id')
            ->with(['teacher', 'user', 'course'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/TeacherReviews/Index', [
            'reviews' => $reviews
        ]);
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);
        return back()->with('success', 'রিভিউটি সফলভাবে এপ্রুভ করা হয়েছে।');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);
        return back()->with('success', 'রিভিউটি বাতিল করা হয়েছে।');
    }
}
