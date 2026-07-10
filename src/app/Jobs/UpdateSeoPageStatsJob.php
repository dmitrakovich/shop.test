<?php

namespace App\Jobs;

use App\Services\Seo\SeoPageStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

use function Sentry\captureException;

class UpdateSeoPageStatsJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SeoPageStatsService $service): void
    {
        $this->log('Старт');

        try {
            $count = $service->syncStats();

            if ($count < 1) {
                throw new RuntimeException('Не обновлено ни одной SEO-страницы');
            }

            $this->log("{$count} страниц");
            $this->log('Успешно выполнено');
        } catch (\Throwable $exception) {
            captureException($exception);
            $this->error($exception->getMessage());
        }
    }
}
