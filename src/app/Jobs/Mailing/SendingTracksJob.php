<?php

namespace App\Jobs\Mailing;

use App\Models\Config;
use App\Models\Orders\Order;
use App\Notifications\SendingTracksSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendingTracksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Mailing identificator
     */
    const MAILING_ID = 3;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $config = Config::findCacheable('sending_tracks');
        if (!empty($config) && $config['active']) {
            Order::query()
                ->whereHas('items', fn (Builder $query) => $query->whereHas('statusLog', fn (Builder $query) => $query->where('sended_at', '>', now()->subDays(1))))
                ->whereHas('track', fn (Builder $query) => $query->whereNotNull('track_number'))
                ->whereDoesntHave('mailings', fn (Builder $query) => $query->where('mailing_id', self::MAILING_ID))
                ->with(['user', 'track'])
                ->each(function (Order $order) use ($config) {
                    $ignoreCities = $config['ignore_cities'] ?? [];
                    $orderCity = $order->city ? mb_strtolower($order->city) : null;
                    if (empty($ignoreCities) || array_search($orderCity, $ignoreCities) === false) {
                        $order->notify(new SendingTracksSms($order));
                    }
                }, 200);
        }
    }
}
