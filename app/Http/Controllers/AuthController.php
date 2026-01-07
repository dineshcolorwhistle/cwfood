<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Validator};
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\{Client,Workspace,
    ClientSubscription,SubscriptionPlan,Labour,ProdLabour,Product,Freight,ProdFreight,
    Machinery,Packaging,Ingredient,ProdMachinery,ProdPackaging,ProdIngredient,image_library, User,Member
};


class AuthController extends Controller
{

    /**
     * Show the login form.
     * If the user is already authenticated, redirect to the intended page(default is the dashboard).
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('views.products');
        }
        return view('auth.login');
    }

    public function showSignupForm()
    {
        if (Auth::check()) {
            return redirect()->route('views.products');
        }   
        return view('auth.signup');
    }
  
  	public function loginByEmail(Request $request)
    {
 
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'User not found'
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('views.products');
    }


    public function company_authenticate(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required','email:rfc,dns',Rule::unique('users')->ignore($id='')],
            'company_name' => 'required',
            'description' => 'required',
            'name'=> 'required',
            'subscription_plan_id'=> 'required',
        ], [
            // Custom error messages
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already associated with an existing client. Please use a different email to create a new company.',
            'company_name.required' => 'Company name cannot is required.',
            'description.required' => 'Description is required.',
            'name.required' => 'Full Name is required.',
            'subscription_plan_id.required' => 'Subscription Plan is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();
        $clinetDetails = $this->create_client_and_ws($validated);
        $data['name'] = $validated['name'];
        $data['email'] = $validated['email'];

        /**
         * Cognito user created
        */
        $controller = app(\App\Http\Controllers\CognitoUserController::class);
        $response = $controller->Check_user_exist($data['email']);
        $decoded = $response->getData(true); // decode JSON to array
        if ($decoded['ok'] === true && $decoded['message'] === 'User not exist') {
            $user_response = $controller->store($data['email']);
            $user_decoded = $user_response->getData(true); // decode JSON to array
            if ($user_decoded['ok'] === false) {
                return response()->json(['status'=>false,'message' => $user_decoded['error']]);
            }
        }else if($decoded['ok'] === false){
            return response()->json(['status'=>false,'message' => $decoded['error']]);
        }
        $this->create_users($validated,$clinetDetails);
        $this->create_default_resources($clinetDetails);
        $this->send_mail_user($validated);  
        return response()->json(['success' => "The company has been created successfully. A password reset email has been sent to the registered email address. Please use it to set your password and log in to the application."]);
    }

    public function create_client_and_ws($validated){
        $clientData = [
            'name' => $validated['company_name'],
            'description' => $validated['description'] ?? null,
            'status' => 1,
            'created_by' => 24,
            'updated_by' => 24
        ];
        $client = Client::create($clientData);
        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $subscriptionData = [
            'plan_id' => $plan->id,
            'client_id' => $client->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(), // 14 days of Trial plan
            'users_allocated' => $plan->min_users,
            'raw_materials_allocated' => $plan->max_raw_materials,
            'skus_allocated' => $plan->max_skus,
            'work_spaces_allocated' => $plan->max_work_spaces,
            'active_status' => 'active',
            'created_by' => 24,
            'updated_by' => 24
        ];
        // Create client subscription
        $clientSubscription = ClientSubscription::create($subscriptionData);
        $client->update(['current_subscription_id' => $clientSubscription->id]);

        $item = new Workspace;
        $item->client_id = $client->id;
        $item->name = "Demo";
        $item->description = "Demo workspace";
        $item->ws_primary = 1;
        $item->created_by = 24;
        $item->updated_by = 24;
        $item->save();

        $result['client_id'] = $client->id;
        $result['workspace_id'] = $item->id; 
        return $result;
    }

    public function create_users($validated,$clinetDetails){
        if (Member::where('email', $validated['email'])->exists()) {
            $user_details = User::where('email', $validated['email'])->first();
            $userID = $user_details->id;
        }else{
            $user = new User; //Create User
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role_id = 2;
            $user->client_id = $clinetDetails['client_id'];
            $user->created_by = 24;
            $user->updated_by = 24;
            $user->save();
            $userID = $user->id;
        }

        $member = new Member; //Create Member
        $member->client_id = $clinetDetails['client_id'];
        $member->user_id = $userID;
        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->role_id = 2;
        $member->assign_workspace = null;
        $member->created_by = 24;
        $member->updated_by = 24;
        $member->save();
        return;
    }

    public function send_mail_user($validated){
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.signup-admin', ['name'=>$validated['company_name']], function($message) use($validated,$nutriflow_admins){
            $message->to($nutriflow_admins);
            $message->subject("New account is created | {$validated['company_name']}");
        });

        Mail::send('email.signup', ['name'=>$validated['name']], function($message) use($validated){
            $message->to($validated['email']);
            $message->subject("New account is created | {$validated['company_name']}");

        });
        return;
    }

    public function create_default_resources($clinetDetails){
        $defaultClient = env('DEFAULT_SETTING_COMPANY');
        $defaultClientWorkspace = env('DEFAULT_SETTING_WORKSPACE');
        create_default_companies($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_ingredients($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_labours($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_machinery($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_packaging($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_freight($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        create_default_products($clinetDetails['client_id'],$clinetDetails['workspace_id'],$defaultClient,$defaultClientWorkspace);
        return;
    }
  
  	public function logout(Request $request): RedirectResponse
    {
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
		 return redirect()->route('login');
        
    }

}
