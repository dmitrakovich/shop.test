<?php

namespace App\Jobs\Mailing;

use App\Models\Config;
use App\Models\User\User;
use App\Notifications\DiscountAfterRegisterSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DiscountAfterRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Mailing identificator
     */
    const MAILING_ID = 1;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $config = Config::findCacheable('newsletter_register');
        if ($config['active']) {
            User::query()
                ->where('created_at', '>', now()->subDays($config['to_days']))
                ->where('created_at', '<', now()->subDays($config['from_days']))
                ->whereDoesntHave('orders')
                ->whereDoesntHave('mailings', fn (Builder $query) => $query->where('mailing_id', self::MAILING_ID))
                ->each(function (User $user) {
                    $user->notify(new DiscountAfterRegisterSms($user->group->discount));
                }, 200);
        }
    }
}
