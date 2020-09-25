<?php

use App\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    protected static $attributesList = [
        'category' => [
            'column' => 'category_id',
            'new_id' => [
                1 => 1, // Женская обувь
                2 => 'del', // Мужская обувь
                3 => 12, // Балетки
                4 => 'del', // Туфли,
                5 => 'del', // Мокасины,
                6 => 2, // Туфли
                7 => 11, // Босоножки
                8 => 15, // Сандалии
                9 => 7, // Лоферы
                10 => 9, // Кеды
                11 => 16, // Ботильоны
                12 => 19, // Ботинки
                13 => 23, // Полусапоги
                14 => 22, // Сапоги
                15 => 'del', // Женские аксессуары
                16 => 'del', // Ботинки
                17 => 'del', // Барсетка
                18 => 'del', // Кляссер
                19 => 'del', // Косметичка
                20 => 'del', // Женская сумка
                21 => 'del', // Мужская сумка
                22 => 'del', // Клатч
                23 => 'del', // Женский кошелек
                24 => 'del', // Маникюрный набор
                26 => 1, // Женщинам
                27 => 'del', // Мужчинам
                28 => 'del', // Мужские аксессуары
                29 => 'del', // Маникюрный набор
                30 => 'del', // Мужской кошелек
                31 => 'del', // Слипоны
                32 => 'del', // Кроссовки
                33 => 10, // Слипоны
                34 => 8, // Слипоны и кеды
                35 => 17, // Ботинки и полуботинки
                36 => 21, // Сапоги и полусапоги
                37 => 6, // Кроссовки
                38 => 'del', // Рюкзак
                39 => 'del', // Сертификаты
                40 => 'del', // Сандалии
                41 => 24, // Ботфорты
                42 => 18, // Полуботинки
                43 => 20, // Эспадрильи
                44 => 13, // Сабо
                45 => 3, // Туфли на каблуке
                46 => 4, // Туфли на шпильке
                47 => 5, // Туфли на низкой подошве
                48 => 'del', // Отзывы
                49 => 14, // Мюли
            ]
        ],
        'sizes' => [],
        'color' =>  [
            'column' => 'color_id',
            'new_id' => [
                22 => 1, // черный
                23 => 6, // белый
                24 => 4, // серый
                37 => 2, // коричневый
                39 => 3, // бежевый
                40 => 5, // молочный
                41 => 7, // бордовый
                42 => 8, // красный
                43 => 10, // желтый
                44 => 11, // зеленый
                45 => 12, // бирюзовый
                46 => 13, // голубой
                47 => 14, // синий
                48 => 15, // фиолетовый
                57 => 9, // оранжевый
                58 => 16, // розовый
            ]
        ],
        'fabrics' => [
            'old_column' => 'extra_field_14',
            'new_id' => [
                25 => 1, // натуральная кожа
                26 => 2, // лакированная кожа
                27 => 3, // натуральная замша
                59 => 4, // экокожа
                60 => 6, // текстиль
                64 => 5, // искусственная кожа
            ]
        ],
        // 'heels' => [],
        // 'styles' => [],
        'season' => [
            'column' => 'season_id',
            'new_id' => [
                17 => 2, // Лето
                18 => 3, // Демисезон
                19 => 1, // Зима
            ]
        ],
        'tags' => [
            'old_column' => 'extra_field_14',
            'new_id' => [
                // 28 => 000000, // на низкой подошве
                // 29 => 000000, // на низком каблуке
                // 30 => 000000, // на высоком каблуке
                // 31 => 000000, // на шпильке
                // 32 => 000000, // на платформе
                // 33 => 000000, // на танкетке
                // 34 => 000000, // на тракторной подошве
                35 => 3, // еврозима
                // 49 => 000000, // без каблука
                // 50 => 000000, // на плоской подошве
                // 51 => 000000, // на протекторе
                // 52 => 000000, // на свадьбу
                // 53 => 000000, // на выпускной
                // 54 => 000000, // вечерняя мода
                55 => 1, // лодочки
            ]
        ],
        'brand' => [
            'column' => 'brand_id',
            'new_id' => [
                1 => 1, // VITACCI
                2 => 2, // Barcelo Biagi
                3 => 3, // Cover
                4 => 4, // Franco Bellucci
                5 => 5, // Franco Osvaldo
                6 => 6, // Grand Gudini
                7 => 7, // Markos
                8 => 8, // Renaissance
                9 => 'del', // Sergio Belotti
                10 => 'del', // Gianni Conti
                11 => 'del', // ZINGER
                12 => 'del', // Mano
                14 => 'del', // F.Marconi
                15 => 9, // Marsalitta
                16 => 10, // Cavaletto
                17 => 11, // Shuanguicheng
                18 => 12, // Fermani
                19 => 13, // Paola Conte
                20 => 14, // Ribellen
                22 => 15, // Modelle
                23 => 16, // La Pinta
                24 => 17, // Gloria Shoes
                25 => 18, // Pera Donna
                26 => 19, // Sherlock Soon
                27 => 20, // Mario Muzi
                28 => 21, // Alpino
                29 => 22, // Amy Michelle
                30 => 23, // D/S
                31 => 24, // Grand Donna
                32 => 25, // Magnolya
                34 => 26, // Mainila
                35 => 27, // Lifexpert
                36 => 28, // Wit Mooni
                37 => 29, // Mossani
                38 => 30, // Vidorcci
                39 => 31, // Evromoda
                40 => 32, // Estomod
                41 => 33, // Ripka
                42 => 34, // Mumin dulun
                43 => 35, // Sasha Fabiani
                44 => 36, // Estro
                45 => 37, // AIDINI
                46 => 38, // Derissi
                47 => 39, // Maria Moro
                48 => 40, // Berkonty
                49 => 41, // Deenoor
                50 => 42, // Berisstini
                51 => 'del', // BAROCCO
                52 => 43, // Tucino
                53 => 44, // AQUAMARIN
                54 => 45, // Chewhite
                55 => 46, // VICTORIA SCARLETT
                56 => 47, // VICES
                57 => 48, // HEALTHSHOES ECONOM
                58 => 49, // KADAR
                59 => 50, // TOP LAND
                60 => 51, // ALBERTO VIOLLI
                61 => 52, // MAST-BUT
                62 => 53, // MARKO-BUT
                63 => 54, // LILY ROSE
                64 => 55, // BETLER
                65 => 56, // BAROCCO style
            ]
        ],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_attributes')->truncate();

        $products = Product::all();

        foreach ($products as $product) {
            foreach (self::$attributesList as $method => $value) {

                if (isset($value['column'])) { // одно значение

                    $insertValue = $value['new_id'][$product->$value['column']] ?? 'del';

                    if ($method == 'color') {
                        $insertValue = explode(',', $insertValue);
                        if (count($insertValue) > 1) {
                            $insertValue = 17; // мультиколор
                        } else {
                            $insertValue = $insertValue[0];
                        }
                    }
                    if ($insertValue === 'del') {
                        $product->delete();
                        continue 2;
                    } else {
                        $product->$value['column'] = $insertValue;
                    }
                } else { // несколько
                    $values = trim($product[$value]);
                    if (empty($values)) {
                        continue;
                    }
                    $values = array_map('trim', explode(',', $values));
                    foreach ($values as $key => &$newValue) {
                        if (isset($value['new_id'][$newValue])) {
                            $newValue = $value['new_id'][$newValue];
                        } else {
                            unset($values[$key]);
                        }
                    }
                    $product->$method()->sync($values);
                }
            }
            // sizes
            $sizesList = Arr::random([1, 2, 3, 4, 5, 6, 7, 8, 9], mt_rand(1, 6));
            $product->$method()->sync($sizesList);

            // slug
            $slug = 'barocco-' . $product->id;
            if (strlen($product['alias_ru-RU']) > 4 && strlen($product['alias_ru-RU']) <= 36) {
                $slug = $product['alias_ru-RU'];
            }
            $product->slug = Str::slug($slug);

            $product->save();
        }
    }
}
