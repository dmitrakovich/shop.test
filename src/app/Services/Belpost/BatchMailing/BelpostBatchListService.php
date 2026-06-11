<?php

namespace App\Services\Belpost\BatchMailing;

use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Batch;
use App\Services\Belpost\Mappers\BelpostBatchMapper;
use App\Services\Belpost\Support\BelpostBatchGuards;
use App\Services\Belpost\Sync\BelpostBatchSyncService;

/**
 * Belpost mailing list lifecycle: create, update, fetch, delete, commit.
 */
class BelpostBatchListService
{
    public function __construct(
        private readonly BelpostBatchMapper $mapper,
        private readonly BelpostBatchSyncService $sync,
        private readonly BelpostBatchGuards $guards,
    ) {}

    public function create(Batch $batch): Batch
    {
        $response = ApiBelpostFacade::batchMailingCreateList()
            ->request($this->mapper->toListPayload($batch))
            ->getBodyFormat();

        return $this->sync->applyListResponse($batch, $response);
    }

    public function update(Batch $batch): Batch
    {
        $this->guards->ensureListId($batch);

        $response = ApiBelpostFacade::batchMailingUpdateList($batch->belpost_list_id)
            ->request($this->mapper->toListPayload($batch))
            ->getBodyFormat();

        return $this->sync->applyListResponse($batch, $response);
    }

    public function fetch(Batch $batch): Batch
    {
        $this->guards->ensureListId($batch);

        $response = ApiBelpostFacade::batchMailingGetList($batch->belpost_list_id)
            ->request()
            ->getBodyFormat();

        return $this->sync->applyListResponse($batch, $response);
    }

    public function delete(Batch $batch): void
    {
        $this->guards->ensureListId($batch);

        ApiBelpostFacade::batchMailingDeleteList($batch->belpost_list_id)->request();

        $this->sync->clearBatchBelpostFields($batch);
    }

    public function commit(Batch $batch): Batch
    {
        $this->guards->ensureListId($batch);
        $this->guards->ensureAllOrdersSyncedToBelpost($batch);

        $response = ApiBelpostFacade::batchMailingCommitList($batch->belpost_list_id)
            ->request()
            ->getBodyFormat();

        $batch = $this->sync->applyListResponse($batch, $response);
        $batch->touch('dispatch_date');

        return $batch;
    }
}
