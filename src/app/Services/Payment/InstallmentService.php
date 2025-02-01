<?php

namespace App\Services\Payment;

use App\Models\Payments\Installment;
use App\Notifications\InstallmentPaymentSms;
use Illuminate\Database\Eloquent\Builder;

class InstallmentService
{
    /**
     * How many days before the installment payment to send a notification
     */
    const DAYS_BEFORE_NOTICE = 5;

    /**
     * InstallmentService constructor.
     */
    public function __construct(private Installment $installment) {}

    /**
     * Send notifications to buyers and return their quantity
     */
    public function sendNotifications(): int
    {
        $notificationsCount = 0;
        $this->installment->query()
            ->where('send_notifications', true)
            ->where(function (Builder $query) {
                $query->where('notice_sent_at', '<', now()->subDays(27))
                    ->orWhereNull('notice_sent_at');
            })
            ->with(['order'])
            ->chunk(200, function ($installments) use (&$notificationsCount) {
                /** @var Installment $installment */
                foreach ($installments as $installment) {
                    $noticeDate = $installment->getNextPaymentDate()->copy()->subDays(self::DAYS_BEFORE_NOTICE);
                    if ($noticeDate->isFuture()) {
                        continue;
                    }
                    $installment->order->notify(
                        new InstallmentPaymentSms($installment)
                    );
                    $installment->touch('notice_sent_at');
                    $notificationsCount++;
                }
            });

        return $notificationsCount;
    }
}
