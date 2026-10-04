<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_courses_page_is_accessible(): void
    {
        $response = $this->get('/courses');
        $response->assertStatus(200);
    }

    public function test_teachers_page_is_accessible(): void
    {
        $response = $this->get('/about/teachers');
        $response->assertStatus(200);
    }

    public function test_fatawa_page_is_accessible(): void
    {
        $response = $this->get('/fatawa');
        $response->assertStatus(200);
    }

    public function test_publications_page_is_accessible(): void
    {
        $response = $this->get('/publications');
        $response->assertStatus(200);
    }

    public function test_articles_page_is_accessible(): void
    {
        $response = $this->get('/articles');
        $response->assertStatus(200);
    }

    public function test_quran_page_is_accessible(): void
    {
        $response = $this->get('/quran');
        $response->assertStatus(200);
    }

    public function test_hadith_page_is_accessible(): void
    {
        $response = $this->get('/hadith');
        $response->assertStatus(200);
    }

    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
    }
}
