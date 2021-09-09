<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManufacturerSeeder extends Seeder
{
    protected $tableName = 'manufacturers';
    protected $values = [
        ['id' => 87, 'name' => 'Фабрика №10', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 86, 'name' => 'Фабрика №9', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 85, 'name' => 'Фабрика №8', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 84, 'name' => 'Фабрика №7', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 83, 'name' => 'Фабрика №6', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 82, 'name' => 'Фабрика №5', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 81, 'name' => 'Фабрика №4', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 80, 'name' => 'Фабрика №3', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 79, 'name' => 'Фабрика №2', 'created_at' => '2021-09-09 14:21:21'],
        ['id' => 78, 'name' => 'Фабрика №1', 'created_at' => '2021-09-09 14:21:21'],
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
