<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index();
                $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
                $table->decimal('discount_amount', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
            }
            if (!Schema::hasColumn('orders', 'sender_phone')) {
                $table->string('sender_phone')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('sender_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'discount_amount', 'sender_phone', 'transaction_id']);
        });
    }
};
