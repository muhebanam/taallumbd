<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Teachers table
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('designation')->nullable();
            $table->string('headline')->nullable();
            $table->text('short_bio')->nullable();
            $table->longText('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('location')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('telegram_url')->nullable();
            $table->json('specialties')->nullable();
            $table->json('knowledge_path')->nullable();
            $table->json('expertise_map')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('experiences')->nullable();
            $table->json('office_hours')->nullable();
            $table->boolean('consultation_enabled')->default(false);
            $table->text('consultation_note')->nullable();
            $table->enum('status', ['pending', 'active', 'inactive', 'rejected'])->default('pending')->index();
            $table->boolean('featured')->default(false)->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('allow_follow')->default(true);
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // 2. Followers table
        Schema::create('teacher_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_followers');
        Schema::dropIfExists('teachers');
    }
};
