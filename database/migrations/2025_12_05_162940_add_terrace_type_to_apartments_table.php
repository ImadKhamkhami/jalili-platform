<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('apartments', function (Blueprint $table) {

            // نضيف العمود فقط إن لم يكن موجوداً
            if (!Schema::hasColumn('apartments', 'terrace_type')) {
                $table->enum('terrace_type', ['terrasse', 'coeur'])
                      ->nullable()
                      ->after('terrace_total_price');
            }

        });
    }

    public function down()
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('terrace_type');
        });
    }
};
