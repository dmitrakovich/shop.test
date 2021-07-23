<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StyleSeeder extends Seeder
{
    protected $tableName = 'styles';
    protected $values = [
        ['name' => 'повседневный', 'slug' => 'everyday', 'seo' => 'повседневный,повседневняя,повседневное,повседневные'],
        ['name' => 'офисный', 'slug' => 'office', 'seo' => 'офисный,офисная,офисное,офисные'],
        ['name' => 'вечерний', 'slug' => 'evening', 'seo' => 'вечерний,вечерняя,вечернее,вечерние'],
        ['name' => 'спортивный', 'slug' => 'sport', 'seo' => 'спортивный,спортивная,спортивное,спортивные'],
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
