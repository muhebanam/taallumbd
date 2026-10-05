<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$checks = [
    'Curriculum Items by Section' => 'SELECT * FROM curriculum_items WHERE section_id = 1 ORDER BY sort_order',
    'Lesson Progress by User & Course' => 'SELECT * FROM lesson_progress WHERE user_id = 1 AND course_id = 1 AND is_completed = 1',
    'Fatawa by Status, Category & PublishedAt' => "SELECT * FROM fatawa WHERE status = 'published' AND category_id = 1 ORDER BY published_at DESC",
    'Courses by Status & Category' => "SELECT * FROM courses WHERE status = 'published' AND category_id = 1",
    'Articles by Status & Category' => "SELECT * FROM articles WHERE status = 'published' AND category_id = 1 ORDER BY published_at DESC",
];

echo '=== Index Verification via EXPLAIN QUERY PLAN ==='.PHP_EOL;

foreach ($checks as $title => $sql) {
    echo PHP_EOL."Query: {$title}".PHP_EOL;
    try {
        $plan = DB::select("EXPLAIN QUERY PLAN {$sql}");
        foreach ($plan as $row) {
            $detail = $row->detail ?? json_encode($row);
            echo "  -> {$detail}".PHP_EOL;
        }
    } catch (Throwable $e) {
        echo '  -> Error: '.$e->getMessage().PHP_EOL;
    }
}
