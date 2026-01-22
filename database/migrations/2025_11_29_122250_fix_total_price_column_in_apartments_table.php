<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::table('apartments', function (Blueprint $table) {
        $table->decimal('total_price', 14, 2)
              ->nullable(false)
              ->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('apartments', function (Blueprint $table) {
        $table->decimal('total_price', 14, 2)
              ->default(0)
              ->change();
    });
    }
};
