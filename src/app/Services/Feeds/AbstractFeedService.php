<?php

namespace App\Services\Feeds;

use App\Contracts\FeedServiceInterface;
use App\Facades\Currency as CurrencyFacade;
use App\Models\Currency;
use App\Models\Feeds\AbstractFeed;
use Illuminate\Support\Facades\Log;

abstract class AbstractFeedService implements FeedServiceInterface
{
    /**
     * @var string
     */
    const FEEDS_DIR = 'xml';

    /**
     * @var AbstractFeed
     */
    protected $feedInstance;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var string
     */
    protected $filePath;

    public function __construct(AbstractFeed $feedInstance, Currency $currency)
    {
        ini_set('memory_limit', '512M');

        $this->feedInstance = $feedInstance;
        $this->currency = $currency;
        $this->filePath = $this->getFilePath();

        Log::channel('feeds')->info('Start generate', [basename($this->filePath)]);

        CurrencyFacade::setCurrentCurrency($this->currency->code);
    }

    /**
     * Return feed file path by instance & currency
     *
     * @param  string  $prefix
     * @param  string  $postfix
     * @return string
     */
    protected function getFilePath(string $prefix = '', string $postfix = ''): string
    {
        return storage_path('app') . DIRECTORY_SEPARATOR
            . self::FEEDS_DIR . DIRECTORY_SEPARATOR
            . $prefix . $this->feedInstance->getKey() . '_'
            . strtolower($this->currency->code) . $postfix
            . '.' . $this->feedInstance::FILE_TYPE;
    }

    /**
     * Backup feed file
     *
     * @return void
     */
    public function backup(): void
    {
        if (!file_exists($this->filePath)) {
            return;
        }

        $backupFilePath = $this->getFilePath('', '.backup');
        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        }

        copy($this->filePath, $backupFilePath);
    }

    public function __destruct()
    {
        Log::channel('feeds')->info('Finish generate', [basename($this->filePath)]);
    }
}
