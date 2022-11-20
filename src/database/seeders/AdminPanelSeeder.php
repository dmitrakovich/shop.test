<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPanelSeeder extends Seeder
{
    protected $tableName = 'admin_menu';

    protected $values = [
        ['id' => 8, 'parent_id' => 9, 'order' => 4, 'title' => 'Размеры', 'icon' => 'fa-signal', 'uri' => 'sizes'],
        ['id' => 9, 'parent_id' => 0, 'order' => 1, 'title' => 'Аттрибуты товаров', 'icon' => 'fa-bars'],
        ['id' => 10, 'parent_id' => 9, 'order' => 5, 'title' => 'Цвет', 'icon' => 'fa-adjust', 'uri' => 'colors'],
        ['id' => 11, 'parent_id' => 9, 'order' => 2, 'title' => 'Категории', 'icon' => 'fa-align-left', 'uri' => 'categories'],
        ['id' => 12, 'parent_id' => 9, 'order' => 3, 'title' => 'Материал', 'icon' => 'fa-500px', 'uri' => 'fabrics'],
        ['id' => 13, 'parent_id' => 9, 'order' => 6, 'title' => 'Высота каблука', 'icon' => 'fa-angle-double-up', 'uri' => 'heel-heights'],
        ['id' => 14, 'parent_id' => 9, 'order' => 7, 'title' => 'Сезон', 'icon' => 'fa-envira', 'uri' => 'seasons'],
        ['id' => 15, 'parent_id' => 9, 'order' => 8, 'title' => 'Теги', 'icon' => 'fa-tags', 'uri' => 'tags'],
        ['id' => 16, 'parent_id' => 9, 'order' => 9, 'title' => 'Бренды', 'icon' => 'fa-adn', 'uri' => 'brands'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
