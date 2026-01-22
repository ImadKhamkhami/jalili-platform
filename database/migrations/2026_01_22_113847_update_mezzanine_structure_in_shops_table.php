<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {

            // 1️⃣ إعادة تسمية العمود القديم
            $table->renameColumn('mezzanin', 'has_mezzanine');

            // 2️⃣ إضافة مساحة الميزانين
            $table->decimal('mezzanine_area', 8, 2)
                ->nullable()
                ->after('has_mezzanine');

            // 3️⃣ ثمن المتر الخاص بالميزانين
            $table->decimal('mezzanine_price_per_m2', 10, 2)
                ->nullable()
                ->after('mezzanine_area');

            // 4️⃣ الثمن الإجمالي للميزانين
            $table->decimal('mezzanine_total_price', 12, 2)
                ->nullable()
                ->after('mezzanine_price_per_m2');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {

            $table->dropColumn([
                'mezzanine_area',
                'mezzanine_price_per_m2',
                'mezzanine_total_price',
            ]);

            $table->renameColumn('has_mezzanine', 'mezzanin');
        });
    }
};
