<?php

use App\Models\Brand;
use App\Models\Size;
use App\Models\Product;
use App\Jobs\UpdateAvailabilityJob;

$availabilityConfigFile = database_path('files/availability.conf.php');
$availabilityConfig = require $availabilityConfigFile;

// Предустановки
$thtime = date("Y-m-d-H:i:s");
$filedate = explode(",",$availabilityConfig['file']);
$service_message = "";

// Методы
if (isset($_POST['act'])) {
	switch ($_POST['act']) {
        case "start":
            $service_message = UpdateAvailabilityJob::dispatchNow(true);
			break;

		case "publish":
			if(empty($_POST['publish_list'])) {
                break;
            }
			$act_count = count($_POST['publish_list']);
			if ($act_count>0) {
                $publish_list = array_keys($_POST['publish_list']);
                Product::withTrashed()->whereIn('id', $publish_list)->restore();
                foreach ($availabilityConfig['publish'] as $actK => $actV) {
                    if (isset($_POST['publish_list'][$actV['id']])) {
                        $availabilityConfig['publish'][$actK]['status']=1;
                    }
                }
                $service_message = "<p class='adminka_message_success'>Опубликовано $act_count</p>";
			}
			break;

		case "del":
			if(empty($_POST['del_list'])) {
                break;
            }
			$act_count = count($_POST['del_list']);
			if ($act_count>0) {
                $del_list = array_keys($_POST['del_list']);
                Product::whereIn('id', $del_list)->delete();
                foreach ($availabilityConfig['del'] as $actK => $actV) {
                    if (isset($_POST['del_list'][$actV['id']])) {
                        $availabilityConfig['del'][$actK]['status']=1;
                    }
                }
                $service_message = "<p class='adminka_message_success'>Снято с публикации $act_count</p>";
			}
			break;

		case "delS":
			if(empty($_POST['delS_list'])) {
                break;
            }
			$act_count = count($_POST['delS_list']);
			if ($act_count>0) {
                $delS_list = implode(',',array_keys($_POST['delS_list']));
                Size::whereIn('id', $delS_list)->url()->detach();
                Size::whereIn('id', $delS_list)->delete();
                foreach ($availabilityConfig['del_size'] as $actK => $actV) {
                    if (isset($_POST['delS_list'][$actV['vid']])) {
                        $availabilityConfig['del_size'][$actK]['status']=1;
                    }
                }
                $service_message = "<p class='adminka_message_success'>Удалено размеров $act_count</p>";
			}
			break;

		case "addS":
			if(empty($_POST['addS_list'])) {
                break;
            }
			$act_count = count($_POST['addS_list']);
			if ($act_count>0) {
                $addS_list = array_keys($_POST['addS_list']);
                $addS_values = "";
                throw new Exception("Bad code Error");
                /*
                for ($i=0;$i<$act_count;$i++) {
                    $addS_list_sec = explode('-',$addS_list[$i]);
                    if (!empty($addS_values)) $addS_values .= ",";
                    $addS_values .= "(".$addS_list_sec[0].",2,".$addS_list_sec[1].",'+',0)";
                }
                //$query = "SELECT * FROM `#__jshopping_products_attr2` LIMIT 1";
                $query = "INSERT INTO `#__jshopping_products_attr2`  (product_id,attr_id,attr_value_id,price_mod,addprice) VALUES $addS_values";
                $db->setQuery($query);
                $db->query();
                foreach ($availabilityConfig['add_size'] as $actK => $actV) {
                    if (isset($_POST['addS_list'][$actV['id']."-".$actV['vid']])) {
                        $availabilityConfig['add_size'][$actK]['status']=1;
                    }
                }*/
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
        file_put_contents(database_path('files/availability.log.txt'), "$thtime [$log_type]: $log_mess".PHP_EOL,FILE_APPEND);
    }
    // Запись в config
    file_put_contents($availabilityConfigFile, "<?php\nreturn " . var_export($availabilityConfig, true) . ';');
} else {
    $service_message = "<p class='adminka_message_info'>Файл $filedate[0]. Актуальное наличие на $availabilityConfig[last_update]</p>";
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

<div id="j-main-container" class="span10">
<div class="availability_container">
	<h2>Наличие</h2>
	<div class="adminka_field">
		{!! $service_message !!}
	</div>
	<div class="adminka_clr"></div>
	<div class="adminka_block">

        <div class="adminka_field">
            <p class="adminka_sub_title">Конфигурация</p>
        </div>

        <form method="post" id="formavailabilityConfig" name="formavailabilityConfig">
            @csrf
            <input type="hidden" name="act" value="save">
            <div class="adminka_field">
                <label>
                    Автоматическое обновление
                    <input type="checkbox" name="auto_del" id="auto_del" {{ $availabilityConfig['auto_del'] == 'on' ? 'checked' : ''}}>
                </label>
            </div>
            <div class="adminka_field">
                <label>
                    Хранить дней
                    <input type="text" name="period" id="period" value="{{ $availabilityConfig['period'] }}"/>
                </label>
            </div>
            <div class="adminka_field">
                <button>Сохранить</button>
            </div>
        </form>

        <div class="adminka_field" style="height: 20px;">
            <p></p>
        </div>

        <form method="post" id="formavailabilityStart" name="formavailabilityStart">
            @csrf
            <input type="hidden" name="act" value="start">
            <div class="adminka_field">
                <p class="adminka_sub_title">Ручное обновление наличия</p>
            </div>
            <div class="adminka_field">
                <button>Обновить</button>
            </div>
        </form>

		{{-- <div class="adminka_field" style="height: 20px;">
			<p>История изменений<br>
           <a href="https://modny.by/administrator/components/com_jshopping/views/panel/tmpl/availability.log.txt" target="_blank">Открыть лог</a></p>
		</div> --}}
	</div>

	<div class="adminka_block">

        <div class="adminka_field">
            <p class="adminka_sub_title">Опубликовать</p>
        </div>

        <div class="adminka_field">
			<form method="post" id="formavailabilityPublish" name="formavailabilityPublish">
                @csrf
				<input type="hidden" name="act" value="publish">
				<?php
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
						if ($availabilityConfig['auto_del'] == 'on') {
                            echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
                        }
						echo "<label $aviC><a href='" .  url("/admin/products/$aviI/edit") . "' title='$aviN' target='_blank'>$aviN</a>";
						if ($avI['status'] != 1 && !isset($avI['err'])) echo "<input type='checkbox' name='publish_list[$aviI]' checked />";
						echo "</label><br>";
					}
					if ($btn_ok > 0) {
                        echo '<button>Применить</button>';
                    }
				?>
			</form>
        </div>

        <div class="adminka_field">
            <p class="adminka_sub_title">Снять с публикации</p>
        </div>


        <div class="adminka_field">
			<form method="post" id="formavailabilityDel" name="formavailabilityDel">
                @csrf
				<input type="hidden" name="act" value="del">
                <?php
					$btn_ok = 0;
					foreach ($availabilityConfig['del'] as $avI) {
						$aviI = $avI['id'];
						$aviN = $avI['name'];
						$aviC = '';
						if ($avI['status'] == 1) {
							$aviC = 'class="complete"';
						} else $btn_ok++;
						if ($availabilityConfig['auto_del'] == 'on') echo ((isset($avI['time']))?($avI['time'].":&nbsp;"):'');
						echo "<label $aviC><a href='" .  url("/admin/products/$aviI/edit") . "' title='$aviN' target='_blank'>$aviN</a>";
						if ($avI['status'] != 1) echo "<input type='checkbox' name='del_list[$aviI]' checked />";
						echo "</label><br>";
                    }
                    if ($btn_ok > 0) {
                        echo '<button>Применить</button>';
                    }
				?>
			</form>
        </div>

        <div class="adminka_field">
            <p class="adminka_sub_title">Добавить размер</p>
        </div>


        <div class="adminka_field">
			<form method="post" id="formavailabilityAdd" name="formavailabilityAdd">
                @csrf
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
						echo "<label $aviC><a href='" .  url("/admin/products/$aviI/edit") . "' title='$aviN' target='_blank'>$aviN</a> р. $aviSN";
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
                @csrf
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
						echo "<label $aviC><a href='" .  url("/admin/products/$aviI/edit") . "' title='$aviN' target='_blank'>$aviN</a> р. $aviSN";
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
			<?php
				foreach ($availabilityConfig['new'] as $avK => $avI) {
                    $name = "$avI[brand] $avI[articul] $avI[cat]";
                    $sizes = is_array($avI['size']) ? ' - р. ' . implode(',', $avI['size']) : '';

                    if (empty($avI['err'])) {
                        $link = route('products.create', [
                            'slug' => $avI['id'],
                            'title' => $avI['articul'],
                            'brand_name' => $avI['brand'],
                            'category_name' => $avI['cat'],
                            'new_sizes' => is_array($avI['size']) ? implode(';', $avI['size']) : null,
                        ]);
                        $link = '&nbsp;<a href="' . $link . '" class="add_but_prod" title="Создать товар" target="_blank">&rArr;</a>';
                    } else {
                        $link = " ($avI[err])";
                    }

					echo $name, $sizes, $link, '<br>';
                }
			?>
        </div>

	</div>

	<div class="adminka_clr"></div>

</div>
</div>
