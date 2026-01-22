<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {

            // حالة الشقة
            $table->string('status')
                  ->default('متاحة')
                  ->after('total_price');

            // صاحب الشقة (اختياري حاليًا)
            $table->unsignedBigInteger('customer_id')
                  ->nullable()
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn(['status', 'customer_id']);
        });
    }
};

