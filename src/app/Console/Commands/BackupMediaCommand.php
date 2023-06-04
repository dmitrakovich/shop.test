<?php

namespace App\Console\Commands;

use Spatie\Backup\Commands\BackupCommand;

class BackupMediaCommand extends BackupCommand
{
    protected $signature = 'backup:media {--filename=} {--only-db} {--db-name=*} {--only-files=true} {--only-to-disk=} {--disable-notifications} {--timeout=}';

    protected $description = 'Run the backup media.';

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
            'products-2_0-4' => [
                storage_path('app/media/products/2/20'),
                storage_path('app/media/products/2/21'),
                storage_path('app/media/products/2/22'),
                storage_path('app/media/products/2/23'),
                storage_path('app/media/products/2/24'),
            ],
            'products-2_5-9' => [
                storage_path('app/media/products/2/25'),
                storage_path('app/media/products/2/26'),
                storage_path('app/media/products/2/27'),
                storage_path('app/media/products/2/28'),
                storage_path('app/media/products/2/29'),
            ],
            'products-3_0-4' => [
                storage_path('app/media/products/3/30'),
                storage_path('app/media/products/3/31'),
                storage_path('app/media/products/3/32'),
                storage_path('app/media/products/3/33'),
                storage_path('app/media/products/3/34'),
            ],
            'products-3_5-9' => [
                storage_path('app/media/products/3/35'),
                storage_path('app/media/products/3/36'),
                storage_path('app/media/products/3/37'),
                storage_path('app/media/products/3/38'),
                storage_path('app/media/products/3/39'),
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
}
