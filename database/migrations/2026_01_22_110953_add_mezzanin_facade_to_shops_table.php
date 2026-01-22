<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {

            // هل يوجد ميزانين
            $table->boolean('mezzanin')
                ->default(false)
                ->after('area');

            // واجهة المحل
            $table->string('facade')
                ->nullable()
                ->after('mezzanin');

        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {

            $table->dropColumn([
                'mezzanin',
                'facade',
            ]);

        });
    }
};
