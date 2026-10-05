<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_and_instructor_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/instructor/dashboard')->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_admin_users_list(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_instructor_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/instructor/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_instructor_course_builder(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/instructor/courses/create');

        $response->assertStatus(403);
    }

    public function test_instructor_can_access_instructor_dashboard(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->get('/instructor/dashboard');

        $response->assertStatus(200);
    }

    public function test_instructor_cannot_access_admin_dashboard(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_instructor_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/instructor/dashboard');

        $response->assertStatus(200);
    }
}
