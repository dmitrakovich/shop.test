<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Xml\AbstractXml;

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

    public function __construct(AbstractXml $xmlInstance, Currency $currency)
    {
        $this->xmlInstance = $xmlInstance;
        $this->currency = $currency;
    }

    /**
     * Backup xml file
     *
     * @return void
     */
    public function backup(): void
    {
        # code..
    }

    /**
     * Generate xml file
     *
     * @return void
     */
    public function generate(): void
    {
        # code...
    }
}
