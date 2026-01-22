<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('land_plots')
            ->where('view_type', 'single')
            ->update(['view_type' => '1']);

        DB::table('land_plots')
            ->where('view_type', 'double')
            ->update(['view_type' => '2']);
    }

    public function down()
    {
        DB::table('land_plots')
            ->where('view_type', '1')
            ->update(['view_type' => 'single']);

        DB::table('land_plots')
            ->where('view_type', '2')
            ->update(['view_type' => 'double']);
    }
};
