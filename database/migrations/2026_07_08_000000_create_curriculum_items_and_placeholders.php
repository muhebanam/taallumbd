<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Step 1: Extend courses.status to accept 'coming_soon' ─────────────
        // Laravel's ->enum() stores as VARCHAR with a CHECK constraint in PostgreSQL,
        // and as an actual ENUM type in MySQL.
        // We handle both drivers here:
        if (DB::getDriverName() === 'pgsql') {
            // Drop the old check constraint (Laravel names it <table>_<col>_check)
            DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_status_check');
            // Add new constraint that includes 'coming_soon'
            DB::statement("ALTER TABLE courses ADD CONSTRAINT courses_status_check
                CHECK (status::text = ANY (ARRAY[
                    'draft'::text,
                    'pending'::text,
                    'published'::text,
                    'rejected'::text,
                    'coming_soon'::text
                ]))");
        } elseif (DB::getDriverName() === 'mysql') {
            // MySQL / MariaDB – modify ENUM in place
            DB::statement("ALTER TABLE courses MODIFY COLUMN status
                ENUM('draft','pending','published','rejected','coming_soon')
                NOT NULL DEFAULT 'draft'");
        }

        // ── Step 2: Add extra scheduling columns to courses ───────────────────
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'enrollment_limit')) {
                $table->unsignedInteger('enrollment_limit')->nullable()->after('status');
            }
            if (! Schema::hasColumn('courses', 'enrollment_start')) {
                $table->timestamp('enrollment_start')->nullable()->after('enrollment_limit');
            }
            if (! Schema::hasColumn('courses', 'enrollment_end')) {
                $table->timestamp('enrollment_end')->nullable()->after('enrollment_start');
            }
            if (! Schema::hasColumn('courses', 'completion_requirements')) {
                $table->json('completion_requirements')->nullable()->after('enrollment_end');
            }
        });

        // ── Step 3: Supplementary tables ─────────────────────────────────────
        if (! Schema::hasTable('resources')) {
            Schema::create('resources', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('file_path')->nullable();
                $table->string('url')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('live_classes')) {
            Schema::create('live_classes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('meeting_url')->nullable();
                $table->timestamp('start_time')->nullable();
                $table->unsignedInteger('duration')->nullable(); // minutes
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('curriculum_items')) {
            Schema::create('curriculum_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->foreignId('section_id')->nullable()->constrained('course_sections')->nullOnDelete();
                $table->string('itemable_type');
                $table->unsignedBigInteger('itemable_id');
                $table->string('item_type'); // lesson, quiz, assignment, resource, live_class
                $table->string('title_snapshot')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_required')->default(true);
                $table->boolean('is_preview')->default(false);
                $table->string('drip_type')->nullable();
                $table->string('drip_value')->nullable();
                $table->timestamps();

                $table->unique(['course_id', 'itemable_type', 'itemable_id'], 'course_itemable_unique');
                $table->index(['itemable_type', 'itemable_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_items');
        Schema::dropIfExists('live_classes');
        Schema::dropIfExists('resources');

        Schema::table('courses', function (Blueprint $table) {
            $cols = array_filter(
                ['enrollment_limit', 'enrollment_start', 'enrollment_end', 'completion_requirements'],
                fn ($c) => Schema::hasColumn('courses', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });

        // Revert status constraint
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_status_check');
            DB::statement("ALTER TABLE courses ADD CONSTRAINT courses_status_check
                CHECK (status::text = ANY (ARRAY[
                    'draft'::text,'pending'::text,'published'::text,'rejected'::text
                ]))");
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY COLUMN status
                ENUM('draft','pending','published','rejected')
                NOT NULL DEFAULT 'draft'");
        }
    }
};
