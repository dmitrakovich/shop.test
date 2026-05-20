<?php

namespace App\Enums\Belpost;

use Filament\Support\Contracts\HasLabel;

enum BelpostPaymentType: string implements HasLabel
{
    case PaymentOrder = 'payment_order';
    case AdvanceReceipt = 'advance_receipt';
    case MonetaryDocument = 'monetary_document';
    case ElectronicPersonalAccount = 'electronic_personal_account';
    case CommitmentLetter = 'commitment_letter';
    case NotSpecified = 'not_specified';
    case Cash = 'cash';

    public function requiresCardNumber(): bool
    {
        return $this === self::ElectronicPersonalAccount;
    }

    public static function tryFromFormState(mixed $state): ?self
    {
        if ($state instanceof self) {
            return $state;
        }

        if (is_string($state) && $state !== '') {
            return self::tryFrom($state);
        }

        return null;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PaymentOrder => 'Платёжное поручение',
            self::AdvanceReceipt => 'Ав. квитанция',
            self::MonetaryDocument => 'Денежный документ',
            self::ElectronicPersonalAccount => 'Электронный л/с',
            self::CommitmentLetter => 'Гарантийное письмо',
            self::NotSpecified => 'Не указано',
            self::Cash => 'Наличные',
        };
    }
}
