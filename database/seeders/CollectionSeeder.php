<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    protected $tableName = 'collections';
    protected $values = [
        ['id' => '66', 'name' => 'Весна-Лето 2020', 'slug' => 'col-vesna-leto-2020', 'seo' => ''],
        ['id' => '67', 'name' => 'Осень-Зима 20/21', 'slug' => 'col-vesna-leto-20-21', 'seo' => ''],
        ['id' => '70', 'name' => 'Весна-Лето 2021', 'slug' => 'col-vesna-leto-2021', 'seo' => ''],
        ['id' => '71', 'name' => 'Осень-Зима 19/20', 'slug' => 'col-vesna-leto-19-20', 'seo' => ''],
        ['id' => '72', 'name' => 'Весна-Лето 2019', 'slug' => 'col-vesna-leto-2019', 'seo' => ''],
        ['id' => '73', 'name' => 'Осень-Зима 18/19', 'slug' => 'col-vesna-leto-18-19', 'seo' => ''],
        ['id' => '74', 'name' => 'Весна-Лето 2018', 'slug' => 'col-vesna-leto-2018', 'seo' => ''],
        ['id' => '75', 'name' => 'Осень-Зима 17/18', 'slug' => 'col-vesna-leto-17-18', 'seo' => ''],
        ['id' => '76', 'name' => 'Весна-Лето 2017', 'slug' => 'col-vesna-leto-2017', 'seo' => ''],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->truncate();

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
