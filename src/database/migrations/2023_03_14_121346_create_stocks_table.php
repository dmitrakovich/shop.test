<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_c_id');
            $table->foreignId('city_id');
            $table->enum('type', ['shop', 'stock'])->default('stock');
            $table->string('name', 50);
            $table->string('internal_name', 50);
            $table->string('description')->nullable();
            $table->string('address')->nullable();
            $table->string('worktime', 50)->nullable();
            $table->string('phone')->nullable();
            $table->boolean('has_pickup')->default(false);
            $table->double('geo_latitude', 10, 7)->nullable();
            $table->double('geo_longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('sorting')->default(0);
            $table->timestamps();
        });

        // shops
        DB::table('stocks')->insert([
            [
                'one_c_id' => 1,
                'city_id' => 4,
                'type' => 'shop',
                'name' => 'BAROCCO',
                'internal_name' => 'ИП Ермаков И.В.',
                'address' => 'пр. Машерова, 17Б, ТЦ Москва',
                'worktime' => '10.30 - 18.00 ежедневно',
                'phone' => '+375298357797',
                'geo_latitude' => 23.696479,
                'geo_longitude' => 52.085094,
                'sorting' => 1,
            ],
            [
                'one_c_id' => 3,
                'city_id' => 4,
                'type' => 'shop',
                'name' => 'VITACCI',
                'internal_name' => '* ИП Ермаков И.В.*',
                'address' => 'ул. Советская 72',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375292465824',
                'geo_latitude' => 23.694924,
                'geo_longitude' => 52.089703,
                'sorting' => 3,
            ],
            [
                'one_c_id' => 4,
                'city_id' => 4,
                'type' => 'shop',
                'name' => 'CITY',
                'internal_name' => 'ЗАО "САНДАЛ"',
                'address' => 'ул. Гоголя 67',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375298367797',
                'geo_latitude' => 23.699547,
                'geo_longitude' => 52.093565,
                'sorting' => 4,
            ],
            [
                'one_c_id' => 7,
                'city_id' => 4,
                'type' => 'shop',
                'name' => 'BAROCCO',
                'internal_name' => '* ЗАО "САНДАЛ"',
                'address' => 'ул. Советская 49',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375292465824',
                'geo_latitude' => 23.692614,
                'geo_longitude' => 52.093012,
                'sorting' => 7,
            ],
        ]);

        // stocks
        DB::table('stocks')->insert([
            [
                'one_c_id' => 2,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'Склад брака',
                'internal_name' => 'Склад брака',
                'sorting' => 2,
            ],
            [
                'one_c_id' => 5,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД ЗИМА',
                'internal_name' => 'СКЛАД ЗИМА',
                'sorting' => 5,
            ],
            [
                'one_c_id' => 6,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД ЛЕТО',
                'internal_name' => 'СКЛАД ЛЕТО',
                'sorting' => 6,
            ],

            [
                'one_c_id' => 8,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'ИНТЕРНЕТ МАГАЗИН',
                'internal_name' => 'ИНТЕРНЕТ МАГАЗИН',
                'sorting' => 8,
            ],
            [
                'one_c_id' => 9,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД CITY ЛЕТО',
                'internal_name' => 'СКЛАД CITY ЛЕТО',
                'sorting' => 9,
            ],
            [
                'one_c_id' => 10,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД VITACCI ЛЕТО',
                'internal_name' => 'СКЛАД VITACCI ЛЕТО',
                'sorting' => 10,
            ],
            [
                'one_c_id' => 11,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД MOSKVA ЛЕТО',
                'internal_name' => 'СКЛАД MOSKVA ЛЕТО',
                'sorting' => 11,
            ],
            [
                'one_c_id' => 12,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД BOROCCO ЛЕТО',
                'internal_name' => 'СКЛАД BOROCCO ЛЕТО',
                'sorting' => 12,
            ],
            [
                'one_c_id' => 13,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД MOSKVA ЗИМА',
                'internal_name' => 'СКЛАД MOSKVA ЗИМА',
                'sorting' => 13,
            ],
            [
                'one_c_id' => 14,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД VITACCI ЗИМА',
                'internal_name' => 'СКЛАД VITACCI ЗИМА',
                'sorting' => 14,
            ],
            [
                'one_c_id' => 15,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД BAROCCO ЗИМА',
                'internal_name' => 'СКЛАД BAROCCO ЗИМА',
                'sorting' => 15,
            ],
            [
                'one_c_id' => 16,
                'city_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД МОСКВА',
                'internal_name' => 'СКЛАД МОСКВА',
                'sorting' => 16,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
