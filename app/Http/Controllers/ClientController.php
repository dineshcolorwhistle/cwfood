<?php

namespace App\Http\Controllers;

use App\Models\{Client,Workspace,
    ClientSubscription,SubscriptionPlan,Labour,ProdLabour,Product,Freight,ProdFreight,
    Machinery,Packaging,Ingredient,ProdMachinery,ProdPackaging,ProdIngredient,image_library,Client_billing
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use App\Http\Controllers\ClientBillingController;

class ClientController extends Controller
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

    /**
     * Show the list of clients.
     */
    public function index()
    {
        $clients = Client::with('currentSubscription.plan')->get();
        $statuses = Client::STATUS;
        $subscriptionPlans = SubscriptionPlan::all();
        return view('backend.client.clients', compact('clients', 'statuses', 'subscriptionPlans'));
    }

    /**
     * Store a newly created client in the database with optional subscription.
     * New
     */
    public function store(Request $request)
    {
        try {
            // Begin database transaction
            DB::beginTransaction();

            // Validate the request
            $validator = Validator::make($request->all(), array_merge(
                Client::validationRules(),
                [
                    'start_date' => 'nullable|date',
                    'end_date' => 'nullable|date|after:start_date'
                ]
            ));

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // $this->create_default_setting(11,14);  

            $data = $validator->validated();
            // Prepare client data
            $clientData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 1,
                'type' => $data['type'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'created_by' => $this->user_id,
                'updated_by' => $this->user_id
            ];

            // Create client
            $client = Client::create($clientData);

            // If a subscription plan is selected
            if (!empty($data['subscription_plan_id'])) {
                $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);

                // Prepare subscription data
                $subscriptionData = [
                    'plan_id' => $plan->id,
                    'client_id' => $client->id,
                    'start_date' => $data['start_date'] ?? now()->toDateString(),
                    'end_date' => $data['end_date'] ?? now()->addYear()->toDateString(),
                    // 'users_allocated' => $data['users_allocated'] ?? $plan->min_users,
                    // 'raw_materials_allocated' => $data['raw_materials_allocated'] ?? $plan->max_raw_materials,
                    // 'skus_allocated' => $data['skus_allocated'] ?? $plan->max_skus,
                    // 'work_spaces_allocated' => $data['work_spaces_allocated'] ?? $plan->max_work_spaces,
                    'active_status' => 'active',
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id
                ];
                // Create client subscription
                $clientSubscription = ClientSubscription::create($subscriptionData);
                // Update client's current subscription
                $client->update(['current_subscription_id' => $clientSubscription->id]);
            }

            $item = new Workspace;
            $item->client_id = $client->id;
            $item->name = "Demo";
            $item->description = "Demo workspace";
            $item->ws_primary = 1;
            $item->created_by = $this->user_id;
            $item->updated_by = $this->user_id;
            $item->save();


            //Create Default Settings
            $defaultClient = env('DEFAULT_SETTING_COMPANY');
            $defaultClientWorkspace = env('DEFAULT_SETTING_WORKSPACE');
            create_default_companies($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_ingredients($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_labours($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_machinery($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_packaging($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_freight($client->id,$item->id,$defaultClient,$defaultClientWorkspace);
            create_default_products($client->id,$item->id,$defaultClient,$defaultClientWorkspace);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'client' => $client
            ]);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified client and their subscription in the database.
     */
    public function update(Request $request, Client $client)
    {
        try {
            // Begin database transaction
            DB::beginTransaction();

            // Validate the request
            $validator = Validator::make($request->all(), array_merge(
                Client::validationRules($client->id),
                [
                    'start_date' => 'nullable|date',
                    'end_date' => 'nullable|date|after:start_date'
                ]
            ));

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $selecetd_plan = $request->selected_plan_id;
            /**
             * Check stripe discount
             */
            $discount = $data['discount'] ?? 0;
            if($discount > 0){
                if($client->discount != $discount){
                    $billingController = new ClientBillingController();
                    $billing_response = $billingController->updateDiscount($client->id,$discount);
                    if($billing_response['status'] == false){
                        return response()->json(['success' => false,'message' => $billing_response['message']]); 
                    }
                }
            }

            // Prepare client data
            $clientData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 1,
                'type' => $data['type'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'updated_by' => $this->user_id
            ];



            // Update client
            $client->update($clientData);

            // If a subscription plan is selected
            if (!empty($selecetd_plan)) {
                $plan = SubscriptionPlan::findOrFail($selecetd_plan);

                // Check if client has an existing subscription
                $existingSubscription = $client->currentSubscription;

                $subscriptionData = [
                    'plan_id' => $plan->id,   
                    'start_date' => $data['start_date'] ?? ($existingSubscription->start_date ?? now()->toDateString()),
                    'end_date' => $data['end_date'] ?? ($existingSubscription->end_date ?? now()->addYear()->toDateString()),
                    // 'users_allocated' => $data['users_allocated'] ?? $plan->min_users,
                    // 'raw_materials_allocated' => $data['raw_materials_allocated'] ?? $plan->max_raw_materials,
                    // 'skus_allocated' => $data['skus_allocated'] ?? $plan->max_skus,
                    // 'work_spaces_allocated' => $data['work_spaces_allocated'] ?? $plan->max_work_spaces,
                    'active_status' => 'active',
                    'updated_by' => $this->user_id
                ];

                if ($existingSubscription) {
                    // Update existing subscription
                    $existingSubscription->update($subscriptionData);
                } else {
                    // Create new subscription
                    $subscriptionData['client_id'] = $client->id;
                    $subscriptionData['created_by'] = $this->user_id;
                    $clientSubscription = ClientSubscription::create($subscriptionData);
                    $client->update(['current_subscription_id' => $clientSubscription->id]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'client' => $client
            ]);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified client and their associated subscriptions in the database.
     */
    public function destroy(Client $client)
    {

        try {
            $billing = Client_billing::where('client_id', $client->id)->first();
            if (!empty($billing)) {
                $billingController = new ClientBillingController();
                $billing_response = $billingController->removeCustomer($billing);
                if($billing_response['status'] == false){
                    return response()->json(['success' => false,'message' => $billing_response['message']]); 
                }
            }

            $workspaces = Workspace::where('client_id',$client->id)->get()->toArray();
            if(sizeof($workspaces) > 0){
                foreach ($workspaces as $key => $value) {
                    delete_workspace_details($value['id'],$value['client_id']);
                }
            }

            // Optional: Delete associated subscriptions
            Client_billing::where('client_id', $client->id)->delete();
            $client->subscriptions()->delete();
            $client->CompanyCategory()->delete();
            $client->CompanyTags()->delete();
            $client->ContactCategory()->delete();
            $client->ContactTags()->delete();
            $client->Contacts()->delete();
            $client->Companies()->delete();
            $client->delete();

            return response()->json(['success' => true, 'message' => 'Client deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

}
