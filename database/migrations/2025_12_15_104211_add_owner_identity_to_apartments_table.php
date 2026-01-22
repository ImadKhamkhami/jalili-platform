<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
{
    Schema::table('apartments', function (Blueprint $table) {
        $table->string('owner_identity')->nullable()->after('customer_name');
    });
}

public function down()
{
    Schema::table('apartments', function (Blueprint $table) {
        $table->dropColumn('owner_identity');
    });
}

};
