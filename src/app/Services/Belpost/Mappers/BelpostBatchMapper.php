<?php

namespace App\Services\Belpost\Mappers;

use App\Enums\Belpost\BelpostPaymentType;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Models\Orders\Batch;

class BelpostBatchMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toListPayload(Batch $batch): array
    {
        $paymentType = $this->resolvePaymentType($batch);

        $payload = [
            'name' => $batch->name ?: "Партия #{$batch->id}",
            'postal_delivery_type' => $batch->postal_delivery_type
                ?? config('belpost.defaults.postal_delivery_type'),
            'direction' => $batch->direction ?? config('belpost.defaults.direction'),
            'payment_type' => $paymentType,
            'negotiated_rate' => $batch->negotiated_rate ? 1 : 0,
            'is_declared_value' => (bool)$batch->is_declared_value,
            'is_partial_receipt' => (bool)$batch->is_partial_receipt,
        ];

        $cardNumber = $this->resolveCardNumber($batch, $paymentType);
        if ($cardNumber !== null) {
            $payload['card_number'] = $cardNumber;
        }

        return $payload;
    }

    private function resolvePaymentType(Batch $batch): string
    {
        $paymentType = $batch->payment_type;

        if ($paymentType instanceof BelpostPaymentType) {
            return $paymentType->value;
        }

        if (is_string($paymentType) && $paymentType !== '') {
            return $paymentType;
        }

        return (string)config('belpost.defaults.payment_type');
    }

    private function resolveCardNumber(Batch $batch, string $paymentType): ?string
    {
        if (!BelpostPaymentType::tryFrom($paymentType)?->requiresCardNumber()) {
            return null;
        }

        $cardNumber = trim((string)($batch->card_number ?? config('belpost.defaults.card_number') ?? ''));

        if ($cardNumber === '') {
            throw new BelpostApiException(
                'Номер карты обязателен для способа оплаты «Электронный л/с». '
                . 'Укажите BELPOST_CARD_NUMBER в .env или поле «Номер карты» в партии.',
            );
        }

        return $cardNumber;
    }
}
