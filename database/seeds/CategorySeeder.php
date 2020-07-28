<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [];

        $cName = 'Женщинам';
        $categories[] = [
            'title' => $cName,
            'slug' => Str::slug($cName),
            'parent_id' => 0,
        ];
        $titles = [
            'Лоферы',
            'Балетки',
            'Сабо',
            'Ботинки',
            'Туфли',
            'Сандали',
            'Босоножки',
            'Сапоги',
            'Ботильоны',
            'Слипоны',
            'Кеды',
            'Эспадрильи'
        ];
        foreach ($titles as $key => $cName) {
            $parentId = ($key > 4) ? mt_rand(1, 4) : 1;
            $categories[] = [
                'title' => $cName,
                'slug' => Str::slug($cName),
                'parent_id' => $parentId,
            ];
        }
        DB::table('categories')->insert($categories);
    }
}
