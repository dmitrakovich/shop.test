<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:dispatch {job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $class = '\\App\\Jobs\\' . ($jobName = $this->argument('job'));

        try {
            $class::dispatchSync();
            $this->components->info("$jobName completed successfully");
        } catch (\Throwable $th) {
            $this->components->error($th->getMessage());
        }
    }
}
