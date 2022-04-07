<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Feeds\AbstractFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Contracts\FeedServiceInterface;
use App\Facades\Currency as CurrencyFacade;

class CsvService implements FeedServiceInterface
{
    /**
     * @var AbstractFeed
     */
    private $csvInstance;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    protected $filePath;

    public function __construct(AbstractFeed $csvInstance, Currency $currency)
    {
        ini_set('memory_limit', '512M');

        $this->csvInstance = $csvInstance;
        $this->currency = $currency;
        $this->filePath = $this->getFilePath();
    }

    /**
     * Return xml file path by instance & currency
     *
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    protected function getFilePath(string $prefix = '', string $postfix = ''): string
    {
        return storage_path('app')
                . DIRECTORY_SEPARATOR . 'xml'
                . DIRECTORY_SEPARATOR . $prefix
                . $this->xmlInstance->getKey() . '_'
                . strtolower($this->currency->code)
                . $postfix . '.xml';
    }

    /**
     * Backup xml file
     *
     * @return void
     */
    public function backup(): void
    {
        return;
        // if (!file_exists($this->filePath)) {
        //     return;
        // }

        // $backupFilePath = $this->getFilePath('', '.backup');
        // if (file_exists($backupFilePath)) {
        //     unlink($backupFilePath);
        // }

        // copy($this->filePath, $backupFilePath);
    }

    /**
     * Generate xml file
     *
     * @return void
     */
    public function generate(): void
    {
        return;
        // Log::channel('feeds')->info('Start generate', [basename($this->filePath)]);

        // CurrencyFacade::setCurrentCurrency($this->currency->code);

        // $data = view('xml.' . $this->xmlInstance->getKey(), [
        //     'currency' => $this->currency,
        //     'data' => $this->xmlInstance->getPreparedData()
        // ]);

        // $this->saveToFile($data);

        // Log::channel('feeds')->info('Finish generate', [basename($this->filePath)]);
    }

    /**
     * Save xml data to file
     *
     * @param View $data
     * @return void
     */
    protected function saveToFile(View $data): void
    {
        file_put_contents($this->filePath, $data->render(), LOCK_EX);
    }
}
