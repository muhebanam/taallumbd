<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fatawa', function (Blueprint $table) {
            if (! Schema::hasColumn('fatawa', 'assigned_scholar_id')) {
                $table->foreignId('assigned_scholar_id')->nullable()->after('teacher_id')->constrained('teachers')->nullOnDelete();
            }
            if (! Schema::hasColumn('fatawa', 'related_course_id')) {
                $table->foreignId('related_course_id')->nullable()->after('assigned_scholar_id')->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('fatawa', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('fatawa', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('status');
            }
            if (! Schema::hasColumn('fatawa', 'references')) {
                $table->text('references')->nullable()->after('answer_body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fatawa', function (Blueprint $table) {
            if (Schema::hasColumn('fatawa', 'assigned_scholar_id')) {
                $table->dropForeign(['assigned_scholar_id']);
                $table->dropColumn('assigned_scholar_id');
            }
            if (Schema::hasColumn('fatawa', 'related_course_id')) {
                $table->dropForeign(['related_course_id']);
                $table->dropColumn('related_course_id');
            }
            $table->dropColumn(['answered_at', 'views_count', 'references']);
        });
    }
};
