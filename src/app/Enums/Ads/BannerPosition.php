<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case INDEX_MAIN = 'index_main';
    case INDEX_DOUBLE = 'index_double';
    /** Homepage tiles: multiple banners, each URL points to a catalog category */
    case INDEX_CATEGORY = 'index_category';
    case MAIN_MENU_CATALOG = 'main_menu_catalog';
    case CATALOG_MAIN = 'catalog_main';
    case FEEDBACK_MAIN = 'feedback_main';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDEX_MAIN => 'На главной — основной',
            self::INDEX_DOUBLE => 'На главной — двойной',
            self::INDEX_CATEGORY => 'На главной — категории',
            self::MAIN_MENU_CATALOG => 'В главном меню | каталог',
            self::CATALOG_MAIN => 'В каталоге',
            self::FEEDBACK_MAIN => 'В отзывах',
        };
    }
}
