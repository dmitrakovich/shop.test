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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->timestamp('phone_verified_at')->nullable()->after('birth_date');
            $table->string('email')->nullable()->change();
            $table->string('first_name', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->after('email_verified_at');
            $table->dropColumn('phone_verified_at');
            $table->string('email')->nullable(false)->change();
            $table->string('first_name', 50)->nullable(false)->change();
        });
    }
};
