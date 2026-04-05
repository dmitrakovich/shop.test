<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case INDEX_MAIN = 'index_main';
    case INDEX_TOP = 'index_top';
    case INDEX_BOTTOM = 'index_bottom';
    case MAIN_MENU_CATALOG = 'main_menu_catalog';
    case CATALOG_TOP = 'catalog_top';
    case CATALOG_MOB = 'catalog_mob';
    case FEEDBACK = 'feedback';
    case FEEDBACK_MOB = 'feedback_mob';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDEX_MAIN => 'На главной основной',
            self::INDEX_TOP => 'На главной сверху',
            self::INDEX_BOTTOM => 'На главной снизу',
            self::MAIN_MENU_CATALOG => 'В главном меню | каталог',
            self::CATALOG_TOP => 'В каталоге',
            self::CATALOG_MOB => 'В каталоге моб.',
            self::FEEDBACK => 'В отзывах.',
            self::FEEDBACK_MOB => 'В отзывах моб.',
        };
    }
}
