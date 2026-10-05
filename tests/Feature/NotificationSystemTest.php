<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\ForumPost;
use App\Models\InstructorApplication;
use App\Models\Lesson;
use App\Models\NotificationPreference;
use App\Models\Order;
use App\Models\Review;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\TaallumNotification;
use App\Services\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_supported_trigger_has_a_notification_contract(): void
    {
        Notification::fake();
        $user = User::factory()->student()->create();
        $types = (new \ReflectionClass(NotificationDispatcher::class))->getConstants();

        foreach ($types as $type) {
            app(NotificationDispatcher::class)->send(
                $user,
                $type,
                'পরীক্ষামূলক নোটিফিকেশন',
                'এই বার্তাটি নোটিফিকেশন চ্যানেল যাচাই করছে।',
                '/dashboard',
            );
        }

        foreach ($types as $type) {
            Notification::assertSentTo($user, function (TaallumNotification $notification) use ($type) {
                return $notification->type === $type;
            });
        }
    }

    public function test_disabled_preference_removes_that_channel_only(): void
    {
        $user = User::factory()->student()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'notification_type' => NotificationDispatcher::ENROLLMENT,
            'channel' => 'mail',
            'enabled' => false,
        ]);

        $notification = new TaallumNotification(
            NotificationDispatcher::ENROLLMENT,
            'ভর্তি সফল',
            'আপনি কোর্সে ভর্তি হয়েছেন।',
        );

        $this->assertContains('database', $notification->via($user));
        $this->assertNotContains('mail', $notification->via($user));
    }

    public function test_each_dispatcher_trigger_notifies_the_expected_recipient(): void
    {
        Notification::fake();
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->published()->create();
        $dispatcher = app(NotificationDispatcher::class);

        $enrollment = Enrollment::factory()->create(['user_id' => $student->id, 'course_id' => $course->id]);
        $dispatcher->enrollment($enrollment);

        $order = Order::factory()->create(['user_id' => $student->id, 'course_id' => $course->id]);
        $dispatcher->paymentVerified($order);
        $dispatcher->paymentCancelled($order);

        $fatwa = Fatwa::factory()->create(['question_user_id' => $student->id]);
        $dispatcher->fatwaAnswered($fatwa);
        $teacher = Teacher::factory()->create();
        $teacherQuestion = Fatwa::factory()->create(['question_user_id' => $student->id, 'teacher_id' => $teacher->id]);
        $dispatcher->teacherQuestionAnswered($teacherQuestion);

        $post = ForumPost::factory()->create(['user_id' => $student->id]);
        $dispatcher->forumComment($post, User::factory()->student()->create());
        $dispatcher->forumSolved($post);

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);
        $dispatcher->lessonPublished($lesson);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'সাপ্তাহিক কাজ',
            'description' => 'কাজ',
            'total_marks' => 20,
        ]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'answer_text' => 'উত্তর',
            'status' => 'reviewed',
            'marks' => 18,
        ]);
        $dispatcher->assignmentGraded($submission);

        $application = InstructorApplication::create([
            'user_id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'phone' => '01700000000',
            'expertise' => 'ফিকহ',
            'experience' => 'অভিজ্ঞতা',
            'status' => 'approved',
        ]);
        $dispatcher->instructorDecision($application);

        $review = Review::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'rating' => 5,
            'comment' => 'ভালো',
            'status' => 'pending',
        ]);
        $dispatcher->teacherReview($review);

        $expected = [
            NotificationDispatcher::ENROLLMENT,
            NotificationDispatcher::PAYMENT_VERIFIED,
            NotificationDispatcher::PAYMENT_CANCELLED,
            NotificationDispatcher::FATWA_ANSWERED,
            NotificationDispatcher::TEACHER_QUESTION_ANSWERED,
            NotificationDispatcher::FORUM_COMMENT,
            NotificationDispatcher::FORUM_SOLVED,
            NotificationDispatcher::LESSON_PUBLISHED,
            NotificationDispatcher::ASSIGNMENT_GRADED,
            NotificationDispatcher::INSTRUCTOR_DECISION,
        ];

        foreach ($expected as $type) {
            Notification::assertSentTo($student, fn (TaallumNotification $notification) => $notification->type === $type);
        }
        Notification::assertSentTo($admin, fn (TaallumNotification $notification) => $notification->type === NotificationDispatcher::TEACHER_REVIEW);
    }

    public function test_notification_page_can_mark_one_and_all_as_read(): void
    {
        $user = User::factory()->student()->create();
        $user->notify(new TaallumNotification('test', 'শিরোনাম', 'বার্তা'));

        $notification = $user->notifications()->firstOrFail();
        $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);

        $user->notify(new TaallumNotification('test-2', 'শিরোনাম', 'বার্তা'));
        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
