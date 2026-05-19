<?php

namespace App\Services\Belpost\Support;

use App\Enums\Belpost\BelpostBatchStatus;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;

class BelpostBatchGuards
{
    public function ensureListId(Batch $batch): void
    {
        if (!$batch->belpost_list_id) {
            throw new BelpostApiException('Batch is not linked to Belpost (belpost_list_id is empty).');
        }
    }

    public function ensureItemId(Order $order): void
    {
        if (!$order->belpost_item_id) {
            throw new BelpostApiException("Order #{$order->id} is not linked to Belpost (belpost_item_id is empty).");
        }
    }

    public function ensureBatchEditable(Batch $batch): void
    {
        if ($batch->belpost_status !== null && !$batch->isBelpostEditable()) {
            throw new BelpostApiException('Belpost batch is already committed and cannot be modified.');
        }
    }

    public function ensureBatchCommitted(Batch $batch): void
    {
        if ($batch->belpost_status === null || $batch->belpost_status === BelpostBatchStatus::Uncommitted) {
            throw new BelpostApiException(
                'Batch must be committed in Belpost before generating or downloading blanks (Сформировать партию).'
            );
        }
    }
}
