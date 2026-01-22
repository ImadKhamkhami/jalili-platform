<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('land_plots', function (Blueprint $table) {
        $table->string('land_number')->nullable()->after('project_id');
    });
}

public function down()
{
    Schema::table('land_plots', function (Blueprint $table) {
        $table->dropColumn('land_number');
    });
}
};
