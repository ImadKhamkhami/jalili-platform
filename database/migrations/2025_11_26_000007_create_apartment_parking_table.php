<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentParkingTable extends Migration
{
    public function up()
    {
         Schema::create('apartment_parkings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->integer('parking_number'); // رقـم الموقف (مرقم)
            $table->decimal('price', 15, 2)->default(0); // ثمن الموقف
            $table->timestamps();

            // تأكد من عدم تكرار نفس رقم الموقف لنفس المشروع/البناية حسب حاجتك
            $table->unique(['apartment_id','parking_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('apartment_parking');
    }
}
