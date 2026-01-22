<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {

            // رقم الشطر (اختياري)
            $table->integer('tranche_number')
                  ->nullable()
                  ->after('floor');

            // صاحب الشقة (نص اختياري)
            $table->string('customer_name')
                  ->nullable()
                  ->after('status');  
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn(['tranche_number', 'customer_name']);
        });
    }
};
