<?php

namespace App\Services\Belpost\Sync;

use App\Models\Orders\Batch;
use Illuminate\Support\Arr;

class BelpostBatchSyncService
{
    public function __construct(
        private readonly BelpostOrderItemSyncService $orderItemSync,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public function applyListResponse(Batch $batch, array $response): Batch
    {
        $batch->update([
            'belpost_list_id' => Arr::get($response, 'id', $batch->belpost_list_id),
            'belpost_status' => Arr::get($response, 'status'),
            'name' => Arr::get($response, 'name') ?: $batch->name,
            'postal_delivery_type' => Arr::get($response, 'postal_delivery_type', $batch->postal_delivery_type),
            'direction' => Arr::get($response, 'direction', $batch->direction),
            'payment_type' => Arr::get($response, 'payment_type', $batch->payment_type),
            'card_number' => Arr::get($response, 'card_number', $batch->card_number),
            'negotiated_rate' => (bool)Arr::get($response, 'negotiated_rate', $batch->negotiated_rate),
            'is_declared_value' => (bool)Arr::get($response, 'is_declared_value', $batch->is_declared_value),
            'is_partial_receipt' => (bool)Arr::get($response, 'is_partial_receipt', $batch->is_partial_receipt),
            'belpost_sync_error' => null,
        ]);

        $this->applyItemsFromListResponse($batch, $response);
        $this->applyDocumentFromListResponse($batch, $response);

        return $batch->refresh();
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function applyDocumentFromListResponse(Batch $batch, array $response): void
    {
        $document = $this->extractDocument($response);

        if ($document === null || !isset($document['id'])) {
            return;
        }

        $batch->update(['belpost_document_id' => $document['id']]);
    }

    public function clearBatchBelpostFields(Batch $batch): void
    {
        $batch->update([
            'belpost_list_id' => null,
            'belpost_status' => null,
            'belpost_document_id' => null,
            'belpost_sync_error' => null,
            'name' => null,
        ]);

        $batch->orders()->update([
            'belpost_item_id' => null,
            'belpost_s10code' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>|null
     */
    public function extractDocument(array $response): ?array
    {
        $documents = Arr::get($response, 'documents');

        if (!is_array($documents)) {
            return null;
        }

        if (isset($documents['id'])) {
            return $documents;
        }

        $first = $documents[0] ?? null;

        return is_array($first) ? $first : null;
    }

    /**
     * Match list items to orders via `foreign_id` (our order primary key).
     *
     * @param  array<string, mixed>  $response
     */
    private function applyItemsFromListResponse(Batch $batch, array $response): void
    {
        $items = Arr::get($response, 'items', []);

        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $foreignId = Arr::get($item, 'foreign_id');

            if ($foreignId === null) {
                continue;
            }

            $order = $batch->orders()->where('id', $foreignId)->first();

            if ($order) {
                $this->orderItemSync->applyItemResponse($order, $item);
            }
        }
    }
}
