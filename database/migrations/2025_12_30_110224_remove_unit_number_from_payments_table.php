<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // حذف العمود نهائياً
            if (Schema::hasColumn('payments', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // إرجاع العمود في حال rollback (اختياري)
            $table->integer('unit_number')->nullable()->after('tranche_number');
        });
    }
};
