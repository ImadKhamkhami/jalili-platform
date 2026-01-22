<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->foreignId('company_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ⚠️ النوعان الوحيدان المسموحان
            $table->enum('type', ['building', 'lot'])->default('building');

            $table->text('address')->nullable();
            $table->integer('floors')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
