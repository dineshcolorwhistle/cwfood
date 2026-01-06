<?php

namespace App\Http\Controllers;
use App\Models\{
    image_library,
    Labour,
    ProdTag,
    Product,
    Machinery,
    Packaging,
    Ingredient,
    ProdLabour,
    ProdMachinery,
    ProdPackaging,
    ProdIngredient,
    ClientProfile,
    Ing_country,
    Ing_allergen,
    Company,
    Client_company,
    ProdLabel,
    ProdFreight,
    Freight,
    ClientSubscription,
    Product_category,
    Product_tag,
    Recipe_component,
    Product_edit_lock,
    client_key_personnel,client_factory_location
};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Log,   
    Auth,
    Validator
};
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class ProductV2Controller extends ProductController
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
     * Display a listing of the products with pagination.
     */
    public function index(Request $request)
    {
        Product_edit_lock::where('user_id',$this->user_id)->delete();
        $clientID = $this->clientID;
        $ws_id = $this->ws_id; 
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Products','Products Read']);
        }else{
            $permission = [];
        }
        $prod_status = get_products_status_array();
        $prod_ranging = get_products_range_array();
        $categories = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $prodTags = Product_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $perPage = $request->input('perPage', 25); // Default value is 25
        $viewType = $request->input('view', 'list');
        // Fetch products with pagination
        $products = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->with(['product_category'])->orderBy('favorite','desc')->latest('updated_at')->get();
        foreach ($products as $key => $product) {
            if($product->prod_tags){
                $ingArray = $product->prod_tags;
                $tags = DB::table('product_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $products[$key]->prod_tags = implode(', ', $tags);
            }else{
                $products[$key]->prod_tags = "";
            }
        }
        return view('backend.product_v2.list-view', compact('products', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','prodTags'));
    }


    public function create()
    {
        $request = request();
        $request->merge(['tab_variant' => 'product_v2']);
        $view = parent::create();
        // parent::create returns a View; reuse data but force v2 view
        return view('backend.product_v2.form', $view->getData());
    }

    public function manage(Request $request)
    {
        $clientID = (int) session('client');
        $ws_id = (int) session('workspace');

        $products = Product::where('client_id', $clientID)
            ->where('workspace_id', $ws_id)
            ->where('archive', 0)
            ->latest('updated_at')
            ->paginate(25);

        return view('backend.product_v2.manage', compact('products', 'clientID', 'ws_id'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product, Request $request)
    {
        $lock = Product_edit_lock::where('sku_id',$product->id)->first();
        if ($lock && $lock->user_id !== $this->user_id && $lock->expires_at > now()) {
            abort(403, 'This SKU is currently being edited by another user.');
        }

        if($lock == null){
            Product_edit_lock::create([
                'sku_id' => $product->id,
                'user_id' => $this->user_id,
                'locked_at' => now(),
                'expires_at' => now()->addMinutes(1),
            ]);
        }

        $session = $request->session()->all();
        $clientID = (int)$session['client'];
        $workspaceID = (int)$session['workspace'];
        $step = $request->query('step', 1);
        $ingredients = Ingredient::with(['supplier'])->where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('archive',0)->get(); 
        $prod_ingres = $product->ingredients()->with('ingredient')->get();
        $prodingNutrition = $this->costing_nutrition($prod_ingres, $product->batch_baking_loss_percent); 
        
        $prod_ings = $prod_ingres->map(function ($item) use ($prodingNutrition,&$ingrs,&$allgs) {
            $pr_name = $item->ing_name;     
            $key = array_search($pr_name, array_column($prodingNutrition['nutrition'], 'name'));
            if ($key === false) {
                $item->mix_percent = null; // or some default value
            } else {
                $item->mix_percent = $prodingNutrition['nutrition'][$key]['mix_percent'] ?? null;
            }
            $item->ing_supplier = null;
            if($item->ingredient){
                if($item->ingredient->supplier_name){
                    $company = Client_company::where('id',$item->ingredient->supplier_name)->pluck('company_name')->toArray();
                    if(count($company) > 0){
                        $item->ing_supplier = $company[0];
                    }
                    
                }
            } 
            return $item;
        });

        //Sort ascending based on weight
        $sorted = $prod_ingres->sortByDesc(function ($item) {
            return (float) $item->quantity_g;
        })->values();
        $ingrs = [];
        $allgs = [];
        $sorted->each(function ($item) use (&$ingrs, &$allgs) {
            if ($item->ingredient?->ingredients_list_supplier) {
                $ingrs[] = $item->ingredient->ingredients_list_supplier;
            }
            if ($item->ingredient?->allergens) {
                $temp = $item->ingredient->allergens;
                $tempAllergs = explode(",", $temp);
                foreach ($tempAllergs as $key => $value) {
                    if(!in_array($value, ["-","&"])){
                        $allgs[] = trim($value);
                    }
                }
            }
        });

        $product->labelling_ingredients = (count($ingrs))?implode(', ', $ingrs):'';
        $product->labelling_allergens   = (count($allgs))?implode(', ', array_unique(array_filter($allgs))):'';
                
        // if ($prod_ings->isEmpty()) {
        //     $prod_ings->push(new ProdIngredient());
        // }

        $labours = Labour::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('archive',0)->get();
        $prod_labours = $product->prodLabours()->with('labour')->get();
        $prod_machinery = ProdMachinery::where('product_id', $product->id)->get();
        $machinery = Machinery::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('archive',0)->get();
        $packaging = Packaging::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('archive',0)->get();
        $prod_packaging = ProdPackaging::where('product_id', $product->id)->get()->toArray();
        foreach($prod_packaging as $key => $value){
            $suppliers = Packaging::where('id', $value['packaging_id'])->select('supplier_id')->first();
            $prod_packaging[$key]['supplier'] = "";
            if($suppliers){
                $Company = Client_company::where('id',$suppliers->supplier_id)->pluck('company_name');
                if ($Company->isNotEmpty()) {
                    $prod_packaging[$key]['supplier'] = $Company[0];
                }
            }
        }
        $freights = Freight::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('archive',0)->get();
        $prod_freights = $product->prodFreights()->with('freight')->get();
        $allergen = Ing_allergen::pluck('name')->toArray(); 
        $prod_labels = ProdLabel::where('product_id', $product->id)->first();
        $categories = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $prod_status = get_products_status_array();
        $prod_ranging = get_products_range_array();
        $recipe_components = Recipe_component::where('client_id', $clientID)->where('workspace_id', $workspaceID)->get();
        $companyProfile['factory'] =client_factory_location::where('client_id', $clientID)->get();
        $companyProfile['keyperson'] =client_key_personnel::where('client_id', $clientID)->get();
        $profile = ClientProfile::where('client_id', $product['client_id'])->first();
        $default_component = Recipe_component::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('default', 1)->value('id') ?? 0;

        return view('backend.product_v2.form', compact('product', 'step', 'ingredients', 'prod_ings', 'labours', 'prod_labours', 'prod_machinery', 'machinery', 'packaging', 'prod_packaging','clientID','workspaceID','allergen','prod_labels','freights','prod_freights','categories','prod_status','prod_ranging','recipe_components','companyProfile','profile','default_component'));
    }

    /**
     * Grid view of Products
     */
    public function grid_view(Request $request)
    {        
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10); // Default to 25 if not provided
        $viewType = $request->input('view', 'grid');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $user_role = $this->role_id;
        $prod_status = get_products_status_array();
        $prod_ranging = get_products_range_array();
        $categories = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $tags = Product_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();

        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Products','Products Read']);
        }else{
            $permission = [];
        }
        // Fetch paginated results using the search term
        $products = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('archive',0)->orderBy('favorite','desc')->latest('updated_at')->search($search)
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage, 'view' => $viewType,'client'=>$clientID,'ws'=>$ws_id,'permission' =>$permission,'user_role'=>$user_role]);
        $paginationLinks = $products->onEachSide(1)->links('backend.product.pagination')->render();

        if ($request->ajax()) {
            // Return the product list view and pagination as part of the response
            return response()->json([
                'data' => view('backend.product.grid-view', compact('products', 'viewType','clientID','ws_id','permission','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
            ]);
        }

        return view('backend.product_v2.grid-view', compact('products', 'search', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','tags'));
    }
}

