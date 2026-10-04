<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('gateway', 50)->index();
                $table->string('type', 50)->index();
                $table->string('gateway_ref', 100)->nullable()->index();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 10)->default('BDT');
                $table->string('status', 50)->default('pending')->index();
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
