<?php

namespace App\Jobs\Mailing;

use App\Models\User\User;
use App\Notifications\DiscountAfterRegisterSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DiscountAfterRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max days after register check order
     */
    const FROM_DAYS = 30;

    /**
     * Days after register check order
     */
    const TO_DAYS = 5;

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
        User::query()
            ->where('created_at', '>', now()->subDays(self::FROM_DAYS))
            ->where('created_at', '<', now()->subDays(self::TO_DAYS))
            ->whereDoesntHave('orders')
            ->whereDoesntHave('mailings', fn (Builder $query) => $query->where('mailing_id', self::MAILING_ID))
            ->each(function (User $user) {
                $user->notify(new DiscountAfterRegisterSms($user->group->discount));
            }, 200);
    }
}
