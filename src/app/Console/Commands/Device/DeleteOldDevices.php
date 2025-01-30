<?php

namespace App\Console\Commands\Device;

use App\Models\User\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteOldDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old unused devices that are not linked to Yandex/Google and have no cart or favorites';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $count = Device::query()
            ->where('yandex_id', 0)
            ->where('google_id', '')
            ->where('created_at', '<', now()->subWeek())
            ->doesntHave('cart')
            ->doesntHave('favorites')
            ->doesntHave('orders')
            ->delete();

        Log::channel('jobs')->info("Удалено {$count} устройств по крону.");
        $this->info("Удалено {$count} устройств.");
    }
}
