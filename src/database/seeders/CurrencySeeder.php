<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\TruncatesTables;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    use TruncatesTables;

    protected $tableName = 'currencies';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTable($this->tableName);

        DB::table($this->tableName)->insert([
            [
                'code' => 'BYN',
                'country' => 'BY',
                'rate' => 1.00,
                'decimals' => 2,
                'symbol' => 'byn',
            ],
            [
                'code' => 'KZT',
                'country' => 'KZ',
                'rate' => 168.71,
                'decimals' => 0,
                'symbol' => '₸',
            ],
            [
                'code' => 'RUB',
                'country' => 'RU',
                'rate' => 28.54,
                'decimals' => 0,
                'symbol' => '₽',
            ],
            [
                'code' => 'USD',
                'country' => 'US',
                'rate' => 0.40,
                'decimals' => 0,
                'symbol' => '$',
            ],
        ]);

        Cache::forget('currencies');
    }
}
