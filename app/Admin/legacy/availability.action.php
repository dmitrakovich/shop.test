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
include_once($pachAd.'availability.conf.php');
// Проверка на авто
if ($availabilityConfig['auto_del'] == 'off') {
	echo 'Автоматическое обновление выключено!';
	exit();
}

// Предустановки
$thtime = date("Y-m-d-H:i:s");
}

$db = JFactory::getDbo();


$prodS = array();
$field = "prod.product_id as id, br.`name_ru-RU` as brand, prod.`name_ru-RU` as name, prod.product_publish as publish, cat.category_id as cat_id, prod.label_id as label";
$query = $db->getQuery(true);
$query = "SELECT $field  FROM `#__jshopping_products` AS prod
		  LEFT JOIN `#__jshopping_manufacturers` AS br ON prod.product_manufacturer_id=br.manufacturer_id
		  LEFT JOIN `#__jshopping_products_to_categories` AS cat USING (product_id)";

$db->setQuery($query);
$res_prod = $db->loadObjectList();

$query = "SELECT pr.id as sid, pr.product_id as pid, pr.attr_value_id as vid, attr.`name_ru-RU` as value FROM `#__jshopping_products_attr2` as pr
		  LEFT JOIN `#__jshopping_attr_values` AS attr ON pr.attr_value_id = attr.value_id";
$db->setQuery($query);
$allAtr = $db->loadObjectList();

$attrL = array();
$fullAttr = array();
$fullProd = array();
$attrName = array();
foreach ($allAtr as $atrV) {
	if (!isset($attrL[$atrV->pid])) $attrL[$atrV->pid] = array();
		$attrL[$atrV->pid][$atrV->value] = $atrV->sid;
		$fullAttr[$atrV->value] = $atrV->sid;
		$attrName[$atrV->value] = $atrV->vid;
}

foreach ($res_prod as $res_prod_v) {
	$res_prod_size = '';
	if (isset($attrL[$res_prod_v->id])) {
		$res_prod_size = $attrL[$res_prod_v->id];
	} else $res_prod_size = 'b';
	$ibrand = trim($res_prod_v->brand);
	$sbrand = strtolower($ibrand);
	$smallN = smallArt($res_prod_v->name);
	$checkB = str_replace(' ','',$ibrand);
	$itemB = array(
					'id'=>$res_prod_v->id,
					'cat_id'=>$res_prod_v->cat_id,
					'status'=>$res_prod_v->publish,
					'articul'=>$res_prod_v->name,
					'brand'=>$ibrand,
					'size'=>$res_prod_size,
					'label'=>$res_prod_v->label
				);
	if (!empty($checkB)) {
		if (!isset($prodS[$sbrand])) $prodS[$sbrand] = array();
		$prodS[$sbrand][$smallN] = $itemB;
		// общий список
		if (!isset($fullProd[$sbrand])) $fullProd[$sbrand] = array();
		$fullProd[$sbrand][$smallN] = $res_prod_v->id;
	}
}

function smallArt($txt) {
	$r = array(' ','-','.','_','*');
	return mb_strtolower(str_replace($r,'',$txt));
}

unset($res_prod,$allAtr,$attrL);

// Яндекс

$context = stream_context_create(array(
	'http' => array(
		'method' => 'GET',
		'header' => 'Authorization: OAuth AgAAAAAb991aAAW4YjwHjdE_60CZpTWD4C4J64o'.PHP_EOL.
					'Content-Type: application/x-yametrika+json' . PHP_EOL
	),
));

	
$url = 'https://cloud-api.yandex.net:443/v1/disk/resources';

$params = array(
    'path' => '/Ostatki/ostatki.txt',
    'field' => 'modified,md5',
);

$infoF = file_get_contents( $url . '?' . http_build_query($params), false, $context);
if (!$infoF) {
	echo "Ошибка! Яндекс Диск не отдал данные о файле.";
	exit;
}
$infoF = json_decode($infoF, true);

$filedate = explode(",",$availabilityConfig['file']);
if ($infoF['md5'] == $filedate[1] && (count($availabilityConfig['publish']) + count($availabilityConfig['add_size']) + count($availabilityConfig['del']) + count($availabilityConfig['del_size']) + count($availabilityConfig['new']))>0 && !isset($_POST['act'])) {
	echo "Файл не обновлялся.";
	exit;
}

$availabilityConfig['file'] = date("Y-m-d-H:i:s",strtotime($infoF['modified'])).",".$infoF['md5'];

$url = 'https://cloud-api.yandex.net:443/v1/disk/resources/download';

$params = array(
    'path'         => '/Ostatki/ostatki.txt'
);

$hrefF = file_get_contents( $url . '?' . http_build_query($params), false, $context);
if (!$hrefF) {
	echo "Ошибка! Яндекс Диск не получил ссылку на скачивание.";
	exit;
}
$hrefF = json_decode($hrefF, true);

$resI = file_get_contents($hrefF['href']);

$resI=mb_convert_encoding($resI, "UTF-8", "windows-1251");


$resI = explode("\n",$resI);

$resD = array();

for ($i=5;$i<count($resI);$i++) {
	if (mb_strpos($resI[$i],' | ')!==false) {
		$itemA = explode("\t",$resI[$i]);
		$itemC = explode(' | ',$itemA[2]);
		$ibrand = trim($itemA[3]);
		$sbrand = strtolower($ibrand);
		$checkB = str_replace(' ','',$ibrand);
		$smallN = smallArt($itemC[0]);
		$sizeNotNull = true;
		if ($itemA[5] == 0) $sizeNotNull = false;
		$isize = 'b';
		if ($itemA[4]!="б/р") $isize = array($itemA[4]=>$itemA[4]);
		$itemB = array(
						'articul'=>$itemC[0],
						'brand'=>$ibrand,
						'size'=>$isize,
						'cat'=>$itemC[1],
						'price'=>str_replace("'00","",$itemA[6])
					);
		if (!empty($checkB) && !empty($smallN) && $sizeNotNull) {
			if (!isset($resD[$sbrand])) $resD[$sbrand] = array();
			// костыль для русских и английских букв
			if (!isset($prodS[$sbrand][$smallN])) {
				$eng_symb = array('a','b','c','e','h','k','m','o','p','t','x');
				$rus_symb = array('а','в','с','е','н','к','м','о','р','т','х');
				$smallNS = str_replace($eng_symb,$rus_symb,$smallN);
				if (isset($prodS[$sbrand][$smallNS])) $smallN = $smallNS;
				$smallNS = str_replace($rus_symb,$eng_symb,$smallN);
				if (isset($prodS[$sbrand][$smallNS])) $smallN = $smallNS;
			}
			if (!isset($resD[$sbrand][$smallN])) {
				$resD[$sbrand][$smallN] = $itemB;
			} elseif (is_array($resD[$sbrand][$smallN]['size'])) {
				$resD[$sbrand][$smallN]['size'][$itemA[4]]=$itemA[4];
			}
			// общий список
			if (!isset($fullProd[$sbrand])) $fullProd[$sbrand] = array();
			if (!isset($fullProd[$sbrand][$smallN])) $fullProd[$sbrand][$smallN] = 'new';
		}
	}
}

unset($resI);
// Сброс
$deadline = date("Y-m-d-H:i:s", mktime(date("H"), date("i"), date("s"), date("n"), date("j") - $availabilityConfig['period'], date("Y")));
$sbros = array('publish','new','add_size','del','del_size');
foreach ($sbros as $sbrosv) {
	if ($availabilityConfig['auto_del'] == 'on' && $sbrosv=='publish') {
		foreach ($availabilityConfig[$sbrosv] as $sbrK => $sbrV) if ($sbrV['status']==0 || $sbrV['time']<$deadline) unset($availabilityConfig[$sbrosv][$sbrK]);
	} elseif ($availabilityConfig['auto_del'] == 'off' || $sbrosv=='new') {
		$availabilityConfig[$sbrosv] = array();
	} else foreach ($availabilityConfig[$sbrosv] as $sbrK => $sbrV) if (!isset($sbrV['time']) || $sbrV['time']<$deadline) unset($availabilityConfig[$sbrosv][$sbrK]);
}

// Сравнение
foreach ($fullProd as $fbK => $fbV) {
	foreach ($fbV as $fK => $fV) {
		$checkIgn = (!isset($prodS[$fbK][$fK]) || $prodS[$fbK][$fK]['label']!=3);
		$checkNoM = (!isset($resD[$fbK][$fK]) || (stripos($resD[$fbK][$fK]['cat'],"мужск"))===false);
		$false_cat = array(4,5,16,21,31,32,39,40,48);
		$checkCat = (!isset($prodS[$fbK][$fK]) || !in_array($prodS[$fbK][$fK]['cat_id'],$false_cat));
		if ($checkIgn && $checkNoM && $checkCat) {
			if (isset($prodS[$fbK][$fK]) && $prodS[$fbK][$fK]['status'] != 1 && isset($resD[$fbK][$fK])) {
				$it = $prodS[$fbK][$fK];
				$availabilityConfig['publish'][]=array('id'=>$it['id'],'name'=>$it['brand'].' '.$it['articul'],'status'=>0,'time'=>$thtime);
			}
			if (isset($prodS[$fbK][$fK]) && $prodS[$fbK][$fK]['status'] == 1 && !isset($resD[$fbK][$fK])) {
				$it = $prodS[$fbK][$fK];
				$availabilityConfig['del'][]=array('id'=>$it['id'],'name'=>$it['brand'].' '.$it['articul'],'status'=>0,'time'=>$thtime);
			}
			if (isset($fullProd[$fbK][$fK]) && $fullProd[$fbK][$fK] == 'new') {
				$it = $resD[$fbK][$fK];
				$it_err = '';
				if(!isset($prodS[$fbK])) $it_err = 'нет бренда';
				if(is_array($it['size'])) {
					$it_err_s = '';
					foreach ($it['size'] as $err_s) if(!isset($attrName[$err_s])) $it_err_s = 'нет размера';
					$it_err .= ((!empty($it_err) && !empty($it_err_s))?', ':'').((!empty($it_err_s))?$it_err_s:'');
				}
				$availabilityConfig['new'][$fbK.'-'.$fK] = array(
				'id'=>$fbK.'-'.$fK,
				'brand'=>$it['brand'],
				'articul'=>$it['articul'],
				'cat'=>$it['cat'],
				'size'=>$it['size'],
				'err'=>$it_err,
				'time'=>$thtime
				);
			}
			// размеры
			if (isset($prodS[$fbK][$fK]) && isset($resD[$fbK][$fK]) && $prodS[$fbK][$fK]['size']!='b' && array_keys($prodS[$fbK][$fK]['size']) != array_keys($resD[$fbK][$fK]['size'])) {
				$it = $prodS[$fbK][$fK];
				$ds = $resD[$fbK][$fK]['size'];
				$ss = $prodS[$fbK][$fK]['size'];
				$fs = $ss + $ds;
				foreach ($fs as $fsk => $fsv) {
					if (!isset($ss[$fsk]) && isset($ds[$fsk])) {
						if (!isset($attrName[$fsk])) $attrName[$fsk] = 'new';
						$availabilityConfig['add_size'][]=array('id'=>$it['id'],'vid'=>$attrName[$fsk],'name'=>$it['brand'].' '.$it['articul'],'size'=>$fsk,'status'=>0,'time'=>$thtime);
					} elseif (isset($ss[$fsk]) && !isset($ds[$fsk])) {
						$availabilityConfig['del_size'][]=array('id'=>$it['id'],'vid'=>$ss[$fsk],'name'=>$it['brand'].' '.$it['articul'],'size'=>$fsk,'status'=>0,'time'=>$thtime);
					}
				}
			}
		}
		
	};
};

$checkLog = 0;
$availabilityConfig['last_update']=$thtime;
$filedate = explode(",",$availabilityConfig['file']);
$service_message = "<p class='adminka_message_success'>Файл ".$filedate[0].". Наличие сверено в ".$availabilityConfig['last_update'];
if ($availabilityConfig['auto_del'] == 'on') {
$act_count = 0;
// публикации
	if (count($availabilityConfig['publish'])>0) {
		$act_count = 0;
		$img_list=array();
		$q_list = array();
		$imgL = array();
		foreach ($availabilityConfig['publish'] as $actK => $actV) {
			if ($availabilityConfig['publish'][$actK]['status']!=1) {
				$img_list[]=$actV['id'];
			} else $imgL[] = $actV['id'];
		}
		
		if (count($img_list) > 0) {
			$img_list = implode(',',$img_list);
			$query = "SELECT product_id as pid FROM #__jshopping_products_images WHERE product_id IN($img_list)";
			$db->setQuery($query);
			$imgFL = $db->loadObjectList();
			
			foreach ($imgFL as $imgV) {
				if(!in_array($imgV->pid,$imgL)) $imgL[] = $imgV->pid;
			}
			foreach ($availabilityConfig['publish'] as $errK => $errV) {
				if (!in_array($errV['id'],$imgL)) {
					$availabilityConfig['publish'][$errK]['err']='нет фото';
				} elseif ($availabilityConfig['publish'][$errK]['status']!=1) {
					$q_list[]=$errV['id'];
					$availabilityConfig['publish'][$errK]['status']=1;
					$act_count++;
				}
			}
		}
		
		if (count($q_list) > 0) {
			
			$q_list = implode(',',$imgL);
			$query = "UPDATE `#__jshopping_products` SET product_publish = 1 WHERE product_id IN($q_list)";
			$db->setQuery($query);
			$db->query();
			$service_message .= ". Опубликовано ".$act_count;
		}
		$checkLog += $act_count;
	}

// снятие с публикации
	if (count($availabilityConfig['del'])>0) {
		$act_count = 0;
		$q_list=array();
		foreach ($availabilityConfig['del'] as $actK => $actV) {
			if ($availabilityConfig['del'][$actK]['status']!=1) {
				$q_list[]=$actV['id'];
				$availabilityConfig['del'][$actK]['status']=1;
				$act_count++;
			}
		}
		if ($act_count > 50) {
			$service_message .= ". !!! Ошибка. Больше 50 снять с публикации";
			$act_count = 0;
		} elseif ($act_count > 0) {
			$q_list = implode(',',$q_list);
			$query = "UPDATE `#__jshopping_products` SET product_publish = 0 WHERE product_id IN($q_list)";
			$db->setQuery($query);
			$db->query();
			$service_message .= ". Снято с публикации ".$act_count;
		}
		$checkLog += $act_count;
	}

// удаление размеров
	if (count($availabilityConfig['del_size'])>0) {
		$act_count = 0;
		$q_list=array();
		foreach ($availabilityConfig['del_size'] as $actK => $actV) {
			if ($availabilityConfig['del_size'][$actK]['status']!=1) {
				$q_list[]=$actV['vid'];
				$availabilityConfig['del_size'][$actK]['status']=1;
				$act_count++;
			}
		}
		if ($act_count > 100) {
			$service_message .= ". !!! Ошибка. Больше 100 удалить размеров";
			$act_count = 0;
		} elseif ($act_count > 0) {
			$q_list = implode(',',$q_list);
			$query = "DELETE FROM `#__jshopping_products_attr2` WHERE id IN($q_list)";
			$db->setQuery($query);
			$db->query();
			$service_message .= ". Удалено размеров ".$act_count;
		}
		$checkLog += $act_count;
	}

// добавление размеров
	if (count($availabilityConfig['add_size'])>0) {
		$act_count = 0;
		$q_list=array();
		foreach ($availabilityConfig['add_size'] as $actK => $actV) {
			if ($availabilityConfig['add_size'][$actK]['status']!=1 && $actV['vid']!='new') {
				$q_list[]="(".$actV['id'].",2,".$actV['vid'].",'+',0)";
				$availabilityConfig['add_size'][$actK]['status']=1;
				$act_count++;
			}
		}
		if ($act_count > 0) {
			$q_list = implode(',',$q_list);
			$query = "INSERT INTO `#__jshopping_products_attr2`  (product_id,attr_id,attr_value_id,price_mod,addprice) VALUES $q_list";
			$db->setQuery($query);
			$db->query();
			$service_message .= ". Добавлено размеров ".$act_count;
		}
		$checkLog += $act_count;
	}
	
// Запись в log
	if ($checkLog > 0) {
		$log_type = ((isset($_POST['act']))?"РУЧНОЕ":"АВТО");
		$log_mess = str_replace(array("<p class='adminka_message_success'>","</p>"),"",$service_message);
		file_put_contents($pachAd."availability.log.txt","$thtime [$log_type]: $log_mess".PHP_EOL,FILE_APPEND);
	}
} else {
		$log_type = ((isset($_POST['act']))?"РУЧНОЕ":"АВТО");
		$log_mess = str_replace(array("<p class='adminka_message_success'>","</p>"),"",$service_message);
		file_put_contents($pachAd."availability.log.txt","$thtime [$log_type]: $log_mess".PHP_EOL,FILE_APPEND);
}
$service_message .= ".</p>";




if (!isset($_POST['act'])) {
// Запись в config
	$strToFile = '<?'.PHP_EOL.'$availabilityConfig = '.var_export($availabilityConfig,true).';'.PHP_EOL.'?>';
	file_put_contents($pachAd.'availability.conf.php',$strToFile);
}
?>

