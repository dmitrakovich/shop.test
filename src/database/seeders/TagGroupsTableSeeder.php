<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagGroupsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('tag_groups')->delete();

        DB::table('tag_groups')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Без группы',
                'created_at' => '2023-05-10 11:00:44',
                'updated_at' => '2023-05-10 11:00:44',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Комплектация',
                'created_at' => '2023-06-21 20:19:48',
                'updated_at' => '2023-06-21 20:19:48',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Цвет и рисунок',
                'created_at' => '2023-06-21 20:20:00',
                'updated_at' => '2023-06-21 20:25:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Материал наружный',
                'created_at' => '2023-06-21 20:20:09',
                'updated_at' => '2023-06-21 20:32:15',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Каблук',
                'created_at' => '2023-06-21 20:20:20',
                'updated_at' => '2023-06-21 20:20:20',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'Носок',
                'created_at' => '2023-06-21 20:20:34',
                'updated_at' => '2023-06-21 20:20:34',
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'Пятка',
                'created_at' => '2023-06-21 20:20:43',
                'updated_at' => '2023-06-21 20:20:43',
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'Элементы',
                'created_at' => '2023-06-21 20:21:02',
                'updated_at' => '2023-06-21 20:21:16',
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'Декор',
                'created_at' => '2023-06-21 20:21:24',
                'updated_at' => '2023-06-21 20:21:24',
            ),
            9 =>
            array (
                'id' => 10,
                'name' => 'Материал внутренний',
                'created_at' => '2023-06-21 20:32:46',
                'updated_at' => '2023-06-21 20:32:46',
            ),
            10 =>
            array (
                'id' => 11,
                'name' => 'Дизайн и стиль',
                'created_at' => '2023-06-21 20:34:37',
                'updated_at' => '2023-06-21 20:39:36',
            ),
            11 =>
            array (
                'id' => 12,
                'name' => 'Подошва',
                'created_at' => '2023-06-21 20:39:19',
                'updated_at' => '2023-06-21 20:39:19',
            ),
        ));


    }
}
