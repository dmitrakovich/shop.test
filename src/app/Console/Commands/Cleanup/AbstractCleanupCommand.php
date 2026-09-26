<?php

namespace App\Console\Commands\Cleanup;

use App\Enums\LogCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class AbstractCleanupCommand extends Command
{
    /**
     * @return Builder<covariant Model>
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
        $resultMessage = sprintf($this->logText(), $this->query()->forceDelete());

        Log::channel(LogCategory::Jobs->value)->info($resultMessage);
        $this->info($resultMessage);
    }
}
