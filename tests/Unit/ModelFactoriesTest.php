<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CurriculumItem;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\ForumPost;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_with_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertEquals('admin', $admin->role);

        $instructor = User::factory()->instructor()->create();
        $this->assertEquals('instructor', $instructor->role);

        $student = User::factory()->student()->create();
        $this->assertEquals('student', $student->role);
    }

    public function test_category_and_teacher_factories(): void
    {
        $category = Category::factory()->course()->create();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $teacher = Teacher::factory()->create();
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
        $this->assertTrue($teacher->is_verified);
    }

    public function test_fatwa_and_forum_post_factories(): void
    {
        $fatwa = Fatwa::factory()->create();
        $this->assertDatabaseHas('fatawa', ['id' => $fatwa->id]);

        $post = ForumPost::factory()->create();
        $this->assertDatabaseHas('forum_posts', ['id' => $post->id]);
    }

    public function test_coupon_order_and_payment_factories(): void
    {
        $coupon = Coupon::factory()->create();
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);

        $order = Order::factory()->create();
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        $payment = Payment::factory()->create(['order_id' => $order->id]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_enrollment_and_certificate_factories(): void
    {
        $enrollment = Enrollment::factory()->create();
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);

        $certificate = Certificate::factory()->create();
        $this->assertDatabaseHas('certificates', ['id' => $certificate->id]);
        $this->assertNotEmpty($certificate->uuid);
    }

    public function test_can_build_full_course_with_curriculum_using_factories(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $section = CourseSection::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);

        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'sort_order' => 1,
        ]);

        $quiz = Quiz::factory()->withQuestions(2)->create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $curriculumLesson = CurriculumItem::factory()->forLesson($lesson)->create();
        $curriculumQuiz = CurriculumItem::factory()->forQuiz($quiz)->create();

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('course_sections', ['id' => $section->id]);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseHas('curriculum_items', ['id' => $curriculumLesson->id]);
        $this->assertDatabaseHas('curriculum_items', ['id' => $curriculumQuiz->id]);

        $quiz->load('questions.options');
        $this->assertCount(2, $quiz->questions);
        $this->assertCount(3, $quiz->questions->first()->options);
    }
}
