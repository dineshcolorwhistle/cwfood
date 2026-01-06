<?php

namespace App\Http\Controllers;

use App\Models\{Client,ClientSubscription,SubscriptionPlan,Member,Ingredient,Product,Workspace,Client_billing};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\ClientBillingController;
use App\Services\SubscriptionService;




class ClientSubscriptionController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the client's current subscription with plan details
     */
    public function show(Request $request)
    {
        $clientID = $this->clientID;
        // Get the client's current subscription with plan details
        $all_subscription = SubscriptionPlan::where('id','!=',7)->get()->map(function ($p) {
            // MONTHLY: from column directly
            $monthly = (float) ($p->monthly_cost_per_user ?? 0);

            // ANNUAL display: use annual_cost_per_user / 12 (monthly-equivalent)
            // If annual_cost_per_user is null/0, fallback to monthly
            $annualTotal   = isset($p->annual_cost_per_user) ? (float)$p->annual_cost_per_user : null;
            $annualMonthly = ($annualTotal && $annualTotal > 0) ? ($annualTotal / 12) : $monthly;

            // Savings % compared to paying monthly for 12 months
            $fullYearAtMonthly = $monthly * 12;
            $savingsPct = 0;
            if ($annualTotal && $annualTotal > 0 && $fullYearAtMonthly > 0) {
                $savingsPct = (int) max(0, round((($fullYearAtMonthly - $annualTotal) / $fullYearAtMonthly) * 100));
            }

            $p->computed = [
                'monthly'       => $monthly,
                'annualMonthly' => $annualMonthly,
                'annualTotal'   => $annualTotal,
                'savingsPct'    => $savingsPct,
            ];
            return $p;
        });
        $subscription = ClientSubscription::with(['plan'])->where('client_id', $clientID)->first();
        $remainingDays = 0;
        if($subscription){
            $start_date = Carbon::parse(now()->toDateString());
            $end_date = Carbon::parse($subscription->end_date);
            $remainingDays = $start_date->diffInDays($end_date);
        }  
        // UI toggle initial state (?cycle=monthly|annual) or default monthly
        $billingCycle = $subscription->plan_period == 0 ? 'monthly' : 'annual';

        // For Upgrade/Downgrade labels (compare by max_users)
        $currentMaxUsers = $subscription?->plan?->max_users;  

        $paymentDetails = Client_billing::where('client_id',$clientID)->first();
        
        $paymentMethod = null;
        if (!empty($paymentDetails) && !empty($paymentDetails->payment_method_id)) {
            $paymentMethod = $paymentDetails->payment_method_id;
        }
    
        return view('backend.client_subscription.new-show', compact('subscription','all_subscription','remainingDays','clientID','billingCycle','currentMaxUsers','paymentDetails','paymentMethod'));
    }

    public function update(Request $request){
        try {

            $cid = $request->input('client');
            $ex_plan = $request->input('plan');
            $billing_period  = $request->input('period');
            $plan_update = $request->input('plan_update');
            
            $client = Client::findOrfail($cid);
            $plan = SubscriptionPlan::findOrfail($ex_plan);
            
            /**
             * Calculate Resource count
             */
            $ing_count = Ingredient::where('client_id',$cid)->count();
            $pro_count = Product::where('client_id',$cid)->count();
            $wsp_count = Workspace::where('client_id',$cid)->count();
            $mem_count = Member::where('client_id',$cid)->count();
            $exceed = [];
            if($ing_count > $plan->max_raw_materials){
                $exceed[] = "Raw Materials";
            }
            if($pro_count > $plan->max_skus){
                $exceed[] = "Products";
            }
            if($mem_count > $plan->max_users){
                $exceed[] = "Users";
            }
            if($wsp_count > $plan->max_work_spaces){
                $exceed[] = "Workspaces";
            }

            if(sizeof($exceed) > 0){
                $list = implode(',',$exceed);
                $msg = "{$list} are exceeds limits. So you cannot do your subscription plan.Please contact the Batchbase admin for assistance.";
                return response()->json(['error' => $msg]);
            }

            $billing = Client_billing::where('client_id', $cid)->first();
            $billingController = new ClientBillingController();
            $message = "Plan Updated";
            $explan = ClientSubscription::where('client_id',$cid)->first();
            
            if (!empty($billing) && !empty($billing->subscription_id)) {
                if($explan->active_status == 'scheduled_cancel'){
                    return response()->json(['error' => 'Your subscription has been scheduled for cancellation.']); 
                }

                $billing_response = $this->subscriptionService->updateSubscriptionPlan(
                                $plan,
                                $billing_period,
                                $billing,
                                $this->clientID 
                            );
                
                // $billing_response = $billingController->updateSubscriptionPlan($plan,$billing_period,$billing);
                if($billing_response['status'] == false){
                    return response()->json(['error' => $billing_response['message']]); 
                }  

                if($plan_update != "Upgrade"){
                    $end_date = $explan->end_date;
                    $plan_name = $plan->subscription_name;
                    $message = "Current features remain available until {$end_date}. {$plan_name} applies from your next renewal.";
                }
            }elseif (!empty($billing)) {
                $billing_response = $billingController->createSubscription($plan,$billing_period,$client);
                if($billing_response['status'] == false){
                    return response()->json(['error' => $billing_response['message']]); 
                }
            }

            $update_data['plan_id'] = $plan->id;
            $update_data['start_date'] = now()->toDateString();
            $update_data['end_date'] = ($billing_period == 'annual') ? now()->addYear()->toDateString() : now()->addMonth()->toDateString();
            $update_data['active_status'] = 'active';            
            $update_data['plan_period'] = ($billing_period == 'annual') ? 1 : 0;            
            ClientSubscription::where('client_id',$cid)->update($update_data);
            return response()->json(['success' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
