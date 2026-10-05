<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CurriculumItem;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Str;

class ProgressService
{
    public function completeLesson(User $user, $lesson): int
    {
        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['course_id' => $lesson->course_id, 'is_completed' => true, 'completed_at' => now()]
        );

        try {
            app(EventTracker::class)->trackLessonCompleted($user, $lesson->id, $lesson->course_id);
        } catch (\Throwable $e) {
            // Non-blocking tracking
        }

        return $this->syncEnrollmentProgress($user, $lesson->course);
    }

    public function syncEnrollmentProgress(User $user, Course $course): int
    {
        $total = $course->lessons()->count();
        $done = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)->where('is_completed', true)->count();
        $progress = $total ? (int) round($done / $total * 100) : 0;

        Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->update([
            'progress' => $progress,
            'status' => $progress === 100 ? 'completed' : 'active',
            'completed_at' => $progress === 100 ? now() : null,
        ]);

        if ($progress === 100) {
            try {
                app(EventTracker::class)->trackCourseCompleted($user, $course->id);
            } catch (\Throwable $e) {
                // Non-blocking tracking
            }
        }

        return $progress;
    }

    /** Certificate requires lessons, quizzes, and assignments checks according to completion_requirements. */
    public function eligibleForCertificate(User $user, Course $course): bool
    {
        $requirements = $course->completion_requirements ?? [
            'lessons_required' => true,
            'quizzes_required' => true,
            'assignments_required' => false,
        ];

        // 1. Check lessons if required
        if (! isset($requirements['lessons_required']) || $requirements['lessons_required']) {
            $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
            if (! $enrollment || (int) $enrollment->progress !== 100) {
                return false;
            }
        }

        // 2. Check quizzes if required
        if (! isset($requirements['quizzes_required']) || $requirements['quizzes_required']) {
            // Find required quizzes in curriculum items, fallback to all course quizzes
            $requiredQuizIds = CurriculumItem::where('course_id', $course->id)
                ->where('item_type', 'quiz')
                ->where('is_required', true)
                ->pluck('itemable_id');

            if ($requiredQuizIds->isEmpty()) {
                $requiredQuizIds = $course->quizzes()->pluck('id');
            }

            if ($requiredQuizIds->isNotEmpty()) {
                $passed = $user->quizAttempts()->whereIn('quiz_id', $requiredQuizIds)
                    ->where('status', 'passed')->distinct('quiz_id')->count('quiz_id');
                if ($passed < $requiredQuizIds->count()) {
                    return false;
                }
            }
        }

        // 3. Check assignments if required
        if (isset($requirements['assignments_required']) && $requirements['assignments_required']) {
            if (! $this->assignmentsCompleted($user, $course)) {
                return false;
            }
        }

        return true;
    }

    public function assignmentsCompleted(User $user, Course $course): bool
    {
        // Find required assignments
        $requiredAssignments = CurriculumItem::where('course_id', $course->id)
            ->where('item_type', 'assignment')
            ->where('is_required', true)
            ->get();

        if ($requiredAssignments->isEmpty()) {
            return true;
        }

        foreach ($requiredAssignments as $item) {
            $assignmentId = $item->itemable_id;
            $submission = AssignmentSubmission::where('assignment_id', $assignmentId)
                ->where('user_id', $user->id)
                ->first();

            if (! $submission) {
                return false;
            }

            // Status must be submitted or reviewed
            if (! in_array($submission->status, ['submitted', 'reviewed'])) {
                return false;
            }

            // If assignment has pass_marks and is reviewed with marks, check pass mark
            $assignment = $submission->assignment;
            if (isset($assignment->pass_marks) && $submission->status === 'reviewed' && ! is_null($submission->marks)) {
                if ($submission->marks < $assignment->pass_marks) {
                    return false;
                }
            }
        }

        return true;
    }

    public function issueCertificate(User $user, Course $course): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'uuid' => (string) Str::uuid(),
            'certificate_no' => 'TBD-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'issued_at' => now(),
        ]);

        app(NotificationDispatcher::class)->send(
            $user,
            NotificationDispatcher::CERTIFICATE,
            'আপনার সার্টিফিকেট ইস্যু হয়েছে',
            "«{$course->title}» কোর্সের সার্টিফিকেট প্রস্তুত হয়েছে।",
            route('student.certificates'),
        );

        return $certificate;
    }
}
