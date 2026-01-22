<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            // 🔹 ربط مباشر بالوحدات
            $table->foreignId('apartment_id')
                ->nullable()
                ->after('project_id')
                ->constrained('apartments')
                ->nullOnDelete();

            $table->foreignId('shop_id')
                ->nullable()
                ->after('apartment_id')
                ->constrained('shops')
                ->nullOnDelete();

            $table->foreignId('land_id')
                ->nullable()
                ->after('shop_id')
                ->constrained('land_plots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['apartment_id']);
            $table->dropForeign(['shop_id']);
            $table->dropForeign(['land_id']);

            $table->dropColumn([
                'apartment_id',
                'shop_id',
                'land_id',
            ]);
        });
    }
};
