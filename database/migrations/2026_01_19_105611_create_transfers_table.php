<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('transfers', function (Blueprint $table) {
        $table->id();

        $table->string('context'); // apartment | shop | land
        $table->unsignedBigInteger('unit_id');

        $table->foreignId('from_customer_id')
              ->constrained('customers')
              ->cascadeOnDelete();

        $table->foreignId('to_customer_id')
              ->constrained('customers')
              ->cascadeOnDelete();

        $table->unsignedInteger('transfer_number');

        $table->date('transfer_date');

        $table->text('notes')->nullable();

        $table->timestamps();

        // مهم: منع تكرار رقم التنازل لنفس الوحدة
        $table->unique(['context', 'unit_id', 'transfer_number']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
