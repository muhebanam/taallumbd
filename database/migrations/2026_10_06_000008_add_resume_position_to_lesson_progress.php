<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist video resume position so mobile/web clients can resume lessons.
     */
    public function up(): void
    {
        if (Schema::hasTable('lesson_progress') && ! Schema::hasColumn('lesson_progress', 'last_position_seconds')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->unsignedInteger('last_position_seconds')->default(0)->after('is_completed');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lesson_progress', 'last_position_seconds')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->dropColumn('last_position_seconds');
            });
        }
    }
};
