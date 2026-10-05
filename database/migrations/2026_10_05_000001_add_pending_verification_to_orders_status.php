<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check
                CHECK (status::text = ANY (ARRAY[
                    'pending'::text,
                    'pending_verification'::text,
                    'paid'::text,
                    'failed'::text,
                    'cancelled'::text
                ]))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending', 'pending_verification', 'paid', 'failed', 'cancelled')
                NOT NULL DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'screenshot_path')) {
                $table->string('screenshot_path')->nullable()->after('transaction_id');
            }
        });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check
                CHECK (status::text = ANY (ARRAY[
                    'pending'::text,
                    'paid'::text,
                    'failed'::text,
                    'cancelled'::text
                ]))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending', 'paid', 'failed', 'cancelled')
                NOT NULL DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'screenshot_path')) {
                $table->dropColumn('screenshot_path');
            }
        });
    }
};
