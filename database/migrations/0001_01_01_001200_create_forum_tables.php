<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->enum('status', ['published', 'hidden'])->default('published');
            $table->timestamps();
        });

        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('body');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('forum_comments');
        Schema::dropIfExists('forum_posts');
    }
};
