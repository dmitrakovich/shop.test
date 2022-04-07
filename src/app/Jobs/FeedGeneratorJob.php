<?php

namespace App\Jobs;

use App\Models\Currency;
use Illuminate\Bus\Queueable;
use App\Models\Feeds\AbstractFeed;
use App\Services\Feeds\CsvService;
use App\Services\Feeds\XmlService;
use Illuminate\Queue\SerializesModels;
use App\Contracts\FeedServiceInterface;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class FeedGeneratorJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var AbstractFeed
     */
    private $feedInstance;

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
    public function __construct(AbstractFeed $feedInstance, Currency $currency)
    {
        $this->feedInstance = $feedInstance;
        $this->currency = $currency;
    }

    /**
     * Return feed service
     *
     * @return FeedServiceInterface
     */
    protected function getFeedService(): FeedServiceInterface
    {
        switch ($this->feedInstance::FILE_TYPE) {
            case 'xml':
                return new XmlService($this->feedInstance, $this->currency);

            case 'csv':
                return new CsvService($this->feedInstance, $this->currency);

            default:
                throw new \Exception('Unknown needed feed file type');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $xmlService = $this->getFeedService();
        $xmlService->backup();
        $xmlService->generate();
    }
}
