<?php

namespace App\Http\Controllers;

use App\Models\{Role,User,Client,Member,ClientSubscription,Client_billing};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\ClientBillingController;

class MembersController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    }

    public function index(Request $request)
    {
        $session = $request->session()->all();
        $clientID = $session['client'];
        // $ws_id = $session['workspace'];
        $users = Member::where('client_id',$clientID)->get();
        $users = $users->map(function ($item) {
            if ($item->assign_workspace !== null) {
                $item->assign = implode(',', json_decode($item->assign_workspace));
                $item->assign_ws_name = get_assign_workspace_names(json_decode($item->assign_workspace));
            }else{
                $item->assign = "";
                $item->assign_ws_name = "";
            }
            return $item;
        });
        $roles = Role::select('id', 'name', 'scope','description')->where('id', '!=', 1)->orderBy('order_number','asc')->get();
        $workspaces = get_ws_list_based_clientID($clientID);
        $pageTitle = 'Members';        
        return view('backend.members.manage', compact('users', 'roles', 'pageTitle','workspaces'));
    }

    public function store(Request $request)
    {
        try {
            $clientID = $this->clientID;
            $validationRules = Member::validationRules();
            $roleScope = Role::find($request->input('role_id'))->scope;

            if ($roleScope === 'platform') {
                $validationRules['client_id'] = 'nullable';
            }
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            if($request->input('role_id') == 4 && $request->input('assign_workspace') == null){
                return response()->json(['success' => false, 'message' => "Please select at least one workspace."]);
            }

            $member_count = Member::where('client_id',$clientID)->count();
            $client_plan = ClientSubscription::where('client_id',$clientID)->with(['plan'])->first();
            if($client_plan && $client_plan->plan->max_users > $member_count){
                $data = $validator->validated();
                $email = $data['email'];
                $assignWorkspace = $request->assign_workspace ? json_encode(explode(',', $request->assign_workspace)) : null;

                // 1. Check if email exists in Users with role_id = 1
                if (User::where('email', $email)->where('role_id', 1)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email already exists in the Batchbase admin. Please use a different email address.'
                    ]);
                }

                // 2. Check if email exists in Members for this client
                if (Member::where('client_id', $clientID)->where('email', $email)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email already exists in the Client. Please use a different email address.'
                    ]);
                }

                // 3. Check if email exists in any Member (other client) & check user table also
                if (User::where('email', $email)->exists()) {
                    $user_details = User::where('email', $email)->first();
                    if ($user_details) {
                        Member::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'role_id' => $data['role_id'],
                            'user_id' => $user_details->id,
                            'assign_workspace' => $assignWorkspace,
                            'client_id' => $clientID
                        ]);

                        /**
                         * Stripe Subsctiption update
                         */
                        $billing = Client_billing::where('client_id', $clientID)->first();
                        if (!empty($billing) && !empty($billing->subscription_id)) {
                            $stripeResponse = $this->updateStripeSubscription($billing,$clientID);
                            if($stripeResponse['success'] == false){
                                return response()->json(['success' => false, 'message' => $stripeResponse['message']]);  
                            }
                        }
                        
                        // $this->send_mail($data,$clientID); //Mail send to user
                        return response()->json(['success' => true,'message' => 'Member created successfully']);
                    }
                }

                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $clientID;
                $data['password'] = $data['password'];
                $user = User::create($data); //New user create
                $remove = ['client_id', 'password'];
                $memberArray = array_diff_key($data, array_flip($remove));
                $memberArray['user_id'] = $user->id;
                $memberArray['assign_workspace'] = ($request->assign_workspace)? json_encode(explode(',',$request->assign_workspace)) : null; 
                $memberArray['client_id'] = $clientID;
                Member::create($memberArray); // New member create
                
                /**
                 * Stripe subscription
                 */
                $billing = Client_billing::where('client_id', $clientID)->first();
                if (!empty($billing) && !empty($billing->subscription_id)) {
                    $stripeResponse = $this->updateStripeSubscription($billing,$clientID);
                    if($stripeResponse['success'] == false){
                      return response()->json(['success' => false, 'message' => $stripeResponse['message']]);  
                    }
                }

                /**
                 * Sending mail
                 */
                // $this->send_mail($data,$clientID); 
                return response()->json([
                    'success' => true,
                    'message' => 'Member created successfully',
                    'user' => $user
                ]);
            }else{
                $message = ($client_plan) ? 'Already users limit reached. Contact Batchbase admin' : 'Your company does not have an active subscription plan. Please contact your administrator.';
                return response()->json(['success' => false, 'message' => $message]);
            } 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateStripeSubscription($billing,$clientID){
        try {
            $plan_minuser = $client_plan->plan->min_users;
            $billingController = new ClientBillingController();
            $subscriptionDetails = $billingController->getSubscriptionDetails($billing->subscription_id);
            if (!empty($subscriptionDetails->items->data)) {
                $itemQty = $subscriptionDetails->items->data[0]->quantity ?? 0;
                $appMemberCount = Member::where('client_id', $clientID)->count();
                if ($appMemberCount > $itemQty) {
                    // More members than Stripe quantity → increase subscription
                    $newQty = $appMemberCount;
                    $billing_response = $billingController->updateSubscription($billing, $newQty);
                    if ($billing_response['status'] == false) {
                        $response = [
                            'success' => false,
                            'message' => $billing_response['message']
                        ];
                        return $response;
                    }
                }
            }

           $response = [
                'success' => true
            ];
            return $response;

        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return $response;
        }
    }

    public function send_mail($data,$clientID){
        $userDetails = User::where('id',$this->user_id)->pluck('name');
        $client = Client::where('id',$clientID)->pluck('name');
        $company_name = $client[0];
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $data['email'],
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);
        $resetUrl = route('reset.password.get', ['token' => $token]);

        Mail::send('email.member-create', ['company'=>$company_name,'name'=>$data['name'], 'inviter'=> $userDetails[0],'resetUrl' => $resetUrl,], function($message) use($data,$company_name){
            $message->to($data['email']);
            $message->subject("Invitation to join {$company_name} on Batchbase");
        });
        return;
    }

    public function update(Request $request, Member $member)
    {
        try {
            $validationRules = Member::validationRules($member->id);
            $roleScope = Role::find($request->input('role_id'))->scope;
            if ($roleScope === 'platform') {
                $validationRules['client_id'] = 'nullable';
            }
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            if (!$request->filled('password')) {
                unset($data['password']);
            }
            $data['updated_by'] = $this->user_id;            
            if ($roleScope === 'platform') {
                $data['client_id'] = null;
            }
            User::where('id',$member->user_id)->update($data);

            $remove = ['client_id', 'password','user_id'];
            $memberArray = array_diff_key($data, array_flip($remove));            
            $memberArray['assign_workspace'] = ($request->assign_workspace)? json_encode(explode(',',$request->assign_workspace)) : null; 
            Member::where('id',$member->id)->update($memberArray);            
            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully',
                'user' => $member
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Member $member)
    {
        try {
            $user_details = User::where('id', $member->user_id)->first();
            if (!$user_details) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }

            $email = $user_details->email;
            $clientID = $member->client_id;
            
            // Check if this email is associated with other clients
            $emailAssociations = Member::where('email', $email)->where('client_id', '!=', $clientID)->count();
            
            $client_plan = ClientSubscription::where('client_id', $clientID)->with(['plan'])->first();
            $plan_minuser = $client_plan && $client_plan->plan ? $client_plan->plan->min_users : 1;

            DB::beginTransaction();

            // Delete Cognito user only if NO other client associations exist
            if ($emailAssociations == 0) {
                User::where('id', $member->user_id)->delete();
            }
            
            $member->delete();

            /**
             * Stripe subscription update
             */
            $billing = Client_billing::where('client_id', $clientID)->first();
            if (!empty($billing) && !empty($billing->subscription_id)) {
                $billingController = new ClientBillingController();
                $subscriptionDetails = $billingController->getSubscriptionDetails($billing->subscription_id);

                if (!empty($subscriptionDetails->items->data)) {
                    $itemQty = $subscriptionDetails->items->data[0]->quantity ?? 0;
                    $appMemberCount = Member::where('client_id', $clientID)->count();

                    if ($appMemberCount < $itemQty && $itemQty > $plan_minuser) {
                        $newQty = max($appMemberCount, $plan_minuser);
                        $billing_response = $billingController->updateSubscription($billing, $newQty);

                        if ($billing_response['status'] == false) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => $billing_response['message']
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

}