<?php

namespace App\Console;

use App\Jobs\SxGeoUpdateJob;
use App\Jobs\UpdateAvailabilityJob;
use App\Jobs\UpdateProductsRatingJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // обновление рейтинга товаров
        $schedule->job(new UpdateProductsRatingJob)
            ->withoutOverlapping()
            ->cron('15 5,11,17,23 * * *');

        $schedule->job(new UpdateAvailabilityJob)
            ->withoutOverlapping()
            ->everyThirtyMinutes();

        // обновление базы Sypex Geo
        $schedule->job(new SxGeoUpdateJob)->dailyAt('03:07');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
