<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

if (!isset($_GET['start']) && !isset($_POST['act'])) {
	echo 'неправильный запрос';
	exit();
} elseif (isset($_GET['start'])) {

define('_JEXEC', 1);

if (file_exists(str_replace('/administrator/components/com_jshopping/views/panel/tmpl','',__DIR__) . '/defines.php'))
{
	include_once str_replace('/administrator/components/com_jshopping/views/panel/tmpl','',__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
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
}

$info = array();
$rating = array();

$db = JFactory::getDbo();
$whereProd = "product_publish = '1' AND product_price!=0 AND label_id!=3";
$whereProdJoin = "pr.product_publish = '1' AND pr.product_price!=0 AND pr.label_id!=3";


$query = $db->getQuery(true);
$query = "SELECT MIN(product_price) AS price_min, MAX(product_price) AS price_max, MIN(DATEDIFF(NOW(),product_date_added)) AS newless_min, MAX(DATEDIFF(NOW(),product_date_added)) AS newless_max FROM `#__jshopping_products` WHERE $whereProd";

				  
$db->setQuery($query);
$res_prod = $db->loadObjectList();
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


$query = $db->getQuery(true);

$query = "SELECT MAX(100*(product_old_price - product_price)/product_old_price) AS discount_max FROM `#__jshopping_products` WHERE $whereProd";
				  

$db->setQuery($query);
$res_prod = $db->loadObjectList();

$info['discount'] = array(
	'min' => 0,
	'max' => $res_prod[0]->discount_max,
	'base' => $res_prod[0]->discount_max,
	'summ' => 0
);

$info['season'] = array(
	'summ' => 0
);
$info['action'] = array(
	'summ' => 0
);
$info['hit'] = array(
	'summ' => 0
);

$info['sale'] = array(
	'summ' => 0
);

$info['category'] = array(
	'summ' => 0
);



$query = $db->getQuery(true);

$query = "SELECT pr.product_id as id, pr.product_price AS price, 100*(pr.product_old_price - pr.product_price)/pr.product_old_price AS discount, IF(DATEDIFF(NOW(),pr.product_date_added) = 0,0.001,DATEDIFF(NOW(),pr.product_date_added)) AS newless, IF(pr.extra_field_7 IN($cur_season),100,0) AS season, IF(pr.extra_field_16 = 1,100,0) AS action, IF(pr.label_id = 1,100,0) AS hit, IF(pr.label_id = 2,100,0) AS sale, IF(cat.category_id IN($false_category),0,100) as cat FROM `#__jshopping_products` as pr
LEFT JOIN `#__jshopping_products_to_categories` AS cat USING (product_id)
WHERE $whereProdJoin";
				  
$db->setQuery($query);
$products = $db->loadObjectList();
foreach ($products as $prod) {
	$newless = 100/sqrt($prod->newless);
	$info['newless']['summ']+=abs($newless);
	$price = 100*($prod->price - $info['price']['min'])/$info['price']['base'];
	$info['price']['summ']+=abs($price);
	$discount = 100*($prod->discount - $info['discount']['min'])/$info['discount']['base'];
	$info['discount']['summ']+=abs($discount);
	$info['season']['summ']+=abs($prod->season);
	$info['action']['summ']+=abs($prod->action);
	$info['hit']['summ']+=abs($prod->hit);
	$info['sale']['summ']+=abs($prod->sale);
	$info['category']['summ']+=abs($prod->cat);
	
	$rating[$prod->id] = array(
		'popular'=>0,
		'purshase'=>0,
		'trand'=>0,
		'newless'=>$newless,
		'price'=>$price,
		'discount'=>$discount,
		'photo'=>0,
		'aviable'=>0,
		'season'=>$prod->season,
		'category'=>$prod->cat,
		'action'=>$prod->action,
		'hit'=>$prod->hit,
		'sale'=>$prod->sale,
	);
}
unset($products);



// photo

$info['photo'] = array(
	'min' => 10,
	'max' => 0,
	'base' => 5,
	'summ' => 0
);

$query = $db->getQuery(true);

$query = "SELECT ph.product_id as id, COUNT(ph.image_name) AS count FROM `#__jshopping_products_images` as ph
		  LEFT JOIN `#__jshopping_products` AS pr ON ph.product_id = pr.product_id WHERE pr.product_publish = '1' GROUP BY ph.product_id";
$db->setQuery($query);
$res_photo = $db->loadObjectList();
foreach ($res_photo as $ph) {
	if(isset($rating[$ph->id])) {
		if ($ph->count == 0) {
			$photo = 0;
		} elseif ($ph->count == 1) {
			$photo = 10;
		} elseif ($ph->count == 2) {
			$photo = 30;
		} elseif ($ph->count == 3) {
			$photo = 50;
		} elseif ($ph->count == 4) {
			$photo = 70;
		} elseif ($ph->count > 4) {
			$photo = 100;
		}
		$info['photo']['summ']+=abs($photo);
		if ($info['photo']['min']>$ph->count) $info['photo']['min'] = $ph->count;
		if ($info['photo']['max']<$ph->count) $info['photo']['max'] = $ph->count;
		$rating[$ph->id]['photo'] = $photo;
	}
}
unset($res_photo);


// aviable

$info['aviable'] = array(
	'min' => 10,
	'max' => 0,
	'base' => 5,
	'summ' => 0
);

$query = $db->getQuery(true);

$query = "SELECT attr.product_id as id, COUNT(attr.id) AS count FROM `#__jshopping_products_attr2` as attr
		  LEFT JOIN `#__jshopping_products` AS pr ON attr.product_id = pr.product_id WHERE pr.product_publish = '1' GROUP BY attr.product_id";
$db->setQuery($query);
$res_aviable = $db->loadObjectList();
foreach ($res_aviable as $av) {
	if(isset($rating[$av->id])) {
		if ($av->count == 0) {
			$aviable = 0;
		} elseif ($av->count == 1) {
			$aviable = 10;
		} elseif ($av->count == 2) {
			$aviable = 30;
		} elseif ($av->count == 3) {
			$aviable = 50;
		} elseif ($av->count == 4) {
			$aviable = 70;
		} elseif ($av->count > 4) {
			$aviable = 100;
		}
		$info['aviable']['summ']+=abs($aviable);
		if ($info['aviable']['min']>$av->count) $info['aviable']['min'] = $av->count;
		if ($info['aviable']['max']<$av->count) $info['aviable']['max'] = $av->count;
		$rating[$av->id]['aviable'] = $aviable;
	}
}
unset($res_aviable);

$context = stream_context_create(array(
	'http' => array(
		'method' => 'GET',
		'header' => 'Authorization: OAuth AgAAAAAb991aAAW4YjwHjdE_60CZpTWD4C4J64o'.PHP_EOL.
					'Content-Type: application/x-yametrika+json' . PHP_EOL
	),
));

	
	

// popular & purshase
$url = 'https://api-metrika.yandex.ru/stat/v1/data';

$params = array(
    'ids'         => '31699806',
    'metrics'     => 'ym:s:productImpressionsUniq,ym:s:productPurchasedUniq',
    'dimensions'  => 'ym:s:productID',
    'date1'       => '30daysAgo',
    'date2'       => 'yesterday',
    'sort'        => 'ym:s:productID',
    'limit'        => 3000
);

$result_popular = file_get_contents( $url . '?' . http_build_query($params), false, $context);
if (!$result_popular) exit;
$result_popular = json_decode($result_popular, true);
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
	if(isset($rating[$x])) {
		$popular = 100*($v['metrics'][0] - $info['popular']['min'])/$info['popular']['base'];
		$rating[$x]['popular'] = $popular;
		$info['popular']['summ'] += abs($popular);
		$purshase = 100*($v['metrics'][1] - $info['purshase']['min'])/$info['purshase']['base'];
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

$result_tranding = file_get_contents( $url . '?' . http_build_query($params), false, $context);
if (!$result_tranding) exit;
$result_tranding = json_decode($result_tranding, true);

$info['trand'] = array(
	'min' => 0,
	'max' => $result_tranding['max'][0],
	'base' => $result_tranding['max'][0],
	'summ' => 0
);
foreach ($result_tranding['data'] as $v) {
	$x = $v['dimensions'][0]['name'];
	if(isset($rating[$x])) {
		$trand = 100*($v['metrics'][0] - $info['trand']['min'])/$info['trand']['base'];
		$rating[$x]['trand'] = $trand;
		$info['trand']['summ'] += abs($trand);
	}
}
unset($result_tranding);

foreach ($rating as $id=>$val) {
	if ($id>0) {
		$itemRat = 0;
		foreach ($info as $par=>$par_v) $itemRat += $Koef[$par]*$val[$par];
		$itemRat = intval($itemRat);
		$query = "UPDATE `#__jshopping_products` SET extra_field_18 = $itemRat WHERE product_id = $id";
		$db->setQuery($query);
		$db->query();
	}
}


$i_summ = 0;
foreach($info as $k=>$v) {
	$i_summ += abs($v['summ']);
	$ratingConfig['basic_summ'][$k] = array('summ'=>$v['summ']);
}
foreach($info as $k=>$v) {
	$ratingConfig['basic_summ'][$k]['segment'] = $info[$k]['summ']/$i_summ;
}
$ratingConfig['last_update']=date("Y-m-d-H:i");
file_put_contents($pachAd.'rating.log.txt',$ratingConfig['last_update'].' - '.count($rating).' товаров'.PHP_EOL,FILE_APPEND);
unset($rating);
if (!isset($_POST['act'])) {
	$strToFile = '<?'.PHP_EOL.'$ratingConfig = '.var_export($ratingConfig,true).';'.PHP_EOL.'?>';
	file_put_contents($pachAd.'rating.conf.php',$strToFile);
}
?>

