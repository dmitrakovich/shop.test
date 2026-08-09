<?php

namespace App\Console\Commands\Cleanup;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Builder;

class CleanupAudits extends AbstractCleanupCommand
{
    private const int TTL_MONTHS = 6;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:audits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete audit records older than 6 months';

    /**
     * @return Builder<Audit>
     */
    protected function query(): Builder
    {
        return Audit::query()
            ->where('created_at', '<', now()->subMonths(self::TTL_MONTHS));
    }
}
