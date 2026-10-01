<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['audio', 'video', 'book', 'ebook', 'poster', 'documentary']);
            $table->string('file_url')->nullable();
            $table->string('external_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('publications'); }
};
