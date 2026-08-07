<?php

namespace App\Enums\Audit;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AuditEvent: string implements HasColor, HasLabel
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function getLabel(): string
    {
        return match ($this) {
            self::Created => 'Создание',
            self::Updated => 'Изменение',
            self::Deleted => 'Удаление',
            self::Restored => 'Восстановление',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Created => 'success',
            self::Updated => 'warning',
            self::Deleted => 'danger',
            self::Restored => 'info',
        };
    }
}
