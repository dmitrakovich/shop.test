<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dd($this->arguments());
        return 0;
    }
}
