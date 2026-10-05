<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ContentReview;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_SCHOLAR_REVIEW = 'scholar_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    public function __construct(
        protected NotificationDispatcher $notificationDispatcher
    ) {}

    /**
     * Determine if a user can transition a given model from its current status to target status.
     */
    public function canTransition(Model $model, User $user, string $toStatus): bool
    {
        $currentStatus = $model->status ?? self::STATUS_DRAFT;

        if ($currentStatus === $toStatus) {
            return false;
        }

        // Admin has full override capabilities
        if ($user->isAdmin()) {
            return true;
        }

        // Author/Creator can submit draft -> in_review or rejected -> in_review
        if (in_array($currentStatus, [self::STATUS_DRAFT, self::STATUS_REJECTED], true) && $toStatus === self::STATUS_IN_REVIEW) {
            return $this->isAuthor($model, $user) || $user->isEditor();
        }

        // Editor can transition in_review -> scholar_review or in_review -> rejected
        if ($currentStatus === self::STATUS_IN_REVIEW && in_array($toStatus, [self::STATUS_SCHOLAR_REVIEW, self::STATUS_REJECTED], true)) {
            return $user->isEditor();
        }

        // Scholar Reviewer can transition scholar_review -> approved or scholar_review -> rejected
        if ($currentStatus === self::STATUS_SCHOLAR_REVIEW && in_array($toStatus, [self::STATUS_APPROVED, self::STATUS_REJECTED], true)) {
            return $user->isScholarReviewer();
        }

        // Editor or Admin can publish approved content
        if ($currentStatus === self::STATUS_APPROVED && $toStatus === self::STATUS_PUBLISHED) {
            return $user->isEditor();
        }

        return false;
    }

    /**
     * Execute a workflow status transition, logging history and dispatching notifications.
     */
    public function transition(
        Model $model,
        User $user,
        string $toStatus,
        string $decision,
        ?string $notes = null,
        array $metadata = []
    ): ContentReview {
        if (! $this->canTransition($model, $user, $toStatus)) {
            throw ValidationException::withMessages([
                'status' => ["আপনাকে '{$toStatus}' স্ট্যাটাসে পরিবর্তনের অনুমতি দেওয়া হয়নি।"],
            ]);
        }

        $fromStatus = $model->status ?? self::STATUS_DRAFT;
        $currentVersion = $model->contentReviews()->max('version') ?? 0;
        $nextVersion = ($toStatus === self::STATUS_IN_REVIEW && $fromStatus !== self::STATUS_DRAFT) ? $currentVersion + 1 : max(1, $currentVersion);

        // Update Model Status
        $model->status = $toStatus;

        if ($toStatus === self::STATUS_PUBLISHED) {
            if (Schema::hasColumn($model->getTable(), 'published_at')) {
                $model->published_at = now();
            }

            // If course certified by scholar
            if ($model instanceof Course && ! empty($metadata['certified_by_scholar_id'])) {
                $model->is_certified = true;
                $model->certified_by_scholar_id = $metadata['certified_by_scholar_id'];
                $model->certified_at = now();
                $model->certification_note = $notes;
            }
        }

        $model->save();

        // Create Content Review Entry
        $role = $user->role;
        if ($user->isAdmin()) {
            $role = 'admin';
        }

        $review = $model->contentReviews()->create([
            'reviewer_id' => $user->id,
            'reviewer_role' => $role,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'decision' => $decision,
            'notes' => $notes,
            'version' => $nextVersion,
        ]);

        // Dispatch notifications
        $this->notifyParties($model, $user, $fromStatus, $toStatus, $notes);

        return $review;
    }

    /**
     * Check if user is the author/instructor of the content.
     */
    protected function isAuthor(Model $model, User $user): bool
    {
        if ($model instanceof Course) {
            return $model->instructor_id === $user->id;
        }

        if ($model instanceof Article || $model instanceof Publication) {
            return $model->user_id === $user->id;
        }

        if ($model instanceof Fatwa) {
            return $model->question_user_id === $user->id || $model->answered_by === $user->id;
        }

        return false;
    }

    /**
     * Send notification updates based on status change.
     */
    protected function notifyParties(Model $model, User $actor, string $from, string $to, ?string $notes): void
    {
        $title = $this->getContentTitle($model);
        $author = $this->getContentAuthor($model);

        // Notify Author of Decision
        if ($author && $author->id !== $actor->id) {
            $msg = match ($to) {
                self::STATUS_SCHOLAR_REVIEW => "আপনার কনটেন্ট «{$title}» প্রাতিষ্ঠানিক সম্পাদনা শেষে বিজ্ঞ স্কলার পর্যালোচনার জন্য প্রেরিত হয়েছে।",
                self::STATUS_APPROVED => "মাশাআল্লাহ! আপনার কনটেন্ট «{$title}» বিজ্ঞ স্কলার কর্তৃক অনুমোদিত হয়েছে।",
                self::STATUS_PUBLISHED => "মুবারকবাদ! আপনার কনটেন্ট «{$title}» আত-তাআল্লুমে সফলভাবে প্রকাশিত হয়েছে।",
                self::STATUS_REJECTED => "আপনার কনটেন্ট «{$title}»-এ পরিমার্জন প্রয়োজন। মন্তব্য: ".($notes ?: 'বিস্তারিত রিভিউ নোটে দেখুন।'),
                default => "আপনার কনটেন্টের স্ট্যাটাস পরিবর্তিত হয়ে '{$to}' হয়েছে।",
            };

            $this->notificationDispatcher->send(
                $author,
                'content_review_update',
                'কনটেন্ট রিভিউ আপডেট',
                $msg,
                $this->getContentUrl($model)
            );
        }
    }

    public function getContentTitle(Model $model): string
    {
        return $model->title ?? $model->question_title ?? 'কনটেন্ট';
    }

    public function getContentAuthor(Model $model): ?User
    {
        if ($model instanceof Course) {
            return $model->instructor;
        }

        if ($model instanceof Article || $model instanceof Publication) {
            return $model->author;
        }

        if ($model instanceof Fatwa) {
            return $model->mufti ?? $model->user;
        }

        return null;
    }

    public function getContentUrl(Model $model): string
    {
        if ($model instanceof Course) {
            return url('/courses/'.$model->slug);
        }

        if ($model instanceof Article) {
            return url('/articles/'.$model->slug);
        }

        if ($model instanceof Fatwa) {
            return url('/fatawa/'.$model->id);
        }

        if ($model instanceof Publication) {
            return url('/publications');
        }

        return url('/');
    }
}
