<?php

namespace App\Console;

use App\Jobs\Payment\SendInstallmentNoticeJob;
use App\Jobs\SxGeoUpdateJob;
use App\Jobs\UpdateAvailabilityJob;
use App\Jobs\UpdateProductsRatingJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;

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
        if (!App::environment('production')) {
            return;
        }
        // $schedule->command('inspire')->hourly();

        $schedule->job(new UpdateProductsRatingJob)->withoutOverlapping()->cron('15 5,11,17,23 * * *');
        $schedule->job(new UpdateAvailabilityJob)->withoutOverlapping()->everyThirtyMinutes();
        $schedule->job(new SxGeoUpdateJob)->dailyAt('03:07');
        $schedule->job(new SendInstallmentNoticeJob)->dailyAt('09:05');

        $schedule->command('feed:generate')->everySixHours();

        $schedule->command('backup:run')->dailyAt('01:00');
        $schedule->command('backup:media')->weeklyOn(Schedule::MONDAY, '03:00');
        $schedule->command('backup:clean')->dailyAt('06:00');
        $schedule->command('backup:monitor')->dailyAt('06:30');
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
