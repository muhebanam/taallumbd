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
        // 1. Users table additions
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale', 5)->default('bn')->after('avatar');
            }
            if (! Schema::hasColumn('users', 'preferred_currency')) {
                $table->string('preferred_currency', 5)->default('BDT')->after('preferred_locale');
            }
        });

        // 2. Courses table additions (USD price & international options)
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'price_usd')) {
                $table->decimal('price_usd', 8, 2)->nullable()->after('price');
            }
        });

        // 3. Orders table additions (currency & USD amount)
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 5)->default('BDT')->after('amount');
            }
            if (! Schema::hasColumn('orders', 'amount_usd')) {
                $table->decimal('amount_usd', 8, 2)->nullable()->after('currency');
            }
        });

        // 4. Content Translations polymorphic table
        if (! Schema::hasTable('content_translations')) {
            Schema::create('content_translations', function (Blueprint $table) {
                $table->id();
                $table->string('translatable_type');
                $table->unsignedBigInteger('translatable_id');
                $table->string('locale', 5)->index(); // bn, en, ar
                $table->string('field', 64)->index();  // title, short_description, description, etc.
                $table->longText('value');
                $table->timestamps();

                $table->index(['translatable_type', 'translatable_id', 'locale'], 'content_trans_type_id_locale_idx');
                $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'content_trans_unique_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_translations');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'amount_usd')) {
                $table->dropColumn('amount_usd');
            }
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'price_usd')) {
                $table->dropColumn('price_usd');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'preferred_currency')) {
                $table->dropColumn('preferred_currency');
            }
            if (Schema::hasColumn('users', 'preferred_locale')) {
                $table->dropColumn('preferred_locale');
            }
        });
    }
};
