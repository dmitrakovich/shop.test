<?php

namespace App\Console\Commands\OneRun;

use App\Enums\User\OrderType;
use App\Models\Orders\OfflineOrder;
use App\Models\Orders\Order as OnlineOrder;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateUserMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:update-user-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user metadata with the date and type of their last order (online or offline).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->output->progressStart(User::query()->count());

        User::query()
            ->with([
                'orders:id,user_id,created_at',
                'offlineOrders:id,user_id,created_at',
            ])
            ->chunk(200, function (Collection $chunk) {
                $chunk->each(function (User $user) {
                    $lastOnlineOrder = $user->orders->sortByDesc('created_at')->first();
                    $lastOfflineOrder = $user->offlineOrders->sortByDesc('created_at')->first();

                    $lastOrder = collect([$lastOnlineOrder, $lastOfflineOrder])
                        ->filter()
                        ->sortByDesc(fn ($order) => $order->created_at)
                        ->first();

                    $lastOrderType = match (true) {
                        $lastOrder instanceof OnlineOrder => OrderType::ONLINE,
                        $lastOrder instanceof OfflineOrder => OrderType::OFFLINE,
                        default => null,
                    };

                    $user->metadata()->updateOrCreate([], [
                        'last_order_type' => $lastOrderType,
                        'last_order_date' => $lastOrder?->created_at,
                    ]);

                    $this->output->progressAdvance();
                });
            });

        $this->output->progressFinish();
    }
}
