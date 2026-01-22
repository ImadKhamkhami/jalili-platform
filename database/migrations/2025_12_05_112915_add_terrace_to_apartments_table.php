<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('apartments', function (Blueprint $table) {

        $table->boolean('has_terrace')->default(false)->after('parking_price');
        $table->decimal('terrace_area', 10, 2)->nullable()->after('has_terrace');
        $table->decimal('terrace_total_price', 10, 2)->nullable()->after('terrace_area');
    });
}

public function down()
{
    Schema::table('apartments', function (Blueprint $table) {
        $table->dropColumn([
            'has_terrace',
            'terrace_area',
            'terrace_total_price'
        ]);
    });
}

   
};
