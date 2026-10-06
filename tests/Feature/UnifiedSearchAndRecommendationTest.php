<?php

namespace Tests\Feature;

use App\Contracts\RecommendationService;
use App\Contracts\SearchEngine;
use App\Models\Article;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\HadithBook;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedSearchAndRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_reindex_command_executes_successfully(): void
    {
        $this->artisan('taallum:search-reindex')
            ->assertExitCode(0);
    }

    public function test_arabic_search_with_and_without_harakat_returns_identical_results(): void
    {
        $book = HadithBook::create([
            'name_arabic' => 'صحيح البخاري',
            'name_bangla' => 'সহীহ বুখারী',
            'name_english' => 'Sahih al-Bukhari',
            'slug' => 'sahih-bukhari',
            'author' => 'ইমাম বুখারী (রহ.)',
            'total_hadith' => 1,
        ]);

        // Arabic text with harakat: الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ
        $hadith = Hadith::create([
            'book_id' => $book->id,
            'number' => 1,
            'hadith_number_in_book' => '1',
            'text_arabic' => 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
            'text_bangla' => 'সমস্ত প্রশংসা বিশ্বজগতের প্রতিপালক আল্লাহর জন্য।',
            'narrator' => 'আবু হুরায়রা (রাঃ)',
        ]);

        $searchEngine = app(SearchEngine::class);

        // Search with harakat
        $resultsWithHarakat = $searchEngine->search('الْحَمْدُ', 'hadiths');

        // Search without harakat
        $resultsWithoutHarakat = $searchEngine->search('الحمد', 'hadiths');

        $this->assertNotEmpty($resultsWithHarakat['results'], 'Search with harakat should find the hadith');
        $this->assertNotEmpty($resultsWithoutHarakat['results'], 'Search without harakat should find the hadith');

        $this->assertEquals(
            $resultsWithHarakat['results'][0]['id'],
            $resultsWithoutHarakat['results'][0]['id'],
            'Both with and without harakat searches must return the exact same record'
        );
    }

    public function test_bengali_synonym_search_finds_cross_referenced_content(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $category = Category::create([
            'name' => 'সালাত ও ইবাদত',
            'slug' => 'salat-ibadat-unique',
            'type' => 'course',
        ]);

        // Course title has 'সালাত', but search query uses synonym 'নামায'
        $course = Course::create([
            'instructor_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'সহীহ সালাত শিক্ষা ও মাসনুন দুআ',
            'slug' => 'sahih-salat-shikkha',
            'short_description' => 'বিশুদ্ধভাবে সালাত আদায়ের নিয়মাবলী',
            'description' => 'বিশুদ্ধভাবে সালাত আদায়ের বিস্তারিত কোর্স বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $searchEngine = app(SearchEngine::class);

        // Search using "নামায" (synonym of সালাত)
        $searchByNamaj = $searchEngine->search('নামায', 'courses');

        $this->assertNotEmpty($searchByNamaj['results'], 'Searching for "নামায" must match course with "সালাত"');
        $courseResultIds = collect($searchByNamaj['results'])->pluck('id')->all();
        $this->assertContains($course->id, $courseResultIds, 'Course with "সালাত" should be found when searching "নামায"');

        // Vice-versa: Fatwa has 'রোজা', search using 'সওম'
        $fatwaCategory = Category::create([
            'name' => 'রোজা ও সিয়াম',
            'slug' => 'roja-siyam-fatwa-unique',
            'type' => 'fatwa',
        ]);

        $fatwa = Fatwa::create([
            'category_id' => $fatwaCategory->id,
            'question_title' => 'রমজানের রোজা ভঙ্গের কারণসমূহ',
            'question_body' => 'কোন কোন কারণে রোজা ভঙ্গ হয়?',
            'answer_body' => 'রোজা ভঙ্গের প্রধান কারণগুলো নিম্নরূপ...',
            'status' => 'published',
            'is_private' => false,
        ]);

        $searchBySawm = $searchEngine->search('সওম', 'fatawa');
        $this->assertNotEmpty($searchBySawm['results'], 'Searching for "সওম" must match fatwa with "রোজা"');
        $fatwaResultIds = collect($searchBySawm['results'])->pluck('id')->all();
        $this->assertContains($fatwa->id, $fatwaResultIds, 'Fatwa with "রোজা" should be found when searching "সওম"');
    }

    public function test_multi_model_search_across_courses_teachers_lessons_and_fatawa(): void
    {
        $instructorUser = User::factory()->create(['role' => 'instructor']);
        $teacher = Teacher::create([
            'user_id' => $instructorUser->id,
            'name' => 'ড. খোন্দকার আব্দুল্লাহ জাহাঙ্গীর',
            'slug' => 'dr-abdullah-jahangir',
            'designation' => 'বিশিষ্ট ইসলামী চিন্তাবিদ ও গবেষক',
            'status' => 'active',
        ]);

        $course = Course::create([
            'instructor_id' => $instructorUser->id,
            'title' => 'ইসলামের মৌলিক আকীদাহ',
            'slug' => 'islamer-moulik-aqeedah',
            'short_description' => 'ঈমান ও তাওহীদের বুনিয়াদী শিক্ষা',
            'description' => 'তাওহীদ ও আকীদার বিস্তারিত বিশ্লেষণ',
            'status' => 'published',
            'price' => 500,
            'is_free' => false,
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'প্রথম অধ্যায়',
            'sort_order' => 1,
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'তাওহীদের পরিচয় ও গুরুত্ব',
            'slug' => 'tawheed-intro',
            'sort_order' => 1,
        ]);

        $articleCategory = Category::create([
            'name' => 'ইসলামী প্রবন্ধ',
            'slug' => 'islami-prabandha-unique',
            'type' => 'article',
        ]);

        $article = Article::create([
            'user_id' => $instructorUser->id,
            'category_id' => $articleCategory->id,
            'title' => 'তাওহীদ বিশ্বাসের প্রতিফলন',
            'slug' => 'tawheed-faith-reflection',
            'excerpt' => 'দৈনন্দিন জীবনে তাওহীদের প্রভাব',
            'body' => 'বিস্তারিত আলোচনা...',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $searchEngine = app(SearchEngine::class);

        // Unified search for "তাওহীদ"
        $results = $searchEngine->search('তাওহীদ');

        $this->assertGreaterThanOrEqual(1, count($results['groups']['courses']));
        $this->assertGreaterThanOrEqual(1, count($results['groups']['lessons']));
        $this->assertGreaterThanOrEqual(1, count($results['groups']['articles']));

        // Web endpoint /search
        $response = $this->get('/search?q=তাওহীদ');
        $response->assertStatus(200);
    }

    public function test_command_palette_live_endpoint_returns_json_suggestions(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        Course::create([
            'instructor_id' => $user->id,
            'title' => 'সহীহ কুরআন তিলাওয়াত শিক্ষা',
            'slug' => 'sahih-quran-tilawat',
            'short_description' => 'সহজ পদ্ধতিতে মাখরাজ ও তাজবীদ',
            'description' => 'কুরআন তিলাওয়াত শিক্ষার বিস্তারিত বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $response = $this->getJson('/search/live?q=কুরআন');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query',
            'total',
            'items',
            'groups',
        ]);
        $this->assertGreaterThan(0, $response->json('total'));
    }

    public function test_search_performed_event_is_tracked(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/search?q=ফিকহ');
        $response->assertStatus(200);

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'search_performed',
            'subject_type' => 'search',
        ]);
    }

    public function test_learning_path_browsing_and_roadmap_steps(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $path = LearningPath::create([
            'title' => 'ইসলামিক ফাউন্ডেশন পাথ',
            'slug' => 'islamic-foundation-path',
            'description' => 'প্রাথমিক থেকে উচ্চতর স্তরের পূর্ণাঙ্গ রোডম্যাপ',
            'level' => 'প্রাথমিক',
            'status' => 'active',
            'duration' => '৩ মাস',
        ]);

        $course1 = Course::create([
            'instructor_id' => $user->id,
            'title' => 'বুনিয়াদী আকীদা ১',
            'slug' => 'buniyadi-aqeedah-1',
            'short_description' => 'প্রথম ধাপ',
            'description' => 'প্রথম ধাপের বিস্তারিত কোর্স বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $course2 = Course::create([
            'instructor_id' => $user->id,
            'title' => 'উন্নত আকীদা ২',
            'slug' => 'unnata-aqeedah-2',
            'short_description' => 'দ্বিতীয় ধাপ',
            'description' => 'দ্বিতীয় ধাপের বিস্তারিত কোর্স বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $path->courses()->attach($course1->id, ['sort_order' => 1, 'prerequisite_course_id' => null]);
        $path->courses()->attach($course2->id, ['sort_order' => 2, 'prerequisite_course_id' => $course1->id]);

        // Browse /learning-paths
        $indexResponse = $this->get('/learning-paths');
        $indexResponse->assertStatus(200);

        // View single path roadmap /learning-paths/{slug}
        $showResponse = $this->get('/learning-paths/'.$path->slug);
        $showResponse->assertStatus(200);

        // Progress check for guest: first course unlocked, second locked
        $guestProgress = $path->calculateProgress(null);
        $this->assertTrue($guestProgress['steps'][0]['is_unlocked']);
        $this->assertFalse($guestProgress['steps'][1]['is_unlocked']);
    }

    public function test_learning_path_enrollment_and_certificate_claim(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $path = LearningPath::create([
            'title' => 'তাজবীদুল কুরআন পাথ',
            'slug' => 'tajweed-quran-path',
            'description' => 'কুরআন তিলাওয়াত প্রশিক্ষণ',
            'status' => 'active',
        ]);

        $course1 = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'তাজবীদ প্রথম ভাগ',
            'slug' => 'tajweed-part-1',
            'short_description' => 'পার্ট ১',
            'description' => 'তাজবীদের প্রাথমিক পাঠ',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $path->courses()->attach($course1->id, ['sort_order' => 1]);

        // 1. Enroll user in path
        $this->actingAs($student)
            ->post('/learning-paths/'.$path->slug.'/enroll')
            ->assertRedirect();

        $this->assertDatabaseHas('learning_path_enrollments', [
            'user_id' => $student->id,
            'learning_path_id' => $path->id,
            'status' => 'enrolled',
        ]);

        // 2. Mark course completed
        Enrollment::where('user_id', $student->id)->where('course_id', $course1->id)
            ->update(['status' => 'completed']);

        $progress = $path->calculateProgress($student);
        $this->assertTrue($progress['is_completed']);
        $this->assertTrue($progress['can_claim_certificate']);

        // 3. Claim certificate
        $this->actingAs($student)
            ->post('/learning-paths/'.$path->slug.'/claim-certificate')
            ->assertRedirect();

        $this->assertDatabaseHas('certificates', [
            'user_id' => $student->id,
            'learning_path_id' => $path->id,
        ]);

        $this->assertDatabaseHas('learning_path_enrollments', [
            'user_id' => $student->id,
            'learning_path_id' => $path->id,
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    public function test_recommendation_engine_excludes_already_enrolled_courses(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $courseA = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স এ - ইতিমধ্যে এনরোলড',
            'slug' => 'course-a-enrolled',
            'short_description' => 'কোর্স এ',
            'description' => 'কোর্স এ বিবরণী',
            'status' => 'published',
            'price' => 100,
            'is_free' => false,
        ]);

        $courseB = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স বি - নতুন সুপারিশ',
            'slug' => 'course-b-recommend',
            'short_description' => 'কোর্স বি',
            'description' => 'কোর্স বি বিবরণী',
            'status' => 'published',
            'price' => 100,
            'is_free' => false,
        ]);

        // Enroll student in Course A
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $courseA->id,
            'status' => 'active',
        ]);

        $recommender = app(RecommendationService::class);
        $recommended = $recommender->recommendForUser($student, 5);

        $recommendedIds = $recommended->pluck('id')->all();

        // CRITICAL ACCEPTANCE CHECK: Enrolled course A MUST be excluded!
        $this->assertNotContains($courseA->id, $recommendedIds, 'Already enrolled course must be excluded from recommendations!');
        $this->assertContains($courseB->id, $recommendedIds, 'Non-enrolled course should be available for recommendation');
    }

    public function test_co_enrollment_recommendation_boost(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        $userTarget = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $courseBase = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'মৌলিক তাজবীদ',
            'slug' => 'base-tajweed',
            'short_description' => 'তাজবীদ বুনিয়াদি',
            'description' => 'মৌলিক তাজবীদ কোর্স বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $courseCoEnrolled = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'উচ্চতর কিরাত',
            'slug' => 'advanced-qirat',
            'short_description' => 'উচ্চতর কিরাত প্রশিক্ষণ',
            'description' => 'উচ্চতর কিরাত বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        $courseUnrelated = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'অন্যান্য বিষয়',
            'slug' => 'other-topic',
            'short_description' => 'অন্যান্য বিষয় সংক্ষেপ',
            'description' => 'অন্যান্য বিষয়ের কোর্স বিবরণী',
            'status' => 'published',
            'price' => 0,
            'is_free' => true,
        ]);

        // User 1 & 2 enrolled in both base and co-enrolled courses
        Enrollment::create(['user_id' => $user1->id, 'course_id' => $courseBase->id, 'status' => 'active']);
        Enrollment::create(['user_id' => $user1->id, 'course_id' => $courseCoEnrolled->id, 'status' => 'active']);

        Enrollment::create(['user_id' => $user2->id, 'course_id' => $courseBase->id, 'status' => 'active']);
        Enrollment::create(['user_id' => $user2->id, 'course_id' => $courseCoEnrolled->id, 'status' => 'active']);

        // Target user enrolls in base course
        Enrollment::create(['user_id' => $userTarget->id, 'course_id' => $courseBase->id, 'status' => 'active']);

        $recommender = app(RecommendationService::class);

        // Check co-enrollment recommendations for CourseBase
        $coEnrolled = $recommender->recommendLearnersAlsoEnrolled($courseBase, $userTarget, 2);
        $this->assertEquals($courseCoEnrolled->id, $coEnrolled->first()->id);

        // Check recommendForUser: co-enrolled course should score higher than unrelated course
        $recommended = $recommender->recommendForUser($userTarget, 2);
        $this->assertEquals($courseCoEnrolled->id, $recommended->first()->id);
    }
}
