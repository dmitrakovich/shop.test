<?php

namespace App\Console\Commands;

use Spatie\Backup\Commands\BackupCommand;

class BackupMediaCommand extends BackupCommand
{
    protected $signature = 'backup:media {--filename=} {--only-db} {--db-name=*} {--only-files=true} {--only-to-disk=} {--disable-notifications} {--timeout=}';

    protected $description = 'Run the backup media.';

    /**
     * Retrieve the paths for different file backup chunks.
     */
    private function getChunks(): array
    {
        return [
            'uploads' => [
                public_path('uploads'),
            ],
            'banners' => [
                storage_path('app/media/b'),
            ],
            'feedbacks' => [
                storage_path('app/media/feedbacks'),
            ],
            'hgrosh' => [
                storage_path('app/media/hgrosh'),
            ],
            'products-1' => [
                storage_path('app/media/products/1'),
            ],
            'products-2' => [
                storage_path('app/media/products/2'),
            ],
            'products-3' => [
                storage_path('app/media/products/3'),
            ],
            'products-4' => [
                storage_path('app/media/products/4'),
            ],
            'products-5' => [
                storage_path('app/media/products/5'),
            ],
            'products-6' => [
                storage_path('app/media/products/6'),
            ],
            'products-7' => [
                storage_path('app/media/products/7'),
            ],
            'products-8' => [
                storage_path('app/media/products/8'),
            ],
            'products-9' => [
                storage_path('app/media/products/9'),
            ],
            'other' => [
                storage_path('app/media/other'),
            ],
        ];
    }

    public function handle()
    {
        config(['backup.backup.source.files.exclude' => []]);

        foreach ($this->getChunks() as $name => $chunk) {
            config([
                'backup.backup.source.files.include' => $chunk,
                'backup.backup.destination.filename_prefix' => "media-$name-",
            ]);
            parent::handle();
        }
    }
}
