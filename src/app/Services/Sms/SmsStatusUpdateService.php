<?php

namespace App\Services\Sms;

use App\Enums\Sms\SmsDeliveryChannel;
use App\Enums\Sms\SmsDeliveryStatus;
use App\Models\Logs\SmsLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Client\Response\SmsTrafficStatusCollectionResponse;
use Illuminate\Notifications\Client\Response\SmsTrafficStatusResponse;
use Illuminate\Notifications\Facades\SmsTraffic;
use Illuminate\Support\Carbon;
use RuntimeException;

class SmsStatusUpdateService
{
    private const BATCH_SIZE = 15;

    public function updateStatuses(): int
    {
        $updated = 0;

        SmsLog::query()
            ->pendingDeliveryStatusUpdate()
            ->orderBy('id')
            ->chunkById(self::BATCH_SIZE, function (Collection $logs) use (&$updated): void {
                $updated += $this->updateChunk($logs);
            });

        return $updated;
    }

    /**
     * @param  Collection<int, SmsLog>  $logs
     */
    private function updateChunk(Collection $logs): int
    {
        /** @var list<string> $smsIds */
        $smsIds = $logs->pluck('sms_id')->filter()->values()->all();

        if ($smsIds === []) {
            return 0;
        }

        $response = SmsTraffic::status($smsIds);

        if (!$response instanceof SmsTrafficStatusCollectionResponse) {
            report(new RuntimeException(
                'Unexpected SmsTraffic status response: ' . ($response->getDescription() ?? 'unknown error')
            ));

            return 0;
        }

        $logsBySmsId = $logs->keyBy('sms_id');
        $updated = 0;

        foreach ($response->getStatuses() as $statusResponse) {
            $smsId = $statusResponse->getSmsId();
            if ($smsId === null) {
                continue;
            }

            /** @var SmsLog|null $log */
            $log = $logsBySmsId->get($smsId);
            if ($log === null) {
                continue;
            }

            if ($this->applyStatusResponse($log, $statusResponse)) {
                $updated++;
            }
        }

        return $updated;
    }

    private function applyStatusResponse(SmsLog $log, SmsTrafficStatusResponse $statusResponse): bool
    {
        if ($statusResponse->hasError()) {
            $log->status = $statusResponse->getError();
            if (!$log->isDirty('status')) {
                return false;
            }

            $log->save();

            return true;
        }

        $log->fill([
            'status' => SmsDeliveryStatus::resolve($statusResponse->getStatus()),
            'delivery_channel' => SmsDeliveryChannel::resolve($statusResponse->getChannel()),
            'delivered_at' => $this->parseDateTime(
                $statusResponse->getDeliveryDate() ?? $statusResponse->getLastStatusChangeDate(),
            ),
            'read_at' => $this->parseDateTime($statusResponse->getReadDate()),
        ]);

        if (!$log->isDirty()) {
            return false;
        }

        $log->save();

        return true;
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
