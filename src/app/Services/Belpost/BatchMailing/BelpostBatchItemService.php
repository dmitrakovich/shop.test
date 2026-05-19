<?php

namespace App\Services\Belpost\BatchMailing;

use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use App\Services\Belpost\Mappers\BelpostOrderItemMapper;
use App\Services\Belpost\Support\BelpostBatchGuards;
use App\Services\Belpost\Sync\BelpostOrderItemSyncService;
use Illuminate\Support\Arr;

/**
 * Belpost list items: add, update, remove, and sync orders in a batch.
 */
class BelpostBatchItemService
{
    public function __construct(
        private readonly BelpostOrderItemMapper $mapper,
        private readonly BelpostOrderItemSyncService $sync,
        private readonly BelpostBatchGuards $guards,
    ) {}

    public function create(Batch $batch, Order $order): Order
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureBatchEditable($batch);

        $response = ApiBelpostFacade::batchMailingCreateListItems($batch->belpost_list_id)
            ->request(['items' => [$this->mapper->toCreatePayload($order, $batch)]])
            ->getBodyFormat();

        $created = Arr::get($response, 'created', []);
        $item = is_array($created) ? ($created[0] ?? null) : null;

        if (!is_array($item)) {
            throw new BelpostApiException($this->resolveCreateFailureReason($response));
        }

        return $this->sync->applyItemResponse($order, $item);
    }

    public function update(Batch $batch, Order $order): Order
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureItemId($order);
        $this->guards->ensureBatchEditable($batch);

        $response = ApiBelpostFacade::batchMailingUpdateListItem($batch->belpost_list_id, $order->belpost_item_id)
            ->request($this->mapper->toCreatePayload($order, $batch))
            ->getBodyFormat();

        return $this->sync->applyItemResponse($order, $response);
    }

    public function delete(Batch $batch, Order $order): void
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureItemId($order);
        $this->guards->ensureBatchEditable($batch);

        ApiBelpostFacade::batchMailingDeleteListItem($batch->belpost_list_id, $order->belpost_item_id)
            ->request();

        $this->sync->clearBelpostFields($order);
    }

    public function fetch(Batch $batch, Order $order): Order
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureItemId($order);

        $response = ApiBelpostFacade::batchMailingGetListItem($batch->belpost_list_id, $order->belpost_item_id)
            ->request()
            ->getBodyFormat();

        return $this->sync->applyItemResponse($order, $response);
    }

    /**
     * @return array<int, Order>
     */
    public function syncAll(Batch $batch): array
    {
        $batch->loadMissing('orders');
        $synced = [];

        foreach ($batch->orders as $order) {
            $synced[] = $order->belpost_item_id
                ? $this->update($batch, $order)
                : $this->create($batch, $order);
        }

        return $synced;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function resolveCreateFailureReason(array $response): string
    {
        $failed = Arr::get($response, 'failed', []);
        $reason = 'Belpost did not return created item.';

        if (is_array($failed) && $failed !== []) {
            $first = $failed[0] ?? null;
            if (is_array($first)) {
                $reason = $first['reason']
                    ?? $first['message']
                    ?? json_encode($first, JSON_UNESCAPED_UNICODE);
            }
        }

        return (string)$reason;
    }
}
