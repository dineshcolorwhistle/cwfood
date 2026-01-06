<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('app:check-ticket-status')->dailyAt('08:00')->description('Check ticket status command');
        $schedule->command('app:xero-syncing')->dailyAt('01:00')->description('Xero syncing contacts,incoice and credit notes');  
        $schedule->command('app:pending-ticket-status-update')->dailyAt('07:00')->description('Pending ticket status update');
        $schedule->command('app:trial-plan-remainder')->dailyAt('08:00')->description('Trial Plan Remainder 3,5,7th days');  
        $schedule->command('app:cognito-token-refresh')->everyFiveMinutes()->description('Cognito access token refresh before expired.');
    }
    
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
