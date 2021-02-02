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
include_once($pachAd.'rating.conf.php');

// Предустановки
$cur_season = $ratingConfig['cur_season']; // текущие сезоны 
$false_category = $ratingConfig['false_category']; // исключенные категории
$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];

$service_message = "";
$db = JFactory::getDbo();
// Методы
switch ($_POST['act']) {
	case "start":
		include_once($pachAd.'rating.action.php');
		$service_message = "<p class='adminka_message_success'>Рейтинг обновлен успешно в ".$ratingConfig['last_update']."</p>";
		break;
		
	case "algoritm":
		foreach ($ratingConfig['algoritm'][$ratingConfig['curr_algoritm']] as $k => $v) $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']][$k] = $_POST[$k];
		$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];
		$service_message = "<p class='adminka_message_success'>Алгоритм <".$ratingConfig['algoritm_name'][$ratingConfig['curr_algoritm']].">сохранен</p>";
		break;

	case "configuration":
		$ratingConfig['cur_season'] = $_POST['cur_season'];
		$ratingConfig['false_category'] = $_POST['false_category'];
		$ratingConfig['curr_algoritm'] = $_POST['curr_algoritm'];
		$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];
		$service_message = "<p class='adminka_message_success'>Конфигурация сохранена</p>";
		break;
	default:
		$service_message = "<p class='adminka_message_info'>Последнее обновление в ".$ratingConfig['last_update']."</p>";
		break;
}
// Запись в config
if (isset($_POST['act'])) {
	$strToFile = '<?'.PHP_EOL.'$ratingConfig = '.var_export($ratingConfig,true).';'.PHP_EOL.'?>';
	file_put_contents($pachAd.'rating.conf.php',$strToFile);
}

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
	.adminka_field label input{
		margin: 0;
	}
	.adminka_field table {border-collapse: collapse;}
	.adminka_field table td {text-align: center;}
	.adminka_field table tr:first-of-type td {font-weight: bold; text-transform: uppercase;}
	.adminka_field table tr:nth-child(2n) {background-color: #CCCCCC;}
	.adminka_field table input {width: 30px;text-align: center;}
</style>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {ratingSumm();},false)
	function ratingSumm() {
		var inps = document.getElementsByClassName('ratingAlgoritmInp'),
			itgs = document.getElementsByClassName('ratingAlgoritmItog'),
			summ = 0;

		for(var i=0; i < inps.length; i++) {
			var el = inps[i],
				name = el.name,
				k = el.value,
				p = itgs[i],
				s = p.getAttribute('summ');
			summ += Math.abs(s*k);
		}
		
		for(var i=0; i < inps.length; i++) {
			var el = inps[i],
				name = el.name,
				k = el.value,
				p = itgs[i],
				s = p.getAttribute('summ');
				text = Math.abs(s*k)/summ*100;
			text=text.toFixed(2);
			p.innerText = text+"%";
		}
	}
	
</script>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<div class="rating_container">

	<h2>Рейтинг</h2>

	<div class="adminka_field">
		<?=$service_message;?>
	</div>
	<div class="adminka_clr"></div>
	<div class="adminka_block">
        
        <div class="adminka_field">
            <p class="adminka_sub_title">Конфигурация</p>
        </div>
		
	<form method="post" id="formRatingConfig" name="formRatingConfig">
		<input type="hidden" name="act" value="configuration">
		
        <div class="adminka_field">
            Активный алгоритм <select name="curr_algoritm" id="curr_algoritm" onChange="this.form.submit();">
				<? foreach ($ratingConfig['algoritm_name'] as $k =>$v) {
						$sel = "";
						if ($k==$ratingConfig['curr_algoritm']) $sel = " selected";
						echo "<option value='$k'$sel>$v</option>";
					}
				?>
			</select>
        </div>
		
        <div class="adminka_field">
            <label>Текущий сезон (id)<input type="text" name="cur_season" id="cur_season" value="<?=$ratingConfig['cur_season'];?>"></label>
        </div>
		
        <div class="adminka_field">
            <label>Исключенные категории (id)<input type="text" name="false_category" id="false_category" value="<?=$ratingConfig['false_category'];?>"></label>
        </div>
		
		<div class="adminka_field">
			<button>Сохранить</button> 
		</div>
		
	</form>
        
		
	</div>
	
	<div class="adminka_block">
    
        <div class="adminka_field">
            <p class="adminka_sub_title">Текущий алгоритм - <?=$ratingConfig['algoritm_name'][$ratingConfig['curr_algoritm']];?></p>
        </div>
		
		<form method="post" id="formRatingAlgoritm" name="formRatingAlgoritm">
			<input type="hidden" name="act" value="algoritm">
		
			<div class="adminka_field">
				<table cellpadding="3px"><tbody>
					<tr>
						<td>Параметр</td><td>Базовые</td><td>Коэффициент</td><td>Итог</td>
					</tr>
						<? foreach($ratingConfig['basic_summ'] as $k=>$v): ?>
					<tr>
						<td><?=$ratingConfig['parametr_name'][$k];?></td>
						<td><?=round($v['segment']*100,2);?>%</td>
						<td>
							<input class="ratingAlgoritmInp" type="text" id="<?=$k;?>ItogField" name="<?=$k;?>" onBlur="ratingSumm()" value="<?=$Koef[$k];?>">
						</td>
						<td id="<?=$k;?>Itog" summ="<?=$v['summ'];?>" class="ratingAlgoritmItog"></td>
					</tr>
						<? endforeach; ?>
				</tbody></table>
			</div>
			
			<div class="adminka_field">
				<button>Сохранить</button> 
			</div>
		
		</form>
	</div>
	
	<div class="adminka_clr"></div>
	
	<form method="post" id="formRatingStart" name="formRatingStart">
		<input type="hidden" name="act" value="start">
		
        <div class="adminka_field">
            <p class="adminka_sub_title">Ручное обновление рейтинга</p>
        </div>
		
	<div class="adminka_field">
		<button>Обновить</button> 
	</div>
	</form>

</div>

</div>
