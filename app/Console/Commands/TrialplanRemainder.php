<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\ScheduleController;

class TrialplanRemainder extends Command
{
    protected $signature = 'app:trial-plan-remainder';
    protected $description = 'Trial Plan Remainder 3,5,7th days';
    public function handle()
    {
        app(ScheduleController::class)->index();
        return;
    }
}