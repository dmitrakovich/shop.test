<?php

namespace App\Enums\Feedback;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReviewDiscountType: string implements HasColor, HasLabel
{
    case Photo = 'photo';
    case Video = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::Photo => 'Скидка за отзыв с фото',
            self::Video => 'Скидка за отзыв с видео',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Photo => 'success',
            self::Video => 'info',
        };
    }
}
