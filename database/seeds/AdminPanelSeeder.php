<?php

use Illuminate\Database\Seeder;

class AdminPanelSeeder extends Seeder
{
    protected $tableName = 'admin_menu';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->insert(['id' => 38353, 'value' => 'Jersey']);

        DB::table($this->tableName)->insert(['id' => 8, 'parent_id' => 9, 'order' => 4, 'title' => 'Размеры', 'icon' => 'fa-signal', 'uri' => 'sizes']);
        DB::table($this->tableName)->insert(['id' => 9, 'parent_id' => 0, 'order' => 1, 'title' => 'Аттрибуты товаров', 'icon' => 'fa-bars']);
        DB::table($this->tableName)->insert(['id' => 10, 'parent_id' => 9, 'order' => 5, 'title' => 'Цвет', 'icon' => 'fa-adjust', 'uri' => 'colors']);
        DB::table($this->tableName)->insert(['id' => 11, 'parent_id' => 9, 'order' => 2, 'title' => 'Категории', 'icon' => 'fa-align-left', 'uri' => 'categories']);
        DB::table($this->tableName)->insert(['id' => 12, 'parent_id' => 9, 'order' => 3, 'title' => 'Материал', 'icon' => 'fa-500px', 'uri' => 'fabrics']);
        DB::table($this->tableName)->insert(['id' => 13, 'parent_id' => 9, 'order' => 6, 'title' => 'Высота каблука', 'icon' => 'fa-angle-double-up', 'uri' => 'heel-heights']);
        DB::table($this->tableName)->insert(['id' => 14, 'parent_id' => 9, 'order' => 7, 'title' => 'Сезон', 'icon' => 'fa-envira', 'uri' => 'seasons']);
        DB::table($this->tableName)->insert(['id' => 15, 'parent_id' => 9, 'order' => 8, 'title' => 'Теги', 'icon' => 'fa-tags', 'uri' => 'tags']);
        DB::table($this->tableName)->insert(['id' => 16, 'parent_id' => 9, 'order' => 9, 'title' => 'Бренды', 'icon' => 'fa-adn', 'uri' => 'brands']);
    }
}