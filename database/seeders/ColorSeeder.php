<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    protected $tableName = 'colors';
    protected $values = [
        ['name' => 'черный', 'slug' => 'black', 'seo' => 'черный,черная,черное,черные', 'value' => '#000000'],
        ['name' => 'коричневый', 'slug' => 'brown', 'seo' => 'коричневый,коричневая,коричневое,коричневые', 'value' => '#964B00'],
        ['name' => 'бежевый', 'slug' => 'beige', 'seo' => 'бежевый,бежевая,бежевое,бежевые', 'value' => '#F5F5DC'],
        ['name' => 'серый', 'slug' => 'gray', 'seo' => 'серый,серая,серое,серые', 'value' => '#808080'],
        ['name' => 'молочный', 'slug' => 'lactic', 'seo' => 'молочный,молочная,молочное,молочные', 'value' => '#FFFFFA'],
        ['name' => 'белый', 'slug' => 'white', 'seo' => 'белый,белая,белое,белые', 'value' => '#FFFFFF'],
        ['name' => 'бордовый', 'slug' => 'burgundy', 'seo' => 'бордовый,бордовая,бордовое,бордовые', 'value' => '#92000A'],
        ['name' => 'красный', 'slug' => 'red', 'seo' => 'красный,красная,красное,красные', 'value' => '#FF0000'],
        ['name' => 'оранжевый', 'slug' => 'orange', 'seo' => 'оранжевый,оранжевая,оранжевое,оранжевые', 'value' => '#FFA500'],
        ['name' => 'желтый', 'slug' => 'yellow', 'seo' => 'желтый,желтая,желтое,желтые', 'value' => '#FFFF00'],
        ['name' => 'зеленый', 'slug' => 'green', 'seo' => 'зеленый,зеленая,зеленое,зеленые', 'value' => '#00FF00'],
        ['name' => 'бирюзовый', 'slug' => 'turquoise', 'seo' => 'бирюзовый,бирюзовая,бирюзовое,бирюзовые', 'value' => '#30D5C8'],
        ['name' => 'голубой', 'slug' => 'bluelight', 'seo' => 'голубой,голубая,голубое,голубые', 'value' => '#007FFF'],
        ['name' => 'синий', 'slug' => 'blue', 'seo' => 'синий,синяя,синее,синие', 'value' => '#0000FF'],
        ['name' => 'фиолетовый', 'slug' => 'violet', 'seo' => 'фиолетовый,фиолетовая,фиолетовое,фиолетовые', 'value' => '#800080'],
        ['name' => 'розовый', 'slug' => 'pink', 'seo' => 'розовый,розовая,розовое,розовые', 'value' => '#FF69B4'],
        ['name' => 'мультиколор', 'slug' => 'multi', 'seo' => 'разноцветный,разноцветная,разноцветное,разноцветные', 'value' => '#FF69B4'],
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
