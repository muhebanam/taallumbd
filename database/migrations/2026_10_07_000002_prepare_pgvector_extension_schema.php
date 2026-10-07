<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run on PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');
            } catch (\Throwable $e) {
                // If extension is not allowed on shared/restricted db, catch gracefully
                \Illuminate\Support\Facades\Log::warning('pgvector extension creation skipped: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // In PostgreSQL, extensions are usually not dropped automatically in down()
    }
};
