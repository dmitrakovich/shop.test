<?php

namespace App\ElasticSearch\Sync;

final class SyncPendingResult
{
    /**
     * @param  int  $pendingTotal  Pending count before sync
     * @param  int  $indexed  Successfully indexed
     * @param  int  $deleted  Successfully deleted from index
     * @param  int  $skipped  Skipped (empty document)
     * @param  int  $bulkErrors  Bulk error count
     * @param  list<array<string, mixed>>  $firstErrors  First bulk errors
     */
    public function __construct(
        public readonly int $pendingTotal = 0,
        public readonly int $indexed = 0,
        public readonly int $deleted = 0,
        public readonly int $skipped = 0,
        public readonly int $bulkErrors = 0,
        public readonly array $firstErrors = [],
    ) {}

    /**
     * Whether bulk completed without errors.
     */
    public function isSuccessful(): bool
    {
        return $this->bulkErrors === 0;
    }

    /**
     * Whether there was pending work.
     */
    public function hasWork(): bool
    {
        return $this->pendingTotal > 0;
    }
}
