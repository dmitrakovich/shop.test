<?php

namespace App\Jobs;

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
     * Имя выполняемой задачи
     */
    protected $jobName = null;

    /**
     * Переменные, которые нужно отразить в context
     *
     * @var array
     */
    protected $contextVars = [];

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $errorMsg = <<<MSG
            Job: {$this->getName()};
            Error: {$exception->getMessage()};
            Line: {$exception->getLine()};
            File: {$exception->getFile()};
            MSG;
        // Trace: {$exception->getTraceAsString()};
        $this->error("{$exception->getMessage()} [{$this->getName()}]");
    }

    /**
     * Получить имя текущей задачи
     */
    protected function getName(): string
    {
        return $this->jobName ?? static::class;
    }

    /**
     * Запись отадочных сообщений в логd
     */
    protected function log(string $msg, string $level = 'info', string $channel = 'jobs'): void
    {
        $msg = "$msg [{$this->getName()}]";
        $context = [];
        foreach ($this->contextVars as $var) {
            switch ($var) {
                case 'usedMemory':
                    $context['usedMemory'] = $this->getFormatedUsedMemory();
                    break;

                default:
                    $context[$var] = $this->$var ?? null;
                    break;
            }
        }
        Log::channel($channel)->log($level, $msg, $context);
    }

    /**
     * Запись сообщения об ошибке в лог
     */
    protected function error(string $msg): void
    {
        $this->log($msg, 'error');
    }

    /**
     * Получить форматированное кол-во использованной памяти
     */
    protected function getFormatedUsedMemory(): string
    {
        $base = log(memory_get_usage(), 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }
}
