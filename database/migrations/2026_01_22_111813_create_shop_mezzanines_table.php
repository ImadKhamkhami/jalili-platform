<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_mezzanines', function (Blueprint $table) {

            $table->id();

            // ربط الميزانين بالمحل
            $table->foreignId('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();

            // مساحة الميزانين (مستقلة عن المحل)
            $table->decimal('area', 8, 2);

            // ثمن المتر الخاص بالميزانين
            $table->decimal('price_per_m2', 10, 2);

            // الثمن الإجمالي للميزانين
            $table->decimal('total_price', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_mezzanines');
    }
};
