<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StocksTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::table('stocks')->delete();

        DB::table('stocks')->insert([
            0 => [
                'id' => 1,
                'one_c_id' => 5,
                'city_id' => 4,
                'chat_id' => 7,
                'type' => 'shop',
                'name' => 'BAROCCO',
                'internal_name' => 'ИП Ермаков И.В.',
                'description' => null,
                'address' => 'пр. Машерова, 17Б, ТЦ Москва',
                'worktime' => '10.30 - 18.00 ежедневно',
                'phone' => '+375298357797',
                'has_pickup' => 0,
                'geo_latitude' => 52.085094,
                'geo_longitude' => 23.696479,
                'check_availability' => 1,
                'sorting' => 5,
                'created_at' => null,
                'updated_at' => '2023-08-08 13:14:26',
            ],
            1 => [
                'id' => 2,
                'one_c_id' => 2,
                'city_id' => 4,
                'chat_id' => 7,
                'type' => 'shop',
                'name' => 'VITACCI',
                'internal_name' => '* ИП Ермаков И.В.*',
                'description' => null,
                'address' => 'ул. Советская 72',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375292465824',
                'has_pickup' => 0,
                'geo_latitude' => 52.089703,
                'geo_longitude' => 23.694924,
                'check_availability' => 1,
                'sorting' => 2,
                'created_at' => null,
                'updated_at' => '2023-08-08 13:14:00',
            ],
            2 => [
                'id' => 3,
                'one_c_id' => 3,
                'city_id' => 4,
                'chat_id' => 8,
                'type' => 'shop',
                'name' => 'CITY',
                'internal_name' => 'ЗАО "САНДАЛ"',
                'description' => null,
                'address' => 'ул. Гоголя 67',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375298367797',
                'has_pickup' => 0,
                'geo_latitude' => 52.093565,
                'geo_longitude' => 23.699547,
                'check_availability' => 1,
                'sorting' => 3,
                'created_at' => null,
                'updated_at' => '2023-08-08 11:30:13',
            ],
            3 => [
                'id' => 4,
                'one_c_id' => 1,
                'city_id' => 4,
                'chat_id' => 8,
                'type' => 'shop',
                'name' => 'BAROCCO',
                'internal_name' => '* ЗАО "САНДАЛ"',
                'description' => null,
                'address' => 'ул. Советская 49',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375292465824',
                'has_pickup' => 0,
                'geo_latitude' => 52.093012,
                'geo_longitude' => 23.692614,
                'check_availability' => 1,
                'sorting' => 1,
                'created_at' => null,
                'updated_at' => '2023-08-08 11:29:47',
            ],
            4 => [
                'id' => 5,
                'one_c_id' => 26,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'Склад брака',
                'internal_name' => 'Склад брака',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 26,
                'created_at' => null,
                'updated_at' => '2023-06-21 21:45:03',
            ],
            5 => [
                'id' => 6,
                'one_c_id' => 31,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'СКЛАД ЗИМА',
                'internal_name' => 'СКЛАД ЗИМА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 31,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:25',
            ],
            6 => [
                'id' => 7,
                'one_c_id' => 32,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'СКЛАД ЛЕТО',
                'internal_name' => 'СКЛАД ЛЕТО',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 32,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:25',
            ],
            7 => [
                'id' => 8,
                'one_c_id' => 4,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'ИНТЕРНЕТ МАГАЗИН',
                'internal_name' => 'ИНТЕРНЕТ МАГАЗИН',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 4,
                'created_at' => null,
                'updated_at' => '2023-07-12 22:03:58',
            ],
            8 => [
                'id' => 9,
                'one_c_id' => 17,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД CITY ЛЕТО',
                'internal_name' => 'СКЛАД CITY ЛЕТО',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 17,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:22',
            ],
            9 => [
                'id' => 10,
                'one_c_id' => 21,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД VITACCI ЛЕТО',
                'internal_name' => 'СКЛАД VITACCI ЛЕТО',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 21,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:20',
            ],
            10 => [
                'id' => 11,
                'one_c_id' => 19,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД MOSKVA ЛЕТО',
                'internal_name' => 'СКЛАД MOSKVA ЛЕТО',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 19,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:19',
            ],
            11 => [
                'id' => 12,
                'one_c_id' => 15,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД BOROCCO ЛЕТО',
                'internal_name' => 'СКЛАД BOROCCO ЛЕТО',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 15,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:18',
            ],
            12 => [
                'id' => 13,
                'one_c_id' => 18,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД MOSKVA ЗИМА',
                'internal_name' => 'СКЛАД MOSKVA ЗИМА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 18,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:18',
            ],
            13 => [
                'id' => 14,
                'one_c_id' => 20,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД VITACCI ЗИМА',
                'internal_name' => 'СКЛАД VITACCI ЗИМА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 20,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:17',
            ],
            14 => [
                'id' => 15,
                'one_c_id' => 14,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД BAROCCO ЗИМА',
                'internal_name' => 'СКЛАД BAROCCO ЗИМА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 14,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:17',
            ],
            15 => [
                'id' => 17,
                'one_c_id' => 6,
                'city_id' => 1,
                'chat_id' => 6,
                'type' => 'shop',
                'name' => 'BAROCCO',
                'internal_name' => 'МИНСК',
                'description' => null,
                'address' => 'г. Минск, ул. Притыцкого, 156, ТЦ “Green City”, 2 этаж',
                'worktime' => '10.00 - 21.00 ежедневно',
                'phone' => '+375447885390',
                'has_pickup' => 0,
                'geo_latitude' => 53.9084571,
                'geo_longitude' => 27.4324877,
                'check_availability' => 1,
                'sorting' => 6,
                'created_at' => null,
                'updated_at' => '2023-08-08 13:14:42',
            ],
            16 => [
                'id' => 19,
                'one_c_id' => 27,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'Склад брака',
                'internal_name' => 'Склад брака',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 27,
                'created_at' => null,
                'updated_at' => null,
            ],
            17 => [
                'id' => 20,
                'one_c_id' => 22,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'Склад брака',
                'internal_name' => 'Склад брака',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 22,
                'created_at' => null,
                'updated_at' => null,
            ],
            18 => [
                'id' => 22,
                'one_c_id' => 34,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'СКЛАД МОСКВА',
                'internal_name' => 'СКЛАД МОСКВА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 34,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:16',
            ],
            19 => [
                'id' => 24,
                'one_c_id' => 23,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'Склад брака                                       ',
                'internal_name' => 'Склад брака                                       ',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 23,
                'created_at' => null,
                'updated_at' => '2023-04-07 17:32:14',
            ],
            20 => [
                'id' => 25,
                'one_c_id' => 25,
                'city_id' => 4,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'Склад брака                                       ',
                'internal_name' => 'Склад брака                                       ',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 25,
                'created_at' => null,
                'updated_at' => '2023-04-07 17:32:15',
            ],
            21 => [
                'id' => 26,
                'one_c_id' => 35,
                'city_id' => 1,
                'chat_id' => null,
                'type' => 'stock',
                'name' => 'ПЕРЕМЕЩЕНИЕ МИНСК',
                'internal_name' => 'ПЕРЕМЕЩЕНИЕ БРЕСТ',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 0,
                'sorting' => 35,
                'created_at' => '2023-06-09 13:54:56',
                'updated_at' => '2023-06-09 13:54:56',
            ],
            22 => [
                'id' => 27,
                'one_c_id' => 16,
                'city_id' => 4,
                'chat_id' => 4,
                'type' => 'stock',
                'name' => 'СКЛАД CITY ЗИМА',
                'internal_name' => 'СКЛАД CITY ЗИМА',
                'description' => null,
                'address' => null,
                'worktime' => null,
                'phone' => null,
                'has_pickup' => 0,
                'geo_latitude' => null,
                'geo_longitude' => null,
                'check_availability' => 1,
                'sorting' => 16,
                'created_at' => null,
                'updated_at' => '2023-03-31 17:40:22',
            ],
        ]);

    }
}
