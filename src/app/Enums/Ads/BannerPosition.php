<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case INDEX_MAIN = 'index_main';
    case INDEX_TOP = 'index_top';
    case INDEX_BOTTOM = 'index_bottom';
    case MAIN_MENU_CATALOG = 'main_menu_catalog';
    case CATALOG_MAIN = 'catalog_main';
    case FEEDBACK_MAIN = 'feedback_main';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDEX_MAIN => 'На главной основной',
            self::INDEX_TOP => 'На главной сверху',
            self::INDEX_BOTTOM => 'На главной снизу',
            self::MAIN_MENU_CATALOG => 'В главном меню | каталог',
            self::CATALOG_MAIN => 'В каталоге',
            self::FEEDBACK_MAIN => 'В отзывах',
        };
    }
}
