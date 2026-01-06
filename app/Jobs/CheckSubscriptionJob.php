<?php

namespace App\Jobs;

use App\Http\Controllers\ScheduleController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\{ClientSubscription,Client,Member};
use Illuminate\Support\Facades\Mail;

class CheckSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('CheckSubscriptionJob running...');
            Mail::send('email.plan_remainder', ['compnay' => 'colorwhistle', 'end_date' => '2025-04-29', 'remaining_day' => '1'], function($message){
                $message->to('dinesh.colorwhistle@gmail.com');
                $message->subject('Plan Remainder');
            });
        } catch (\Exception $e) {
            Log::error('Error in CheckSubscriptionJob: ' . $e->getMessage());
        }
        
    }
}
