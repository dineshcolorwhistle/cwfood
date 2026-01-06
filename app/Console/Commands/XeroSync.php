<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\XeroController;

class XeroSync extends Command
{
    protected $signature = 'app:xero-syncing';
    protected $description = 'Xero Sync contact,incoice and notes';
    public function handle()
    {
        app(XeroController::class)->syncSchedule();
        return;
    }
}
