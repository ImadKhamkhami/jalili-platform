<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            /* ===============================
             * نوع الوحدة
             * apartment | shop | land
             * =============================== */
            $table->enum('context', ['apartment', 'shop', 'land']);

            /* ===============================
             * المشروع
             * =============================== */
            $table->unsignedBigInteger('project_id');

            /* ===============================
             * معلومات التحديد
             * =============================== */
            $table->integer('building_number')->nullable(); // إجباري للشقق والمحلات
            $table->integer('tranche_number')->nullable();  // اختياري
            $table->integer('unit_number');                 // شقة / محل / قطعة

            /* ===============================
             * معلومات الدفع
             * =============================== */
            $table->enum('payment_method', [
                'cash',     // نقدا
                'check',    // شيك
                'transfer', // تحويل
                'bill',     // كمبيالة
            ]);

            $table->decimal('amount', 12, 2);
            $table->date('paid_at')->nullable();

            $table->timestamps();

            /* ===============================
             * فهارس (مهم للأداء)
             * =============================== */
            $table->index(['context', 'project_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
