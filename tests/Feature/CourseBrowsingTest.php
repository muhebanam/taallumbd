<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_courses_listing(): void
    {
        $response = $this->get('/courses');
        $response->assertStatus(200);
    }

    public function test_can_filter_courses_by_category(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'tafsir', 'type' => 'course'],
            ['name' => 'তাফসীর ও উলূমুল কুরআন', 'status' => 'active']
        );

        $response = $this->get('/courses/category/'.$category->slug);
        $response->assertStatus(200);
    }

    public function test_can_view_published_course_detail(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $category = Category::firstOrCreate(
            ['slug' => 'fiqh', 'type' => 'course'],
            ['name' => 'ফিকহ', 'status' => 'active']
        );

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'সহজ ফিকহুস সুন্নাহ',
            'slug' => 'easy-fiqh-us-sunnah',
            'short_description' => 'দৈনন্দিন জীবনের প্রয়োজনীয় ফিকহ',
            'description' => 'বিস্তারিত ফিকহ কোর্স',
            'price' => 0,
            'is_free' => true,
            'status' => 'published',
        ]);

        $response = $this->get('/courses/'.$course->slug);
        $response->assertStatus(200);
    }

    public function test_draft_course_returns_404(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'ড্রাফট কোর্স',
            'slug' => 'draft-course',
            'short_description' => 'ড্রাফট বর্ণনা',
            'description' => 'ড্রাফট বিস্তারিত',
            'price' => 0,
            'is_free' => true,
            'status' => 'draft',
        ]);

        $response = $this->get('/courses/'.$course->slug);
        $response->assertStatus(404);
    }
}
