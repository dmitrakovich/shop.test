<?php

namespace App\Filament\Resources\Departures\Batches\Pages;

use App\Enums\Belpost\BelpostDirection;
use App\Enums\Belpost\BelpostPaymentType;
use App\Enums\Belpost\BelpostPostalDeliveryType;
use App\Filament\Resources\Departures\Batches\BatchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['postal_delivery_type'] ??= BelpostPostalDeliveryType::tryFrom(
            (string)config('belpost.defaults.postal_delivery_type')
        )?->value;
        $data['direction'] ??= BelpostDirection::tryFrom(
            (string)config('belpost.defaults.direction')
        )?->value;
        $data['payment_type'] ??= BelpostPaymentType::tryFrom(
            (string)config('belpost.defaults.payment_type')
        )?->value;
        if (
            BelpostPaymentType::tryFromFormState($data['payment_type'] ?? null)?->requiresCardNumber()
            && blank($data['card_number'] ?? null)
        ) {
            $data['card_number'] = config('belpost.defaults.card_number');
        }

        $enum = BelpostPostalDeliveryType::tryFromFormState($data['postal_delivery_type'] ?? null);
        if ($enum?->isEcommercePostal()) {
            $data['is_partial_receipt'] = false;
        }
        if ($enum !== null && !$enum->supportsDeclaredValueListFlag()) {
            $data['is_declared_value'] = false;
        }
        if ($enum?->requiresNegotiatedRateFalseForApi()) {
            $data['negotiated_rate'] = false;
        }

        return $data;
    }
}
