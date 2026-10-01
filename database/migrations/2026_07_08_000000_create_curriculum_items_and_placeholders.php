<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Update courses.status column to add 'coming_soon' value
        //    PostgreSQL does not support MySQL's "MODIFY COLUMN ... ENUM()" syntax.
        //    The original column was created as string/enum via Laravel's ->enum().
        //    We handle this in a DB-agnostic way:
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL: drop old check constraint if it exists, then add new one
            $quotedValues = "'draft','pending','published','rejected','coming_soon'";
            DB::statement("ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_status_check");
            DB::statement("ALTER TABLE courses ADD CONSTRAINT courses_status_check CHECK (status IN ({$quotedValues}))");
            DB::statement("ALTER TABLE courses ALTER COLUMN status SET DEFAULT 'draft'");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'rejected', 'coming_soon') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('enrollment_limit')->nullable()->after('status');
            $table->timestamp('enrollment_start')->nullable()->after('enrollment_limit');
            $table->timestamp('enrollment_end')->nullable()->after('enrollment_start');
            $table->json('completion_requirements')->nullable()->after('enrollment_end');
        });

        // 2. Create placeholder tables for resources and live_classes
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });

        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('meeting_url')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->unsignedInteger('duration')->nullable(); // in minutes
            $table->timestamps();
        });

        // 3. Create curriculum_items table
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

    public function down(): void
    {
        Schema::dropIfExists('curriculum_items');
        Schema::dropIfExists('live_classes');
        Schema::dropIfExists('resources');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['enrollment_limit', 'enrollment_start', 'enrollment_end', 'completion_requirements']);
        });

        if (DB::getDriverName() === 'pgsql') {
            $quotedValues = "'draft','pending','published','rejected'";
            DB::statement("ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_status_check");
            DB::statement("ALTER TABLE courses ADD CONSTRAINT courses_status_check CHECK (status IN ({$quotedValues}))");
        } else {
            DB::statement("ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'rejected') NOT NULL DEFAULT 'draft'");
        }
    }
};
