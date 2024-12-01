<?php

namespace App\Models\OneC;

/**
 * @property string $ID ID object
 * @property string $CODE object code
 * @property string $DESCR object description
 * @property string $ISMARK Flag is Market
 * @property string $VERSTAMP Version stamp
 * @property string $SP4352 Фамилия
 * @property string $SP4353 Имя
 * @property string $SP4354 Отчество
 * @property string $SP4355 Представление
 * @property string $SP3966 ТелефонДом
 * @property string $SP3967 ТелефонМоб
 * @property string $SP3968 Почта
 * @property string $SP3969 Адрес
 * @property string $SP3970 ДеньРождения
 * @property string $SP3972 Скидка
 * @property string $SP3997 ДатаВыдачи
 * @property string $SP4356 Пол
 * @property string $SP3965 ФИО
 * @property string $SP4934 ПостояннаяСкидка
 * @property string $SP4935 НакопительнаяСумм
 * @property string $SP5001 Дополнительно
 * @property string $SP5538 НачальнаяСумма
 * @property string $SP5604 СогласенНаРассылку
 * @property string $SP5749 Бонусная
 * @property string $SP5750 КоличествоБонусов
 */
class DiscountCard extends AbstractOneCModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SC3964';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'SP3970' => 'date',
    ];

    /**
     * Array of fields that should not be trimmed during hydration
     */
    public array $doNotHydrate = ['ID'];
}
