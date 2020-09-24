<?php

use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    protected $tableName = 'seasons';
    protected $values = [
        ['name' => 'зима', 'slug' => 'winter', 'seo' => 'зимний,зимняя,зимнее,зимние'],
        ['name' => 'лето', 'slug' => 'summer', 'seo' => 'летний,летняя,летнее,летние'],
        ['name' => 'демисезон', 'slug' => 'demi', 'seo' => 'демисезонный,демисезонная,демисезонное,демисезонные'],
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
