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
       Schema::create('commissions', function (Blueprint $table) {
      $table->id();

    // نوع الوحدة
    $table->enum('context', ['land', 'apartment', 'shop']);

    // الربط مع الوحدة
    $table->foreignId('land_id')->nullable()->constrained('land_plots')->nullOnDelete();
    $table->foreignId('apartment_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();

    // بيانات السمسرة
    $table->decimal('amount', 12, 2);
    $table->date('commission_date');

    // السمسار (اختياري)
    $table->string('broker_name')->nullable();

    $table->text('notes')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
