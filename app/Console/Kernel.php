<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the overdue tasks check daily at midnight
        $schedule->command('tasks:check-overdue')->daily();
        
        // For testing, you can use:
        // $schedule->command('tasks:check-overdue')->everyMinute();
        // $schedule->command('tasks:check-overdue')->everyFiveMinutes();
        // $schedule->command('tasks:check-overdue')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}