<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Xml\AbstractXml;
use Illuminate\Support\Facades\Log;

class XmlService
{
    /**
     * @var AbstractXml
     */
    private $xmlInstance;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    protected $filePath;

    public function __construct(AbstractXml $xmlInstance, Currency $currency)
    {
        $this->xmlInstance = $xmlInstance;
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
                . $this->xmlInstance->getPartFilename()
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
        if (!file_exists($this->filePath)) {
            return;
        }

        $backupFilePath = $this->getFilePath('', '.backup');
        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        }

        copy($this->filePath, $backupFilePath);
    }

    /**
     * Generate xml file
     *
     * @return void
     */
    public function generate(): void
    {
        Log::channel('xml')->info('Start generate', [basename($this->filePath)]);

        # code...

        Log::channel('xml')->info('Finish generate', [basename($this->filePath)]);
    }
}
