<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_submit_draft_for_review(): void
    {
        $author = User::factory()->instructor()->create();
        $course = Course::factory()->create([
            'instructor_id' => $author->id,
            'status' => WorkflowService::STATUS_DRAFT,
        ]);

        $this->actingAs($author)
            ->post("/admin/reviews/course/{$course->id}/decision", [
                'to_status' => WorkflowService::STATUS_IN_REVIEW,
                'decision' => 'সম্পাদনার জন্য জমা দেওয়া হয়েছে',
                'notes' => 'সকল লেকচার ও কুইজ সংযোজিত।',
            ])
            ->assertRedirect();

        $course->refresh();
        $this->assertEquals(WorkflowService::STATUS_IN_REVIEW, $course->status);
        $this->assertDatabaseHas('content_reviews', [
            'reviewable_id' => $course->id,
            'reviewable_type' => Course::class,
            'from_status' => WorkflowService::STATUS_DRAFT,
            'to_status' => WorkflowService::STATUS_IN_REVIEW,
            'reviewer_id' => $author->id,
        ]);
    }

    public function test_editor_can_send_in_review_to_scholar_review(): void
    {
        $editor = User::factory()->editor()->create();
        $article = Article::factory()->create([
            'status' => WorkflowService::STATUS_IN_REVIEW,
        ]);

        $this->actingAs($editor)
            ->post("/admin/reviews/article/{$article->id}/decision", [
                'to_status' => WorkflowService::STATUS_SCHOLAR_REVIEW,
                'decision' => 'শরিয়াহ মূল্যায়নের জন্য স্কলারের কাছে প্রেরণ',
                'notes' => 'ফিকহী মাসআলা যাচাই প্রয়োজন।',
            ])
            ->assertRedirect();

        $article->refresh();
        $this->assertEquals(WorkflowService::STATUS_SCHOLAR_REVIEW, $article->status);
        $this->assertDatabaseHas('content_reviews', [
            'reviewable_id' => $article->id,
            'reviewable_type' => Article::class,
            'from_status' => WorkflowService::STATUS_IN_REVIEW,
            'to_status' => WorkflowService::STATUS_SCHOLAR_REVIEW,
            'reviewer_id' => $editor->id,
        ]);
    }

    public function test_scholar_reviewer_can_approve_content(): void
    {
        $scholar = User::factory()->scholarReviewer()->create();
        $fatwa = Fatwa::factory()->create([
            'status' => WorkflowService::STATUS_SCHOLAR_REVIEW,
        ]);

        $this->actingAs($scholar)
            ->post("/admin/reviews/fatwa/{$fatwa->id}/decision", [
                'to_status' => WorkflowService::STATUS_APPROVED,
                'decision' => 'শরিয়াহ যাচাই সমাপ্ত ও অনুমোদিত',
                'notes' => 'উদ্ধৃত হাদীস ও ফিকহী ইবারত সঠিক আছে।',
            ])
            ->assertRedirect();

        $fatwa->refresh();
        $this->assertEquals(WorkflowService::STATUS_APPROVED, $fatwa->status);
    }

    public function test_editor_or_admin_can_publish_approved_content(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'status' => WorkflowService::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->post("/admin/reviews/course/{$course->id}/decision", [
                'to_status' => WorkflowService::STATUS_PUBLISHED,
                'decision' => 'ওয়েবসাইটে সার্বজনীন প্রকাশ',
            ])
            ->assertRedirect();

        $course->refresh();
        $this->assertEquals(WorkflowService::STATUS_PUBLISHED, $course->status);
    }

    public function test_reviewer_can_reject_content_with_notes(): void
    {
        $editor = User::factory()->editor()->create();
        $article = Article::factory()->create([
            'status' => WorkflowService::STATUS_IN_REVIEW,
        ]);

        $this->actingAs($editor)
            ->post("/admin/reviews/article/{$article->id}/decision", [
                'to_status' => WorkflowService::STATUS_REJECTED,
                'decision' => 'পরিমার্জন ও সংশোধন প্রয়োজন',
                'notes' => 'অনুগ্রহ করে রেফারেন্সের সূত্র উল্লেখ করুন।',
            ])
            ->assertRedirect();

        $article->refresh();
        $this->assertEquals(WorkflowService::STATUS_REJECTED, $article->status);
        $this->assertDatabaseHas('content_reviews', [
            'reviewable_id' => $article->id,
            'decision' => 'পরিমার্জন ও সংশোধন প্রয়োজন',
            'to_status' => WorkflowService::STATUS_REJECTED,
        ]);
    }

    public function test_unauthorized_user_cannot_transition_workflow(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create([
            'status' => WorkflowService::STATUS_IN_REVIEW,
        ]);

        // Student cannot access admin reviews or transition
        $response = $this->actingAs($student)
            ->post("/admin/reviews/course/{$course->id}/decision", [
                'to_status' => WorkflowService::STATUS_APPROVED,
                'decision' => 'বেআইনি অনুমোদন',
            ]);

        $response->assertStatus(403);
        $course->refresh();
        $this->assertEquals(WorkflowService::STATUS_IN_REVIEW, $course->status);
    }

    public function test_review_queue_dashboard_accessible_to_authorized_roles(): void
    {
        $editor = User::factory()->editor()->create();
        $scholar = User::factory()->scholarReviewer()->create();

        $this->actingAs($editor)
            ->get('/admin/reviews')
            ->assertStatus(200);

        $this->actingAs($scholar)
            ->get('/admin/reviews')
            ->assertStatus(200);
    }
}
