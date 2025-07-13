<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    protected $tableName = 'sizes';

    protected $values = [
        ['name' => 'без размера', 'slug' => 'size-none', 'insole' => 0],
        ['name' => 31, 'slug' => 'size-31', 'insole' => 19.5],
        ['name' => 32, 'slug' => 'size-32', 'insole' => 20.5],
        ['name' => 33, 'slug' => 'size-33', 'insole' => 21],
        ['name' => 34, 'slug' => 'size-34', 'insole' => 21.5],
        ['name' => 35, 'slug' => 'size-35', 'insole' => 22.5],
        ['name' => 36, 'slug' => 'size-36', 'insole' => 23],
        ['name' => 37, 'slug' => 'size-37', 'insole' => 23.5],
        ['name' => 38, 'slug' => 'size-38', 'insole' => 24.5],
        ['name' => 39, 'slug' => 'size-39', 'insole' => 25],
        ['name' => 40, 'slug' => 'size-40', 'insole' => 25.5],
        ['name' => 41, 'slug' => 'size-41', 'insole' => 26.5],
        ['name' => 42, 'slug' => 'size-42', 'insole' => 27],
        ['name' => 43, 'slug' => 'size-43', 'insole' => 27.5],
        ['name' => 44, 'slug' => 'size-44', 'insole' => 28.5],
        ['name' => 45, 'slug' => 'size-45', 'insole' => 29],
        ['name' => 46, 'slug' => 'size-46', 'insole' => 29.5],
        ['name' => 47, 'slug' => 'size-47', 'insole' => 30.5],
        ['name' => 48, 'slug' => 'size-48', 'insole' => 31],
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
