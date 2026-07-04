<?php

namespace App\Enums\Seo;

use Filament\Support\Contracts\HasLabel;

enum SeoPageType: string implements HasLabel
{
    case Catalog = 'catalog';
    case InfoPage = 'info-page';

    public function getLabel(): string
    {
        return match ($this) {
            self::Catalog => 'Каталог',
            self::InfoPage => 'Инфо-страница',
        };
    }
}
