<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Support\Arr;
use App\Jobs\XmlGeneratorJob;
use App\Models\Xml\GoogleXml;
use App\Models\Xml\YandexXml;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GenerateXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:generate
                            {instance? : Xml instance name}
                            {currency? : Currency for xml}';

    /**
     * @var array
     */
    const INSTANCES = [
        'yandex' => YandexXml::class,
        'google' => GoogleXml::class,
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all instances or instance from arguments
     *
     * @return array
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
     * @return EloquentCollection
     */
    protected function getCurrencies(): EloquentCollection
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
                dispatch(new XmlGeneratorJob(new $instance, $currency));
            }
        }

        return 0;
    }
}
