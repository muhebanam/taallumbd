<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\InstructorApplication;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_courses' => Course::count(),
                'published_courses' => Course::where('status', 'published')->count(),
                'total_enrollments' => Enrollment::count(),
                'total_students' => User::where('role', 'student')->count(),
                'total_instructors' => User::where('role', 'instructor')->count(),
                'total_earnings' => (float) Order::where('status', 'paid')->sum('amount'),
                'pending_instructor_applications' => InstructorApplication::where('status', 'pending')->count(),
                'pending_course_approvals' => Course::where('status', 'pending')->count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
                'pending_articles' => Article::where('status', 'pending')->count(),
                'pending_fatawa' => Fatwa::where('status', 'pending')->count(),
                'unread_messages' => ContactMessage::where('status', 'unread')->count(),
            ],
            'recentOrders' => Order::with(['user:id,name', 'course:id,title'])->latest()->take(8)->get(),
        ]);
    }
}
