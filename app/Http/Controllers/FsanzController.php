<?php

namespace App\Http\Controllers;

use App\Models\Fsanz;
use App\Models\Fsanz_weight_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FsanzController extends Controller
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
     * Returns a view with the nutrition data of all FSANZ foods.
     */
    public function nutrition(Request $request)
    {
        //  $nutrition = Fsanz::all(['food_id', 'food_name', 'energy_kj', 'protein_g', 'fat_total_g', 'fat_saturated_g', 'carbohydrate_g', 'total_sugars_g', 'sodium_mg']);
        $nutrition = Fsanz::all();
        $clientID = $this->clientID;
        $workspaceID = $this->ws_id;
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - FSANZ Nutrition','Resources - FSANZ Nutrition Read']);
        }else{
            $permission = [];
        }

        return view('backend.fsanz.nutrition', compact('nutrition','permission','user_role'));
    }

    /**
     * Returns a view with the food properties of all FSANZ foods.
     */
    public function properties()
    {
        $properties = Fsanz::all(['food_id', 'food_name', 'description', 'specific_gravity']);
        return view('backend.fsanz.properties', compact('properties'));
    }

    /**
     * Displays the details of an FSANZ food item.
     */
    public function details($id, Request $request)
    {
        $food = Fsanz::where('food_id', $id)->firstOrFail();
        $backUrl = $this->getBackUrl($request);
        return view('backend.fsanz.details', compact('food', 'backUrl'));
    }

    private function getBackUrl(Request $request)
    {
        $referer = $request->headers->get('referer');
        if (strpos($referer, 'nutrition') !== false) {
            return route('fsanz.nutrition');
        } elseif (strpos($referer, 'properties') !== false) {
            return route('fsanz.properties');
        }
        // Default to nutrition if we can't determine the source
        return route('fsanz.nutrition');
    }


    public function nutrition_weight(Request $request)
    {
        $nutrition = Fsanz_weight_detail::all();
        $clientID = $this->clientID;
        $workspaceID = $this->ws_id;
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - FSANZ Weight','Resources - FSANZ Weight Read']);
        }else{
            $permission = [];
        }

        return view('backend.fsanz_weight.nutrition', compact('nutrition','permission'));
    }
}
