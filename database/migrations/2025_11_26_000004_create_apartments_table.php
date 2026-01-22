<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            // كل شقة تنتمي إلى مبنى (building)
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('number')->nullable(); // رقم/اسم الشقة
            $table->integer('floor')->nullable();
            $table->float('area')->nullable(); // المساحة بالمتر²
            $table->decimal('price_per_m2', 12, 2)->default(0);
            $table->decimal('parking_price', 12, 2)->nullable(); // إذا اختار موقفًا
            $table->integer('parking_number')->nullable(); // رقم الموقف إن وجد
            $table->decimal('total_price', 14, 2)->default(0); // يحسب عند الحفظ
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('apartments');
    }
};
