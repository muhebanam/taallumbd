<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\ForumPost;
use App\Models\InstructorApplication;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Notifications\TaallumNotification;
use Illuminate\Support\Facades\Notification;

class NotificationDispatcher
{
    public const ENROLLMENT = 'enrollment_success';

    public const PAYMENT_VERIFIED = 'payment_verified';

    public const PAYMENT_CANCELLED = 'payment_cancelled';

    public const FATWA_ANSWERED = 'fatwa_answered';

    public const TEACHER_QUESTION_ANSWERED = 'teacher_question_answered';

    public const FORUM_COMMENT = 'forum_comment';

    public const FORUM_SOLVED = 'forum_solved';

    public const CERTIFICATE = 'certificate_issued';

    public const ASSIGNMENT_GRADED = 'assignment_graded';

    public const LESSON_PUBLISHED = 'lesson_published';

    public const INSTRUCTOR_DECISION = 'instructor_application_decision';

    public const TEACHER_REVIEW = 'teacher_review';

    public function send(User|iterable $recipients, string $type, string $title, string $message, ?string $url = null, array $context = []): void
    {
        Notification::send($recipients, new TaallumNotification($type, $title, $message, $url, $context));
    }

    public function enrollment(Enrollment $enrollment): void
    {
        $enrollment->loadMissing('user', 'course');
        $this->send($enrollment->user, self::ENROLLMENT, 'কোর্সে ভর্তি সফল হয়েছে', "আপনি «{$enrollment->course->title}» কোর্সে সফলভাবে ভর্তি হয়েছেন।", route('student.courses.show', $enrollment->course));
    }

    public function paymentVerified(Order $order): void
    {
        $order->loadMissing('user', 'course');
        $this->send($order->user, self::PAYMENT_VERIFIED, 'পেমেন্ট যাচাই সম্পন্ন', "আপনার «{$order->course->title}» কোর্সের পেমেন্ট যাচাই হয়েছে।", route('student.orders.index'));
    }

    public function paymentCancelled(Order $order): void
    {
        $order->loadMissing('user', 'course');
        $this->send($order->user, self::PAYMENT_CANCELLED, 'পেমেন্ট বাতিল হয়েছে', "«{$order->course->title}» কোর্সের পেমেন্ট বাতিল করা হয়েছে।", route('student.orders.index'));
    }

    public function fatwaAnswered(Fatwa $fatwa): void
    {
        $fatwa->loadMissing('user');
        if ($fatwa->user) {
            $this->send($fatwa->user, self::FATWA_ANSWERED, 'আপনার ফাতওয়ার উত্তর প্রকাশিত হয়েছে', "«{$fatwa->question_title}» প্রশ্নটির উত্তর প্রকাশিত হয়েছে।", route('fatawa.show', $fatwa));
        }
    }

    public function teacherQuestionAnswered(Fatwa $question): void
    {
        $question->loadMissing('user');
        if ($question->user) {
            $this->send($question->user, self::TEACHER_QUESTION_ANSWERED, 'আপনার শিক্ষকের প্রশ্নের উত্তর এসেছে', "«{$question->question_title}» প্রশ্নটির উত্তর প্রকাশিত হয়েছে।", route('fatawa.show', $question));
        }
    }

    public function lessonPublished(Lesson $lesson): void
    {
        $lesson->loadMissing('course');
        $users = User::whereHas('enrollments', fn ($q) => $q->where('course_id', $lesson->course_id)->whereIn('status', ['active', 'completed']))->get();
        $this->send($users, self::LESSON_PUBLISHED, 'নতুন পাঠ প্রকাশিত হয়েছে', "«{$lesson->course->title}» কোর্সে নতুন পাঠ «{$lesson->title}» প্রকাশিত হয়েছে।", route('student.lessons.show', $lesson));
    }

    public function forumComment(ForumPost $post, User $commenter): void
    {
        $post->loadMissing('user');
        if ($post->user && $post->user->id !== $commenter->id) {
            $this->send($post->user, self::FORUM_COMMENT, 'আপনার আলোচনায় নতুন মন্তব্য', "«{$post->title}» আলোচনায় নতুন মন্তব্য এসেছে।", route('community.show', $post));
        }
    }

    public function forumSolved(ForumPost $post): void
    {
        $post->loadMissing('user');
        if ($post->user) {
            $this->send($post->user, self::FORUM_SOLVED, 'আলোচনা সমাধান হিসেবে চিহ্নিত', "«{$post->title}» আলোচনাটি সমাধান হিসেবে চিহ্নিত হয়েছে।", route('community.show', $post));
        }
    }

    public function assignmentGraded(AssignmentSubmission $submission): void
    {
        $submission->loadMissing('user', 'assignment');
        $this->send($submission->user, self::ASSIGNMENT_GRADED, 'অ্যাসাইনমেন্ট মূল্যায়ন সম্পন্ন', "«{$submission->assignment->title}» অ্যাসাইনমেন্টের নম্বর প্রকাশিত হয়েছে।", route('student.assignments.show', $submission->assignment));
    }

    public function instructorDecision(InstructorApplication $application): void
    {
        $application->loadMissing('user');
        if ($application->user) {
            $status = $application->status === 'approved' ? 'অনুমোদিত' : 'প্রত্যাখ্যাত';
            $this->send($application->user, self::INSTRUCTOR_DECISION, 'ইনস্ট্রাক্টর আবেদন '.$status, "আপনার ইনস্ট্রাক্টর আবেদন {$status} হয়েছে।", route('dashboard'));
        }
    }

    public function teacherReview(Review $review): void
    {
        $admins = User::where('role', 'admin')->get();
        $this->send($admins, self::TEACHER_REVIEW, 'নতুন শিক্ষক রিভিউ অপেক্ষমাণ', 'একটি নতুন শিক্ষক রিভিউ মডারেশনের জন্য অপেক্ষা করছে।', route('admin.teacher-reviews.index'));
    }
}
