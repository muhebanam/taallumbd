<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        User::factory()->create(['name' => 'অ্যাডমিন', 'email' => 'admin@taallumbd.local', 'role' => 'admin']);
        User::factory()->create(['name' => 'মুফতী আব্দুল্লাহ', 'email' => 'instructor@taallumbd.local', 'role' => 'instructor']);
        User::factory()->create(['name' => 'মাওলানা ইউসুফ', 'email' => 'instructor2@taallumbd.local', 'role' => 'instructor']);
        User::factory()->create(['name' => 'শিক্ষার্থী', 'email' => 'student@taallumbd.local', 'role' => 'student']);
        User::factory()->count(9)->create(['role' => 'student']);
    }
}
