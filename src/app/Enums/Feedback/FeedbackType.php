<?php

namespace App\Enums\Feedback;

use Filament\Support\Contracts\HasLabel;

enum FeedbackType: int implements HasLabel
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
}
