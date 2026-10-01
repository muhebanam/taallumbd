<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Hadith Books table (যেমন: সহীহ বুখারী, সহীহ মুসলিম, সুনান আবূ দাউদ, ইত্যাদি)
        if (!Schema::hasTable('hadith_books')) {
            Schema::create('hadith_books', function (Blueprint $table) {
                $table->id();
                $table->string('name_arabic');
                $table->string('name_bangla');
                $table->string('name_english')->nullable();
                $table->string('slug')->unique();
                $table->string('author');
                $table->unsignedMediumInteger('total_hadith')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Hadith Chapters table (কিতাবের অধ্যায় বা কিতাবসমূহ)
        if (!Schema::hasTable('hadith_chapters')) {
            Schema::create('hadith_chapters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('hadith_books')->cascadeOnDelete();
                $table->unsignedSmallInteger('number');
                $table->string('title_arabic')->nullable();
                $table->string('title_bangla');
                $table->timestamps();

                $table->index(['book_id', 'number']);
            });
        }

        // 3. Hadiths table (হাদীসের মূল আরবী ও বাংলা অনুবাদ)
        if (!Schema::hasTable('hadiths')) {
            Schema::create('hadiths', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('hadith_books')->cascadeOnDelete();
                $table->foreignId('chapter_id')->nullable()->constrained('hadith_chapters')->nullOnDelete();
                $table->unsignedMediumInteger('number');
                $table->string('hadith_number_in_book')->nullable();
                $table->longText('text_arabic');
                $table->longText('text_bangla');
                $table->string('narrator')->nullable(); // যেমন: আবূ হুরায়রা (রা.)
                $table->string('grade')->nullable(); // সহীহ, হাসান, জয়ীফ
                $table->string('grade_by')->nullable(); // আলবানী, আহমাদ শাকির ইত্যাদি
                $table->text('explanation')->nullable(); // সংক্ষিপ্ত ব্যাখ্যা
                $table->timestamps();

                $table->index(['book_id', 'chapter_id']);
                $table->index(['book_id', 'number']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hadiths');
        Schema::dropIfExists('hadith_chapters');
        Schema::dropIfExists('hadith_books');
    }
};
