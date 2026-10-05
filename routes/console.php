<?php

use Illuminate\Support\Facades\Schedule;

// Automatically reconcile pending payments older than 30 minutes
Schedule::command('payments:reconcile')->everyThirtyMinutes();

// Daily analytics aggregation at 00:05
Schedule::command('analytics:aggregate')->dailyAt('00:05');

// Weekly data retention cleanup (prunes raw events older than 90 days)
Schedule::command('analytics:prune-events --days=90 --force')->weekly();
