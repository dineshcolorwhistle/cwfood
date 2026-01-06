<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\AdminSupportController;

class SupportStatus extends Command
{
    protected $signature = 'app:pending-ticket-status-update';
    protected $description = 'Pending ticket status update';
    public function handle()
    {
        app(AdminSupportController::class)->PendingTicketUpdate();
        return;
    }
}
