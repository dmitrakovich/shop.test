<?php

namespace App\Services\Belpost\BatchMailing;

use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use App\Services\Belpost\Mappers\BelpostOrderItemMapper;
use App\Services\Belpost\Support\BelpostBatchGuards;
use App\Services\Belpost\Sync\BelpostBatchSyncService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

/**
 * Batch PDF blanks: generate, resolve document id, download.
 */
class BelpostBatchDocumentService
{
    public function __construct(
        private readonly BelpostBatchSyncService $sync,
        private readonly BelpostBatchGuards $guards,
        private readonly BelpostOrderItemMapper $mapper,
        private readonly BelpostBatchItemService $items,
    ) {}

    public function generateBatchBlanks(Batch $batch): Batch
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureBatchCommitted($batch);

        $response = ApiBelpostFacade::batchMailingGenerateListBlank($batch->belpost_list_id)
            ->request()
            ->getBodyFormat();

        return $this->sync->applyListResponse($batch, $response);
    }

    public function generateItemBlanks(Batch $batch, Order $order): Order
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureItemId($order);

        ApiBelpostFacade::batchMailingGenerateListItemBlank($batch->belpost_list_id, $order->belpost_item_id)
            ->request(['items' => [$this->mapper->toCreatePayload($order, $batch)]]);

        return $this->items->fetch($batch, $order);
    }

    public function resolveDocumentId(Batch $batch): Batch
    {
        $this->guards->ensureListId($batch);

        if ($batch->belpost_document_id) {
            return $batch;
        }

        $response = ApiBelpostFacade::batchMailingGetList($batch->belpost_list_id)
            ->request()
            ->getBodyFormat();

        $this->sync->applyDocumentFromListResponse($batch, $response);
        $batch->refresh();

        if ($batch->belpost_document_id) {
            return $batch;
        }

        $this->findDocumentInUserDocuments($batch);

        return $batch->refresh();
    }

    public function download(Batch $batch): Response
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureBatchCommitted($batch);

        if (!$batch->belpost_document_id) {
            $this->generateBatchBlanks($batch);
            $batch->refresh();
        }

        if (!$batch->belpost_document_id) {
            $this->resolveDocumentId($batch);
            $batch->refresh();
        }

        if (!$batch->belpost_document_id) {
            throw new BelpostApiException(
                'Belpost document id is not available for this batch. Generate blanks first (Сгенерировать бланки).'
            );
        }

        $this->waitUntilReady($batch);

        return ApiBelpostFacade::httpClient()->downloadDocument($batch->belpost_document_id);
    }

    /** Fallback when `documents` is not embedded in the list response. */
    private function findDocumentInUserDocuments(Batch $batch): void
    {
        $page = 1;
        $lastPage = 1;

        while ($page <= $lastPage) {
            $response = ApiBelpostFacade::batchMailingGetDocuments()
                ->request([
                    'page' => $page,
                    'perPage' => 100,
                ])
                ->getBodyFormat();

            $lastPage = (int)($response['last_page'] ?? 1);

            foreach (Arr::get($response, 'data', []) as $document) {
                if (!is_array($document)) {
                    continue;
                }

                if ((int)($document['list_id'] ?? 0) === (int)$batch->belpost_list_id) {
                    $batch->update(['belpost_document_id' => $document['id']]);

                    return;
                }
            }

            $page++;
        }
    }

    /**
     * Belpost generates PDFs asynchronously (`new` → `processing` → `done`).
     */
    private function waitUntilReady(Batch $batch, int $maxAttempts = 15, int $sleepSeconds = 2): void
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $response = ApiBelpostFacade::batchMailingGetList($batch->belpost_list_id)
                ->request()
                ->getBodyFormat();

            $document = $this->sync->extractDocument($response);

            if ($document !== null && isset($document['id'])) {
                $batch->update(['belpost_document_id' => $document['id']]);
            }

            $status = (string)($document['status'] ?? '');

            if ($status === 'done') {
                return;
            }

            if ($status === 'error') {
                throw new BelpostApiException(
                    "Belpost failed to generate batch document for list #{$batch->belpost_list_id}."
                );
            }

            if ($document === null && $batch->belpost_document_id) {
                return;
            }

            if (in_array($status, ['new', 'processing'], true) && $attempt < $maxAttempts - 1) {
                sleep($sleepSeconds);

                continue;
            }

            if ($status === '' && $batch->belpost_document_id) {
                return;
            }

            if ($attempt < $maxAttempts - 1) {
                sleep($sleepSeconds);
            }
        }

        throw new BelpostApiException(
            'Belpost document is still being generated. Try downloading again in a minute.'
        );
    }
}
