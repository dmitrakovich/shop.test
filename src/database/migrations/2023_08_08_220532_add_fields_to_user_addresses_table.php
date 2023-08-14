<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\Address;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Address::doesntHave('user')->delete();

        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('zip', 10)->comment('Почтовый индекс')->nullable()->change();
            $table->string('region', 128)->comment('Область/край')->nullable()->change();
            $table->string('city', 64)->comment('Населенный пункт')->nullable()->change();

            $table->string('street', 96)->comment('Улица')->nullable();
            $table->string('house', 32)->comment('Дом')->nullable();
            $table->string('corpus', 32)->comment('Корпус')->nullable();
            $table->string('room', 32)->comment('Квартира')->nullable();
            $table->boolean('approve')->comment('Подтверждение о проверке')->default(0);

            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn('district');
            $table->dropColumn('street');
            $table->dropColumn('house');
            $table->dropColumn('corpus');
            $table->dropColumn('room');
            $table->dropColumn('approve');
        });
    }
};
