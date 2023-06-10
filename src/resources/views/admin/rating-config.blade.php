<?php

use App\Models\Config;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateProductsRatingJob;

$ratingConfigModel = Config::findOrFail('rating');
$ratingConfig = $ratingConfigModel->config;
$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];

// Методы
switch (request()->input(['act'])) {
	case "start":
        UpdateProductsRatingJob::dispatchSync();
        admin_success("Рейтинг обновлен успешно в $ratingConfig[last_update]");
		break;

	case "algoritm":
		foreach ($ratingConfig['algoritm'][$ratingConfig['curr_algoritm']] as $k => $v) {
            $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']][$k] = request()->input([$k]);
        }
		$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];
        admin_success("Алгоритм <{$ratingConfig['algoritm_name'][$ratingConfig['curr_algoritm']]}> сохранен");
		break;

	case "configuration":
		$ratingConfig['cur_season'] = request()->input(['cur_season']);
		$ratingConfig['false_category'] = request()->input(['false_category']);
		$ratingConfig['curr_algoritm'] = request()->input(['curr_algoritm']);
		$Koef = $ratingConfig['algoritm'][$ratingConfig['curr_algoritm']];
        admin_success('Конфигурация сохранена');
		break;
}

// Запись в config
if (request()->input(['act'])) {
    $ratingConfigModel->update(['config' => $ratingConfig]);
}
?>

<style type="text/css">
	.adminka_block {
		padding-bottom: 10px;
	}
	@media all and (min-width: 600px) {
		.adminka_block:nth-child(2n) {
			float: right;
		}
		.adminka_block {
			width: 45%;
		}
	}
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

@include('admin::partials.alerts')

<div class="box grid-box">
    <div class="box-header with-border">
        <b style="color:#AAAAAA">
            Последнее обновление в {{ $ratingConfig['last_update'] }}
        </b>
        <form method="post" id="formRatingStart" name="formRatingStart">
            @csrf
            <input type="hidden" name="act" value="start">
            <div class="btn-group pull-right grid-create-btn" style="margin-right: 10px">
                <button type="submit" class="btn btn-sm btn-success">
                    Ручное обновление рейтинга
                </button>
            </div>
        </form>
    </div>

    <div class="adminka_block">
        <div class="adminka_field">
            <p class="adminka_sub_title">Конфигурация</p>
        </div>

        <form method="post" id="formRatingConfig" name="formRatingConfig">
            @csrf
            <input type="hidden" name="act" value="configuration">

            <div class="adminka_field">
                Активный алгоритм
                <select name="curr_algoritm" id="curr_algoritm" onChange="this.form.submit();">
                    @foreach ($ratingConfig['algoritm_name'] as $key => $value)
                        <option value="{{ $key }}" {{ $key == $ratingConfig['curr_algoritm'] ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="adminka_field">
                <label>Текущий сезон (id)
                    <input type="text" name="cur_season" id="cur_season" value="{{ $ratingConfig['cur_season'] }}">
                </label>
            </div>

            <div class="adminka_field">
                <label>Исключенные категории (id)
                    <input type="text" name="false_category" id="false_category" value="{{ $ratingConfig['false_category'] }}">
                </label>
            </div>

            <div class="adminka_field">
                <button class="btn btn-info">Сохранить</button>
            </div>

        </form>
    </div>


    <div class="adminka_block">
        <div class="adminka_field">
            <p class="adminka_sub_title">
                Текущий алгоритм - {{ $ratingConfig['algoritm_name'][$ratingConfig['curr_algoritm']] }}
            </p>
        </div>

        <form method="post" id="formRatingAlgoritm" name="formRatingAlgoritm">
            @csrf
            <input type="hidden" name="act" value="algoritm">

            <div class="adminka_field">
                <table cellpadding="3px" style="width: 100%;"><tbody>
                    <tr>
                        <td>Параметр</td>
                        <td>Базовые</td>
                        <td>Коэффициент</td>
                        <td>Итог</td>
                    </tr>
                    @foreach($ratingConfig['basic_summ'] as $key => $value)
                        <tr>
                            <td>{{ $ratingConfig['parametr_name'][$key] }}</td>
                            <td>{{ round($value['segment']*100,2) }}%</td>
                            <td>
                                <input
                                    class="ratingAlgoritmInp"
                                    type="text"
                                    id="{{ $key }}ItogField"
                                    name="{{ $key }}"
                                    onBlur="ratingSumm()"
                                    value="{{ $Koef[$key] }}">
                                </td>
                            <td id="{{ $key }}Itog" summ="{{ $value['summ'] }}" class="ratingAlgoritmItog"></td>
                        </tr>
                    @endforeach
                </tbody></table>
            </div>

            <div class="adminka_field">
                <button class="btn btn-info">Сохранить</button>
            </div>
        </form>
    </div>
</div>
