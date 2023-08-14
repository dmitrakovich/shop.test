<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserGroupsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::table('user_groups')->delete();

        DB::table('user_groups')->insert([
            0 => [
                'id' => 1,
                'name' => 'Новая регистрация',
                'discount' => 7.0,
            ],
            1 => [
                'id' => 2,
                'name' => 'Скидка 5%',
                'discount' => 5.0,
            ],
            2 => [
                'id' => 3,
                'name' => 'Скидка 7%',
                'discount' => 7.0,
            ],
            3 => [
                'id' => 4,
                'name' => 'Скидка 10%',
                'discount' => 10.0,
            ],
            4 => [
                'id' => 5,
                'name' => 'Скидка 15%',
                'discount' => 15.0,
            ],
            5 => [
                'id' => 6,
                'name' => 'Контрольная группа',
                'discount' => 0.0,
            ],
            6 => [
                'id' => 7,
                'name' => 'Пользователь',
                'discount' => 0.0,
            ],
        ]);

    }
}
