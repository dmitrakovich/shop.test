<?php

namespace App\Models\OneC;

/**
 * @property int $CODE идентификатор товара
 * @property string $SP2608 Артикул товара (SKU)
 * @property string $SP6111 Url товара на сайте
 * @property string $SP6116 Url картинки товара
 * @property string $SP6122 Страна
 * @property string $SP6123 Фабрика
 * @property string $SP6124 Тип (категория)
 * @property string $SP6125 СезонСайт (коллекция)
 * @property int $SP6142 id товара с сайта
 * @property float $SP6155 ЦенаНовая
 * @property float $SP6156 ЦенаСтарая
 * @property float $SP6157 Скидка
 */
class Product extends AbstractOneCModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SC418';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'SP6111',
        'SP6116',
        'SP6122',
        'SP6123',
        'SP6124',
        'SP6125',
        'SP6142',
        'SP6155',
        'SP6156',
        'SP6157',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'CODE' => 'integer',
    ];
}
