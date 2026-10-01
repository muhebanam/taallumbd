<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_posts', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('course_id')->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('forum_posts', 'topic')) {
                $table->string('topic')->default('general')->after('title');
            }
            if (!Schema::hasColumn('forum_posts', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('body');
            }
            if (!Schema::hasColumn('forum_posts', 'upvotes_count')) {
                $table->unsignedInteger('upvotes_count')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('forum_posts', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('status');
            }
            if (!Schema::hasColumn('forum_posts', 'is_solved')) {
                $table->boolean('is_solved')->default(false)->after('is_pinned');
            }
        });

        if (!Schema::hasTable('forum_likes')) {
            Schema::create('forum_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('forum_post_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
                $table->foreignId('forum_comment_id')->nullable()->constrained('forum_comments')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'forum_post_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_likes');

        Schema::table('forum_posts', function (Blueprint $table) {
            if (Schema::hasColumn('forum_posts', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
            $table->dropColumn(['topic', 'views_count', 'upvotes_count', 'is_pinned', 'is_solved']);
        });
    }
};
