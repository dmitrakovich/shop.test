<?php

namespace App\Jobs\OneC;

use App\Jobs\AbstractJob;
use App\Models\OneC\OfflineOrder as OfflineOrder1C;
use App\Models\Orders\OfflineOrder;

class UpdateOfflineOrdersJob extends AbstractJob
{
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 500;

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        dd($this->getLatestCode());

        // получить последний
        // по нему получить все новые
        // найти все зависимости
        // создать пользователя
        // создать дисконтную карту
        //
    }

    private function getLatestCode(): int
    {
        $receiptNumber = OfflineOrder::query()->latest('id')->value('receipt_number');

        return OfflineOrder1C::getLatestCodeByReceipNumber($receiptNumber);
    }
}
