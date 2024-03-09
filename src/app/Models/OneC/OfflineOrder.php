<?php

namespace App\Models\OneC;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $ID
 * @property string $CODE
 * @property string $DESCR object description
 * @property string $ISMARK Flag Object is Marke
 * @property string $VERSTAMP Version stamp
 * @property string $SP6089 ДисконтнаяКарта
 * @property string $SP6090 КодДК
 * @property string $SP6091 Товар
 * @property string $SP6092 КодТовара
 * @property string $SP6093 Артикул
 * @property string $SP6094 НаименованиеТовар
 * @property string $SP6095 Магазин
 * @property string $SP6096 КодМагазина
 * @property string $SP6097 ДатаПродажи
 * @property string $SP6098 НомерЧека
 * @property string $SP6099 Количество
 * @property string $SP6100 Размер
 * @property string $SP6101 Сумма
 * @property string $SP6102 Телефон
 */
class OfflineOrder extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SC6104';
}