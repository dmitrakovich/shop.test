<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    protected $tableName = 'products';

    protected $startId = 0; // for add new products

    protected $limit = 50000;

    protected $oldTableName = 'cyizj_jshopping_products';

    protected $oldCategoriesTable = 'cyizj_jshopping_products_to_categories';

    protected $oldImagesTable = 'cyizj_jshopping_products_images';

    protected $oldSizesTable = 'cyizj_jshopping_products_attr2';

    protected static $currentDateTime;

    public $attributesList = [
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
                15 => 25, // Женские аксессуары
                16 => 'del', // Ботинки
                17 => 'del', // Барсетка
                18 => 'del', // Кляссер
                19 => 'del', // Косметичка
                20 => 26, // Женская сумка
                21 => 'del', // Мужская сумка
                22 => 27, // Клатч
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
                38 => 28, // Рюкзак
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
            ],
        ],
        'sizes' => [
            'new_id' => [
                12 => 4, // 35
                13 => 5, // 36
                14 => 6, // 37
                15 => 7, // 38
                16 => 8, // 39
                17 => 9, // 40
                18 => 10, // 41
                // 19 => 'del', // 42
                // 20 => 'del', // 43
                // 21 => 'del', // 44
                // 22 => 'del', // 45
                23 => 2, // 33
                24 => 3, // 34
            ],
        ],
        'collection' => [
            'column' => 'collection_id',
            'new_id' => [
                66 => 66, // Весна-Лето 2020
                67 => 67, // Осень-Зима 20/21
                70 => 70, // Весна-Лето 2021
                71 => 71, // Осень-Зима 19/20
                72 => 72, // Весна-Лето 2019
                73 => 73, // Осень-Зима 18/19
                74 => 74, // Весна-Лето 2018
                75 => 75, // Осень-Зима 17/18
                76 => 76, // Весна-Лето 2017
            ],
        ],
        'colors' => [
            'old_column' => 'extra_field_13',
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
            ],
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
            ],
        ],
        // 'heels' => [],
        // 'styles' => [],
        'season' => [
            'column' => 'season_id',
            'new_id' => [
                17 => 2, // Лето
                18 => 3, // Демисезон
                19 => 1, // Зима
            ],
        ],
        'tags' => [
            'old_column' => 'extra_field_15',
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
                68 => 4, // Челси
                69 => 5, // Казаки
                88 => 10, // Рептилия
                89 => 9, // Пряжка
                90 => 7, // Цепь
            ],
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
                51 => 57, // BAROCCO
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
                66 => 59, // COSOTTINNI
                67 => 67, // BASCONI
                68 => 61, // ROMANTIC PERCENTAGE
                69 => 68, // GELUSSI
                70 => 69, // PAVI
            ],
        ],
        'manufacturer' => [
            'column' => 'manufacturer_id',
            'new_id' => [
                87 => 87, // Фабрика №10
                86 => 86, // Фабрика №9
                85 => 85, // Фабрика №8
                84 => 84, // Фабрика №7
                83 => 83, // Фабрика №6
                82 => 82, // Фабрика №5
                81 => 81, // Фабрика №4
                80 => 80, // Фабрика №3
                79 => 79, // Фабрика №2
                78 => 78, // Фабрика №1
            ],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::$currentDateTime = date('Y-m-d H:i:s');

        if ($this->startId <= 0) {
            DB::table($this->tableName)->truncate();
            DB::table('product_attributes')->truncate();
            DB::table('urls')->where('model_type', Product::class)->delete();
            DB::table('media')->where('model_type', Product::class)->delete();
        }

        $oldProducts = DB::connection('old_mysql')
            ->table($this->oldTableName)
            ->leftJoin($this->oldCategoriesTable, "$this->oldTableName.product_id", '=', "$this->oldCategoriesTable.product_id")
            ->where('product_date_added', '<>', '0000-00-00 00:00:00')
            ->where('date_modify', '<>', '0000-00-00 00:00:00')
            ->where('alias_ru-RU', '<>', '')
            ->where("$this->oldTableName.product_id", '>', $this->startId)
            ->limit($this->limit)
            ->get([
                "{$this->oldTableName}.product_id as id",
                'product_publish as publish',
                'alias_ru-RU as slug',
                'name_ru-RU as sku',
                'product_buy_price as buy_price',
                'product_price as price',
                'product_old_price as old_price',
                'category_id',
                'product_manufacturer_id as brand_id',
                'extra_field_1 as color_txt',
                'extra_field_2 as fabric_top_txt',
                'extra_field_8 as fabric_inner_txt',
                'extra_field_9 as fabric_insole_txt',
                'extra_field_10 as fabric_outsole_txt',
                'extra_field_11 as heel_txt',
                'description_ru-RU as description',
                'product_date_added as created_at',
                'date_modify as updated_at',

                'label_id', // ярлык на товарах
                'extra_field_3 as collection_id', // Коллекция
                'extra_field_7 as season_id', // Сезон
                // 'extra_field_12', // Размер аксессуара
                'extra_field_13', // Цвет фильтра
                'extra_field_14', // Материал фильтра
                'extra_field_15', // Теги
                'extra_field_16 as action', // Акция
                // 'extra_field_17', // Поднять
                'extra_field_18 as rating', // Рейтинг
                'extra_field_21 as manufacturer_id', // Производитель
            ])
            ->keyBy('id');

        $oldProductImages = DB::connection('old_mysql')
            ->table($this->oldImagesTable)
            ->get(['product_id', 'image_name', 'name', 'ordering'])
            ->groupBy('product_id')
            ->toArray();

        $oldProductSizes = DB::connection('old_mysql')
            ->table($this->oldSizesTable)
            // ->leftJoin('cyizj_jshopping_attr_values', "$this->oldSizesTable.attr_value_id", '=', 'cyizj_jshopping_attr_values.value_id')
            ->orderByDesc('product_id')
            ->get(['product_id', 'attr_value_id'])
            ->groupBy('product_id')
            ->toArray();

        foreach ($oldProducts as $productId => $oldProduct) {
            // fixes
            $oldProduct->action = intval($oldProduct->action);
            $oldProduct->rating = $oldProduct->rating > 0 ? $oldProduct->rating : 0;

            $insertData = $oldProduct = (array)$oldProduct;
            unset($insertData['extra_field_3'],
                $insertData['extra_field_13'],
                $insertData['extra_field_14'],
                $insertData['extra_field_15']);

            foreach ($this->attributesList as $method => $value) {
                if (isset($value['column'])) { // одно значение
                    $insertValue = $value['new_id'][$oldProduct[$value['column']]] ?? 0;

                    if ($insertValue === 'del') {
                        continue 2;
                    } else {
                        $insertData[$value['column']] = $insertValue;
                    }
                }
            }

            $insertData['deleted_at'] = $insertData['publish'] ? null : self::$currentDateTime;
            unset($insertData['publish']);

            $product = new Product($insertData);
            $product->save();

            foreach ($this->attributesList as $method => $value) {
                if (isset($value['old_column'])) { // несколько
                    $values = trim($oldProduct[$value['old_column']]);
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
            $sizesList = array_column($oldProductSizes[$productId] ?? [], 'attr_value_id');
            foreach ($sizesList as $key => &$newValue) {
                if (isset($this->attributesList['sizes']['new_id'][$newValue])) {
                    $newValue = $this->attributesList['sizes']['new_id'][$newValue];
                } else {
                    unset($sizesList[$key]);
                }
            }
            $sizesList = empty($sizesList) ? [1] : $sizesList;
            $product->sizes()->sync($sizesList);

            // images
            $imagesList = $oldProductImages[$productId] ?? [];
            foreach ($imagesList as $image) {
                // $pathToFile = 'C:/OSPanel/domains/shop.test/public/images/products/' . $image;
                $urlToFile = 'https://modny.by/components/com_jshopping/files/img_products/' . $image->image_name;
                $customProperties = [];

                if ($image->name === 'imidj') {
                    $customProperties['imidj'] = 1;
                }

                if (strpos($image->name, 'youtube') !== false) {
                    $customProperties['video'] = $image->name;
                }

                try {
                    $media = $product
                        // ->addMedia($pathToFile)
                        ->addMediaFromUrl($urlToFile)
                        ->preservingOriginal();

                    if (!empty($customProperties)) {
                        $media->withCustomProperties($customProperties);
                    }

                    $media->toMediaCollection();
                } catch (\Throwable $th) {
                    // echo $th->getMessage();
                }
            }

            $product->save();

            $product->url()->create([
                'slug' => $product->slug,
                'model_id' => $product->id,
                'model_type' => Product::class,
            ]);
        }
    }
}
