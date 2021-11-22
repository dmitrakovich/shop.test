<?php

namespace App\Jobs;

use Throwable;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
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
        $this->complete("{$exception->getMessage()} [{$this->getName()}]", 'jobs', 'error');
        // Telegram::sendMessage([
        //     'chat_id' => config('telegram.chat_id_for_errors'),
        //     'text' => $errorMsg
        // ]);
    }
    /**
     * Получить имя текущей задачи
     *
     * @return string
     */
    protected function getName(): string
    {
        return $this->jobName ?? static::class;
    }
    /**
     * Запись отадочных сообщений в лог
     *
     * @param string $msg сообщение
     * @param string $channel канал для логов
     * @param string $type тип сообщения
     * @return void
     */
    protected function debug(string $msg, string $channel = 'jobs', string $type = 'info'): void
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
        Log::channel($channel)->$type($msg, $context);
    }
    /**
     * Запись сообщения об ошибке в лог
     *
     * @param string $msg сообщение
     * @return void
     */
    protected function error(string $msg): void
    {
        $this->debug($msg, 'jobs', 'error');
    }
    /**
     * Запись сообщения об окончании работы
     *
     * @param string $msg сообщение
     * @return void
     */
    protected function complete(string $msg, string $channel = 'jobs', string $type = 'info'): void
    {
        $this->debug($msg, $channel, $type);
        // Log::channel($channel)->info(str_repeat('-', 40));
    }
    /**
     * Получить форматированное кол-во использованной памяти
     *
     * @return string
     */
    protected function getFormatedUsedMemory(): string
    {
        $base = log(memory_get_usage(), 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }
}
