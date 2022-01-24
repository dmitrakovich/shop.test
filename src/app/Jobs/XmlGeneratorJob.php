<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\Xml\AbstractXml;
use App\Services\XmlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class XmlGeneratorJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var AbstractXml
     */
    private $xmlInstance;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AbstractXml $xmlInstance, Currency $currency)
    {
        $this->xmlInstance = $xmlInstance;
        $this->currency = $currency;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $xmlService = new XmlService($this->xmlInstance, $this->currency);
        $xmlService->backup();
        $xmlService->generate();
    }
}
