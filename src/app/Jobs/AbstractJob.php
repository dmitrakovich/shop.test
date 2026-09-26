<?php

namespace App\Jobs;

use App\Enums\LogCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->error('Ошибка выполнения', ['error' => $exception->getMessage()]);
    }

    protected function logCategory(): LogCategory
    {
        return LogCategory::Jobs;
    }

    /**
     * Attributes attached to every log of this job.
     *
     * @return array<string, scalar|null>
     */
    protected function logContext(): array
    {
        return [];
    }

    /**
     * Keep the message constant and pass changing values in `$context`, so Explore can group by message.
     *
     * @param  array<string, scalar|null>  $context
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        Log::channel($this->logCategory()->value)->log($level, $message, [
            'job' => class_basename(static::class),
            'memory_mb' => round(memory_get_usage() / 1024 / 1024, 1),
            ...$this->logContext(),
            ...$context,
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $context
     */
    protected function error(string $message, array $context = []): void
    {
        $this->log($message, $context, 'error');
    }
}
