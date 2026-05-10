<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case INDEX_MAIN = 'index_main';
    case INDEX_DOUBLE = 'index_double';
    case INDEX_CATEGORY = 'index_category';
    case CATALOG_MAIN = 'catalog_main';
    case FEEDBACK_MAIN = 'feedback_main';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDEX_MAIN => 'На главной: основной',
            self::INDEX_DOUBLE => 'На главной: двойной',
            self::INDEX_CATEGORY => 'На главной: категории',
            self::CATALOG_MAIN => 'В каталоге',
            self::FEEDBACK_MAIN => 'В отзывах',
        };
    }

    /**
     * These slots only use desktop creatives in Filament; {@see BannerForm} hides mobile uploads.
     */
    public function isDesktopOnly(): bool
    {
        return match ($this) {
            self::INDEX_DOUBLE, self::INDEX_CATEGORY => true,
            default => false,
        };
    }
}
