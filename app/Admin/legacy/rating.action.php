<?php

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// как я понял это для обновления по крону через wget
/*if (!isset($_GET['start']) && !isset($_POST['act'])) {
	echo 'неправильный запрос';
	exit();
} elseif (isset($_GET['start'])) {
    define('_JEXEC', 1);

    if (file_exists(str_replace('/administrator/components/com_jshopping/views/panel/tmpl','',__DIR__) . '/defines.php')) {
        include_once str_replace('/administrator/components/com_jshopping/views/panel/tmpl','',__DIR__) . '/defines.php';
    }

    if (!defined('_JDEFINES')) {
        define('JPATH_BASE', str_replace('/administrator/components/com_jshopping/views/panel/tmpl','',__DIR__));
        require_once JPATH_BASE . '/includes/defines.php';
    }

    require_once JPATH_BASE . '/includes/framework.php';

    JFactory::getApplication('site')->initialise();
    $pachAd = JPATH_BASE.'/administrator/components/com_jshopping/views/panel/tmpl/';
    include_once($pachAd.'rating.conf.php');

    // Предустановки
    $cur_season = $ratingConfig['cur_season']; // текущие сезоны
    $false_category = $ratingConfig['false_category']; // исключенные категории
    $Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];
}*/

$info = array();
$rating = array();

$res_prod = DB::table('products')
    ->where('publish', true)
    ->where('price', '<>', 0)
    ->where('label_id', '<>', 3)
    ->selectRaw('MIN(price) AS price_min, MAX(price) AS price_max,
        MIN(DATEDIFF(NOW(), created_at)) AS newless_min,
        MAX(DATEDIFF(NOW(), created_at)) AS newless_max')
    ->get();

$info['newless'] = array(
    'min' => $res_prod[0]->newless_min,
    'max' => $res_prod[0]->newless_max,
    'base' => $res_prod[0]->newless_max - $res_prod[0]->newless_min,
    'summ' => 0
);

$info['price'] = array(
    'min' => $res_prod[0]->price_min,
    'max' => $res_prod[0]->price_max,
    'base' => $res_prod[0]->price_max - $res_prod[0]->price_min,
    'summ' => 0
);


$res_prod = DB::table('products')
    ->where('publish', true)
    ->where('price', '<>', 0)
    ->where('label_id', '<>', 3)
    ->selectRaw('MAX(100*(old_price - price)/old_price) AS discount_max')
    ->get();

$info['discount'] = array(
    'min' => 0,
    'max' => $res_prod[0]->discount_max,
    'base' => $res_prod[0]->discount_max,
    'summ' => 0
);


$info['season'] = ['summ' => 0];
$info['action'] = ['summ' => 0];
$info['hit'] = ['summ' => 0];
$info['sale'] = ['summ' => 0];
$info['category'] = ['summ' => 0];


$select = "id, price, 100*(old_price - price)/old_price AS discount,
    IF(DATEDIFF(NOW(), created_at) = 0,0.001, DATEDIFF(NOW(), created_at)) AS newless,
    IF(season_id IN($cur_season),100,0) AS season,
    IF(action = 1,100,0) AS action,
    IF(label_id = 1,100,0) AS hit,
    IF(label_id = 2,100,0) AS sale,
    IF(category_id IN($false_category),0,100) as cat";

$products = DB::table('products')
    ->where('publish', true)
    ->where('price', '<>', 0)
    ->where('label_id', '<>', 3)
    ->selectRaw($select)
    ->get();

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

    $rating[$prod->id] = array(
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
    );
}

$productsIds = $products->pluck('id')->toArray();
unset($products);

// photo
$info['photo'] = array(
    'min' => 10,
    'max' => 0,
    'base' => 5,
    'summ' => 0
);

$productsCounters = Product::select(['id'])
    ->whereIn('id', $productsIds)
    ->withCount('media')
    ->withCount('sizes')
    ->get();

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
        if ($info['photo']['min'] > $ph->media_count) $info['photo']['min'] = $ph->media_count;
        if ($info['photo']['max'] < $ph->media_count) $info['photo']['max'] = $ph->media_count;
        $rating[$ph->id]['photo'] = $photo;
    }
}

// aviable
$info['aviable'] = array(
    'min' => 10,
    'max' => 0,
    'base' => 5,
    'summ' => 0
);


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
        if ($info['aviable']['min'] > $av->sizes_count) $info['aviable']['min'] = $av->sizes_count;
        if ($info['aviable']['max'] < $av->sizes_count) $info['aviable']['max'] = $av->sizes_count;
        $rating[$av->id]['aviable'] = $aviable;
    }
}
unset($productsCounters);


// popular & purshase
$params = array(
    'ids'         => '31699806',
    'metrics'     => 'ym:s:productImpressionsUniq,ym:s:productPurchasedUniq',
    'dimensions'  => 'ym:s:productID',
    'date1'       => '30daysAgo',
    'date2'       => 'yesterday',
    'sort'        => 'ym:s:productID',
    'limit'        => 3000
);

$result_popular = Http::withHeaders(['Authorization' => 'OAuth AgAAAAAb991aAAW4YjwHjdE_60CZpTWD4C4J64o'])
    ->get('https://api-metrika.yandex.ru/stat/v1/data', $params)
    ->json();

if (empty($result_popular)) {
    throw new Exception('Яндекс метрика не вернула данные');
}

$info['popular'] = array(
    'min' => 0,
    'max' => $result_popular['max'][0],
    'base' => $result_popular['max'][0],
    'summ' => 0
);

$info['purshase'] = array(
    'min' => 0,
    'max' => $result_popular['max'][1],
    'base' => $result_popular['max'][1],
    'summ' => 0
);

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
$params = array(
    'ids'         => '31699806',
    'metrics'     => 'ym:s:productBasketsUniq',
    'dimensions'  => 'ym:s:productID',
    'date1'       => '7daysAgo',
    'date2'       => 'yesterday',
    'sort'        => 'ym:s:productID',
    'limit'        => 3000
);

$result_tranding = Http::withHeaders(['Authorization' => 'OAuth AgAAAAAb991aAAW4YjwHjdE_60CZpTWD4C4J64o'])
    ->get('https://api-metrika.yandex.ru/stat/v1/data', $params)
    ->json();

if (empty($result_tranding)) {
    throw new Exception('Яндекс метрика не вернула данные (2)');
}

$info['trand'] = array(
    'min' => 0,
    'max' => $result_tranding['max'][0],
    'base' => $result_tranding['max'][0],
    'summ' => 0
);

foreach ($result_tranding['data'] as $v) {
    $x = $v['dimensions'][0]['name'];
    if (isset($rating[$x])) {
        $trand = 100 * ($v['metrics'][0] - $info['trand']['min']) / $info['trand']['base'];
        $rating[$x]['trand'] = $trand;
        $info['trand']['summ'] += abs($trand);
    }
}
unset($result_tranding);


foreach ($rating as $id => $val) {
    if ($id > 0) {
        $itemRat = 0;
        foreach ($info as $par => $par_v) {
            $itemRat += $Koef[$par] * $val[$par];
        }
        DB::table('products')
            ->where('id', $id)
            ->update(['rating' => intval(abs($itemRat))]);
    }
}

$i_summ = 0;
foreach ($info as $k => $v) {
    $i_summ += abs($v['summ']);
    $ratingConfig['basic_summ'][$k] = array('summ' => $v['summ']);
}

foreach ($info as $k => $v) {
    $ratingConfig['basic_summ'][$k]['segment'] = $info[$k]['summ'] / $i_summ;
}

$ratingConfig['last_update'] = date("Y-m-d-H:i");

file_put_contents(database_path('files/rating.log.txt'), $ratingConfig['last_update'] . ' - ' . count($rating) . ' товаров' . PHP_EOL, FILE_APPEND);
unset($rating);

if (!isset($_POST['act'])) {
    $strToFile = "<?php\nreturn " . var_export($ratingConfig, true) . ';';
    file_put_contents(database_path('files/rating.conf.php'), $strToFile);
}
