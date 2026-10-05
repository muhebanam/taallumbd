<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Curriculum Items
        if (Schema::hasTable('curriculum_items')) {
            Schema::table('curriculum_items', function (Blueprint $table) {
                $table->index(['section_id', 'sort_order'], 'curriculum_items_section_sort_idx');
                $table->index(['course_id', 'sort_order'], 'curriculum_items_course_sort_idx');
            });
        }

        // 2. Lesson Progress
        if (Schema::hasTable('lesson_progress')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->index(['user_id', 'course_id', 'is_completed'], 'lesson_progress_user_course_completed_idx');
            });
        }

        // 3. Fatawa
        if (Schema::hasTable('fatawa')) {
            Schema::table('fatawa', function (Blueprint $table) {
                $table->index(['status', 'category_id', 'published_at'], 'fatawa_status_cat_pub_idx');
                if (Schema::hasColumn('fatawa', 'teacher_id')) {
                    $table->index(['teacher_id', 'status'], 'fatawa_teacher_status_idx');
                }
            });
        }

        // 4. Courses
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index(['status', 'category_id'], 'courses_status_category_idx');
                $table->index(['status', 'is_free'], 'courses_status_is_free_idx');
            });
        }

        // 5. Articles
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->index(['status', 'category_id', 'published_at'], 'articles_status_cat_pub_idx');
            });
        }

        // 6. Publications
        if (Schema::hasTable('publications')) {
            Schema::table('publications', function (Blueprint $table) {
                $table->index(['status', 'category_id', 'published_at'], 'publications_status_cat_pub_idx');
            });
        }

        // 7. Enrollments
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'enrollments_user_status_idx');
                $table->index(['course_id', 'status'], 'enrollments_course_status_idx');
            });
        }

        // 8. Orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'orders_user_status_idx');
            });
        }

        // 9. Teachers
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->index(['status', 'is_verified', 'sort_order'], 'teachers_status_verified_sort_idx');
                if (Schema::hasColumn('teachers', 'featured')) {
                    $table->index(['status', 'featured'], 'teachers_status_featured_idx');
                }
            });
        }

        // 10. Reviews
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['course_id', 'status'], 'reviews_course_status_idx');
                if (Schema::hasColumn('reviews', 'teacher_id')) {
                    $table->index(['teacher_id', 'status'], 'reviews_teacher_status_idx');
                }
            });
        }

        // 11. Hadiths
        if (Schema::hasTable('hadiths')) {
            Schema::table('hadiths', function (Blueprint $table) {
                $table->index(['book_id', 'chapter_id', 'number'], 'hadiths_book_chap_num_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hadiths')) {
            Schema::table('hadiths', function (Blueprint $table) {
                $table->dropIndex('hadiths_book_chap_num_idx');
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_course_status_idx');
                if (Schema::hasColumn('reviews', 'teacher_id')) {
                    $table->dropIndex('reviews_teacher_status_idx');
                }
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropIndex('teachers_status_verified_sort_idx');
                if (Schema::hasColumn('teachers', 'featured')) {
                    $table->dropIndex('teachers_status_featured_idx');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_user_status_idx');
            });
        }

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropIndex('enrollments_user_status_idx');
                $table->dropIndex('enrollments_course_status_idx');
            });
        }

        if (Schema::hasTable('publications')) {
            Schema::table('publications', function (Blueprint $table) {
                $table->dropIndex('publications_status_cat_pub_idx');
            });
        }

        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropIndex('articles_status_cat_pub_idx');
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropIndex('courses_status_category_idx');
                $table->dropIndex('courses_status_is_free_idx');
            });
        }

        if (Schema::hasTable('fatawa')) {
            Schema::table('fatawa', function (Blueprint $table) {
                $table->dropIndex('fatawa_status_cat_pub_idx');
                if (Schema::hasColumn('fatawa', 'teacher_id')) {
                    $table->dropIndex('fatawa_teacher_status_idx');
                }
            });
        }

        if (Schema::hasTable('lesson_progress')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->dropIndex('lesson_progress_user_course_completed_idx');
            });
        }

        if (Schema::hasTable('curriculum_items')) {
            Schema::table('curriculum_items', function (Blueprint $table) {
                $table->dropIndex('curriculum_items_section_sort_idx');
                $table->dropIndex('curriculum_items_course_sort_idx');
            });
        }
    }
};
