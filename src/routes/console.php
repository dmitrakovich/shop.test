<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new \App\Jobs\SxGeoUpdateJob())->dailyAt('03:07');
Schedule::job(new \App\Jobs\Mailing\DiscountAfterRegisterJob())->dailyAt('09:00');
Schedule::job(new \App\Jobs\Payment\SendInstallmentNoticeJob())->dailyAt('09:05');
Schedule::job(new \App\Jobs\Mailing\LeaveFeedbackAfterOrderJob())->dailyAt('09:15');
Schedule::job(new \App\Jobs\Mailing\SendingTracksJob())->dailyAt('10:15');
Schedule::job(new \App\Jobs\OneC\UpdateOfflineOrdersJob())->withoutOverlapping()->everyTenMinutes();

Schedule::command('rating:update')->withoutOverlapping()->cron('15 5,11,17,23 * * *');
Schedule::command('inventory:update')->withoutOverlapping()->everyFiveMinutes()->sentryMonitor();

Schedule::command('sanctum:prune-expired')->dailyAt('00:10');
Schedule::command('backup:run')->dailyAt('01:00');
Schedule::command('backup:clean')->dailyAt('06:00');
Schedule::command('backup:monitor')->dailyAt('06:30');

Schedule::command('cleanup:devices')->dailyAt('04:20');
Schedule::command('cleanup:defective-products')->weeklyOn(Carbon::SATURDAY, '04:30');

Schedule::command('feed:generate')->everySixHours();
Schedule::command('generate:sitemap')->dailyAt('00:30');

Schedule::command('erip:update-statuses')->everyTenMinutes();
Schedule::command('belpost:cod-parse-from-email')->hourly()->between('8:00', '18:00');
