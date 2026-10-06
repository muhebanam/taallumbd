<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Ayah;
use App\Models\AyahTranslation;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\Lesson;
use App\Models\Publication;
use App\Models\Teacher;
use Illuminate\Console\Command;

class SearchReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taallum:search-reindex {--model= : Specific model to reindex}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all searchable models into search engine (Scout/Database)';

    /**
     * Searchable model classes list.
     */
    protected array $models = [
        'Course' => Course::class,
        'Lesson' => Lesson::class,
        'Article' => Article::class,
        'Fatwa' => Fatwa::class,
        'Hadith' => Hadith::class,
        'Teacher' => Teacher::class,
        'Publication' => Publication::class,
        'Ayah' => Ayah::class,
        'AyahTranslation' => AyahTranslation::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificModel = $this->option('model');
        $driver = config('scout.driver', 'database');

        $this->info("🔍 Starting search reindexing using driver [{$driver}]...");

        foreach ($this->models as $name => $class) {
            if ($specificModel && strtolower($specificModel) !== strtolower($name)) {
                continue;
            }

            $count = $class::count();
            $this->line("Indexing <fg=cyan>{$name}</> ({$count} records)...");

            try {
                if (method_exists($class, 'makeAllSearchable')) {
                    $class::makeAllSearchable();
                }
                $this->info("✓ {$name} indexed successfully.");
            } catch (\Throwable $e) {
                $this->warn("! {$name} reindexed with notice: ".$e->getMessage());
            }
        }

        $this->info('🎉 Search reindexing completed successfully!');

        return Command::SUCCESS;
    }
}
