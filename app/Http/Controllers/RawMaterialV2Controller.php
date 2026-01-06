<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Ingredient, Client_company, Ing_country, Ing_allergen, Rawmaterial_category, Rawmaterial_tag, IngLabel, Ingredient_edit_lock};
use Illuminate\Support\Facades\DB;

class RawMaterialV2Controller extends IngredientController
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

        return redirect()->route('rawmaterial_v2.manage');
    }

    public function create()
    {
        $ingredient = new Ingredient();
        
        return view('backend.rawmaterial_v2.form', [
            'ingredient' => $ingredient,
            'hasIngredient' => false,
            'statusArray' => get_rawmaterial_status_array(),
            'rangeArray' => get_rawmaterial_range_array(),
            'supplier' => Client_company::where('client_id', $this->clientID)->get()->toArray(),
            'country' => Ing_country::all()->toArray(),
            'allergen' => Ing_allergen::all()->toArray(),
            'categories' => Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get()->toArray(),
            'Tags' => Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get()->toArray(),
            'details' => [],
            'prod_labels' => null,
        ]);
    }
   
    /**
     * Manage Rawmaterials
     */

    public function manage(Request $request)
    {
        Ingredient_edit_lock::where('user_id',$this->user_id)->delete();
        $perPage = $request->input('perPage', 10); // Default value is 10
        $viewType = $request->input('view', 'list');
        $clientID = $this->clientID;  
        $ws_id = $this->ws_id; 
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - Raw Material','Resources - Raw Material Read']);
        }else{
            $permission = [];
        }
        $prod_status = get_rawmaterial_status_array();
        $prod_ranging = get_rawmaterial_range_array();
        $categories = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $ingtags = Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $lists = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->orderBy('favorite','desc')->with(['supplier', 'raw_category','Country'])->latest('updated_at')->get();
        foreach ($lists as $key => $ingredient) {
            if($ingredient->ing_tags){
                $ingArray = json_decode($ingredient->ing_tags);
                $tags = DB::table('rawmaterial_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $lists[$key]->ing_tags = implode(', ', $tags);
            }else{
                $lists[$key]->ing_tags = "";
            }
        }          
        return view('backend.rawmaterial_v2.list-view', compact('lists', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','ingtags'));
    }


    public function grid_view(Request $request)
    {
        $perPage = $request->input('perPage', 10); // Default value is 10
        $viewType = $request->input('view', 'grid');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id; 
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - Raw Material','Resources - Raw Material Read']);
        }else{
            $permission = [];
        }
        $prod_status = get_rawmaterial_status_array();
        $prod_ranging = get_rawmaterial_range_array();
        $categories = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $tags = Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        // Fetch products with pagination
        $lists = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('archive',0)->orderBy('favorite','desc')->with('supplier')->latest('updated_at')->paginate($perPage);
        $paginationLinks = $lists->appends(['view' => $viewType])
        ->onEachSide(1)
        ->links('backend.ingredient.pagination')
        ->render();
        if ($request->ajax()) {
            // Return the product list view and pagination as part of the response
            return response()->json([
                'data' => view('backend.rawmaterial_v2.grid', compact('lists', 'viewType','permission','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
            ]);
        }
        // dd($lists);

        return view('backend.rawmaterial_v2.grid', compact('lists', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','tags'));
    }


    public function edit($id)
    {
        $lock = Ingredient_edit_lock::where('sku_id', $id)->first();
        if ($lock && $lock->user_id !== $this->user_id && $lock->expires_at > now()) {
            abort(403, 'This SKU is currently being edited by another user.');
        }

        if($lock == null){
            Ingredient_edit_lock::create([
                'sku_id' => $id,
                'user_id' => $this->user_id,
                'locked_at' => now(),
                'expires_at' => now()->addMinutes(15),
            ]);
        }

        $ingredient = Ingredient::where('id', $id)->firstOrFail();
        $ingr = $ingredient->toArray();

        // if(!empty($ingr['australian_percent'])){
        //     $ingr['australian_percent'] = $ingr['australian_percent'] * 100;
        // }

        return view('backend.rawmaterial_v2.form', [
            'ingredient' => $ingredient,
            'hasIngredient' => true,
            'statusArray' => get_rawmaterial_status_array(),
            'rangeArray' => get_rawmaterial_range_array(),
            'categories' => Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get()->toArray(),
            'Tags' => Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get()->toArray(),
            'supplier' => Client_company::where('client_id', $this->clientID)->get()->toArray(),
            'country' => Ing_country::all()->toArray(),
            'allergen' => Ing_allergen::all()->toArray(),
            'details' => $ingr,
            'prod_labels' => IngLabel::where('rawmaterial_id', $id)->first(),
        ]);
    }
}

