<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            CourseSeeder::class,
            PlatformContentSeeder::class,
        ]);

        $this->command->info('Backfilling curriculum items...');
        \Illuminate\Support\Facades\Artisan::call('curriculum:backfill-items');
    }
}
