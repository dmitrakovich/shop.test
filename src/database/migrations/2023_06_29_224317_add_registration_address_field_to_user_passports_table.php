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
        Schema::table('user_passports', function (Blueprint $table) {
            $table->string('registration_address')->nullable()->comment('Адрес прописки');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_passports', function (Blueprint $table) {
            $table->dropColumn('registration_address');
        });
    }
};
