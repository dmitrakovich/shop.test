<?php

namespace App\Jobs;

use App\Models\Config;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateProductsRatingJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->log('Старт');
        $ratingConfigModel = Config::findOrFail('rating');
        $ratingConfig = $ratingConfigModel->config;
        $counterYandexId = config('services.yandex.counter_id');

        // Предустановки
        $Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];

        $info = $this->getInitInfo();
        $this->setNewlessesAndPrices($info);
        $this->setDiscount($info);

        $rating = [];
        $products = $this->getCalculatedProductsData($ratingConfig);
        foreach ($products as $prod) {
            $newless = 100 / sqrt($prod->newless);
            $info['newless']['summ'] += abs($newless);
            $price = 100 * ($prod->price - $info['price']['min']) / $info['price']['base'];
            $info['price']['summ'] += abs($price);
            $discount = 100 * ($prod->discount - $info['discount']['min']) / $info['discount']['base'];
            $info['discount']['summ'] += abs($discount);
            $info['season']['summ'] += abs($prod->season);
            $info['action']['summ'] += abs($prod->action);
            $info['hit']['summ'] += abs($prod->hit);
            $info['sale']['summ'] += abs($prod->sale);
            $info['category']['summ'] += abs($prod->cat);

            $rating[$prod->id] = [
                'popular' => 0,
                'purshase' => 0,
                'trand' => 0,
                'newless' => $newless,
                'price' => $price,
                'discount' => $discount,
                'photo' => 0,
                'aviable' => 0,
                'season' => $prod->season,
                'category' => $prod->cat,
                'action' => $prod->action,
                'hit' => $prod->hit,
                'sale' => $prod->sale,
            ];
        }

        $productsIds = $products->pluck('id')->toArray();
        unset($products);

        // photo
        $productsCounters = $this->getProductsCounters($productsIds);
        foreach ($productsCounters as $ph) {
            if (isset($rating[$ph->id])) {
                if ($ph->media_count == 0) {
                    $photo = 0;
                } elseif ($ph->media_count == 1) {
                    $photo = 10;
                } elseif ($ph->media_count == 2) {
                    $photo = 30;
                } elseif ($ph->media_count == 3) {
                    $photo = 50;
                } elseif ($ph->media_count == 4) {
                    $photo = 70;
                } elseif ($ph->media_count > 4) {
                    $photo = 100;
                }

                $info['photo']['summ'] += abs($photo);
                if ($info['photo']['min'] > $ph->media_count) {
                    $info['photo']['min'] = $ph->media_count;
                }
                if ($info['photo']['max'] < $ph->media_count) {
                    $info['photo']['max'] = $ph->media_count;
                }
                $rating[$ph->id]['photo'] = $photo;
            }
        }

        // aviable
        foreach ($productsCounters as $av) {
            if (isset($rating[$av->id])) {
                if ($av->sizes_count == 0) {
                    $aviable = 0;
                } elseif ($av->sizes_count == 1) {
                    $aviable = 10;
                } elseif ($av->sizes_count == 2) {
                    $aviable = 30;
                } elseif ($av->sizes_count == 3) {
                    $aviable = 50;
                } elseif ($av->sizes_count == 4) {
                    $aviable = 70;
                } elseif ($av->sizes_count > 4) {
                    $aviable = 100;
                }

                $info['aviable']['summ'] += abs($aviable);
                if ($info['aviable']['min'] > $av->sizes_count) {
                    $info['aviable']['min'] = $av->sizes_count;
                }
                if ($info['aviable']['max'] < $av->sizes_count) {
                    $info['aviable']['max'] = $av->sizes_count;
                }
                $rating[$av->id]['aviable'] = $aviable;
            }
        }
        unset($productsCounters);

        // popular & purshase
        $result_popular = $this->getYandexMetrikaData([
            'ids' => $counterYandexId,
            'metrics' => 'ym:s:productImpressionsUniq,ym:s:productPurchasedUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '30daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        $info['popular'] = [
            'min' => 0,
            'max' => $result_popular['max'][0],
            'base' => $result_popular['max'][0],
            'summ' => 0,
        ];

        $info['purshase'] = [
            'min' => 0,
            'max' => $result_popular['max'][1],
            'base' => $result_popular['max'][1],
            'summ' => 0,
        ];

        foreach ($result_popular['data'] as $v) {
            $x = $v['dimensions'][0]['name'];
            if (isset($rating[$x])) {
                $popular = 100 * ($v['metrics'][0] - $info['popular']['min']) / $info['popular']['base'];
                $rating[$x]['popular'] = $popular;
                $info['popular']['summ'] += abs($popular);
                $purshase = 100 * ($v['metrics'][1] - $info['purshase']['min']) / $info['purshase']['base'];
                $rating[$x]['purshase'] = $purshase;
                $info['purshase']['summ'] += abs($purshase);
            }
        }
        unset($result_popular);

        // trand
        $result_tranding = $this->getYandexMetrikaData([
            'ids' => $counterYandexId,
            'metrics' => 'ym:s:productBasketsUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '7daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        $info['trand'] = [
            'min' => 0,
            'max' => $result_tranding['max'][0],
            'base' => $result_tranding['max'][0],
            'summ' => 0,
        ];

        foreach ($result_tranding['data'] as $v) {
            $x = $v['dimensions'][0]['name'];
            if (isset($rating[$x])) {
                $trand = 100 * ($v['metrics'][0] - $info['trand']['min']) / $info['trand']['base'];
                $rating[$x]['trand'] = $trand;
                $info['trand']['summ'] += abs($trand);
            }
        }
        unset($result_tranding);

        foreach (array_chunk($rating, 1000, true) as $ratingChunk) {
            $cases = '';
            foreach ($ratingChunk as $id => $val) {
                if ($id > 0) {
                    $productRating = 0;
                    foreach ($info as $par => $par_v) {
                        $productRating += $Koef[$par] * $val[$par];
                    }
                    $productRating = intval(abs($productRating));
                    $cases .= "WHEN id = {$id} THEN {$productRating} ";
                }
            }
            DB::statement("UPDATE products SET rating = (CASE {$cases} ELSE rating END)");
        }

        $i_summ = 0;
        foreach ($info as $k => $v) {
            $i_summ += abs($v['summ']);
            $ratingConfig['basic_summ'][$k] = ['summ' => $v['summ']];
        }

        foreach ($info as $k => $v) {
            $ratingConfig['basic_summ'][$k]['segment'] = $info[$k]['summ'] / $i_summ;
        }

        $ratingConfig['last_update'] = date('Y-m-d H:i:s');

        $this->log(count($rating) . ' товаров');
        unset($rating);

        $ratingConfigModel->update(['config' => $ratingConfig]);

        $this->log('Успешно выполнено');
    }

    private function getInitInfo(): array
    {
        return [
            'season' => ['summ' => 0],
            'action' => ['summ' => 0],
            'hit' => ['summ' => 0],
            'sale' => ['summ' => 0],
            'category' => ['summ' => 0],
            'photo' => ['min' => 10, 'max' => 0, 'base' => 5, 'summ' => 0],
            'aviable' => ['min' => 10, 'max' => 0, 'base' => 5, 'summ' => 0],
        ];
    }

    /**
     * Sets the newlesses and prices information in the provided array.
     */
    private function setNewlessesAndPrices(array &$info): void
    {
        $result = DB::table('products')
            ->whereNull('deleted_at')
            ->where('price', '<>', 0)
            ->where('label_id', '<>', 3)
            ->selectRaw('MIN(price) AS price_min, MAX(price) AS price_max,
                MIN(DATEDIFF(NOW(), created_at)) AS newless_min,
                MAX(DATEDIFF(NOW(), created_at)) AS newless_max')
            ->first();

        $info['newless'] = [
            'min' => $result->newless_min,
            'max' => $result->newless_max,
            'base' => $result->newless_max - $result->newless_min,
            'summ' => 0,
        ];
        $info['price'] = [
            'min' => $result->price_min,
            'max' => $result->price_max,
            'base' => $result->price_max - $result->price_min,
            'summ' => 0,
        ];
    }

    /**
     * Sets the discount information in the provided array.
     */
    private function setDiscount(array &$info): void
    {
        $maxDiscount = DB::table('products')
            ->whereNull('deleted_at')
            ->where('price', '<>', 0)
            ->where('label_id', '<>', 3)
            ->selectRaw('MAX(100*(old_price - price)/old_price) AS max_discount')
            ->value('max_discount');

        $info['discount'] = [
            'min' => 0,
            'max' => $maxDiscount,
            'base' => $maxDiscount,
            'summ' => 0,
        ];
    }

    /**
     * Retrieves the calculated products data based on the provided configuration.
     */
    private function getCalculatedProductsData(array $config): Collection
    {
        $currentSeasonId = $config['cur_season'];
        $excludedCategories = $config['false_category'];

        return DB::table('products')
            ->whereNull('deleted_at')
            ->where('price', '<>', 0)
            ->where('label_id', '<>', 3)
            ->selectRaw(<<<SQL
                id, price, 100*(old_price - price)/old_price AS discount,
                IF(DATEDIFF(NOW(), created_at) = 0,0.001, DATEDIFF(NOW(), created_at)) AS newless,
                IF(season_id IN($currentSeasonId),100,0) AS season,
                IF(action = 1,100,0) AS action,
                IF(label_id = 1,100,0) AS hit,
                IF(label_id = 2,100,0) AS sale,
                IF(category_id IN($excludedCategories),0,100) as cat
            SQL)
            ->get();
    }

    /**
     * Retrieves the counters for the given product IDs.
     */
    private function getProductsCounters(array $productsIds): EloquentCollection
    {
        $products = Product::select(['id'])
            ->whereIn('id', $productsIds)
            ->withCount('media')
            ->get();

        $sizeCounts = DB::table('product_attributes')
            ->where('attribute_type', Size::class)
            ->whereIn('product_id', $productsIds)
            ->selectRaw('count(product_id) as count, product_id')
            ->groupBy('product_id')
            ->pluck('count', 'product_id')
            ->toArray();

        foreach ($products as $product) {
            $product->sizes_count = $sizeCounts[$product->id] ?? 0;
        }

        return $products;
    }

    /**
     * Get metrika data from yandex api
     *
     * @throws \Exception
     */
    private function getYandexMetrikaData(array $params): array
    {
        $result = Http::withToken(config('services.yandex.token'), 'OAuth')
            ->get('https://api-metrika.yandex.ru/stat/v1/data', $params)
            ->json();

        if (empty($result) || isset($result['errors'])) {
            throw new \Exception($result['message'] ?? 'Яндекс метрика не вернула данные');
        }

        return $result;
    }
}
