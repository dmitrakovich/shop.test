<?php

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;


// Предустановки
$thtime = date("Y-m-d-H:i:s");
$dShop = array();
$dSklad = array();
$dSize = array();
$fltr = array(
	'place'=>array("* ЗАО САНДАЛ","* ИП Ермаков И.В.*","ЗАО САНДАЛ","ИП Ермаков И.В.","ИНТЕРНЕТ МАГАЗИН"),
	'brand'=>array(),
	'count'=>999,
	'status'=>'all',
	'season'=>array('17','18','19'),
	'days'=>365,
	'excel'=>0
);

foreach ($fltr as $fltrK => $fltrV) {
    if (isset($_POST[$fltrK])) {
        $fltr[$fltrK] = $_POST[$fltrK];
    }
}

$statusArr = array('all'=>'Все','new'=>'Новинки','sale'=>'Скидки','sold'=>'Продано');
$seasonArr = array('17'=>'Лето','18'=>'Деми','19'=>'Зима');


// Продукты
$res_prod = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
    ->with('media')
    // ->with('category')
    ->get([
        'products.id',
        'brand_id',
        'brands.name as brand',
        'category_id as cat_id',
        'title as name',
        'publish',
        'label_id as label',
        'price',
        'season_id as season'
    ]);


// Модели по дате
$prod_fordate = DB::table('products')
    ->where('publish', false)
    ->whereRaw('DATEDIFF(NOW(), created_at) < ' . (int)$fltr['days'])
    ->get(['id', 'created_at as adddate']);

$prod_actuel = array();
foreach ($prod_fordate as $prod_fordate_v) {
    $prod_actuel[] = $prod_fordate_v->id;
}

// Категории
$categories = Category::pluck('title', 'id');

foreach ($res_prod as $product) {
	$ibrand = trim($product->brand);
	$sbrand = strtolower($ibrand);
	$smallN = smallArt($product->name);
	$checkB = str_replace(' ','',$ibrand);

	$itemB = [
		'id' => $product->id,
		'cat' =>  $categories[$product->cat_id] ?? '',
		'status' => $product->publish,
		'articul' => $product->name,
		'brand' => $ibrand,
		'img_url' => optional($product->getFirstMedia())->getUrl('thumb'),
		'img_path' => optional($product->getFirstMedia())->getPath('thumb'),
		'label' => $product->label,
		'season' => $product->season,
		'price' => $product->price
	];

	if (!empty($checkB)) {
		if (!isset($dShop[$sbrand])) $dShop[$sbrand] = array();
		$dShop[$sbrand][$smallN] = $itemB;
	}

	// Вставка в склад популярных моделей
	if (!empty($checkB) && in_array($product->id,$prod_actuel) && $fltr['status'] == 'sold') {
			if (!isset($dSklad[$sbrand])) $dSklad[$sbrand] = array();
			if (!isset($dSklad[$sbrand][$smallN])) $dSklad[$sbrand][$smallN] = $itemB;
			$dSklad[$sbrand][$smallN]['discount'] = "0%";
			$dSklad[$sbrand][$smallN]['price_im'] = $product->price;
			$dSklad[$sbrand][$smallN]['size'] = array();
	}
}

function smallArt($txt) {
	$r = array(' ','-','.','_','*');
	return mb_strtolower(str_replace($r,'',$txt));
}

unset($res_prod);


// Яндекс
$hrefF = Http::withToken(config('api.yandex.token'), 'OAuth')
    ->get('https://cloud-api.yandex.net/v1/disk/resources/download', ['path' => '/Ostatki/ostatki.txt'])
    ->json();

if (empty($hrefF)) {
	throw new Exception('Ошибка! Яндекс Диск не получил ссылку на скачивание.');
}

$resI = file_get_contents($hrefF['href']);
$resI = mb_convert_encoding($resI, "UTF-8", "windows-1251");
$resI = explode("\n",$resI);

$place = '';
$placeArr = array();
$brandArr = array();

for ($i=4;$i<count($resI);$i++) {
	// помечаем в каком складе
	if (mb_strpos($resI[$i],'Место хранения')!==false) {
		$place = str_replace("\t","",$resI[$i]);
		$place = str_replace(array('Место хранения : ','"'),'',$place);
		$place = trim($place);
		//if (in_array($place,array("СКЛАД ЗИМА","СКЛАД ЛЕТО"))) break;
		if (!in_array($place,$placeArr)) $placeArr[] = $place;
	}

	if (mb_strpos($resI[$i],' | ')!==false) {
		$itemA = explode("\t",$resI[$i]);
		$itemC = explode(' | ',$itemA[2]);
		$ibrand = trim($itemA[3]);
		$sbrand = strtolower($ibrand);
		$checkB = str_replace(' ','',$ibrand);

		// сохраняем бренд в список
		if (!empty($checkB) && !isset($brandArr[$sbrand])) $brandArr[$sbrand] = $ibrand;

		$smallN = smallArt($itemC[0]);
		$itemCount = intval($itemA[5]);
		$isize = trim($itemA[4]);

		$itemB = array(
			'img_url'=>'',
			'articul'=>$itemC[0],
			'brand'=>$ibrand,
			'cat'=>str_replace(" женские","",$itemC[1]),
			'price'=>floatval(str_replace("'00","",$itemA[6])),
			'discount'=>'0%',
			'price_im'=>'-',
			'size'=>array()
		);

		if (!empty($checkB) && !empty($smallN) && $itemCount>0) {
			// костыль для русских и английских букв
			if (!isset($dShop[$sbrand][$smallN])) {
				$eng_symb = array('a','b','c','e','h','k','m','o','p','t','x');
				$rus_symb = array('а','в','с','е','н','к','м','о','р','т','х');
				$smallNS = str_replace($eng_symb,$rus_symb,$smallN);
				if (isset($dShop[$sbrand][$smallNS])) $smallN = $smallNS;
				$smallNS = str_replace($rus_symb,$eng_symb,$smallN);
				if (isset($dShop[$sbrand][$smallNS])) $smallN = $smallNS;
			}

			if (isset($dShop[$sbrand][$smallN])) { // подтягиваем данные из каталога
				$shopInfo = $dShop[$sbrand][$smallN];
				$itemB['cat']=$shopInfo['cat'];
				$discount = 0;
				if (!empty($itemB['price']) && $itemB['price']!=0) {
					$discount = ceil(($shopInfo['price'] - $itemB['price'])/$itemB['price'] * 100);
				}
				$itemB['discount']=$discount.'%';
				$itemB['price_im']=$shopInfo['price'];
				$itemB['img_url']=$shopInfo['img_url'];
				$itemB['img_path']=$shopInfo['img_path'];
				$itemB['season']=$shopInfo['season'];
			}


			if (!isset($dSklad[$sbrand])) $dSklad[$sbrand] = array();
			if (!isset($dSklad[$sbrand][$smallN])) $dSklad[$sbrand][$smallN] = $itemB;
			if (!isset($dSklad[$sbrand][$smallN]['size'][$place])) $dSklad[$sbrand][$smallN]['size'][$place] = array();

			while ($itemCount != 0) {
				$dSklad[$sbrand][$smallN]['size'][$place][] = $isize;
				$itemCount--;
			}
		}
	}
}

unset($resI);
ksort($dSklad);
ksort($brandArr);
?>



<style type="text/css">
	.adminka_sub_title {
		text-indent: 15px;
		font-weight: bold;
	}
	.adminka_field {
		margin: 10px 0;
		padding: 0 10px;
	}

	/****Filter*****/
	.filterSklad {display:none;}
	.filterSklad.active {display:block;}
	.filterSkladName {
		text-decoration:underline;
		cursor:pointer;
		text-transform:uppercase;
		font-weight:bold;
	}
	.filterSkladName .filterSkladSymbol {
		font-size:large;
		text-decoration:none;
		font-weight:normal;
	}
	.adminka_field_name {font-weight:bold;}
	.adminka_field_brand, .adminka_field_place {
		display:flex;
		flex-flow: row wrap;
	}
	.adminka_field_brand label, .adminka_field_place label {
		flex: 0 1 18%;
		padding:5px;
		margin:0;
	}
	.adminka_field_count label, .adminka_field_status label, .adminka_field_control label {
		font-weight:bold;
	}
	.adminka_field_control {display:flex;}
	.adminka_field_control label {padding-right:10px;}

	/****Table*****/
	.adminka_field table {border-collapse: collapse; max-width:100%;}
	.adminka_field table td {text-align: center; border: 1px solid #000000;}
	.adminka_field table td.prodSize, .adminka_field table td.prodName {text-align:left;}
	.adminka_field table tr:first-of-type td {font-weight: bold; text-transform: uppercase; background-color: #777777;color:#ffffff;}
	.adminka_field table tr:first-of-type {position: sticky; top: 31px;}
	.adminka_field table tr.prodRowNot {background-color: #CCCCCC;}
	.adminka_field table tr.prodRowSold {background-color: #FF0000;}
	.adminka_field table tr.prodRowSale {background-color:yellow;}
	.adminka_field table td.prodSize span {
		display:inline-block;
		margin:3px;
		padding:3px;
		line-height:normal;
		background-color:blue;
		color:#ffffff;
	}
	.adminka_field table td.prodImg {
		width:150px;
	}
	div.subhead-collapse.collapse, header.header {display:none;}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js" type="text/javascript"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('.filterSkladName').click(function() {
			$('.filterSklad').toggleClass('active');
			if($('.filterSklad').hasClass('active')) {
				$('.filterSkladSymbol').text('-');
			} else $('.filterSkladSymbol').text('+');
		});
    });
</script>

<div id="j-main-container" class="span10">

<div class="sklad_container">

	<h2>Складские остатки</h2>

    <form id="formSklad" name="formSklad" method="post">
		@csrf
		<div class="filterSkladName">Фильтр<span class="filterSkladSymbol">&nbsp;+</span></div>
		<div class="filterSklad">

			<div class="adminka_field adminka_field_name">Бренды</div>
			<div class="adminka_field adminka_field_brand">
				@foreach ($brandArr as $brandK => $brandV)
					<label>
						<input type="checkbox" name="brand[]" value="{{ $brandK }}" {{ in_array($brandK, $fltr['brand']) ? 'checked' : '' }}>
						&nbsp;{{ $brandV }}
					</label>
				@endforeach
			</div>

			<div class="adminka_field adminka_field_name">Магазины</div>
			<div class="adminka_field adminka_field_place">
				@foreach ($placeArr as $placeV)
					<label>
						<input type="checkbox" name="place[]" value="{{ $placeV }}" {{ in_array($placeV, $fltr['place']) ? 'checked' : '' }}>
						&nbsp;{{ $placeV }}
					</label>
				@endforeach
			</div>

			<div class="adminka_field adminka_field_count">
				<label>Макс. кол-во ед.&nbsp;
					<input type="text" name="count" value="{{ $fltr['count'] }}">
				</label>
			</div>

			<div class="adminka_field adminka_field_status">
				<label>Статус&nbsp;
					<select name="status">
						@foreach ($statusArr as $statusK => $statusV)
							<option value="{{ $statusK }}" {{ $statusK == $fltr['status'] ? 'selected' : '' }}>
								{{ $statusV }}
							</option>
						@endforeach
					</select>
				</label>
			</div>

			<div class="adminka_field adminka_field_name">Сезон</div>
			<div class="adminka_field adminka_field_season">
				@foreach ($seasonArr as $seasonK => $seasonV)
					<label>
						<input name="season[]" type="checkbox" value="{{ $seasonK }}" {{ in_array($seasonK,$fltr['season']) ? 'checked' : '' }}>
						&nbsp;{{ $seasonV }}
					</label>
				@endforeach
			</div>

			<div class="adminka_field adminka_field_days">
				<label>Кол-во дней&nbsp;<input type="text" name="days" value="{{ $fltr['days'] }}"></label>
			</div>

		</div>

		<div class="adminka_field adminka_field_control">
			<label>
				Экспорт в Excel&nbsp;
				<input type="checkbox" name="excel" value="1" id="excel" {{ $fltr['excel'] == 1 ? 'checked' : '' }}>
			</label>
			<button>Применить</button>
		</div>

    </form>


@if ($fltr['excel'] == 1) @php
/*********EXCEL*************/

// Подключаем класс для работы с excel
require_once app_path('Admin/legacy/PHPExcel.php');

// Подключаем класс для вывода данных в формате excel
require_once app_path('Admin/legacy/PHPExcel/Writer/Excel2007.php');


$xls = new PHPExcel(); // Создаем объект класса PHPExcel
$xls->setActiveSheetIndex(0); // Устанавливаем индекс активного листа
$sheet = $xls->getActiveSheet(); // Получаем активный лист
$sheet->setTitle('Склад'); // Подписываем лист

// Стили ячеек
$style_title = array(
    'font' => array(
        'name' => 'Arial',
		'size' => 12,
		'bold' => true,
        'color' => array ('rgb' => 'FFFFFF')
	),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array ('rgb' => '777777')
    ),
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	),
    'alignment' => array (
		'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'       	=> true,
	)
);

$style_normal = array(
    'font' => array(
        'name' => 'Arial',
		'size' => 10,
        'color' => array ('rgb' => '000000')
	),
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	),
    'alignment' => array (
		'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'       	=> true,
	)
);

$style_none = array(
    'font' => array(
        'name' => 'Arial',
		'size' => 10,
        'color' => array ('rgb' => '000000')
	),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array ('rgb' => 'CCCCCC')
    ),
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	),
    'alignment' => array (
		'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'       	=> true,
	)
);

$style_sold = array(
    'font' => array(
        'name' => 'Arial',
		'size' => 10,
        'color' => array('rgb' => '000000')
	),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FF0000')
    ),
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	),
    'alignment' => array(
		'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'       	=> true,
	)
);

$style_sale = array(
    'font' => array(
        'name' => 'Arial',
		'size' => 10,
        'color' => array('rgb' => '000000')
	),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFF00')
    ),
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	),
    'alignment' => array(
		'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'       	=> true,
	)
);

// Устанавливаем начальные значения
$cN = array('A','B','C','D','E','F','G','H','I','J','K','L');
$rw = 1;
$cl = 0;

// Заголовок
$sheet->setCellValue($cN[$cl].$rw,'Фото');
$sheet->getColumnDimension($cN[$cl])->setWidth(17);
$cl++;
$sheet->setCellValue($cN[$cl].$rw,'Модель');
$sheet->getColumnDimension($cN[$cl])->setWidth(30);
$cl++;

foreach($placeArr as $placeV) {
	if (!in_array($placeV,$fltr['place'])) continue;
	$sheet->setCellValue($cN[$cl].$rw,$placeV);
	$sheet->getColumnDimension($cN[$cl])->setWidth(15);
	$cl++;
}

$sheet->setCellValue($cN[$cl].$rw,'Цена');
$sheet->getColumnDimension($cN[$cl])->setWidth(10);
$cl++;
$sheet->setCellValue($cN[$cl].$rw,'Скидка');
$sheet->getColumnDimension($cN[$cl])->setWidth(10);
$cl++;
$sheet->setCellValue($cN[$cl].$rw,'Цена на сайте');
$sheet->getColumnDimension($cN[$cl])->setWidth(10);
$sheet->getStyle('A1:'.$cN[$cl].$rw)->applyFromArray($style_title);

// Содержимое
foreach($dSklad as $kBrand => $dBrand) {
	if (count($fltr['brand']) > 0 && !in_array($kBrand,$fltr['brand'])) continue;
	ksort($dBrand);
	foreach($dBrand as $prod) {
		// проверки фильтра
		$countOfPlace = 0;
		$countOfProduct = 0;
		foreach($placeArr as $placeV) {
			if (isset($prod['size'][$placeV])) {
				$countOfProduct += count($prod['size'][$placeV]);
				if(in_array($placeV,$fltr['place'])) $countOfPlace += count($prod['size'][$placeV]);
			}
		}
		if ($countOfPlace == 0 && $fltr['status'] != 'sold') continue;
		if ($fltr['count']<$countOfProduct) continue;
		if (($fltr['status'] == 'new' && $prod['discount'] != '0%') || ($fltr['status'] == 'sale' && $prod['discount'] == '0%') || ($fltr['status'] == 'sold' && $countOfProduct != 0)) continue;
		if(!empty($prod['season']) && !in_array($prod['season'],$fltr['season'])) continue;
		$rw++;
		$cl = 0;
		$prodName = $prod['brand']." ".$prod['articul'];

		// изображение
		if(!empty($prod['img_path'])) {
			$sheet->getRowDimension($rw)->setRowHeight(85);
			$pic = new PHPExcel_Worksheet_Drawing();
			$pic->setName($prodName);
			$pic->setPath($prod['img_path']);
			$pic->setCoordinates($cN[$cl].$rw);
			$pic->setOffsetX(5);
			$pic->setOffsetY(5);
			$pic->setWorksheet($sheet);
		} else {
			$sheet->setCellValue($cN[$cl].$rw,"нет фото");
		}
		$cl++;

		// описание
		$sheet->setCellValue($cN[$cl].$rw,$prodName."\r\n".$prod['cat']);
		$cl++;

		// размеры
		foreach($placeArr as $placeV) {
			if (!in_array($placeV,$fltr['place'])) continue;
			if(isset($prod['size'][$placeV])) $sheet->setCellValue($cN[$cl].$rw,implode(', ',$prod['size'][$placeV]));
			$cl++;
		}

		// цены
		$sheet->setCellValue($cN[$cl].$rw,$prod['price']);
		$cl++;
		$sheet->setCellValue($cN[$cl].$rw,$prod['discount']);
		$cl++;
		$sheet->setCellValue($cN[$cl].$rw,$prod['price_im']);

		// оформление
		if (empty($prod['img_url'])) {
			$style_row = $style_none;
		} elseif ($prod['discount'] != '0%') {
			$style_row = $style_sale;
		} elseif ($countOfProduct == 0) {
			$style_row = $style_sold;
		} else {
			$style_row = $style_normal;
		}

		$sheet->getStyle('A'.$rw.':'.$cN[$cl].$rw)->applyFromArray($style_row);
		$sheet->getStyle('B'.$rw.':'.$cN[$cl - 3].$rw)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	}
}

// автофильтр
$sheet->setAutoFilter('A1:'.$cN[$cl].$rw);
$sheet->freezePane('D2');

// Выводим содержимое файла
$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save(storage_path('app/temp/sklad.xlsx'));

 /*********END EXCEL*************/
@endphp

    <div class="adminka_field adminka_field_excel">
		<a href="/temp/sklad.xlsx" target="_blank" title="">скачать sklad.xlsx</a>
    </div>

@else

	<div class="adminka_field">
		<table><tbody>
			<tr>
				<td colspan="2">Модель</td>
				@foreach($placeArr as $placeV)
					@if (in_array($placeV, $fltr['place']))
						<td>{{ $placeV }}</td>
					@endif
				@endforeach
				<td>Цена</td>
				<td>Скидка</td>
				<td>Цена на сайте</td>
			</tr>

			@foreach($dSklad as $kBrand => $dBrand)
				@if (count($fltr['brand']) > 0 && !in_array($kBrand,$fltr['brand']))
					@continue
				@endif

				@php
					ksort($dBrand);
				@endphp

				@foreach($dBrand as $prod)
					@php
						$countOfPlace = 0;
						$countOfProduct = 0;
						foreach($placeArr as $placeV) {
							if (isset($prod['size'][$placeV])) {
								$countOfProduct += count($prod['size'][$placeV]);
								if(in_array($placeV,$fltr['place'])) {
									$countOfPlace += count($prod['size'][$placeV]);
								}
							}
						}
						if ($countOfPlace == 0 && $fltr['status'] != 'sold') continue;
						if ($fltr['count']<$countOfProduct) continue;
						if (($fltr['status'] == 'new' && $prod['discount'] != '0%') || ($fltr['status'] == 'sale' && $prod['discount'] == '0%') || ($fltr['status'] == 'sold' && $countOfProduct != 0)) continue;
						if(!empty($prod['season']) && !in_array($prod['season'],$fltr['season'])) continue;

						$prodName = $prod['brand']." ".$prod['articul'];
					@endphp

				<tr class="
					@if (empty($prod['img_url']))
						prodRowNot
					@elseif ($countOfProduct == 0)
						prodRowSold
					@elseif ($prod['discount']!="0%")
						prodRowSale
					@endif"
				>

					<td class="prodImg">
						{!! !empty($prod['img_url']) ? "<img src=\"$prod[img_url]\" title=\"$prodName\">" : 'нет фото' !!}
					</td>

					<td class="prodName">
						{{ $prodName }}<br>
						{{ $prod['cat'] }}
					</td>

					@foreach($placeArr as $placeV)
						@if (in_array($placeV,$fltr['place']))
							<td class="prodSize">
								@if(isset($prod['size'][$placeV]))
									<span>{!! implode("</span><span>",$prod['size'][$placeV]) !!}</span>
								@endif
							</td>
						@endif
					@endforeach

					<td>{{ $prod['price'] }}</td>
					<td>{{ $prod['discount'] }}</td>
					<td>{{ round($prod['price_im'],2) }}</td>
				</tr>

				@endforeach
			@endforeach
		</tbody></table>
	</div>

</div>

</div>

@endif
