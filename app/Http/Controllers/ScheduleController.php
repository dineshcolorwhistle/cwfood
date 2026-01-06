<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{ClientSubscription,Client,Member};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(){
        $this->send_oneday_notification();
        $this->send_threeday_notification();
        $this->send_sevenday_notification();
        return;
    }

    public function send_oneday_notification(){
        Log::info('Remainder Notification processiong');
        $end_date = now()->addDays(1)->toDateString();
        $details = ClientSubscription::where('plan_id',7)->where('end_date',$end_date)->get()->toArray();
        if(sizeof($details) > 0){
            foreach ($details as $key => $detail) {
                $this->get_client_details($detail,1);
            }
        }
        return;
    }

    public function send_threeday_notification(){
        $end_date = now()->addDays(3)->toDateString();
        $details = ClientSubscription::where('plan_id',7)->where('end_date',$end_date)->get()->toArray();
        if(sizeof($details) > 0){
            foreach ($details as $key => $detail) {
                $this->get_client_details($detail,3);
            }
        }
        return;
    }

    public function send_sevenday_notification(){
        $end_date = now()->addDays(7)->toDateString();
        $details = ClientSubscription::where('plan_id',7)->where('end_date',$end_date)->get()->toArray();
        if(sizeof($details) > 0){
            foreach ($details as $key => $detail) {
                $this->get_client_details($detail,7);
            }
        }
        return;
    }
     
    public function get_client_details($detail,$remaining){
        $super_admins = Member::where('client_id',$detail['client_id'])->whereIn('role_id',[2])->pluck('name','email')->toArray();
        if(sizeof($super_admins) > 0){
            $client = Client::where('id',$detail['client_id'])->first();
            $end_date = Carbon::parse($detail['end_date'])->format('F j, Y');
            Mail::send('email.plan_remainder', ['compnay' => $client->name, 'end_date' => $end_date, 'remaining_day' => $remaining], function($message) use($super_admins){
                $message->to($super_admins);
                $message->subject('Plan Remainder');
            });
        }
        return;
    }
}