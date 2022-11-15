<?php

namespace App\Jobs\Payment;

use App\Jobs\AbstractJob;
use App\Services\Payment\InstallmentService;
use Drandin\DeclensionNouns\Facades\DeclensionNoun;

class SendInstallmentNoticeJob extends AbstractJob
{
    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InstallmentService $installmentService)
    {
        $this->debug('Отправка уведомлений о рассрочке');

        $count = $installmentService->sendNotifications();

        $this->debug('Отправлено '.DeclensionNoun::make($count, 'уведомление'));
    }
}
