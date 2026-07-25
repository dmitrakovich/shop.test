<?php

namespace App\Enums\Feedback;

use Filament\Support\Contracts\HasLabel;

enum ReviewDiscountType: string implements HasLabel
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
}
