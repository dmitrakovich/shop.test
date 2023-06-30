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
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('user_last_name', 64)->nullable()->comment('Фамилия');
            $table->string('user_patronymic_name', 32)->nullable()->comment('Отчество');
            $table->string('trust_number', 128)->nullable()->comment('Номер доверенности');
            $table->date('trust_date')->nullable()->comment('Дата доверенности');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('user_last_name');
            $table->dropColumn('user_patronymic_name');
            $table->dropColumn('trust_number');
            $table->dropColumn('trust_date');
        });
    }
};
