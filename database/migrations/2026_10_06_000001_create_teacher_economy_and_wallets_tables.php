<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Teacher Wallets Table
        Schema::create('teacher_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('balance')->default(0)->comment('Available balance in poisha (1 BDT = 100 poisha)');
            $table->bigInteger('pending_balance')->default(0)->comment('Pending escrow balance in poisha');
            $table->string('currency', 10)->default('BDT');
            $table->timestamps();

            $table->unique('teacher_id');
            $table->unique('user_id');
            $table->index(['user_id', 'balance']);
        });

        // 2. Wallet Transactions (Double-entry style immutable ledger)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('teacher_wallets')->cascadeOnDelete();
            $table->string('type', 20)->comment('credit or debit');
            $table->string('balance_type', 20)->default('available')->comment('available or pending');
            $table->bigInteger('amount')->comment('Amount in poisha (always positive)');
            $table->bigInteger('balance_after')->comment('Wallet balance snapshot in poisha after transaction');
            $table->string('reference_type', 50)->comment('order, payout_request, payout_rejected, refund_reversal, matured_release, adjustment');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description', 255);
            $table->json('metadata')->nullable();
            $table->timestamp('hold_until')->nullable()->comment('When escrow balance matures into available balance');
            $table->timestamp('matured_at')->nullable()->comment('When matured release was processed');
            $table->timestamps();

            $table->index(['wallet_id', 'balance_type', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['hold_until', 'matured_at']);
        });

        // 3. Revenue Shares Table (Course/Teacher overrides, or platform default)
        Schema::create('revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->decimal('instructor_share_percentage', 5, 2)->default(70.00);
            $table->decimal('platform_share_percentage', 5, 2)->default(30.00);
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
            $table->index(['teacher_id', 'is_active']);
        });

        // 4. Payout Requests Table
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('teacher_wallets')->cascadeOnDelete();
            $table->bigInteger('amount')->comment('Requested amount in poisha');
            $table->string('method', 30)->comment('bkash, nagad, rocket, bank');
            $table->text('account_info')->comment('Encrypted payment receiving details');
            $table->string('status', 30)->default('pending')->comment('pending, approved, paid, rejected');
            $table->string('transaction_reference', 100)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // 5. Add Consultation fields to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'consultation_fee')) {
                $table->bigInteger('consultation_fee')->default(0)->comment('Fee in poisha');
            }
            if (! Schema::hasColumn('teachers', 'consultation_session_duration')) {
                $table->integer('consultation_session_duration')->default(30)->comment('Duration in minutes');
            }
            if (! Schema::hasColumn('teachers', 'consultation_booking_stub')) {
                $table->json('consultation_booking_stub')->nullable();
            }
            if (! Schema::hasColumn('teachers', 'consultation_currency')) {
                $table->string('consultation_currency', 10)->default('BDT');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'consultation_fee')) {
                $table->dropColumn([
                    'consultation_fee',
                    'consultation_session_duration',
                    'consultation_booking_stub',
                    'consultation_currency',
                ]);
            }
        });

        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('revenue_shares');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('teacher_wallets');
    }
};
