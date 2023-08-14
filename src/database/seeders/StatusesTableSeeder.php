<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('statuses')->delete();

        DB::table('statuses')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'новинки',
                'slug' => 'st-new',
                'created_at' => '2022-03-07 12:12:11',
                'updated_at' => '2022-03-07 12:12:14',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'скидки',
                'slug' => 'st-sale',
                'created_at' => '2022-03-07 12:12:38',
                'updated_at' => '2022-03-07 12:12:41',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'акция',
                'slug' => 'promotion',
                'created_at' => '2022-10-09 17:23:44',
                'updated_at' => '2022-10-09 17:23:44',
            ),
        ));


    }
}
