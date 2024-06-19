<?php

namespace App\Jobs;

use App\Contracts\FeedServiceInterface;
use App\Models\Currency;
use App\Models\Feeds\AbstractFeed;
use App\Services\Feeds\CsvService;
use App\Services\Feeds\XmlService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FeedGeneratorJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private AbstractFeed $feedInstance,
        private Currency $currency
    ) {}

    /**
     * Return feed service
     */
    protected function getFeedService(): FeedServiceInterface
    {
        return match ($this->feedInstance::FILE_TYPE) {
            'xml' => new XmlService($this->feedInstance, $this->currency),
            'csv' => new CsvService($this->feedInstance, $this->currency),
            default => throw new \Exception('Unknown needed feed file type'),
        };
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
