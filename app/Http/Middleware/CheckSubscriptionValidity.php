<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{ClientSubscription,Client,Member,User};

class CheckSubscriptionValidity
{
    public function handle(Request $request, Closure $next)
    {
        $excludedRoutes = ['login','logout'];
        if ($this->isExcluded($request, $excludedRoutes)) {
            return $next($request);
        }
        if ($request->routeIs('signup')) {
            return $next($request);
        }

        if(!$request->isMethod('get')) {
            return $next($request);
        }
        
        if($request->routeIs('client.subscription.show')) {
            return $next($request);
        }

        if($request->routeIs('member.subscription.show')) {
            return $next($request);
        }

        $auth0user = Auth::user();
        if($auth0user){
            $user = User::where('email', $auth0user->email)->first();
            if (!$user) {
                Auth::logout();
                session()->flush(); // Clear all session data
                return redirect()->route('signup'); 
                
                // Redirect to Auth0 logout and back to login page
                // $returnTo = urlencode("https://nutriflow.firehawkanalytics.com.au/login"); // or just use '/login'
                // $auth0Domain = env('AUTH0_DOMAIN');
                // return redirect()->away("https://{$auth0Domain}/v2/logout?returnTo={$returnTo}&client_id=" . env('AUTH0_CLIENT_ID'));
            }

            $session = $request->session()->all();
            session()->put('user_id', $user->id);

            if(!array_key_exists('role_id',$session)){ 
                session()->put('role_id', $user->role_id);
            }

            // Assuming you have a subscription_end_date column on the users table or related subscription table
            if ($user && $user->role_id != 1) {
                if(!array_key_exists('client',$session)){ 
                    $cid = $user->client_id;
                    if($user->role_id == 4 || $user->role_id == 6 || $user->role_id == 7) {
                        $wsid = get_default_member_ws($cid, $user->id);
                    } else {
                        $wsid = get_default_ws($cid);
                    }
                    session()->put('client', $cid); 
                    session()->put('workspace', $wsid);
                }
                $subscription_details = ClientSubscription::where('client_id', $user->client_id)->first();
                $grace_period = (int) env('GRACE_PERIOD',7); // Grace period
                
                if($subscription_details){
                    if($subscription_details->active_status == 'inactive'){
                        if (in_array($user->role_id, [4, 6, 7])) {
                            return redirect()->route('member.subscription.show');
                        } else if (in_array($user->role_id, [2, 3])) {
                            return redirect()->route('client.subscription.show'); 
                        }
                    }
                    
                    $now = Carbon::now();
                    if($subscription_details->plan_id != 7){
                        $subscriptionEnd = Carbon::parse($subscription_details->end_date)->addDays($grace_period); // Add grace period
                    }else{
                        $subscriptionEnd = Carbon::parse($subscription_details->end_date);
                    }
                       
                    if ($subscriptionEnd->lt($now)) {
                        // Subscription expired - redirect to a specific page
                        if (in_array($user->role_id, [4, 6, 7])) {
                            return redirect()->route('member.subscription.show');
                        } else if (in_array($user->role_id, [2, 3])) {
                            return redirect()->route('client.subscription.show'); 
                        }
                    }
                }else{
                    if (in_array($user->role_id, [4, 6, 7])) {
                        return redirect()->route('member.subscription.show');
                    } else if (in_array($user->role_id, [2, 3])) {
                        return redirect()->route('client.subscription.show'); 
                    }
                }   
            }else{
                if(!array_key_exists('client',$session)){ 
                    $details = get_default_client_list();
                    session()->put('client', $details['first_client']); 
                    session()->put('workspace', $details['first_ws']);
                }
            }      
        }
        return $next($request);
    }

    protected function isExcluded($request, $excludedRoutes)
    {
        foreach ($excludedRoutes as $route) {
            if ($request->is($route) || $request->is("$route/*")) {
                return true;
            }
        }
        return false;
    }
}
