<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeelSeeder extends Seeder
{
    protected $tableName = 'heels';

    protected $values = [
        ['name' => 'без каблука', 'slug' => 'heel-without', 'seo' => 'без каблука'],
        ['name' => 'низкий каблук', 'slug' => 'heel-low', 'seo' => 'на низком каблуке'],
        ['name' => 'высокий каблук', 'slug' => 'heel-height', 'seo' => 'на высоком каблуке'],
        ['name' => 'шпилька', 'slug' => 'heel-stiletto', 'seo' => 'на шпильках'],
        ['name' => 'платформа', 'slug' => 'heel-platf', 'seo' => 'на платформе'],
        ['name' => 'танкетка', 'slug' => 'heel-tank', 'seo' => 'на танкетке'],
        ['name' => 'протектор', 'slug' => 'heel-prot', 'seo' => 'на протекторе'],
        ['name' => 'тракторная подошва', 'slug' => 'heel-trak', 'seo' => 'на тракторной подошве'],
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
