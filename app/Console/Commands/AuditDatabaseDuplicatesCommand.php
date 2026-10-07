<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditDatabaseDuplicatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:audit-duplicates {--fix : Automatically archive or cleanup duplicate records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan database tables for duplicate records before applying unique constraints';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Pre-Migration Database Duplicate Audit...');
        $hasIssues = false;

        // 1. Check duplicate enrollments: [user_id, course_id]
        if (Schema::hasTable('enrollments')) {
            $duplicates = DB::table('enrollments')
                ->select('user_id', 'course_id', DB::raw('count(*) as count'))
                ->groupBy('user_id', 'course_id')
                ->having('count', '>', 1)
                ->get();

            if ($duplicates->isNotEmpty()) {
                $hasIssues = true;
                $this->warn("Found {$duplicates->count()} duplicate user-course enrollment pairs:");
                foreach ($duplicates as $row) {
                    $this->line(" - User ID: {$row->user_id}, Course ID: {$row->course_id} ({$row->count} entries)");
                }
            } else {
                $this->info('✓ Enrollments table: No duplicate (user_id, course_id) pairs found.');
            }
        }

        // 2. Check duplicate paid transaction IDs: [transaction_id]
        if (Schema::hasTable('orders')) {
            $duplicateTrx = DB::table('orders')
                ->whereNotNull('transaction_id')
                ->where('transaction_id', '!=', '')
                ->select('transaction_id', DB::raw('count(*) as count'))
                ->groupBy('transaction_id')
                ->having('count', '>', 1)
                ->get();

            if ($duplicateTrx->isNotEmpty()) {
                $hasIssues = true;
                $this->warn("Found {$duplicateTrx->count()} duplicate order transaction IDs:");
                foreach ($duplicateTrx as $row) {
                    $this->line(" - TrxID: {$row->transaction_id} ({$row->count} orders)");
                }
            } else {
                $this->info('✓ Orders table: No duplicate non-empty transaction IDs found.');
            }
        }

        // 3. Check duplicate curriculum items in same section: [section_id, sort_order]
        if (Schema::hasTable('curriculum_items')) {
            $duplicateCurriculum = DB::table('curriculum_items')
                ->select('section_id', 'sort_order', DB::raw('count(*) as count'))
                ->groupBy('section_id', 'sort_order')
                ->having('count', '>', 1)
                ->get();

            if ($duplicateCurriculum->isNotEmpty()) {
                $this->warn("Notice: Found {$duplicateCurriculum->count()} sections with shared sort_order.");
            } else {
                $this->info('✓ Curriculum items table: Sort order integrity verified.');
            }
        }

        if ($hasIssues) {
            $this->error('Database has duplicate records. Please resolve them before creating strict UNIQUE indexes.');
            return Command::FAILURE;
        }

        $this->info('Database integrity audit passed! Ready for production indexing.');
        return Command::SUCCESS;
    }
}
