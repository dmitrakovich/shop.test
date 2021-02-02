<?php 
/**
* @version      4.10.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');
$pachAd = JPATH_BASE.'/components/com_jshopping/views/panel/tmpl/';
include_once($pachAd.'availability.conf.php');

// Предустановки
$thtime = date("Y-m-d-H:i:s");
$filedate = explode(",",$availabilityConfig['file']);

$service_message = "";
$db = JFactory::getDbo();

// добавление товара
	$chAdd = false;
if (isset($_GET['add'])) {	
	foreach ($availabilityConfig['new'] as $chK => $chV) if ($chV['id']==$_GET['add']) $chAdd = true;
}

if ($chAdd) {
	$chAdd = false;
	foreach ($availabilityConfig['new'] as $chK => $chV) if ($chV['status']==0 || $sbrV['time']<$deadline) unset($availabilityConfig[$sbrosv][$sbrK]);
	
	$new_pr = $availabilityConfig['new'][$_GET['add']];
	
	// бренды
	$query = "SELECT manufacturer_id as id, `name_ru-RU` as name FROM `#__jshopping_manufacturers`";
	$db->setQuery($query);
	$preBr = $db->loadObjectList();
	$new_pr_ch = 0;
	foreach ($preBr as $preBrV) {
		if ($preBrV->name == $new_pr['brand']) {
			$new_pr_br = $preBrV->id;
			$new_pr_ch = 1;
		}
	}
	if ($new_pr_ch == 0) {
		echo "Ошибка в названии бренда. Товар не создан.";
		exit();
	}
	
	// создание модели
	$m_query = array($_GET['add'],$new_pr_br,$new_pr['articul'],0);
	$m_query = implode(',',$m_query);
	$query = "INSERT INTO `#__jshopping_products` (`alias_ru-RU`,product_manufacturer_id,`name_ru-RU`,product_publish,product_date_added) VALUES ('".$_GET['add']."','".$new_pr_br."','".$new_pr['articul']."',0,NOW())";
	$db->setQuery($query);
	$db->query();
	
	$query = "SELECT product_id as id FROM `#__jshopping_products` WHERE `alias_ru-RU`='".$_GET['add']."'";
	$db->setQuery($query);
	$new_id = $db->loadObjectList();
	$new_id = $new_id[0];
	$new_id = $new_id->id;
	
	
	// размеры
	$query = "SELECT value_id as id, `name_ru-RU` as name FROM `#__jshopping_attr_values`";
	$db->setQuery($query);
	$preAttr = $db->loadObjectList();
	$baseAttr = array();
	foreach ($preAttr as $preAV) {
		$baseAttr[$preAV->name] = $preAV->id;
	}
	$q_list=array();
	foreach ($new_pr['size'] as $newSI) {
		$q_list[]="(".$new_id.",2,".$baseAttr[$newSI].",'+',0)";
	}
	$q_list = implode(',',$q_list);
	$query = "INSERT INTO `#__jshopping_products_attr2`  (product_id,attr_id,attr_value_id,price_mod,addprice) VALUES $q_list";
	$db->setQuery($query);
	$db->query();
	
	// категории
	$new_pr['cat'] = mb_strtolower($new_pr['cat'], 'UTF-8');
	$preCatV->name = mb_strtolower($preCatV->name, 'UTF-8');
	$new_pr_ch = 0;
	$query = "SELECT category_id as id, `name_ru-RU` as name FROM `#__jshopping_categories` WHERE category_id!=39";
	$db->setQuery($query);
	$preCat = $db->loadObjectList();
	foreach ($preCat as $preCatV) {
		$baseCat[$preCatV->name] = $preCatV->id;
		if (strpos($new_pr['cat'],$preCatV->name) !== false) {
			$new_pr_cat = $preCatV->id;
			$new_pr_ch = 1;
		}
	}
	if ($new_pr_ch == 0) {
		$new_pr_cat = 1;
	}
		
	$query = "INSERT INTO `#__jshopping_products_to_categories` (product_id,category_id) VALUES ($new_id,$new_pr_cat)";
	$db->setQuery($query);
	$db->query();
	
	?>
	Подождите идет загрузка...
	<script type="text/javascript">
		window.location="https://modny.by/administrator/index.php?option=com_jshopping&controller=products&task=edit&product_id=<?=$new_id;?>";
	</script>
	<?
	exit();
}

// Методы
if (isset($_POST['act'])) {
	switch ($_POST['act']) {
		case "start":
			include_once($pachAd.'availability.action.php');
			break;
			
		case "publish":
			if(empty($_POST['publish_list'])) break;
			$act_count = count($_POST['publish_list']);
			if ($act_count>0) {
			$publish_list = implode(',',array_keys($_POST['publish_list']));
			$query = "UPDATE `#__jshopping_products` SET product_publish = 1 WHERE product_id IN($publish_list)";
			$db->setQuery($query);
			$db->query();
			foreach ($availabilityConfig['publish'] as $actK => $actV) if (isset($_POST['publish_list'][$actV['id']])) $availabilityConfig['publish'][$actK]['status']=1;
			$service_message = "<p class='adminka_message_success'>Опубликовано ".$act_count."</p>";
			}
			break;
			
		case "del":
			if(empty($_POST['del_list'])) break;
			$act_count = count($_POST['del_list']);
			if ($act_count>0) {
			$del_list = implode(',',array_keys($_POST['del_list']));
			$query = "UPDATE `#__jshopping_products` SET product_publish = 0 WHERE product_id IN($del_list)";
			$db->setQuery($query);
			$db->query();
			foreach ($availabilityConfig['del'] as $actK => $actV) if (isset($_POST['del_list'][$actV['id']])) $availabilityConfig['del'][$actK]['status']=1;
			$service_message = "<p class='adminka_message_success'>Снято с публикации ".$act_count."</p>";
			}
			break;
			
		case "delS":
			if(empty($_POST['delS_list'])) break;
			$act_count = count($_POST['delS_list']);
			if ($act_count>0) {
			$delS_list = implode(',',array_keys($_POST['delS_list']));
			$query = "DELETE FROM `#__jshopping_products_attr2` WHERE id IN($delS_list)";
			$db->setQuery($query);
			$db->query();
			foreach ($availabilityConfig['del_size'] as $actK => $actV) if (isset($_POST['delS_list'][$actV['vid']])) $availabilityConfig['del_size'][$actK]['status']=1;
			$service_message = "<p class='adminka_message_success'>Удалено размеров ".$act_count."</p>";
			}
			break;
			
		case "addS":
			if(empty($_POST['addS_list'])) break;
			$act_count = count($_POST['addS_list']);
			if ($act_count>0) {
			$addS_list = array_keys($_POST['addS_list']);
			$addS_values = "";
			for ($i=0;$i<$act_count;$i++) {
				$addS_list_sec = explode('-',$addS_list[$i]);
				//$addS_list_item = array($addS_list_sec[0],2,$addS_list_sec[1],'+',0);
				if (!empty($addS_values)) $addS_values .= ",";
				$addS_values .= "(".$addS_list_sec[0].",2,".$addS_list_sec[1].",'+',0)";
			}
			//$query = "SELECT * FROM `#__jshopping_products_attr2` LIMIT 1";
			$query = "INSERT INTO `#__jshopping_products_attr2`  (product_id,attr_id,attr_value_id,price_mod,addprice) VALUES $addS_values";
			$db->setQuery($query);
			$db->query();
			foreach ($availabilityConfig['add_size'] as $actK => $actV) if (isset($_POST['addS_list'][$actV['id']."-".$actV['vid']])) $availabilityConfig['add_size'][$actK]['status']=1;
			$service_message = "<p class='adminka_message_success'>Добавлено размеров ".$act_count."</p>";
			}
			break;
			
		case "save":
			$availabilityConfig['auto_del'] = (empty($_POST['auto_del']))?'off':'on';
			$availabilityConfig['ignore'] = array_map(trim,explode(',',$_POST['ignore']));
			$availabilityConfig['period'] = intval($_POST['period']);
			$sbros = array('publish','new','add_size','del','del_size');
			foreach ($sbros as $sbrosv) {
				$availabilityConfig[$sbrosv] = array();
			}
			$service_message = "<p class='adminka_message_success'>Конфигурация сохранена. Вся история обновления удалена!</p>";
			break;
	}
// Запись в log
	if (($_POST['act']=="save") || ($_POST['act']!="start" && isset($act_count) && $act_count>0)) {
		$log_type = ((isset($_POST['act']))?"РУЧНОЕ":"АВТО");
		$log_mess = str_replace(array("<p class='adminka_message_success'>","</p>"),"",$service_message);
		file_put_contents($pachAd."availability.log.txt","$thtime [$log_type]: $log_mess".PHP_EOL,FILE_APPEND);
	}
// Запись в config
	$strToFile = '<?'.PHP_EOL.'$availabilityConfig = '.var_export($availabilityConfig,true).';'.PHP_EOL.'?>';
	file_put_contents($pachAd.'availability.conf.php',$strToFile);
} else $service_message = "<p class='adminka_message_info'>Файл ".$filedate[0].". Актуальное наличие на ".$availabilityConfig['last_update']."</p>";

?>
<style type="text/css">
	.adminka_message_error {
		color:red;
		font-weight:bold;
	}
	.adminka_message_success {
		color:green;
		font-weight:bold;
	}
	.adminka_message_info {
		color:#AAAAAA;
		font-weight:bold;
	}
	.adminka_block {
		margin: 0;
		padding: 0;
	}
	@media all and (min-width: 600px) {
		.adminka_block:nth-child(2n) {
			float: right;
		}
		.adminka_block {
			width: 45%;
		}
	}
	.adminka_clr{clear: both; height: 0;}
	.adminka_sub_title {
		text-indent: 15px;
		font-weight: bold;
	}
	.adminka_field {
		margin: 10px 0;
		padding: 0 10px;
	}
	.adminka_field label{
		display: inline-block;
		margin-right: 10px;
	}
		.adminka_field label.complete{
			background-color: #77ff77;
		}
		.adminka_field label.err{
			background-color: #ff7777;
		}
	.adminka_field label input{
		margin-left: 20px;
	}
	.adminka_field table {border-collapse: collapse;}
	.adminka_field table td {text-align: center;}
	.adminka_field table tr:first-of-type td {font-weight: bold; text-transform: uppercase;}
	.adminka_field table tr:nth-child(2n) {background-color: #CCCCCC;}
	.adminka_field table input {width: 30px;text-align: center;}
</style>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<div class="availability_container">

	<h2>Наличие</h2>

	<div class="adminka_field">
		<?=$service_message;?>
	</div>
	<div class="adminka_clr"></div>
	<div class="adminka_block">
        
        <div class="adminka_field">
            <p class="adminka_sub_title">Конфигурация</p>
        </div>
		
	<form method="post" id="formavailabilityConfig" name="formavailabilityConfig">
		<input type="hidden" name="act" value="save">
		
		
        <div class="adminka_field">
            <label>Автоматическое обновление <input type="checkbox" name="auto_del" id="auto_del"<?=($availabilityConfig['auto_del']=='on')?' checked':'';?>></label>
        </div> 
		
		<? /*
        <div class="adminka_field">
            <label>Модели для исключения (id)<textarea name="ignore" id="ignore"><?=implode(',',$availabilityConfig['ignore']);?></textarea></label>
        </div>
		*/?>
		
		
        <div class="adminka_field">
            <label>Хранить дней <input type="text" name="period" id="period" value="<?=$availabilityConfig['period'];?>"/></label>
        </div>
		
		<div class="adminka_field">
			<button>Сохранить</button> 
		</div>
		
	</form>
		
		<div class="adminka_field" style="height: 20px;">
			<p></p> 
		</div>
		
		
	<form method="post" id="formavailabilityStart" name="formavailabilityStart">
		<input type="hidden" name="act" value="start">
		
        <div class="adminka_field">
            <p class="adminka_sub_title">Ручное обновление наличия</p>
        </div>
		
	<div class="adminka_field">
		<button>Обновить</button> 
	</div>
	</form>
        
		<div class="adminka_field" style="height: 20px;">
			<p>История изменений<br>
           <a href="https://modny.by/administrator/components/com_jshopping/views/panel/tmpl/availability.log.txt" target="_blank">Открыть лог</a></p> 
		</div>
		
	</div>
	
	<div class="adminka_block">
        
        <div class="adminka_field">
            <p class="adminka_sub_title">Опубликовать</p>
        </div>
        
        <div class="adminka_field">
			<form method="post" id="formavailabilityPublish" name="formavailabilityPublish">
				<input type="hidden" name="act" value="publish">
				<?
					$btn_ok = 0;
					foreach ($availabilityConfig['publish'] as $avI) {
						$aviI = $avI['id'];
						$aviN = $avI['name'];
						$aviC = '';
						if ($avI['status'] == 1) {
							$aviC = 'class="complete"';
						} elseif (isset($avI['err'])) {
							$aviC = 'class="err"';
							$aviN .= ' ('.$avI['err'].')';
						} else $btn_ok++;
						if ($availabilityConfig['auto_del'] == 'on') echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
						echo "<label $aviC><a href='https://modny.by/administrator/index.php?option=com_jshopping&controller=products&task=edit&product_id=$aviI' title='$aviN' target='_blank'>$aviN</a>";
						if ($avI['status'] != 1 && !isset($avI['err'])) echo "<input type='checkbox' name='publish_list[$aviI]' checked />";
						echo "</label><br>";
					}
					if ($btn_ok > 0):
				?>
				<button>Применить</button> 
                <? endif; ?>
			</form>
        </div>
		
        <div class="adminka_field">
            <p class="adminka_sub_title">Снять с публикации</p>
        </div>
        
        
        <div class="adminka_field">
			<form method="post" id="formavailabilityDel" name="formavailabilityDel">
				<input type="hidden" name="act" value="del">
				<?
					$btn_ok = 0;
					foreach ($availabilityConfig['del'] as $avI) {
						$aviI = $avI['id'];
						$aviN = $avI['name'];
						$aviC = '';
						if ($avI['status'] == 1) {
							$aviC = 'class="complete"';
						} else $btn_ok++;
						if ($availabilityConfig['auto_del'] == 'on') echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
						echo "<label $aviC><a href='https://modny.by/administrator/index.php?option=com_jshopping&controller=products&task=edit&product_id=$aviI' title='$aviN' target='_blank'>$aviN</a>";
						if ($avI['status'] != 1) echo "<input type='checkbox' name='del_list[$aviI]' checked />";
						echo "</label><br>";
					}
					if ($btn_ok > 0):
				?>
				<button>Применить</button> 
                <? endif; ?>
			</form>
        </div>

        
        <div class="adminka_field">
            <p class="adminka_sub_title">Добавить размер</p>
        </div>
        
        
        <div class="adminka_field">
			<form method="post" id="formavailabilityAdd" name="formavailabilityAdd">
				<input type="hidden" name="act" value="addS">
				<?
					$btn_ok = 0;
					foreach ($availabilityConfig['add_size'] as $avI) {
						$aviI = $avI['id'];
						$aviN = $avI['name'];
						$aviS = $avI['vid'];
						$aviSN = $avI['size'];
						if ($aviS=='new') $aviSN .= "!!! добавить размер в админку !!!";
						$aviC = '';
						if ($avI['status'] == 1) {
							$aviC = 'class="complete"';
						} else $btn_ok++;
						if ($availabilityConfig['auto_del'] == 'on') echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
						echo "<label $aviC><a href='https://modny.by/administrator/index.php?option=com_jshopping&controller=products&task=edit&product_id=$aviI' title='$aviN' target='_blank'>$aviN</a> р. $aviSN";
						if ($avI['status'] != 1) echo "<input type='checkbox' name='addS_list[$aviI-$aviS]' checked />";
						echo "</label><br>";
					}
					if ($btn_ok > 0):
				?>
				<button>Применить</button> 
                <? endif; ?>
			</form>
        </div>
        
        <div class="adminka_field">
            <p class="adminka_sub_title">Удалить размер</p>
        </div>
        
        
        <div class="adminka_field">
			<form method="post" id="formavailabilityDelS" name="formavailabilityDelS">
				<input type="hidden" name="act" value="delS">
				<?
					$btn_ok = 0;
					foreach ($availabilityConfig['del_size'] as $avI) {
						$aviI = $avI['id'];
						$aviN = $avI['name'];
						$aviS = $avI['vid'];
						$aviSN = $avI['size'];
						$aviC = '';
						if ($avI['status'] == 1) {
							$aviC = 'class="complete"';
						} else $btn_ok++;
						if ($availabilityConfig['auto_del'] == 'on') echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
						echo "<label $aviC><a href='https://modny.by/administrator/index.php?option=com_jshopping&controller=products&task=edit&product_id=$aviI' title='$aviN' target='_blank'>$aviN</a> р. $aviSN";
						if ($avI['status'] != 1) echo "<input type='checkbox' name='delS_list[$aviS]' checked />";
						echo "</label><br>";
					}
					if ($btn_ok > 0):
				?>
				<button>Применить</button> 
                <? endif; ?>
			</form>
        </div>
        
	</div>
	
        <div class="adminka_field">
            <p class="adminka_sub_title">Новые модели</p>
        </div>
        
        
        <div class="adminka_field">
			<?
				foreach ($availabilityConfig['new'] as $avK => $avI) {
					echo $avI['brand'].' '.$avI['articul'].' '.$avI['cat'].(is_array($avI['size'])?(' - р. '.implode(',',$avI['size'])):'').(!empty($avI['err'])?' ('.$avI['err'].')':'&nbsp;<a href="https://modny.by/administrator/index.php?option=com_jshopping&controller=availability&add='.$avI['id'].'" class="add_but_prod" title="Создать товар" target="_blank">&rArr;</a>').'<br>';				}
			?>

        </div>
        
	</div>
	
	<div class="adminka_clr"></div>
	

</div>

</div>
