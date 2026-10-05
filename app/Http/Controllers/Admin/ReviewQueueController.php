<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Publication;
use App\Models\Teacher;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewQueueController extends Controller
{
    public function __construct(
        protected WorkflowService $workflowService
    ) {}

    /**
     * Map type slug to model class.
     */
    protected function getModelClass(string $type): string
    {
        return match (strtolower($type)) {
            'course', 'courses' => Course::class,
            'article', 'articles' => Article::class,
            'fatwa', 'fatawa' => Fatwa::class,
            'publication', 'publications' => Publication::class,
            default => abort(404, 'Invalid content type'),
        };
    }

    /**
     * Display the review queue.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type', 'all');
        $status = $request->input('status');

        // Default status based on user role
        if (! $status) {
            if ($user->role === 'scholar_reviewer') {
                $status = WorkflowService::STATUS_SCHOLAR_REVIEW;
            } elseif ($user->role === 'editor') {
                $status = WorkflowService::STATUS_IN_REVIEW;
            } else {
                $status = 'pending'; // all pending reviews
            }
        }

        $items = collect();

        // 1. Courses
        if ($type === 'all' || in_array($type, ['course', 'courses'], true)) {
            $q = Course::with(['instructor:id,name,email', 'category:id,name', 'contentReviews.reviewer:id,name'])
                ->latest('updated_at');

            if ($status === 'pending') {
                $q->whereIn('status', [WorkflowService::STATUS_IN_REVIEW, WorkflowService::STATUS_SCHOLAR_REVIEW]);
            } elseif ($status !== 'all') {
                $q->where('status', $status);
            }

            foreach ($q->take(20)->get() as $course) {
                $items->push([
                    'id' => $course->id,
                    'type' => 'course',
                    'type_label' => 'কোর্স',
                    'title' => $course->title,
                    'author' => $course->instructor?->name ?? 'অজ্ঞাত',
                    'category' => $course->category?->name ?? 'সাধারণ',
                    'status' => $course->status,
                    'is_certified' => (bool) $course->is_certified,
                    'updated_at' => $course->updated_at->toIso8601String(),
                    'latest_review' => $course->contentReviews->first(),
                ]);
            }
        }

        // 2. Articles
        if ($type === 'all' || in_array($type, ['article', 'articles'], true)) {
            $q = Article::with(['author:id,name,email', 'category:id,name', 'contentReviews.reviewer:id,name'])
                ->latest('updated_at');

            if ($status === 'pending') {
                $q->whereIn('status', [WorkflowService::STATUS_IN_REVIEW, WorkflowService::STATUS_SCHOLAR_REVIEW]);
            } elseif ($status !== 'all') {
                $q->where('status', $status);
            }

            foreach ($q->take(20)->get() as $art) {
                $items->push([
                    'id' => $art->id,
                    'type' => 'article',
                    'type_label' => 'প্রবন্ধ',
                    'title' => $art->title,
                    'author' => $art->author?->name ?? 'অজ্ঞাত',
                    'category' => $art->category?->name ?? 'সাধারণ',
                    'status' => $art->status,
                    'is_certified' => false,
                    'updated_at' => $art->updated_at->toIso8601String(),
                    'latest_review' => $art->contentReviews->first(),
                ]);
            }
        }

        // 3. Fatawa
        if ($type === 'all' || in_array($type, ['fatwa', 'fatawa'], true)) {
            $q = Fatwa::with(['user:id,name,email', 'mufti:id,name', 'category:id,name', 'contentReviews.reviewer:id,name'])
                ->latest('updated_at');

            if ($status === 'pending') {
                $q->whereIn('status', [WorkflowService::STATUS_IN_REVIEW, WorkflowService::STATUS_SCHOLAR_REVIEW]);
            } elseif ($status !== 'all') {
                $q->where('status', $status);
            }

            foreach ($q->take(20)->get() as $fatwa) {
                $items->push([
                    'id' => $fatwa->id,
                    'type' => 'fatwa',
                    'type_label' => 'ফাতাওয়া',
                    'title' => $fatwa->question_title,
                    'author' => $fatwa->mufti?->name ?? ($fatwa->user?->name ?? $fatwa->questioner_name),
                    'category' => $fatwa->category?->name ?? 'সাধারণ',
                    'status' => $fatwa->status,
                    'is_certified' => false,
                    'updated_at' => $fatwa->updated_at->toIso8601String(),
                    'latest_review' => $fatwa->contentReviews->first(),
                ]);
            }
        }

        // 4. Publications
        if ($type === 'all' || in_array($type, ['publication', 'publications'], true)) {
            $q = Publication::with(['author:id,name,email', 'category:id,name', 'contentReviews.reviewer:id,name'])
                ->latest('updated_at');

            if ($status === 'pending') {
                $q->whereIn('status', [WorkflowService::STATUS_IN_REVIEW, WorkflowService::STATUS_SCHOLAR_REVIEW]);
            } elseif ($status !== 'all') {
                $q->where('status', $status);
            }

            foreach ($q->take(20)->get() as $pub) {
                $items->push([
                    'id' => $pub->id,
                    'type' => 'publication',
                    'type_label' => 'প্রকাশনা',
                    'title' => $pub->title,
                    'author' => $pub->author?->name ?? 'অজ্ঞাত',
                    'category' => $pub->category?->name ?? 'সাধারণ',
                    'status' => $pub->status,
                    'is_certified' => false,
                    'updated_at' => $pub->updated_at->toIso8601String(),
                    'latest_review' => $pub->contentReviews->first(),
                ]);
            }
        }

        // Sort combined queue by updated_at descending
        $sortedItems = $items->sortByDesc('updated_at')->values()->all();

        // Count pending
        $counts = [
            'in_review' => Course::where('status', WorkflowService::STATUS_IN_REVIEW)->count() +
                           Article::where('status', WorkflowService::STATUS_IN_REVIEW)->count() +
                           Fatwa::where('status', WorkflowService::STATUS_IN_REVIEW)->count() +
                           Publication::where('status', WorkflowService::STATUS_IN_REVIEW)->count(),
            'scholar_review' => Course::where('status', WorkflowService::STATUS_SCHOLAR_REVIEW)->count() +
                                Article::where('status', WorkflowService::STATUS_SCHOLAR_REVIEW)->count() +
                                Fatwa::where('status', WorkflowService::STATUS_SCHOLAR_REVIEW)->count() +
                                Publication::where('status', WorkflowService::STATUS_SCHOLAR_REVIEW)->count(),
            'approved' => Course::where('status', WorkflowService::STATUS_APPROVED)->count() +
                          Article::where('status', WorkflowService::STATUS_APPROVED)->count() +
                          Fatwa::where('status', WorkflowService::STATUS_APPROVED)->count() +
                          Publication::where('status', WorkflowService::STATUS_APPROVED)->count(),
        ];

        return Inertia::render('Admin/Reviews/Index', [
            'items' => $sortedItems,
            'filters' => [
                'type' => $type,
                'status' => $status,
            ],
            'counts' => $counts,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Show detailed review item and review history.
     */
    public function show(Request $request, string $type, int $id)
    {
        $modelClass = $this->getModelClass($type);
        $model = $modelClass::with(['contentReviews.reviewer:id,name,role'])->findOrFail($id);

        $scholars = Teacher::active()->verified()->get(['id', 'name', 'designation']);

        return Inertia::render('Admin/Reviews/Show', [
            'type' => $type,
            'item' => $model,
            'reviews' => $model->contentReviews,
            'scholars' => $scholars,
            'userRole' => $request->user()->role,
            'canApproveScholar' => $request->user()->isScholarReviewer(),
            'canEditorReview' => $request->user()->isEditor(),
        ]);
    }

    /**
     * Process decision on review item.
     */
    public function decision(Request $request, string $type, int $id)
    {
        $validated = $request->validate([
            'to_status' => 'required|string|in:in_review,scholar_review,approved,published,rejected',
            'decision' => 'required|string',
            'notes' => 'nullable|string|max:2000',
            'certified_by_scholar_id' => 'nullable|exists:teachers,id',
        ]);

        $modelClass = $this->getModelClass($type);
        $model = $modelClass::findOrFail($id);

        $this->workflowService->transition(
            $model,
            $request->user(),
            $validated['to_status'],
            $validated['decision'],
            $validated['notes'] ?? null,
            [
                'certified_by_scholar_id' => $validated['certified_by_scholar_id'] ?? null,
            ]
        );

        return redirect()->route('admin.reviews.index')
            ->with('success', 'কনটেন্ট স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।');
    }
}
