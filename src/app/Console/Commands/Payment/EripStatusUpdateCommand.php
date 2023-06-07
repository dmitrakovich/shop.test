<?php

namespace App\Console\Commands\Payment;

use App\Services\Payment\Methods\PaymentEripService;
use Illuminate\Console\Command;

class EripStatusUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erip:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(PaymentEripService $paymentEripService)
    {
        $paymentEripService->updateStatuses();
    }
}
