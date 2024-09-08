<?php

namespace App\Console\Commands\OneRun;

use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateUserOnlineOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:update-user-online-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the has_online_orders flag for all users based on their orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->output->progressStart(User::query()->count());

        User::query()
            ->withCount('orders')
            ->chunk(200, function (Collection $chunk) {
                $chunk->each(function (User $user) {
                    if ($user->orders_count > 0) {
                        $user->update(['has_online_orders' => true]);
                    }
                    $this->output->progressAdvance();
                });
            });

        $this->output->progressFinish();
    }
}
