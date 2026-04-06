<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerType: string implements HasLabel
{
    case IMAGE = 'image';
    case VIDEO = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::IMAGE => 'Фото',
            self::VIDEO => 'Видео',
        };
    }

    public function isImage(): bool
    {
        return $this === self::IMAGE;
    }

    public function isVideo(): bool
    {
        return $this === self::VIDEO;
    }
}
