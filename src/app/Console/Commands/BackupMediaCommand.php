<?php

namespace App\Console\Commands;


use Exception;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Exceptions\InvalidCommand;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupMediaCommand extends BackupCommand
{
    protected $signature = 'backup:media';

    protected $description = 'Run the backup media.';

    public function handle()
    {
        consoleOutput()->comment('Starting media backup...');
    }
}
