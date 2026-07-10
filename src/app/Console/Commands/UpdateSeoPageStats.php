<?php

namespace App\Console\Commands;

use App\Jobs\UpdateSeoPageStatsJob;
use Illuminate\Console\Command;

class UpdateSeoPageStats extends Command
{
    /**
     * @var string
     */
    protected $signature = 'seo-page-stats:update';

    /**
     * @var string
     */
    protected $description = 'Update SEO page view statistics from Yandex Metrika';

    public function handle(): void
    {
        UpdateSeoPageStatsJob::dispatchSync();
    }
}
