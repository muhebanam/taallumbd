<?php

use Illuminate\Support\Facades\Schedule;

// Automatically reconcile pending payments older than 30 minutes
Schedule::command('payments:reconcile')->everyThirtyMinutes();
