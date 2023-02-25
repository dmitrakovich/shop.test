<?php

namespace App\Jobs\Mailing;

use App\Models\Config;
use App\Models\Orders\Order;
use App\Notifications\LeaveFeedbackSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LeaveFeedbackAfterOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max days after complete order check feedback
     */
    const FROM_DAYS = 7;

    /**
     * Mailing identificator
     */
    const MAILING_ID = 2;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Order::query()
            ->where('status_key', 'complete')
            ->where('status_updated_at', '>', now()->subDays(self::FROM_DAYS))
            ->where('status_updated_at', '<', now()->subHours((int)Config::findCacheable('feedback')['send_after']))
            ->whereRelation('items', 'status_key', 'complete')
            ->whereDoesntHave('mailings', fn (Builder $query) => $query->where('mailing_id', self::MAILING_ID))
            ->with(['user', 'items'])
            ->each(function (Order $order) {
                if (!empty($order->user) && $order->user->hasReviewAfterOrder()) {
                    return;
                }
                $order->notify(new LeaveFeedbackSms($order));
            }, 200);
    }
}
