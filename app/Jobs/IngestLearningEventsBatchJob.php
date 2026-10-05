<?php

namespace App\Jobs;

use App\Models\LearningEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngestLearningEventsBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $events
     */
    public function __construct(public array $events) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->events)) {
            return;
        }

        $now = now();
        $formatted = [];

        foreach ($this->events as $item) {
            $formatted[] = [
                'user_id' => $item['user_id'] ?? null,
                'event_type' => $item['event_type'] ?? 'unknown',
                'subject_type' => $item['subject_type'] ?? null,
                'subject_id' => $item['subject_id'] ?? null,
                'course_id' => $item['course_id'] ?? null,
                'properties' => isset($item['properties']) ? (is_string($item['properties']) ? $item['properties'] : json_encode($item['properties'])) : null,
                'session_id' => $item['session_id'] ?? null,
                'occurred_at' => $item['occurred_at'] ?? $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunk inserts for high throughput and memory efficiency
        foreach (array_chunk($formatted, 250) as $chunk) {
            LearningEvent::insert($chunk);
        }
    }
}
