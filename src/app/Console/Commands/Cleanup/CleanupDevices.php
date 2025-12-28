<?php

namespace App\Console\Commands\Cleanup;

use App\Models\User\Device;
use Illuminate\Database\Eloquent\Builder;

class CleanupDevices extends AbstractCleanupCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:devices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old unused devices that are not linked to Yandex/Google and have no cart or favorites';

    /**
     * @return Builder<Device>
     */
    protected function query(): Builder
    {
        return Device::query()
            ->where('yandex_id', 0)
            ->where('google_id', '')
            ->where('created_at', '<', now()->subWeek())
            ->doesntHave('cart')
            ->doesntHave('favorites')
            ->doesntHave('orders');
    }
}
