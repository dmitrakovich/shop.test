<?php

namespace App\Services\Sms;

use App\Models\Logs\SmsLog;
use Illuminate\Notifications\Facades\SmsTraffic;

class SmsStatusUpdateService
{
    private const BATCH_SIZE = 15;

    public function updateStatuses(): int
    {
        $updated = 0;

        $sms = SmsLog::query()
            ->latest(['id'])
            ->first();

        dd(SmsTraffic::status($sms->sms_id));

        return $updated;
    }
}
