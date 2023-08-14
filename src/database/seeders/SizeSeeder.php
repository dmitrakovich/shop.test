<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    protected $tableName = 'sizes';

    protected $values = [
        ['name' => 'без размера', 'slug' => 'size-none', 'value' => 0],
        ['name' => 31, 'slug' => 'size-31', 'value' => 19.5],
        ['name' => 32, 'slug' => 'size-32', 'value' => 20.5],
        ['name' => 33, 'slug' => 'size-33', 'value' => 21],
        ['name' => 34, 'slug' => 'size-34', 'value' => 21.5],
        ['name' => 35, 'slug' => 'size-35', 'value' => 22.5],
        ['name' => 36, 'slug' => 'size-36', 'value' => 23],
        ['name' => 37, 'slug' => 'size-37', 'value' => 23.5],
        ['name' => 38, 'slug' => 'size-38', 'value' => 24.5],
        ['name' => 39, 'slug' => 'size-39', 'value' => 25],
        ['name' => 40, 'slug' => 'size-40', 'value' => 25.5],
        ['name' => 41, 'slug' => 'size-41', 'value' => 26.5],
        ['name' => 42, 'slug' => 'size-42', 'value' => 27],
        ['name' => 43, 'slug' => 'size-43', 'value' => 27.5],
        ['name' => 44, 'slug' => 'size-44', 'value' => 28.5],
        ['name' => 45, 'slug' => 'size-45', 'value' => 29],
        ['name' => 46, 'slug' => 'size-46', 'value' => 29.5],
        ['name' => 47, 'slug' => 'size-47', 'value' => 30.5],
        ['name' => 48, 'slug' => 'size-48', 'value' => 31],
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
