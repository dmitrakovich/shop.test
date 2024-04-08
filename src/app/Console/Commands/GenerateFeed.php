<?php

namespace App\Console\Commands;

use App\Jobs\FeedGeneratorJob;
use App\Models\Currency;
use App\Models\Feeds\GoogleCsv;
use App\Models\Feeds\GoogleXml;
use App\Models\Feeds\YandexXml;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;

class GenerateFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:generate
                            {instance? : Feed instance name}
                            {currency? : Currency for feed}';

    /**
     * @var array
     */
    final const INSTANCES = [
        'yandex_xml' => YandexXml::class,
        'google_xml' => GoogleXml::class,
        'google_csv' => GoogleCsv::class,
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Get all instances or instance from arguments
     */
    protected function getInstances(): array
    {
        $instances = self::INSTANCES;

        if (!empty($this->argument('instance'))) {
            $instance = strtolower($this->argument('instance'));
            $instances = Arr::only($instances, $instance);

            if (empty($instances)) {
                throw new \Exception('Unknown instance');
            }
        }

        return $instances;
    }

    /**
     * Get all currencies or currency from argument
     *
     * @return EloquentCollection<Currency>
     */
    protected function getCurrencies()
    {
        $allCurrencies = Currency::all(['code', 'country', 'rate', 'decimals', 'symbol'])->keyBy('code');

        if (!empty($this->argument('currency'))) {
            $currency = strtoupper($this->argument('currency'));
            $allCurrencies = $allCurrencies->only($currency);

            if ($allCurrencies->isEmpty()) {
                throw new \Exception('Unknown currency');
            }
        }

        return $allCurrencies;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ($this->getInstances() as $instance) {
            foreach ($this->getCurrencies() as $currency) {
                dispatch(new FeedGeneratorJob(new $instance, $currency));
            }
        }

        $this->info('Tasks created');

        return 0;
    }
}
