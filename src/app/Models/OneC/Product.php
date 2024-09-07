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
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'SP6111',
        'SP6116',
        'SP6122',
        'SP6123',
        'SP6124',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CODE' => 'integer',
    ];
}
