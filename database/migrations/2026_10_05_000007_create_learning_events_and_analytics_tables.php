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
        if (! Schema::hasTable('learning_events')) {
            Schema::create('learning_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('event_type', 64)->index();
                $table->string('subject_type', 100)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('course_id')->nullable()->index();
                $table->json('properties')->nullable();
                $table->string('session_id', 64)->nullable()->index();
                $table->timestamp('occurred_at')->useCurrent()->index();
                $table->timestamps();

                // Composite indexes for sub-second aggregations and queries
                $table->index(['event_type', 'occurred_at'], 'idx_le_type_occurred');
                $table->index(['user_id', 'occurred_at'], 'idx_le_user_occurred');
                $table->index(['course_id', 'event_type', 'occurred_at'], 'idx_le_course_type_occurred');
                $table->index(['subject_type', 'subject_id'], 'idx_le_subject');
            });
        }

        if (! Schema::hasTable('daily_metrics')) {
            Schema::create('daily_metrics', function (Blueprint $table) {
                $table->id();
                $table->date('date')->index();
                $table->string('metric_key', 64)->index();
                $table->decimal('metric_value', 14, 2)->default(0);
                $table->json('breakdown')->nullable();
                $table->timestamps();

                $table->unique(['date', 'metric_key'], 'uniq_daily_metrics_date_key');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
        Schema::dropIfExists('learning_events');
    }
};
