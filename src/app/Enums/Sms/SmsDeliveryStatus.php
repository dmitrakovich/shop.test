<?php

namespace App\Enums\Sms;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Notifications\Client\Response\SmsTrafficStatusResponse;

enum SmsDeliveryStatus: string implements HasColor, HasLabel
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
    case MessageIsSpam = 'Message is spam';
    case QueuedOneMessage = 'queued 1 messages';

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

    /**
     * @return list<string>
     */
    public static function trackableValues(): array
    {
        return array_values(array_map(
            fn (self $case) => $case->value,
            array_filter(
                self::cases(),
                fn (self $case) => $case->isTrackable(),
            ),
        ));
    }

    public static function colorFor(self|string|null $status): ?string
    {
        if ($status instanceof self) {
            return $status->getColor();
        }

        return $status !== null ? 'danger' : null;
    }

    public function isTrackable(): bool
    {
        return $this !== self::Skipped
            && $this !== self::MessageIsSpam
            && !in_array($this->value, self::finalValues(), true);
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
            self::MessageIsSpam => 'Спам',
            self::QueuedOneMessage => 'В очереди',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Read, self::Delivered, self::ClassicDelivered => 'success',
            self::BufferedSmsc, self::QueuedOneMessage => 'info',
            self::Expired, self::ClassicExpired => 'warning',
            self::Rejected, self::ClassicRejected,
            self::Failed, self::Undelivered, self::ClassicNonDelivered,
            self::MessageIsSpam => 'danger',
            self::Deleted, self::ClassicDeleted, self::Unknown, self::Skipped => 'gray',
        };
    }
}
