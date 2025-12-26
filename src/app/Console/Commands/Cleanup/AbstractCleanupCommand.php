<?php

namespace App\Console\Commands\Cleanup;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class AbstractCleanupCommand extends Command
{
    /**
     * @return Builder<Model>
     */
    abstract protected function query(): Builder;

    protected function logText(): string
    {
        return class_basename(static::class) . ': удалено %s записей.';
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $resultMessage = sprintf($this->logText(), $this->query()->delete());

        Log::channel('jobs')->info($resultMessage);
        $this->info($resultMessage);
    }
}
