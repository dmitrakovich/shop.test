<?php

namespace App\Console\Commands;

use Spatie\Backup\Commands\BackupCommand;

class BackupMediaCommand extends BackupCommand
{
    protected $signature = 'backup:media {--filename=} {--only-db} {--db-name=*} {--only-files=true} {--only-to-disk=} {--disable-notifications} {--timeout=}';

    protected $description = 'Run the backup media.';

    public function handle()
    {
        config([
            'backup.backup.source.files.include' => [storage_path('app/media')],
            'backup.backup.source.files.exclude' => [],
        ]);

        parent::handle();
    }
}
