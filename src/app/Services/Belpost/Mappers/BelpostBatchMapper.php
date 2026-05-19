<?php

namespace App\Services\Belpost\Mappers;

use App\Models\Orders\Batch;

class BelpostBatchMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toListPayload(Batch $batch): array
    {
        return [
            'name' => $batch->name ?: "Партия #{$batch->id}",
            'postal_delivery_type' => $batch->postal_delivery_type
                ?? config('belpost.defaults.postal_delivery_type'),
            'direction' => $batch->direction ?? config('belpost.defaults.direction'),
            'payment_type' => $batch->payment_type ?? config('belpost.defaults.payment_type'),
            'negotiated_rate' => $batch->negotiated_rate ? 1 : 0,
            'is_declared_value' => (bool)$batch->is_declared_value,
            'is_partial_receipt' => (bool)$batch->is_partial_receipt,
        ];
    }
}
