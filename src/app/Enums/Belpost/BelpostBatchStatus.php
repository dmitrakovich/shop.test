<?php

namespace App\Enums\Belpost;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BelpostBatchStatus: string implements HasColor, HasLabel
{
    case Uncommitted = 'uncommitted';
    case Committed = 'committed';
    case InOps = 'in_ops';

    public function getLabel(): string
    {
        return match ($this) {
            self::Uncommitted => 'Не сформирована',
            self::Committed => 'Сформирована',
            self::InOps => 'В ОПС',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Uncommitted => 'warning',
            self::Committed => 'success',
            self::InOps => 'info',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Uncommitted;
    }
}
