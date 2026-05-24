<?php

namespace App\Enums\Product;

use Filament\Support\Contracts\HasLabel;

enum RatingFactor: string implements HasLabel
{
    case Views = 'views';
    case Carts = 'carts';
    case Purchases = 'purchases';
    case Price = 'price';
    case Discount = 'discount';
    case CategoryUp = 'category_up';
    case CategoryDown = 'category_down';
    case Season = 'season';
    case CreatedAt = 'created_at';
    case ProductUp = 'product_up';
    case ProductDown = 'product_down';

    public function getLabel(): string
    {
        return match ($this) {
            self::Views => 'Кол-во просмотров (Я.Метрика)',
            self::Carts => 'Кол-во добавлений в корзину',
            self::Purchases => 'Кол-во покупок',
            self::Price => 'Цена',
            self::Discount => 'Размер скидки',
            self::CategoryUp => 'Категории на повышение',
            self::CategoryDown => 'Категории на понижение',
            self::Season => 'Актуальный сезон',
            self::CreatedAt => 'Дата создания',
            self::ProductUp => 'Товар на повышение',
            self::ProductDown => 'Товар на понижение',
        };
    }

    public function coefficientColumn(): string
    {
        return $this->value . '_coefficient';
    }
}
