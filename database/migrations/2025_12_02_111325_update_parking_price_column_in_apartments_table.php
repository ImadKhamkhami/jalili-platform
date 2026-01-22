<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('apartments', function (Blueprint $table) {

            // أولاً اجعل الحقل nullable (لتجنب الخطأ 1138)
            $table->decimal('parking_price', 12, 2)
                ->nullable()
                ->default(0)
                ->change();
        });
    }

    public function down()
    {
        Schema::table('apartments', function (Blueprint $table) {
            // الرجوع لما كان سابقًا
            $table->decimal('parking_price', 12, 2)->nullable()->change();
        });
    }
};

