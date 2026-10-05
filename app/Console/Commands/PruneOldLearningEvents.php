<?php

namespace App\Console\Commands;

use App\Models\LearningEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneOldLearningEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:prune-events {--days=90 : The number of days of raw events to retain} {--force : Force prune without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old raw learning events older than retention threshold (e.g. 90 days) for privacy and storage optimization';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days < 7) {
            $this->error('Retention days must be at least 7 days.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);

        $count = LearningEvent::where('occurred_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info("No raw learning events older than {$days} days found.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete {$count} learning events older than {$cutoff->toDateString()}?")) {
            $this->info('Pruning aborted.');

            return self::SUCCESS;
        }

        $this->info("Pruning {$count} learning events older than {$cutoff->toDateString()}...");

        // Delete in chunks to avoid locking or huge transaction logs
        $deleted = 0;
        do {
            $affected = LearningEvent::where('occurred_at', '<', $cutoff)->limit(1000)->delete();
            $deleted += $affected;
        } while ($affected > 0);

        $this->info("Successfully pruned {$deleted} learning events.");

        return self::SUCCESS;
    }
}
