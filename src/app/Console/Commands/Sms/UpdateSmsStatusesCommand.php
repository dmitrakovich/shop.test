<?php

namespace App\Console\Commands\Sms;

use App\Services\Sms\SmsStatusUpdateService;
use Illuminate\Console\Command;

class UpdateSmsStatusesCommand extends Command
{
    protected $signature = 'sms:update-statuses';

    protected $description = 'Update SMS delivery statuses from SmsTraffic';

    public function handle(SmsStatusUpdateService $smsStatusUpdateService): int
    {
        $updated = $smsStatusUpdateService->updateStatuses();

        $this->info("Updated {$updated} SMS statuses.");

        return self::SUCCESS;
    }
}
