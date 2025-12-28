<?php

namespace App\Console\Commands\Cleanup;

use App\Models\DefectiveProduct;
use Illuminate\Database\Eloquent\Builder;

class CleanupDefectiveProducts extends AbstractCleanupCommand
{
    private const int TTL_DAYS = 60;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:defective-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete defective products that were soft deleted more than 2 months ago';

    /**
     * @return Builder<DefectiveProduct>
     */
    protected function query(): Builder
    {
        return DefectiveProduct::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(self::TTL_DAYS));
    }
}
