<?php

namespace App\Console\Commands;

use Spatie\Backup\Commands\BackupCommand;

class BackupMediaCommand extends BackupCommand
{
    protected $signature = 'backup:media {--filename=} {--only-db} {--db-name=*} {--only-files=true} {--only-to-disk=} {--disable-notifications} {--timeout=}';

    protected $description = 'Run the backup media.';

    public function handle(): int
    {
        config(['backup.backup.source.files.exclude' => []]);

        foreach ($this->getChunks() as $name => $chunk) {
            config([
                'backup.backup.source.files.include' => $chunk,
                'backup.backup.destination.filename_prefix' => "media-$name-",
            ]);
            parent::handle();
        }

        return static::SUCCESS;
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
            'products-1_0-4' => [
                storage_path('app/media/products/1/10'),
                storage_path('app/media/products/1/11'),
                storage_path('app/media/products/1/12'),
                storage_path('app/media/products/1/13'),
                storage_path('app/media/products/1/14'),
            ],
            'products-1_5-9' => [
                storage_path('app/media/products/1/15'),
                storage_path('app/media/products/1/16'),
                storage_path('app/media/products/1/17'),
                storage_path('app/media/products/1/18'),
                storage_path('app/media/products/1/19'),
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
            'products-4_0-4' => [
                storage_path('app/media/products/4/40'),
                storage_path('app/media/products/4/41'),
                storage_path('app/media/products/4/42'),
                storage_path('app/media/products/4/43'),
                storage_path('app/media/products/4/44'),
            ],
            'products-4_5-9' => [
                storage_path('app/media/products/4/45'),
                storage_path('app/media/products/4/46'),
                storage_path('app/media/products/4/47'),
                storage_path('app/media/products/4/48'),
                storage_path('app/media/products/4/49'),
            ],
            'products-5_0-4' => [
                storage_path('app/media/products/5/50'),
                storage_path('app/media/products/5/51'),
                storage_path('app/media/products/5/52'),
                storage_path('app/media/products/5/53'),
                storage_path('app/media/products/5/54'),
            ],
            'products-5_5-9' => [
                storage_path('app/media/products/5/55'),
                storage_path('app/media/products/5/56'),
                storage_path('app/media/products/5/57'),
                storage_path('app/media/products/5/58'),
                storage_path('app/media/products/5/59'),
            ],
            'products-6_0-4' => [
                storage_path('app/media/products/6/60'),
                storage_path('app/media/products/6/61'),
                storage_path('app/media/products/6/62'),
                storage_path('app/media/products/6/63'),
                storage_path('app/media/products/6/64'),
            ],
            'products-6_5-9' => [
                storage_path('app/media/products/6/65'),
                storage_path('app/media/products/6/66'),
                storage_path('app/media/products/6/67'),
                storage_path('app/media/products/6/68'),
                storage_path('app/media/products/6/69'),
            ],
            'products-7_0-4' => [
                storage_path('app/media/products/7/70'),
                storage_path('app/media/products/7/71'),
                storage_path('app/media/products/7/72'),
                storage_path('app/media/products/7/73'),
                storage_path('app/media/products/7/74'),
            ],
            'products-7_5-9' => [
                storage_path('app/media/products/7/75'),
                storage_path('app/media/products/7/76'),
                storage_path('app/media/products/7/77'),
                storage_path('app/media/products/7/78'),
                storage_path('app/media/products/7/79'),
            ],
            'products-8_0-4' => [
                storage_path('app/media/products/8/80'),
                storage_path('app/media/products/8/81'),
                storage_path('app/media/products/8/82'),
                storage_path('app/media/products/8/83'),
                storage_path('app/media/products/8/84'),
            ],
            'products-8_5-9' => [
                storage_path('app/media/products/8/85'),
                storage_path('app/media/products/8/86'),
                storage_path('app/media/products/8/87'),
                storage_path('app/media/products/8/88'),
                storage_path('app/media/products/8/89'),
            ],
            'products-9_0-4' => [
                storage_path('app/media/products/9/90'),
                storage_path('app/media/products/9/91'),
                storage_path('app/media/products/9/92'),
                storage_path('app/media/products/9/93'),
                storage_path('app/media/products/9/94'),
            ],
            'products-9_5-9' => [
                storage_path('app/media/products/9/95'),
                storage_path('app/media/products/9/96'),
                storage_path('app/media/products/9/97'),
                storage_path('app/media/products/9/98'),
                storage_path('app/media/products/9/99'),
            ],
            'other' => [
                storage_path('app/media/other'),
            ],
        ];
    }
}
