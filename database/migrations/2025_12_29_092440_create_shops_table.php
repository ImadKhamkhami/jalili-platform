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
    Schema::create('shops', function (Blueprint $table) {
        $table->id();

        $table->foreignId('building_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('number');              // رقم المحل
        $table->integer('tranche_number');     // رقم الشطر

        $table->decimal('area', 8, 2);
        $table->decimal('price_per_m2', 10, 2);
        $table->decimal('total_price', 12, 2);

        $table->enum('status', ['متاح', 'محجوز', 'مباع'])
              ->default('متاح');

        $table->string('customer_name')->nullable();
        $table->foreignId('customer_id')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
