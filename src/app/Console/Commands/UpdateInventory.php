<?php

namespace App\Console\Commands;

use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Services\LogService;
use Illuminate\Console\Command;

class UpdateInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product inventory';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            UpdateAvailabilityJob::dispatchSync(app(LogService::class));
        } catch (\Throwable $th) {
            \Sentry\captureException($th);
        }
    }
}
