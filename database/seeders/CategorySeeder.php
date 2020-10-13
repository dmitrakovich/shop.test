<?php

namespace Database\Seeders;

use App\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        DB::table('categories')->truncate();

        $catData = [
            [0, 'Каталог'],                       // 1
            [1, 'Туфли'],                         // 2
            [2, 'Туфли на каблуке'],              // 3
            [2, 'Туфли на шпильке'],              // 4
            [2, 'Туфли на низкой подошве'],       // 5
            [1, 'Кроссовки'],                     // 6
            [1, 'Лоферы'],                        // 7
            [1, 'Слипоны и кеды'],                // 8
            [8, 'Кеды'],                          // 9
            [8, 'Слипоны'],                       // 10
            [1, 'Босоножки'],                     // 11
            [1, 'Балетки'],                       // 12
            [1, 'Сабо'],                          // 13
            [1, 'Мюли'],                          // 14
            [1, 'Сандалии'],                      // 15
            [1, 'Ботильоны'],                     // 16
            [1, 'Ботинки и полуботинки'],         // 17
            [17, 'Полуботинки'],                  // 18
            [17, 'Ботинки'],                      // 19
            [1, 'Эспадрильи'],                    // 20
            [1, 'Сапоги и полусапоги'],           // 21
            [21, 'Сапоги'],                       // 22
            [21, 'Полусапоги'],                   // 23
            [21, 'Ботфорты'],                     // 24
        ];

        foreach ($catData as list($parentId, $cName)) {
            $category = new Category();
            $category->title = $cName;
            $category->slug = $cName == 'Каталог' ? 'catalog' : Str::slug($cName);
            $category->parent_id = $parentId;
            $category->generatePath()->save();
        }
    }
}
