<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single table for the fatwa lifecycle: submitted question -> answered -> published.
        Schema::create('fatawa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('questioner_name')->nullable();
            $table->string('questioner_email')->nullable();
            $table->string('questioner_phone')->nullable();
            $table->string('question_title');
            $table->longText('question_body');
            $table->longText('answer_body')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fatawa');
    }
};
