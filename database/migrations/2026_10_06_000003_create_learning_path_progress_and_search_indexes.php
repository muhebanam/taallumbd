<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add prerequisite_course_id to learning_path_courses pivot
        if (Schema::hasTable('learning_path_courses')) {
            Schema::table('learning_path_courses', function (Blueprint $table) {
                if (! Schema::hasColumn('learning_path_courses', 'prerequisite_course_id')) {
                    $table->foreignId('prerequisite_course_id')
                        ->nullable()
                        ->after('course_id')
                        ->constrained('courses')
                        ->nullOnDelete();
                }
            });
        }

        // 2. Allow certificates to be linked to learning_paths and make course_id nullable
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                try {
                    $table->foreignId('course_id')->nullable()->change();
                } catch (Throwable $e) {
                    // Ignored on engines that do not support inline foreign nullability change
                }

                if (! Schema::hasColumn('certificates', 'learning_path_id')) {
                    $table->foreignId('learning_path_id')
                        ->nullable()
                        ->after('course_id')
                        ->constrained('learning_paths')
                        ->nullOnDelete();
                }
            });
        }

        // 3. Learning Path Enrollments and Progress tracking
        if (! Schema::hasTable('learning_path_enrollments')) {
            Schema::create('learning_path_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
                $table->string('status', 30)->default('enrolled'); // enrolled, in_progress, completed
                $table->unsignedInteger('completed_courses_count')->default(0);
                $table->unsignedInteger('total_courses_count')->default(0);
                $table->unsignedTinyInteger('progress_percentage')->default(0);
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('certificate_id')->nullable()->constrained('certificates')->nullOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'learning_path_id']);
                $table->index(['user_id', 'status']);
            });
        }

        // 4. PostgreSQL Full-Text Search (GIN) indexes for Neon/production
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement("CREATE INDEX IF NOT EXISTS courses_fts_gin ON courses USING gin(to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(short_description, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS lessons_title_fts_gin ON lessons USING gin(to_tsvector('simple', coalesce(title, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS articles_fts_gin ON articles USING gin(to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(excerpt, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS fatawa_fts_gin ON fatawa USING gin(to_tsvector('simple', coalesce(question_title, '') || ' ' || coalesce(question_body, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS hadiths_fts_gin ON hadiths USING gin(to_tsvector('simple', coalesce(text_bangla, '') || ' ' || coalesce(narrator, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS teachers_fts_gin ON teachers USING gin(to_tsvector('simple', coalesce(name, '') || ' ' || coalesce(designation, '')));");
                DB::statement("CREATE INDEX IF NOT EXISTS publications_fts_gin ON publications USING gin(to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(description, '')));");
            } catch (Throwable $e) {
                // Ignore if permission or extension restriction in certain managed pgsql
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('DROP INDEX IF EXISTS courses_fts_gin');
                DB::statement('DROP INDEX IF EXISTS lessons_title_fts_gin');
                DB::statement('DROP INDEX IF EXISTS articles_fts_gin');
                DB::statement('DROP INDEX IF EXISTS fatawa_fts_gin');
                DB::statement('DROP INDEX IF EXISTS hadiths_fts_gin');
                DB::statement('DROP INDEX IF EXISTS teachers_fts_gin');
                DB::statement('DROP INDEX IF EXISTS publications_fts_gin');
            } catch (Throwable $e) {
            }
        }

        Schema::dropIfExists('learning_path_enrollments');

        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'learning_path_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                try {
                    $table->dropForeign(['learning_path_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('learning_path_id');
            });
        }

        if (Schema::hasTable('learning_path_courses') && Schema::hasColumn('learning_path_courses', 'prerequisite_course_id')) {
            Schema::table('learning_path_courses', function (Blueprint $table) {
                try {
                    $table->dropForeign(['prerequisite_course_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('prerequisite_course_id');
            });
        }
    }
};
