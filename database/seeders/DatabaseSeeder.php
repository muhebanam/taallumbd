<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Production environment detected: Only seeding essential categories.');
            $this->call([
                CategorySeeder::class,
            ]);

            return;
        }

        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            CourseSeeder::class,
            PlatformContentSeeder::class,
        ]);

        $this->command->info('Backfilling curriculum items...');
        Artisan::call('curriculum:backfill-items');
    }
}
