<?php

namespace App\Providers;

use App\Libraries\Belpost\Api;
use App\Libraries\Belpost\BelpostGeoDirectory;
use App\Libraries\Belpost\HttpClient;
use App\Services\Belpost\BatchMailing\BelpostBatchDocumentService;
use App\Services\Belpost\BatchMailing\BelpostBatchItemService;
use App\Services\Belpost\BatchMailing\BelpostBatchListService;
use App\Services\Belpost\Geo\BelpostGeoDirectoryService;
use App\Services\Belpost\Geo\BelpostRecipientAddressResolver;
use App\Services\Belpost\Mappers\BelpostBatchMapper;
use App\Services\Belpost\Mappers\BelpostOrderItemMapper;
use App\Services\Belpost\Mappers\BelpostRecipientMapper;
use App\Services\Belpost\Support\BelpostBatchGuards;
use App\Services\Belpost\Support\BelpostPhoneNormalizer;
use App\Services\Belpost\Sync\BelpostBatchSyncService;
use App\Services\Belpost\Sync\BelpostOrderItemSyncService;
use Illuminate\Support\ServiceProvider;

class BelpostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HttpClient::class, function () {
            return new HttpClient(
                (string)config('belpost.base_url'),
                config('belpost.token'),
            );
        });

        $this->app->singleton(Api::class, function ($app) {
            return new Api($app->make(HttpClient::class));
        });

        $this->app->singleton(BelpostGeoDirectory::class);

        $this->app->singleton(BelpostPhoneNormalizer::class);
        $this->app->singleton(BelpostBatchGuards::class);
        $this->app->singleton(BelpostBatchMapper::class);
        $this->app->singleton(BelpostOrderItemSyncService::class);
        $this->app->singleton(BelpostBatchSyncService::class);

        $this->app->singleton(BelpostGeoDirectoryService::class);
        $this->app->singleton(BelpostRecipientAddressResolver::class);
        $this->app->singleton(BelpostRecipientMapper::class);
        $this->app->singleton(BelpostOrderItemMapper::class);

        $this->app->singleton(BelpostBatchListService::class);
        $this->app->singleton(BelpostBatchItemService::class);
        $this->app->singleton(BelpostBatchDocumentService::class);
    }
}
