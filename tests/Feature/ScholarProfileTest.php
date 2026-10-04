<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_scholars_directory(): void
    {
        $response = $this->get('/about/teachers');
        $response->assertStatus(200);
    }

    public function test_can_view_individual_scholar_profile(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => 'শায়খ আহমাদুল্লাহ',
            'slug' => 'shaykh-ahmadullah',
            'designation' => 'বিশিষ্ট দাঈ ও স্কলার',
            'headline' => 'চেয়ারম্যান, আস-সুন্নাহ ফাউন্ডেশন',
            'status' => 'active',
        ]);

        $response = $this->get('/teachers/'.$teacher->slug);
        $response->assertStatus(200);
    }

    public function test_inactive_scholar_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => 'নিষ্ক্রিয় শিক্ষক',
            'slug' => 'inactive-teacher',
            'designation' => 'শিক্ষক',
            'status' => 'inactive',
        ]);

        $response = $this->get('/teachers/'.$teacher->slug);
        $response->assertStatus(404);
    }
}
