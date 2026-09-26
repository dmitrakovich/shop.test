<?php

namespace App\Jobs\Payment;

use App\Jobs\AbstractJob;
use App\Services\Payment\InstallmentService;

class SendInstallmentNoticeJob extends AbstractJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InstallmentService $installmentService)
    {
        $this->log('Старт');

        $count = $installmentService->sendNotifications();

        $this->log('Успешно выполнено', ['count' => $count]);
    }
}
