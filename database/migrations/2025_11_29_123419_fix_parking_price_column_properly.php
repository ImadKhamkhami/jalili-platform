<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // 1️⃣ إصلاح أي قيم NULL
        DB::table('apartments')
            ->whereNull('parking_price')
            ->update(['parking_price' => 0]);

        // 2️⃣ تعديل العمود بعد التأكد من عدم وجود NULL
        Schema::table('apartments', function (Blueprint $table) {
            $table->decimal('parking_price', 12, 2)->default(0)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->decimal('parking_price', 12, 2)->nullable()->change();
        });
    }
};

