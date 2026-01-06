<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\support_ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class CheckTicketStatus extends Command
{
    protected $signature = 'app:check-ticket-status';
    protected $description = 'Check ticket status command';
    public function handle()
    {
        $tickets = support_ticket::with(['RequesterDetails'])->where('status', 'Waiting for customer')->whereNotNull('requester')->where('updated_at', '<', now()->subDays(2))->get();
        foreach ($tickets as $key => $ticket) {
            if (empty($ticket->requesterDetails) || empty($ticket->requesterDetails->email)) {
                continue;
            }
            $data = $ticket->toArray();
            Mail::send('email.status_notification', ['details' => $data], function($message) use($data){
                $message->to($data['requester_details']['email']);
                $message->subject("Ticket Closure Notification - {$data['topic']}");
            });
        }
        return;
    }
}
