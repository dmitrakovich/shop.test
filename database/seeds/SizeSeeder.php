<?php

use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    protected $tableName = 'sizes';
    protected $values = [
        ['name' => 'без размера', 'slug' => 'none', 'value' => 0],
        ['name' => 33, 'slug' => 33, 'value' => 21],
        ['name' => 34, 'slug' => 34, 'value' => 21.5],
        ['name' => 35, 'slug' => 35, 'value' => 22.5],
        ['name' => 36, 'slug' => 36, 'value' => 23],
        ['name' => 37, 'slug' => 37, 'value' => 23.5],
        ['name' => 38, 'slug' => 38, 'value' => 24.5],
        ['name' => 39, 'slug' => 39, 'value' => 25],
        ['name' => 40, 'slug' => 40, 'value' => 25.5],
        ['name' => 41, 'slug' => 41, 'value' => 26.5],
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
