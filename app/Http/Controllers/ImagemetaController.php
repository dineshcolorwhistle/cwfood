<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{
    image_library,
    Product,
    Ingredient
};

class ImagemetaController extends Controller
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

    public function index(Request $request){
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        
        $productList = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->whereNotNull('prod_image')
                ->get()
                ->map(function ($item) {
                    return image_library::where('module', 'product')
                        ->where('module_id', $item->id)
                        ->get()->toArray();
                })->flatten(1)->toArray();
               
        $productList = array_filter($productList, function ($var){
            return (sizeof($var) > 0);
        });

        $ingredients = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('ing_image', '!=', null)
                ->get()
                ->map(function ($item) {  
                    return image_library::where('module', 'raw_material')
                        ->where('module_id', $item->id)
                        ->get()->toArray();
                })->flatten(1)->toArray();
        $ingredients = array_filter($ingredients, function ($var){
            return (sizeof($var) > 0);
        });

        $data['all'] = array_merge($productList,$ingredients);
        // dd($all);
        return view('backend.image-meta.manage', $data);
    }
}
