<?php

use Illuminate\Database\Seeder;

class HeelSeeder extends Seeder
{
    protected $tableName = 'heels';
    protected $values = [
        ['name' => 'без каблука', 'slug' => 'without', 'seo' => 'без каблука'],
        ['name' => 'низкий каблук', 'slug' => 'low', 'seo' => 'на низком каблуке'],
        ['name' => 'высокий каблук', 'slug' => 'height', 'seo' => 'на высоком каблуке'],
        ['name' => 'шпилька', 'slug' => 'stiletto', 'seo' => 'на шпильках'],
        ['name' => 'платформа', 'slug' => 'platf', 'seo' => 'на платформе'],
        ['name' => 'танкетка', 'slug' => 'tank', 'seo' => 'на танкетке'],
        ['name' => 'протектор', 'slug' => 'prot', 'seo' => 'на протекторе'],
        ['name' => 'тракторная подошва', 'slug' => 'trak', 'seo' => 'на тракторной подошве'],
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
