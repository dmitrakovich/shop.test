<?php

namespace App\Enums\Feedback;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeedbackType: int implements HasColor, HasLabel
{
    case SPAM = 0;
    case REVIEW = 1;
    // case QUESTION = 2;
    // case SUGGESTION = 3;
    // case BUG = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::SPAM => 'Спам',
            self::REVIEW => 'Отзыв',
            // self::QUESTION => 'Вопрос',
            // self::SUGGESTION => 'Предложение',
            // self::BUG => 'Баг',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SPAM => 'danger',
            self::REVIEW => 'success',
        };
    }
}
