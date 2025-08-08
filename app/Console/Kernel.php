<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DataByMinute;
use App\Console\Commands\DataByHour;
use App\Console\Commands\DataByDay;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\DataByMinute::class,
        \App\Console\Commands\DataByHour::class,
        \App\Console\Commands\DataByDay::class,
        \App\Console\Commands\OvernightStay::class,
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->everySecond();
        $schedule->command('databyminute:cron')->everyMinute();
        $schedule->command('databyhour:cron')->hourly();
        $schedule->command('databyday:cron')->daily();
        $schedule->command('overnightstay:cron')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
