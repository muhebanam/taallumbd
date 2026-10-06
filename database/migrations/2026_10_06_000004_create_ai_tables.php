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
        // 1. AI Interactions log table
        if (! Schema::hasTable('ai_interactions')) {
            Schema::create('ai_interactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('feature', 50)->index(); // course_tutor, search_summary, quiz_generator, content_assist, recommendation_v2
                $table->string('prompt_hash', 64)->index();
                $table->text('prompt_redacted');
                $table->longText('response');
                $table->json('sources')->nullable(); // array of cited Ayahs, Hadiths, Fatawa, Lessons
                $table->unsignedInteger('tokens')->default(0);
                $table->decimal('cost', 10, 6)->default(0);
                $table->unsignedInteger('latency_ms')->default(0);
                $table->boolean('flagged')->default(false)->index();
                $table->string('flag_reason')->nullable();
                $table->tinyInteger('feedback_rating')->nullable(); // 1 = helpful, -1 = unhelpful, or 1-5
                $table->text('feedback_notes')->nullable();
                $table->boolean('scholar_reviewed')->default(false)->index();
                $table->foreignId('scholar_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('scholar_notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. AI Content Chunks for RAG Vector/Semantic Search
        if (! Schema::hasTable('ai_content_chunks')) {
            Schema::create('ai_content_chunks', function (Blueprint $table) {
                $table->id();
                $table->string('chunkable_type', 100);
                $table->unsignedBigInteger('chunkable_id');
                $table->string('title');
                $table->longText('content');
                $table->string('source_reference'); // e.g. "সহীহ বুখারী: ১", "সূরা আল-ফাতিহা: ১-২"
                $table->string('source_url')->nullable();
                $table->json('embedding')->nullable(); // float array
                $table->unsignedInteger('token_count')->default(0);
                $table->timestamps();

                $table->index(['chunkable_type', 'chunkable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_content_chunks');
        Schema::dropIfExists('ai_interactions');
    }
};
