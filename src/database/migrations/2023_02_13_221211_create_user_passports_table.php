<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_passports', function (Blueprint $table) {
            $table->id();
            $table->string('passport_number', 32)->nullable()->comment('Номер паспорта');
            $table->string('series', 32)->nullable()->comment('Серия паспорта');
            $table->string('issued_by', 256)->nullable()->comment('Кем выдан');
            $table->date('issued_date')->nullable()->comment('Когда выдан');
            $table->string('personal_number', 32)->nullable()->comment('Личный номер');
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_passports');
    }
};
