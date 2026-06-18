<?php

namespace App\Enums\Product;

use Filament\Support\Contracts\HasLabel;

enum ProductSort: string implements HasLabel
{
    case Rating = 'rating';
    case Newness = 'newness';
    case PriceUp = 'price-up';
    case PriceDown = 'price-down';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rating => 'по популярности',
            self::Newness => 'новинки',
            self::PriceUp => 'по возрастанию цены',
            self::PriceDown => 'по убыванию цены',
        };
    }

    public static function default(): self
    {
        return self::Newness;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string)$value) ?? self::default();
    }
}
