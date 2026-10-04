<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lesson Notes (শিক্ষার্থীদের পাঠভিত্তিক ব্যক্তিগত নোট)
        if (! Schema::hasTable('lesson_notes')) {
            Schema::create('lesson_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->longText('note');
                $table->timestamps();

                $table->unique(['user_id', 'lesson_id']);
            });
        }

        // 2. Lesson Bookmarks (নির্দিষ্ট সময় বা লেসন বুকমার্ক)
        if (! Schema::hasTable('lesson_bookmarks')) {
            Schema::create('lesson_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('timestamp_seconds')->default(0);
                $table->string('title')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'lesson_id']);
            });
        }

        // 3. Lesson Comments / Q&A (উস্তাযের সাথে সরাসরি পাঠভিত্তিক আলোচনা)
        if (! Schema::hasTable('lesson_comments')) {
            Schema::create('lesson_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('lesson_comments')->cascadeOnDelete();
                $table->text('body');
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();

                $table->index(['lesson_id', 'parent_id']);
            });
        }

        // 4. Learning Paths (প্রাতিষ্ঠানিক স্তরভিত্তিক লার্নিং রোডম্যাপ)
        if (! Schema::hasTable('learning_paths')) {
            Schema::create('learning_paths', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('level')->default('beginner'); // beginner, intermediate, advanced
                $table->string('icon')->nullable();
                $table->string('duration')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 5. Learning Path Courses pivot
        if (! Schema::hasTable('learning_path_courses')) {
            Schema::create('learning_path_courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('learning_path_id')->constrained('learning_paths')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['learning_path_id', 'course_id']);
            });
        }

        // 6. Add UUID to certificates table for public verification URL /verify/{uuid}
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (! Schema::hasColumn('certificates', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('certificates', 'uuid')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }

        Schema::dropIfExists('learning_path_courses');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('lesson_comments');
        Schema::dropIfExists('lesson_bookmarks');
        Schema::dropIfExists('lesson_notes');
    }
};
