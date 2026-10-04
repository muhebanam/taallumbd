<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Surahs table
        if (! Schema::hasTable('surahs')) {
            Schema::create('surahs', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('number')->unique();
                $table->string('name_arabic');
                $table->string('name_bangla');
                $table->string('name_transliteration')->nullable();
                $table->unsignedSmallInteger('ayah_count');
                $table->enum('revelation_type', ['Meccan', 'Medinan'])->default('Meccan');
                $table->unsignedSmallInteger('revelation_order')->nullable();
                $table->timestamps();
            });
        }

        // 2. Ayahs table
        if (! Schema::hasTable('ayahs')) {
            Schema::create('ayahs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surah_id')->constrained('surahs')->cascadeOnDelete();
                $table->unsignedSmallInteger('number');
                $table->unsignedMediumInteger('number_in_quran')->nullable();
                $table->longText('text_uthmani');
                $table->unsignedTinyInteger('juz')->default(1);
                $table->boolean('sajdah')->default(false);
                $table->timestamps();

                $table->unique(['surah_id', 'number']);
            });
        }

        // 3. Ayah Translations table
        if (! Schema::hasTable('ayah_translations')) {
            Schema::create('ayah_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayah_id')->constrained('ayahs')->cascadeOnDelete();
                $table->string('language', 10)->default('bn');
                $table->string('translator')->default('মুহিউদ্দীন খান');
                $table->text('text');
                $table->timestamps();

                $table->index(['ayah_id', 'language']);
            });
        }

        // 4. Ayah Tafsir table
        if (! Schema::hasTable('ayah_tafsir')) {
            Schema::create('ayah_tafsir', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayah_id')->constrained('ayahs')->cascadeOnDelete();
                $table->string('source')->default('তাফসীরে তাওযীহুল কুরআন');
                $table->longText('text');
                $table->timestamps();

                $table->index(['ayah_id', 'source']);
            });
        }

        // 5. Memorization Progress table (হিফজ ট্র্যাকার)
        if (! Schema::hasTable('memorization_progress')) {
            Schema::create('memorization_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('surah_id')->constrained('surahs')->cascadeOnDelete();
                $table->unsignedSmallInteger('ayah_from')->default(1);
                $table->unsignedSmallInteger('ayah_to')->default(1);
                $table->enum('status', ['memorizing', 'memorized', 'revising'])->default('memorizing');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'surah_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('memorization_progress');
        Schema::dropIfExists('ayah_tafsir');
        Schema::dropIfExists('ayah_translations');
        Schema::dropIfExists('ayahs');
        Schema::dropIfExists('surahs');
    }
};
