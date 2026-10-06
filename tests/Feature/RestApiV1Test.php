<?php

namespace Tests\Feature;

use App\Models\Ayah;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\HadithBook;
use App\Models\HadithChapter;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Surah;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RestApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api_and_receives_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'তামিম ইকবাল',
            'email' => 'tamim@example.com',
            'phone' => '01711223344',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'device_name' => 'Flutter-Pixel-8',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'role',
                        'avatar_url',
                        'is_instructor',
                        'is_admin',
                    ],
                    'token',
                    'token_type',
                    'abilities',
                ],
            ]);

        $this->assertEquals(true, $response->json('success'));
        $this->assertDatabaseHas('users', [
            'email' => 'tamim@example.com',
            'role' => 'student',
        ]);
    }

    public function test_user_registration_validation_fails_with_standard_json(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_user_can_login_with_device_name_and_correct_role_abilities(): void
    {
        $user = User::factory()->instructor()->create([
            'email' => 'scholar@taallum.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'scholar@taallum.com',
            'password' => 'password123',
            'device_name' => 'iPad Pro 12.9',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                    'abilities',
                ],
            ]);

        $this->assertContains('instructor', $response->json('data.abilities'));
        $this->assertContains('courses:manage', $response->json('data.abilities'));
    }

    public function test_user_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'student@taallum.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@taallum.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'avatar_url',
                ],
            ]);
    }

    public function test_unauthenticated_user_access_returns_standard_401_json(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে লগইন করুন।',
            ]);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'সফলভাবে লগআউট সম্পন্ন হয়েছে।',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_courses_index_returns_paginated_json(): void
    {
        $category = Category::factory()->create(['name' => 'আকিদাহ ও উলূম']);
        $instructor = User::factory()->instructor()->create();

        Course::factory()->published()->create([
            'category_id' => $category->id,
            'instructor_id' => $instructor->id,
            'title' => 'সহীহ আকিদাহ পরিচিতি',
            'level' => 'beginner',
            'price' => 0,
        ]);

        Course::factory()->published()->create([
            'category_id' => $category->id,
            'instructor_id' => $instructor->id,
            'title' => 'উসুলুল ফিকহ ১ম খণ্ড',
            'level' => 'intermediate',
            'price' => 1200,
        ]);

        $response = $this->getJson('/api/v1/courses?level=beginner');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'price',
                        'level',
                        'is_free',
                        'category',
                        'instructor',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('সহীহ আকিদাহ পরিচিতি', $response->json('data.0.title'));
    }

    public function test_course_show_returns_full_curriculum_and_details(): void
    {
        $course = Course::factory()->published()->create([
            'title' => 'কুরআন তাদাব্বুর কোর্স',
            'slug' => 'quran-tadabbur-course',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'প্রথম অধ্যায়: ভূমিকা',
            'order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'তাদাব্বুর কী ও কেন?',
            'slug' => 'what-is-tadabbur',
            'is_preview' => true,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'content' => 'ভূমিকা ক্লাসের পূর্ণ বিবরণী...',
        ]);

        $response = $this->getJson('/api/v1/courses/'.$course->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'sections' => [
                        '*' => [
                            'id',
                            'title',
                            'lessons',
                        ],
                    ],
                ],
            ]);

        $this->assertEquals('কুরআন তাদাব্বুর কোর্স', $response->json('data.title'));
    }

    public function test_curriculum_and_free_preview_lesson_accessible_publicly(): void
    {
        $course = Course::factory()->published()->create();
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'অধ্যায় ১', 'order' => 1]);

        $freeLesson = Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'ফ্রি প্রিভিউ পাঠ',
            'slug' => 'free-preview-lesson',
            'is_preview' => true,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'content' => 'ফ্রি পাঠের বিস্তারিত বিষয়বস্তু...',
        ]);

        $response = $this->getJson('/api/v1/lessons/'.$freeLesson->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'is_free_preview',
                    'is_accessible',
                    'content',
                    'video_stream_url',
                ],
            ]);

        $this->assertTrue($response->json('data.is_accessible'));
    }

    public function test_locked_lesson_returns_403_for_unenrolled_student(): void
    {
        $course = Course::factory()->published()->create(['price' => 1000]);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'অধ্যায় ১', 'order' => 1]);

        $lockedLesson = Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'লক করা প্রিমিয়াম পাঠ',
            'slug' => 'premium-locked-lesson',
            'is_preview' => false,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'content' => 'গোপন লেকচার নোটস...',
        ]);

        $student = User::factory()->student()->create();

        $response = $this->actingAs($student, 'sanctum')->getJson('/api/v1/lessons/'.$lockedLesson->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'এই পাঠটি দেখতে অনুগ্রহ করে কোর্সে এনরোল করুন।',
            ]);
    }

    public function test_enrolled_student_can_access_locked_lesson(): void
    {
        $course = Course::factory()->published()->create(['price' => 1000]);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'অধ্যায় ১', 'order' => 1]);

        $lockedLesson = Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'লক করা প্রিমিয়াম পাঠ',
            'slug' => 'premium-locked-lesson',
            'is_preview' => false,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'content' => 'গোপন লেকচার নোটস...',
        ]);

        $student = User::factory()->student()->create();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student, 'sanctum')->getJson('/api/v1/lessons/'.$lockedLesson->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'is_accessible',
                    'content',
                    'video_stream_url',
                ],
            ]);

        $this->assertTrue($response->json('data.is_accessible'));
    }

    public function test_student_can_update_lesson_progress_and_completion(): void
    {
        $course = Course::factory()->published()->create();
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'অধ্যায় ১', 'order' => 1]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => 'পাঠ ১',
            'slug' => 'lesson-1',
        ]);

        $student = User::factory()->student()->create();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student, 'sanctum')->postJson('/api/v1/lessons/'.$lesson->id.'/progress', [
            'is_completed' => true,
            'last_watched_seconds' => 450,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'lesson_id',
                    'is_completed',
                    'last_watched_seconds',
                    'course_progress_percentage',
                ],
            ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'is_completed' => 1,
        ]);
    }

    public function test_student_can_fetch_course_progress_percentage(): void
    {
        $course = Course::factory()->published()->create();
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'অধ্যায় ১', 'order' => 1]);
        $lesson1 = Lesson::create(['course_id' => $course->id, 'section_id' => $section->id, 'title' => 'পাঠ ১', 'slug' => 'l-1']);
        $lesson2 = Lesson::create(['course_id' => $course->id, 'section_id' => $section->id, 'title' => 'পাঠ ২', 'slug' => 'l-2']);

        $student = User::factory()->student()->create();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        LessonProgress::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson1->id,
            'is_completed' => true,
        ]);

        $response = $this->actingAs($student, 'sanctum')->getJson('/api/v1/courses/'.$course->id.'/progress');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'course_id' => $course->id,
                    'is_enrolled' => true,
                    'total_lessons' => 2,
                    'completed_lessons' => 1,
                ],
            ]);
    }

    public function test_quiz_details_attempts_and_submission(): void
    {
        $course = Course::factory()->published()->create();
        $student = User::factory()->student()->create();
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'status' => 'active', 'enrolled_at' => now()]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'ঈমান ও তাওহীদ কুইজ',
            'total_marks' => 10,
            'pass_marks' => 6,
        ]);

        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'তাওহীদ কত প্রকার?',
            'type' => 'single_choice',
            'marks' => 10,
        ]);

        $correctOpt = QuizOption::create([
            'question_id' => $q1->id,
            'option_text' => '৩ প্রকার',
            'is_correct' => true,
        ]);

        $wrongOpt = QuizOption::create([
            'question_id' => $q1->id,
            'option_text' => '৫ প্রকার',
            'is_correct' => false,
        ]);

        // 1. Show quiz
        $showRes = $this->actingAs($student, 'sanctum')->getJson('/api/v1/quizzes/'.$quiz->id);
        $showRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'total_marks',
                    'pass_marks',
                    'questions',
                ],
            ]);

        // 2. Submit quiz
        $submitRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/quizzes/'.$quiz->id.'/submit', [
            'answers' => [
                $q1->id => [$correctOpt->id],
            ],
        ]);

        $submitRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'score' => 10,
                    'status' => 'passed',
                    'passed' => true,
                ],
            ]);

        // 3. Attempts history
        $attemptsRes = $this->actingAs($student, 'sanctum')->getJson('/api/v1/quizzes/'.$quiz->id.'/attempts');
        $attemptsRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'score',
                        'status',
                        'passed',
                    ],
                ],
            ]);
    }

    public function test_payment_initiate_for_free_course_auto_enrolls(): void
    {
        $freeCourse = Course::factory()->published()->create(['price' => 0]);
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'course_id' => $freeCourse->id,
            'payment_method' => 'free',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_free' => true,
                'status' => 'completed',
            ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $freeCourse->id,
        ]);
    }

    public function test_payment_initiate_for_paid_course_generates_order(): void
    {
        $paidCourse = Course::factory()->published()->create(['price' => 1500]);
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'course_id' => $paidCourse->id,
            'payment_method' => 'bkash',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'order_id',
                'order_number',
                'amount',
                'currency',
                'gateway',
                'gateway_url',
            ]);

        $this->assertEquals(1500, $response->json('amount'));
    }

    public function test_teachers_directory_and_follow_toggle(): void
    {
        $user = User::factory()->instructor()->create(['name' => 'শায়খ আহমদুল্লাহ']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => 'শায়খ আহমদুল্লাহ',
            'designation' => 'মুহাদ্দিস ও গবেষক',
            'slug' => 'shaykh-ahmadullah',
            'featured' => true,
            'status' => 'active',
            'is_verified' => true,
            'bio' => 'প্রখ্যাত ইসলামিক স্কলার ও গবেষক।',
        ]);

        $student = User::factory()->student()->create();

        // 1. Directory
        $listRes = $this->getJson('/api/v1/teachers');
        $listRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);

        // 2. Detail
        $detailRes = $this->getJson('/api/v1/teachers/'.$teacher->slug);
        $detailRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'teacher' => [
                    'slug' => 'shaykh-ahmadullah',
                ],
            ]);

        // 3. Follow
        $followRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/teachers/'.$teacher->slug.'/follow');
        $followRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_following' => true,
            ]);

        // 4. Unfollow
        $unfollowRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/teachers/'.$teacher->slug.'/follow');
        $unfollowRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_following' => false,
            ]);
    }

    public function test_public_certificate_verification_by_code(): void
    {
        $student = User::factory()->student()->create(['name' => 'মো: রাশেদ']);
        $course = Course::factory()->published()->create(['title' => 'তাজবীদুল কুরআন']);
        $cert = Certificate::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_no' => 'TLM-2026-998877',
            'issued_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/certificates/verify/'.$cert->certificate_no);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'valid',
                'certificate' => [
                    'certificate_no' => 'TLM-2026-998877',
                    'student_name' => 'মো: রাশেদ',
                    'course_title' => 'তাজবীদুল কুরআন',
                ],
            ]);
    }

    public function test_quran_surahs_and_memorization_progress(): void
    {
        $surah = Surah::create([
            'number' => 1,
            'name_arabic' => 'الفاتحة',
            'name_bangla' => 'আল-ফাতিহা',
            'name_transliteration' => 'Al-Fatihah',
            'ayah_count' => 7,
            'revelation_type' => 'Meccan',
            'revelation_order' => 5,
        ]);

        $ayah = Ayah::create([
            'surah_id' => $surah->id,
            'number' => 1,
            'number_in_quran' => 1,
            'text_uthmani' => 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ',
            'juz' => 1,
            'sajdah' => false,
        ]);

        $student = User::factory()->student()->create();

        // 1. Surahs list
        $surahsRes = $this->getJson('/api/v1/quran/surahs');
        $surahsRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['number', 'name_arabic', 'name_bangla', 'ayah_count'],
                ],
            ]);

        // 2. Surah detail with Ayahs
        $surahDetailRes = $this->getJson('/api/v1/quran/surahs/1');
        $surahDetailRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'surah' => [
                    'number' => 1,
                    'name_bangla' => 'আল-ফাতিহা',
                ],
            ]);

        // 3. Update memorization progress
        $memRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/quran/progress', [
            'surah_number' => 1,
            'ayah_from' => 1,
            'ayah_to' => 7,
            'status' => 'memorized',
        ]);

        $memRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'কুরআন অধ্যায়ন ও হিফজ প্রগ্রেস সংরক্ষিত হয়েছে।',
            ]);
    }

    public function test_hadith_books_and_hadith_details(): void
    {
        $book = HadithBook::create([
            'name_arabic' => 'صحيح البخاري',
            'name_bangla' => 'সহীহ বুখারী',
            'name_english' => 'Sahih al-Bukhari',
            'author' => 'ইমাম বুখারী (রহ.)',
            'slug' => 'sahih-bukhari',
            'total_hadith' => 7563,
        ]);

        $chapter = HadithChapter::create([
            'book_id' => $book->id,
            'number' => 1,
            'title_arabic' => 'بدء الوحي',
            'title_bangla' => 'ওহীর সূচনা অধ্যায়',
        ]);

        $hadith = Hadith::create([
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'number' => 1,
            'text_arabic' => 'إِنَّمَا الأَعْمَالُ بِالنِّইَّاتِ',
            'text_bangla' => 'সকল কাজ নিয়তের ওপর নির্ভরশীল...',
            'grade' => 'সহীহ',
        ]);

        // 1. Books list
        $booksRes = $this->getJson('/api/v1/hadith/books');
        $booksRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name_bangla', 'slug'],
                ],
            ]);

        // 2. Hadith detail
        $hadithRes = $this->getJson('/api/v1/hadith/'.$hadith->id);
        $hadithRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'hadith' => [
                    'id' => $hadith->id,
                    'grade' => 'সহীহ',
                ],
            ]);
    }

    public function test_fatawa_list_show_and_submit_question(): void
    {
        $category = Category::factory()->create(['name' => 'মুআমালাত']);
        $scholar = User::factory()->instructor()->create();
        $fatwa = Fatwa::create([
            'category_id' => $category->id,
            'question_title' => 'অনলাইনে ক্রয় বিক্রয়ের শরয়ী বিধান',
            'question_body' => 'অনলাইনে ড্রপশিপিং কি জায়েজ?',
            'answer_body' => 'নির্দিষ্ট শর্তসাপেক্ষে ড্রপশিপিং জায়েজ...',
            'status' => 'answered',
            'answered_by' => $scholar->id,
            'answered_at' => now(),
        ]);

        $student = User::factory()->student()->create();

        // 1. List
        $listRes = $this->getJson('/api/v1/fatawa');
        $listRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'question', 'answer', 'status'],
                ],
            ]);

        // 2. Show
        $showRes = $this->getJson('/api/v1/fatawa/'.$fatwa->id);
        $showRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'fatwa' => [
                    'id' => $fatwa->id,
                    'title' => 'অনলাইনে ক্রয় বিক্রয়ের শরয়ী বিধান',
                ],
            ]);

        // 3. Ask question
        $askRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/fatawa/ask', [
            'question_title' => 'ডিজিটাল মুদ্রার বিধান',
            'question_body' => 'ক্রিপ্টোকারেন্সি লেনদেন কি শরীয়াহসম্মতভাবে বৈধ?',
            'category_id' => $category->id,
        ]);

        $askRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'আপনার প্রশ্নটি সফলভাবে জমা নেওয়া হয়েছে। বিজ্ঞ মুফতি সাহেব পর্যালোচনা করে উত্তর প্রদান করবেন।',
            ]);
    }

    public function test_notifications_and_mobile_device_token_registration(): void
    {
        $user = User::factory()->student()->create();

        // 1. Register device token
        $regRes = $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices/register', [
            'token' => 'fcm_sample_device_token_123456789',
            'platform' => 'android',
        ]);

        $regRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'মোবাইল ডিভাইস টোকেন সফলভাবে নিবন্ধিত হয়েছে।',
            ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm_sample_device_token_123456789',
            'platform' => 'android',
        ]);

        // 2. Notifications index
        $notifRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications');
        $notifRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'unread_count',
                'data',
                'meta',
            ]);
    }

    public function test_subscription_plans_and_status(): void
    {
        $user = User::factory()->student()->create();

        $plansRes = $this->getJson('/api/v1/subscriptions/plans');
        $plansRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'price_bdt', 'interval'],
                ],
            ]);

        $statusRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/subscriptions/status');
        $statusRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'is_subscribed',
                'user',
            ]);
    }

    public function test_scramble_openapi_docs_endpoint_is_accessible(): void
    {
        $response = $this->get('/docs/api.json');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'openapi',
                'info' => [
                    'title',
                    'version',
                ],
                'paths',
            ]);

        $this->assertEquals('Taallum BD API Documentation', $response->json('info.title'));
    }
}
