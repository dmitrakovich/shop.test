<?php

namespace App\Jobs\Payment;

use App\Models\Payments\OnlinePayment;
use App\Services\Payment\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateQrcodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private OnlinePayment $onlinePayment)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PaymentService $paymentService)
    {
        $paymentService->createOnlinePaymentQrCode($this->onlinePayment);
    }
}
