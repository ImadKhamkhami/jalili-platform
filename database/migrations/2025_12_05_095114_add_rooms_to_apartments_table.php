<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('apartments', function (Blueprint $table) {
        $table->unsignedTinyInteger('rooms')->default(2)->after('floor'); // غرفتان افتراضيًا
    });
}

public function down()
{
    Schema::table('apartments', function (Blueprint $table) {
        $table->dropColumn('rooms');
    });
}

};
