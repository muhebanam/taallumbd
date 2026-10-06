<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningPathController extends Controller
{
    /**
     * Display a listing of active learning paths.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $paths = LearningPath::active()
            ->with(['courses'])
            ->withCount('courses')
            ->get()
            ->map(function ($path) use ($user) {
                $progress = $path->calculateProgress($user);

                return [
                    'id' => $path->id,
                    'title' => $path->title,
                    'slug' => $path->slug,
                    'description' => $path->description,
                    'level' => $path->level,
                    'icon' => $path->icon,
                    'duration' => $path->duration,
                    'courses_count' => $path->courses_count,
                    'is_enrolled' => $progress['is_enrolled'] ?? false,
                    'progress_percentage' => $progress['progress_percentage'] ?? 0,
                    'is_completed' => $progress['is_completed'] ?? false,
                ];
            });

        return Inertia::render('LearningPaths/Index', [
            'learningPaths' => $paths,
        ]);
    }

    /**
     * Display a learning path details, roadmap steps and progress.
     */
    public function show(Request $request, LearningPath $learningPath): Response
    {
        $user = $request->user();
        $progress = $learningPath->calculateProgress($user);

        return Inertia::render('LearningPaths/Show', [
            'learningPath' => [
                'id' => $learningPath->id,
                'title' => $learningPath->title,
                'slug' => $learningPath->slug,
                'description' => $learningPath->description,
                'level' => $learningPath->level,
                'icon' => $learningPath->icon,
                'duration' => $learningPath->duration,
            ],
            'progress' => $progress,
        ]);
    }

    /**
     * Enroll authenticated user in the learning path.
     */
    public function enroll(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $learningPath->enrollUser($user);

        return back()->with('success', 'লার্নিং পাথে সফলভাবে এনরোল করা হয়েছে!');
    }

    /**
     * Claim learning path certificate once all courses are completed.
     */
    public function claimCertificate(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $certificate = $learningPath->awardCertificate($user);

            return back()->with('success', 'অভিনন্দন! আপনার লার্নিং পাথ সার্টিফিকেট নং '.$certificate->certificate_no.' সফলভাবে ইস্যু করা হয়েছে।');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
