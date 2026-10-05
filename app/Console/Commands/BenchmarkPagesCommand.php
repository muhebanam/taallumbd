<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\HadithBook;
use App\Models\Publication;
use App\Models\Surah;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BenchmarkPagesCommand extends Command
{
    protected $signature = 'app:benchmark-pages {--output-json= : Path to save JSON metrics}';

    protected $description = 'Benchmark the 15 key pages of Taallum BD for query counts, execution time, and duplicate queries';

    public function handle(): int
    {
        $this->info('Starting Performance Benchmark for 15 Key Pages of Taallum BD...');

        // Ensure baseline seed data exists
        $this->ensureSeedData();

        $student = User::where('role', 'student')->first() ?: User::factory()->create(['role' => 'student']);
        $publishedCourse = Course::published()->first();
        $scholar = Teacher::where('status', 'active')->first();
        $fatwa = Fatwa::published()->first();
        $article = Article::published()->first();
        $hadithBook = HadithBook::first();
        $surah = Surah::where('number', 1)->first();

        $routes = [
            [
                'name' => 'Home Page',
                'url' => '/',
                'auth' => false,
            ],
            [
                'name' => 'Courses Index',
                'url' => '/courses',
                'auth' => false,
            ],
            [
                'name' => 'Course Detail',
                'url' => $publishedCourse ? "/courses/{$publishedCourse->slug}" : '/courses',
                'auth' => false,
            ],
            [
                'name' => 'Scholars Directory',
                'url' => '/teachers',
                'auth' => false,
            ],
            [
                'name' => 'Scholar Profile',
                'url' => $scholar ? "/scholars/{$scholar->slug}" : '/teachers',
                'auth' => false,
            ],
            [
                'name' => 'Fatawa Index',
                'url' => '/fatawa',
                'auth' => false,
            ],
            [
                'name' => 'Fatawa Detail',
                'url' => $fatwa ? "/fatawa/{$fatwa->id}" : '/fatawa',
                'auth' => false,
            ],
            [
                'name' => 'Publications Index',
                'url' => '/publications',
                'auth' => false,
            ],
            [
                'name' => 'Articles Index',
                'url' => '/articles',
                'auth' => false,
            ],
            [
                'name' => 'Article Detail',
                'url' => $article ? "/articles/{$article->slug}" : '/articles',
                'auth' => false,
            ],
            [
                'name' => 'Quran Index',
                'url' => '/quran',
                'auth' => false,
            ],
            [
                'name' => 'Surah Detail (Al-Fatihah)',
                'url' => '/quran/1',
                'auth' => false,
            ],
            [
                'name' => 'Hadith Index',
                'url' => '/hadith',
                'auth' => false,
            ],
            [
                'name' => 'Hadith Book (Bukhari)',
                'url' => $hadithBook ? "/hadith/{$hadithBook->slug}" : '/hadith/sahih-bukhari',
                'auth' => false,
            ],
            [
                'name' => 'Student Dashboard',
                'url' => '/dashboard',
                'auth' => true,
                'user' => $student,
            ],
        ];

        $results = [];
        $currentQueries = [];
        $currentDbTime = 0.0;
        $isListening = false;

        DB::listen(function ($query) use (&$currentQueries, &$currentDbTime, &$isListening) {
            if ($isListening) {
                $currentQueries[] = $query->sql;
                $currentDbTime += $query->time;
            }
        });

        foreach ($routes as $route) {
            $currentQueries = [];
            $currentDbTime = 0.0;

            if ($route['auth']) {
                Auth::login($route['user']);
            } else {
                Auth::logout();
            }

            $startTime = microtime(true);
            $isListening = true;

            try {
                $request = Request::create($route['url'], 'GET', [], [], [], [
                    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml',
                ]);
                $response = app()->handle($request);
                $statusCode = $response->getStatusCode();
            } catch (\Throwable $e) {
                $statusCode = 500;
                $this->error("Error on {$route['url']}: {$e->getMessage()}");
            } finally {
                $isListening = false;
            }
            $totalPageTime = round((microtime(true) - $startTime) * 1000, 2);

            // Calculate duplicates
            $counts = array_count_values($currentQueries);
            $duplicateCount = 0;
            foreach ($counts as $count) {
                if ($count > 1) {
                    $duplicateCount += ($count - 1);
                }
            }

            $results[] = [
                'name' => $route['name'],
                'url' => $route['url'],
                'status' => $statusCode,
                'query_count' => count($currentQueries),
                'duplicates' => $duplicateCount,
                'db_time_ms' => round($currentDbTime, 2),
                'page_time_ms' => $totalPageTime,
            ];
        }

        $this->table(
            ['Page Name', 'URL', 'Status', 'Queries', 'Duplicates', 'DB Time (ms)', 'Total Time (ms)'],
            array_map(fn ($r) => [
                $r['name'],
                $r['url'],
                $r['status'],
                $r['query_count'],
                $r['duplicates'],
                $r['db_time_ms'],
                $r['page_time_ms'],
            ], $results)
        );

        if ($jsonPath = $this->option('output-json')) {
            file_put_contents($jsonPath, json_encode($results, JSON_PRETTY_PRINT));
            $this->info("Saved metrics to: {$jsonPath}");
        }

        return Command::SUCCESS;
    }

    private function ensureSeedData(): void
    {
        if (Course::count() === 0) {
            $this->line('Seeding courses for realistic benchmark...');
            Course::factory()->count(10)->create(['status' => 'published']);
        }

        if (Teacher::count() === 0) {
            $this->line('Seeding teachers for realistic benchmark...');
            Teacher::factory()->count(6)->create(['status' => 'active']);
        }

        if (Fatwa::count() === 0) {
            $this->line('Seeding fatawa for realistic benchmark...');
            Fatwa::factory()->count(10)->create(['status' => 'published']);
        }

        if (Article::count() === 0) {
            $this->line('Seeding articles for realistic benchmark...');
            Article::factory()->count(10)->create(['status' => 'published']);
        }

        if (Publication::count() === 0) {
            $this->line('Seeding publications for realistic benchmark...');
            Publication::factory()->count(8)->create(['status' => 'published']);
        }
    }
}
