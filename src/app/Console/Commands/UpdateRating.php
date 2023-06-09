<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductsRatingJob;
use Illuminate\Console\Command;

class UpdateRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rating:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products rating';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        UpdateProductsRatingJob::dispatchSync();
    }
}
