<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // جعل كل قيم NULL تساوي 0 قبل تعديل العمود
        DB::table('apartments')
            ->whereNull('parking_price')
            ->update(['parking_price' => 0]);
    }

    public function down()
    {
        // رجوع لـ NULL إذا احتجت
        DB::table('apartments')
            ->where('parking_price', 0)
            ->update(['parking_price' => null]);
    }
};

