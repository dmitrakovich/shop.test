<?php

namespace Spurit\Core\Console\Commands;

use Illuminate\Console\Command;

class OpcacheReset extends Command
{
    /**
     * @var string
     */
    protected $signature = 'opcache:reset';

    /**
     * @var string
     */
    protected $description = 'Make get-request to /opcache-reset route for clear that cache';

    /**
     *
     */
    public function handle(): void
    {
        exec('curl -k ' . route('opcache.reset'), $out, $result);
        if ($result === 0) {
            $this->info('Opcache was successfully reseted');
        } else {
            $this->error('Opcache was NOT RESETED!');
        }
    }
}
