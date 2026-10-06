<?php

namespace Tests\Feature;

use App\Models\ContentReport;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\LiveClass;
use App\Models\Order;
use App\Models\ReputationPoint;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\Teacher;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ReputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityUpgradeAndReputationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $instructor;

    protected User $student1;

    protected User $student2;

    protected Course $course;

    protected Teacher $teacherProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'অ্যাডমিন ইউজার']);
        $this->instructor = User::factory()->create(['role' => 'instructor', 'name' => 'শায়খ আব্দুল্লাহ']);
        $this->student1 = User::factory()->create(['role' => 'student', 'name' => 'তালিবুল ইলম ১']);
        $this->student2 = User::factory()->create(['role' => 'student', 'name' => 'তালিবুল ইলম ২']);

        $this->teacherProfile = Teacher::create([
            'user_id' => $this->instructor->id,
            'name' => 'শায়খ আব্দুল্লাহ',
            'slug' => 'shaykh-abdullah',
            'is_verified' => true,
            'status' => 'active',
            'platform_fee_percent' => 20,
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'কুরআন তাদাব্বুর কোর্স',
            'slug' => 'quran-tadabbur',
            'short_description' => 'কুরআনের গভীর তাদাব্বুর',
            'description' => 'কুরআনের গভীর অর্থ ও তাদাব্বুর',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);
    }

    /**
     * 1. Public study group accessibility and joining.
     */
    public function test_public_study_group_accessible_by_anyone_and_users_can_join(): void
    {
        $group = StudyGroup::create([
            'name' => 'উন্মুক্ত তাদাব্বুর হালাকা',
            'slug' => 'open-tadabbur',
            'type' => 'public',
            'creator_id' => $this->instructor->id,
            'invite_code' => 'OPENTAD1',
            'max_members' => 50,
            'members_count' => 1,
        ]);

        // Student can view public group
        $response = $this->actingAs($this->student1)->get("/community/groups/{$group->slug}");
        $response->assertStatus(200);

        // Student can join
        $joinResponse = $this->actingAs($this->student1)->post("/community/groups/{$group->slug}/join");
        $joinResponse->assertRedirect();

        $this->assertTrue($group->fresh()->isMember($this->student1));
        $this->assertEquals(2, $group->fresh()->members_count);

        // Student can post in group
        $postResponse = $this->actingAs($this->student1)->post("/community/groups/{$group->slug}/posts", [
            'body' => 'আসসালামু আলাইকুম, আজকের হালাকা সংক্রান্ত প্রশ্ন...',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('study_group_posts', [
            'study_group_id' => $group->id,
            'user_id' => $this->student1->id,
        ]);
    }

    /**
     * 2. Private study group privacy and invite code access.
     */
    public function test_private_study_group_privacy_and_invite_code_access(): void
    {
        $group = StudyGroup::create([
            'name' => 'গোপন তাহকীক গ্রুপ',
            'slug' => 'private-tahqeeq',
            'type' => 'private',
            'creator_id' => $this->instructor->id,
            'invite_code' => 'SECRET88',
            'max_members' => 10,
            'members_count' => 1,
        ]);

        // Non-member student is forbidden from viewing private group
        $forbiddenResponse = $this->actingAs($this->student1)->get("/community/groups/{$group->slug}");
        $forbiddenResponse->assertStatus(403);

        // Student joins via secret invite code
        $inviteResponse = $this->actingAs($this->student1)->get("/community/groups/invite/{$group->invite_code}");
        $inviteResponse->assertRedirect("/community/groups/{$group->slug}");

        // Now student has full access
        $this->assertTrue($group->fresh()->isMember($this->student1));
        $allowedResponse = $this->actingAs($this->student1)->get("/community/groups/{$group->slug}");
        $allowedResponse->assertStatus(200);
    }

    /**
     * 3. Course-linked study group allows only enrolled students.
     */
    public function test_course_linked_group_allows_only_enrolled_students(): void
    {
        $group = StudyGroup::create([
            'name' => 'তাদাব্বুর কোর্স স্টাডি গ্রুপ',
            'slug' => 'course-linked-group',
            'type' => 'course_linked',
            'course_id' => $this->course->id,
            'creator_id' => $this->instructor->id,
            'invite_code' => 'COURSELK',
            'max_members' => 30,
            'members_count' => 1,
        ]);

        // Student1 is NOT enrolled -> cannot join directly
        $failResponse = $this->actingAs($this->student1)->post("/community/groups/{$group->slug}/join");
        $failResponse->assertRedirect();
        $this->assertFalse($group->fresh()->isMember($this->student1));

        // Enroll Student1 in course
        Enrollment::create([
            'user_id' => $this->student1->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        // Now enrolled student can join
        $successResponse = $this->actingAs($this->student1)->post("/community/groups/{$group->slug}/join");
        $successResponse->assertRedirect();
        $this->assertTrue($group->fresh()->isMember($this->student1));
    }

    /**
     * 4. Weekly goal progress tracking and reputation points award.
     */
    public function test_study_group_weekly_goal_progress_updates_and_awards_reputation(): void
    {
        $group = StudyGroup::create([
            'name' => 'হিফয ও তাদাব্বুর গ্রুপ',
            'slug' => 'hifz-group',
            'type' => 'public',
            'creator_id' => $this->instructor->id,
            'invite_code' => 'HIFZGOAL',
            'weekly_goal' => 'সূরা মুলক ১-৩০ আয়াত অর্থসহ পাঠ',
            'weekly_goal_target' => 100,
        ]);

        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $this->student1->id,
            'role' => 'member',
            'status' => 'active',
            'weekly_goal_progress' => 0,
            'weekly_goal_completed' => false,
        ]);

        $initialPoints = $this->student1->reputation_points;

        // Progress update to 100%
        $response = $this->actingAs($this->student1)->post("/community/groups/{$group->slug}/goal", [
            'progress' => 100,
        ]);
        $response->assertRedirect();

        $member = StudyGroupMember::where('study_group_id', $group->id)->where('user_id', $this->student1->id)->first();
        $this->assertTrue($member->weekly_goal_completed);

        // Reputation awarded (+10)
        $this->student1->refresh();
        $this->assertEquals($initialPoints + 10, $this->student1->reputation_points);
        $this->assertDatabaseHas('user_badges', [
            'user_id' => $this->student1->id,
            'badge_slug' => 'goal_crusher',
        ]);
    }

    /**
     * 5. Reputation system: voting, level progression, and anti-gaming limits.
     */
    public function test_reputation_points_awarded_for_upvotes_and_anti_gaming_cap(): void
    {
        $post = ForumPost::create([
            'user_id' => $this->student1->id,
            'title' => 'তাহাজ্জুদের নামাজের সর্বোত্তম সময় কোনটি?',
            'topic' => 'general',
            'body' => 'রাসূলুল্লাহ (সাঃ) রাতের শেষ তৃতীয়াংশে সালাত আদায় করতেন...',
            'status' => 'published',
            'upvotes_count' => 0,
        ]);

        // Student2 upvotes post -> student1 gets +5 points
        $this->actingAs($this->student2)->post("/community/{$post->id}/vote", [
            'vote_type' => 'upvote',
        ]);

        $this->assertEquals(5, $this->student1->fresh()->reputation_points);

        // Anti-gaming: Author cannot upvote own post to get points
        $this->actingAs($this->student1)->post("/community/{$post->id}/vote", [
            'vote_type' => 'upvote',
        ]);
        // Still 5 points
        $this->assertEquals(5, $this->student1->fresh()->reputation_points);

        // Test daily upvote cap (50 points max per day from upvotes)
        $repService = app(ReputationService::class);
        for ($i = 0; $i < 15; $i++) {
            $dummyVoter = User::factory()->create();
            $repService->awardPoints($this->student1, ReputationService::ACTION_UPVOTE_RECEIVED, $post, $dummyVoter);
        }

        // Must not exceed 50 points from upvotes
        $dailyUpvotePoints = ReputationPoint::where('user_id', $this->student1->id)
            ->where('action', ReputationService::ACTION_UPVOTE_RECEIVED)
            ->sum('points');

        $this->assertLessThanOrEqual(50, $dailyUpvotePoints);
    }

    /**
     * 6. Scholar verification of forum answer and badge award.
     */
    public function test_scholar_can_verify_forum_answer_and_awards_scholar_verified_badge(): void
    {
        $post = ForumPost::create([
            'user_id' => $this->student1->id,
            'title' => 'সফরে কসর সালাতের বিধান',
            'topic' => 'fiqh-masala',
            'body' => 'সফরের দূরত্ব কত কিলোমিটার হলে কসর প্রযোজ্য?',
            'status' => 'published',
        ]);

        $comment = ForumComment::create([
            'forum_post_id' => $post->id,
            'user_id' => $this->student2->id,
            'body' => 'জমহুর ফুকাহায়ে কেরামের মতে প্রায় ৪৮ মাইল বা ৭৭.৫ কিলোমিটার...',
        ]);

        $initialPoints = $this->student2->reputation_points;

        // Scholar verifies the answer
        $response = $this->actingAs($this->instructor)->post("/community/comments/{$comment->id}/verify");
        $response->assertRedirect();

        $comment->refresh();
        $this->assertTrue($comment->is_scholar_verified);
        $this->assertEquals($this->instructor->id, $comment->verified_by_scholar_id);

        // Student2 receives +15 reputation points and scholar verified contributor badge
        $this->student2->refresh();
        $this->assertEquals($initialPoints + 15, $this->student2->reputation_points);
        $this->assertDatabaseHas('user_badges', [
            'user_id' => $this->student2->id,
            'badge_slug' => 'scholar_verified_contributor',
        ]);
    }

    /**
     * 7. Free Scholar Session registration flow.
     */
    public function test_scholar_session_free_registration_flow(): void
    {
        $session = LiveClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'কুরআনের অলৌকিকত্ব বিষয়ক উন্মুক্ত ওয়েবিনার',
            'session_type' => 'webinar',
            'platform' => 'zoom',
            'meeting_url' => 'https://zoom.us/j/123456789',
            'start_time' => now()->addDays(2),
            'duration' => 60,
            'fee' => 0,
            'max_participants' => 100,
            'status' => 'scheduled',
        ]);

        // Student1 registers for free
        $response = $this->actingAs($this->student1)->post("/scholar-sessions/{$session->id}/register", [
            'notes' => 'আমি সেশনে উপস্থিত থাকব ইনশাআল্লাহ',
        ]);
        $response->assertRedirect();

        $this->assertTrue($session->fresh()->isRegisteredBy($this->student1));
        $this->assertTrue($session->fresh()->canAccessMeeting($this->student1));
        $this->assertEquals(1, $session->fresh()->registered_count);
    }

    /**
     * 8. Paid Scholar Session booking, payment, and teacher wallet credit end-to-end.
     */
    public function test_scholar_session_paid_booking_payment_and_teacher_wallet_credit_end_to_end(): void
    {
        $session = LiveClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'ব্যক্তিগত তাজবীদ ও মাখরাজ কনসালটেশন',
            'session_type' => 'consultation',
            'platform' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'start_time' => now()->addDays(3),
            'duration' => 45,
            'fee' => 500.00,
            'max_participants' => 1,
            'status' => 'scheduled',
        ]);

        // Student initiates booking -> creates pending order
        $bookingResponse = $this->actingAs($this->student1)->post("/scholar-sessions/{$session->id}/register", [
            'notes' => 'আমার মাখরাজ সংশোধন সংক্রান্ত পরামর্শ দরকার',
        ]);

        $order = Order::where('live_class_id', $session->id)->where('user_id', $this->student1->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('scholar_session', $order->order_type);
        $this->assertEquals(500.00, (float) $order->amount);

        // Simulate payment completion via PaymentService
        $paymentService = app(PaymentService::class);
        $order->update([
            'status' => 'pending_verification',
            'payment_method' => 'bkash',
            'transaction_id' => 'TRXSESSION123',
            'sender_phone' => '01711223344',
        ]);

        $paymentService->markAsPaid($order);

        // Verify registration confirmed
        $this->assertTrue($session->fresh()->isRegisteredBy($this->student1));
        $this->assertTrue($session->fresh()->canAccessMeeting($this->student1));

        // Verify Instructor TeacherWallet credited
        $wallet = TeacherWallet::where('teacher_id', $this->teacherProfile->id)->first();
        $this->assertNotNull($wallet);

        // 80% instructor share of 500 BDT = 400 BDT = 40000 poisha in pending balance
        $this->assertEquals(40000, $wallet->pending_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'reference_type' => 'scholar_session',
            'reference_id' => $session->id,
            'amount' => 40000,
        ]);
    }

    /**
     * 9. Community moderation: auto-flag prohibited keywords, reporting, and admin mute.
     */
    public function test_community_moderation_auto_flags_prohibited_keywords_and_admin_can_resolve(): void
    {
        // Student creates post with gambling/prohibited spam
        $response = $this->actingAs($this->student1)->post('/community', [
            'title' => 'অনলাইন ক্যাসিনো ও দ্রুত আয়ের উপায়',
            'topic' => 'general',
            'body' => 'এখানে 1xbet ও ক্যাসিনো গেমের দারুণ সুযোগ রয়েছে...',
        ]);

        // Content auto-flagged and hidden
        $flaggedPost = ForumPost::where('title', 'অনলাইন ক্যাসিনো ও দ্রুত আয়ের উপায়')->first();
        $this->assertNotNull($flaggedPost);
        $this->assertEquals('hidden', $flaggedPost->status);

        // Report in pending queue
        $this->assertDatabaseHas('content_reports', [
            'reportable_id' => $flaggedPost->id,
            'status' => 'pending',
        ]);

        $report = ContentReport::where('reportable_id', $flaggedPost->id)->first();

        // Admin resolves report by muting author for 48 hours
        $adminResponse = $this->actingAs($this->admin)->post("/admin/community/reports/{$report->id}/resolve", [
            'action' => 'mute_author',
            'mute_hours' => 48,
        ]);
        $adminResponse->assertRedirect();

        $this->student1->refresh();
        $this->assertTrue($this->student1->isMutedInCommunity());

        // Muted user cannot post new discussion (returns 403)
        $blockedResponse = $this->actingAs($this->student1)->post('/community', [
            'title' => 'নতুন প্রশ্ন করতে চাই',
            'topic' => 'general',
            'body' => 'আমার আরেকটি গুরুত্বপূর্ণ প্রশ্ন আছে...',
        ]);
        $blockedResponse->assertStatus(403);
    }
}
