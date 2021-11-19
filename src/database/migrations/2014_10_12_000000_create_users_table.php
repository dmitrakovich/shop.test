<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_token')->nullable()->unique();
            $table->foreignId('usergroup_id')->default(0);
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('patronymic_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
