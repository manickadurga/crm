<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendDueDateReminders::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        Log::info('Kernel schedule method called at ' . now());
        $schedule->command('invoices:remind-due-date')->everyMinute();
        
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
