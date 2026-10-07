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
        // 1. Enrollments Table: Composite index for fast student lookups & status
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                // Add index on status & created_at for dashboard queries
                $table->index(['status', 'created_at'], 'idx_enrollments_status_created');
            });
        }

        // 2. Course Sections: Optimize section ordering queries
        if (Schema::hasTable('course_sections')) {
            Schema::table('course_sections', function (Blueprint $table) {
                $table->index(['course_id', 'sort_order'], 'idx_course_sections_course_sort');
            });
        }

        // 3. Curriculum Items: Polymorphic & section ordering
        if (Schema::hasTable('curriculum_items')) {
            Schema::table('curriculum_items', function (Blueprint $table) {
                $table->index(['section_id', 'sort_order'], 'idx_curriculum_items_sec_sort');
                $table->index(['itemable_type', 'itemable_id'], 'idx_curriculum_items_polymorphic');
            });
        }

        // 4. Learning Events: High-traffic analytics query index
        if (Schema::hasTable('learning_events')) {
            Schema::table('learning_events', function (Blueprint $table) {
                $table->index(['user_id', 'event_type', 'created_at'], 'idx_learning_events_user_type_created');
            });
        }

        // 5. Fatawa: Category & status filtering
        if (Schema::hasTable('fatawa')) {
            Schema::table('fatawa', function (Blueprint $table) {
                $table->index(['status', 'category_id', 'created_at'], 'idx_fatawa_status_cat_created');
            });
        }

        // 6. Payment Transactions: Gateway & status auditing
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->index(['gateway', 'status', 'created_at'], 'idx_payment_trx_gw_status_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropIndex('idx_enrollments_status_created');
            });
        }

        if (Schema::hasTable('course_sections')) {
            Schema::table('course_sections', function (Blueprint $table) {
                $table->dropIndex('idx_course_sections_course_sort');
            });
        }

        if (Schema::hasTable('curriculum_items')) {
            Schema::table('curriculum_items', function (Blueprint $table) {
                $table->dropIndex('idx_curriculum_items_sec_sort');
                $table->dropIndex('idx_curriculum_items_polymorphic');
            });
        }

        if (Schema::hasTable('learning_events')) {
            Schema::table('learning_events', function (Blueprint $table) {
                $table->dropIndex('idx_learning_events_user_type_created');
            });
        }

        if (Schema::hasTable('fatawa')) {
            Schema::table('fatawa', function (Blueprint $table) {
                $table->dropIndex('idx_fatawa_status_cat_created');
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_payment_trx_gw_status_created');
            });
        }
    }
};
