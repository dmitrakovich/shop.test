<?php

namespace App\Services\Feeds;

use App\Contracts\FeedServiceInterface;
use App\Enums\LogCategory;
use App\Metrics\ApplicationMetrics;
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

        CurrencyFacade::setCurrentCurrency($this->currency->code);
    }

    public function generate(): void
    {
        $startedAt = microtime(true);
        memory_reset_peak_usage();

        $this->write();

        // backup() stats this path via file_exists(); drop PHP's stat cache so filesize() sees the new file.
        clearstatcache(true, $this->filePath);

        $sizeBytes = (int)filesize($this->filePath);
        $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
        $memoryPeakMb = round(memory_get_peak_usage() / 1024 / 1024, 1);

        Log::channel(LogCategory::Feeds->value)->info('Фид сгенерирован', [
            'feed' => $this->feedInstance->getKey(),
            'format' => $this->feedInstance::FILE_TYPE,
            'currency' => $this->currency->code,
            'size_bytes' => $sizeBytes,
            'duration_ms' => $durationMs,
            'memory_peak_mb' => $memoryPeakMb,
        ]);

        app(ApplicationMetrics::class)->feedGenerated(
            (string)$this->feedInstance->getKey(),
            $this->feedInstance::FILE_TYPE,
            $this->currency->code,
            $sizeBytes,
            $durationMs,
            $memoryPeakMb,
        );
    }

    /**
     * Write the feed to `$this->filePath`.
     */
    abstract protected function write(): void;

    /**
     * Return feed file path by instance & currency
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
}
