<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter reviews table - make course_id nullable first
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                // Drop existing foreign key if it exists
                try {
                    $table->dropForeign(['course_id']);
                } catch (Exception $e) {
                }

                // Re-add as nullable
                if (Schema::hasColumn('reviews', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->change();
                }
            });
        }

        // 2. Alter reviews - add teacher_id
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'teacher_id')) {
                $table->foreignId('teacher_id')->after('user_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        // 3. Alter fatawa table
        Schema::table('fatawa', function (Blueprint $table) {
            if (! Schema::hasColumn('fatawa', 'teacher_id')) {
                $table->foreignId('teacher_id')->after('category_id')->nullable()->constrained('teachers')->nullOnDelete();
            }
        });

        // 4. Alter publications table
        Schema::table('publications', function (Blueprint $table) {
            if (! Schema::hasColumn('publications', 'user_id')) {
                $table->foreignId('user_id')->after('category_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            if (Schema::hasColumn('publications', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('fatawa', function (Blueprint $table) {
            if (Schema::hasColumn('fatawa', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
        });
    }
};
