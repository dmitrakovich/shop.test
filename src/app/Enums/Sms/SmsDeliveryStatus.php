<?php

namespace App\Enums\Sms;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Notifications\Client\Response\SmsTrafficStatusResponse;

enum SmsDeliveryStatus: string implements HasLabel
{
    case Read = 'READ';
    case Delivered = 'DELIVERED';
    case ClassicDelivered = 'Delivered';
    case Expired = 'EXPIRED';
    case ClassicExpired = 'Expired';
    case Rejected = 'REJECTED';
    case ClassicRejected = 'Rejected';
    case Deleted = 'DELETED';
    case ClassicDeleted = 'Deleted';
    case Failed = 'FAILED';
    case Undelivered = 'UNDELIVERED';
    case ClassicNonDelivered = 'Non Delivered';
    case Unknown = 'Unknown status';
    case BufferedSmsc = 'Buffered SMSC';
    case Skipped = 'skipped';

    /**
     * @return list<string>
     */
    public static function finalValues(): array
    {
        return SmsTrafficStatusResponse::finalStatuses();
    }

    public static function tryFromCaseInsensitive(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }

        return null;
    }

    public static function resolve(?string $value): self|string|null
    {
        if ($value === null) {
            return null;
        }

        return self::tryFromCaseInsensitive($value) ?? $value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Read => 'Прочитано',
            self::Delivered, self::ClassicDelivered => 'Доставлено',
            self::Expired, self::ClassicExpired => 'Просрочено',
            self::Rejected, self::ClassicRejected => 'Отклонено',
            self::Deleted, self::ClassicDeleted => 'Удалено',
            self::Failed => 'Ошибка доставки',
            self::Undelivered, self::ClassicNonDelivered => 'Не доставлено',
            self::Unknown => 'Неизвестный статус',
            self::BufferedSmsc => 'Доставляется',
            self::Skipped => 'Пропущено',
        };
    }
}
