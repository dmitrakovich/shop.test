<?php

namespace App\Console\Commands\Payment;

use App\Models\Config;

use App\Services\Payment\BelpostCODService;
use Illuminate\Console\Command;

class BelpostCODParseFromEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'belpost:cod-parse-from-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(BelpostCODService $belpostCODService)
    {
        if((Config::findCacheable('auto_order_statuses')['belpost_parse_email'] ?? false)) {
            $belpostCODService->parseEmail();
        }
    }
}
