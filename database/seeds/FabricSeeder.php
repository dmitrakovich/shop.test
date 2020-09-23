<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FabricSeeder extends Seeder
{
    protected $tableName = 'fabrics';
    protected $values = [
        ['value' => 'натуральная кожа'],
        ['value' => 'лакированная кожа'],
        ['value' => 'натуральный замш'],
        ['value' => 'искусственная кожа'],
        ['value' => 'текстиль'],
        ['value' => 'экокожа'],
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
            DB::table($this->tableName)->insert([
                'value' => $value['value'],
                'slug' => Str::slug($value['value'])
            ]);
        }
    }
}
