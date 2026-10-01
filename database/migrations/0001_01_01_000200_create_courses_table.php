<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->longText('description');
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(true);
            $table->string('level')->nullable();
            $table->string('duration')->nullable();
            $table->json('learn_points')->nullable();
            $table->json('requirements')->nullable();
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('draft')->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('courses'); }
};
