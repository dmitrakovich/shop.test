<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\Xml\AbstractXml;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class XmlGeneratorJob implements ShouldQueue
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
        //
    }
}
