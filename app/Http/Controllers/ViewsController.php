<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Product,Ingredient, Product_category,Product_tag,Rawmaterial_tag,Rawmaterial_category};
use Illuminate\Support\Facades\{DB};

class ViewsController extends Controller
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

    public function product_views(Request $request){

        $prod_status = get_products_status_array();
        $prod_ranging = get_products_range_array();
        $categories = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $pr_tags = Product_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();

        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10); // Default to 25 if not provided
        $viewType = $request->input('view', 'grid');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $user_role = $this->role_id;
        // Fetch paginated results using the search term
        $products = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('archive',0)->with(['product_category'])->orderBy('favorite','desc')->latest('updated_at')->search($search)
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage, 'view' => $viewType,'client'=>$clientID,'ws'=>$ws_id,'user_role'=>$user_role]);
            $paginationLinks = $products->onEachSide(1)->links('backend.product.pagination')->render();
        foreach ($products as $key => $product) {
            if($product->prod_tags){
                $ingArray = $product->prod_tags;
                $tags = DB::table('product_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $products[$key]->prod_tags = implode(', ', $tags);
            }else{
                $products[$key]->prod_tags = "";
            }
        }
        return view('backend.product-views.product-grid-view', compact('products', 'search', 'viewType', 'perPage','clientID','ws_id','user_role', 'prod_status','prod_ranging','categories','pr_tags'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 25); // Default to 25 if not provided
        $viewType = $request->input('view', 'list');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $user_role = $this->role_id;
        $permission = [];

        $category = $request->input('category');
        $status = $request->input('status');
        $ranging = $request->input('ranging');
        $tags = $request->input('tags');

        $query = Product::where('client_id', $this->clientID)
        ->where('workspace_id', $this->ws_id)
        ->where('archive', 0)
        ->with(['product_category']);
        
        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($category)) {
            $query->where('prod_category', $category);
        }

        if (!empty($status)) {
            $query->where('product_status', $status);
        }

        if (!empty($ranging)) {
            $query->where('product_ranging', $ranging);
        }

        if (!empty($tags) && is_array($tags)) {
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('prod_tags', $tag);
                }
            });
        }
        $products = $query->orderBy('favorite', 'desc')
        ->latest('updated_at')
        ->paginate($perPage)
        ->appends([
            'search' => $search,
            'perPage' => $perPage,
            'view' => $viewType,
            'category' => $category,
            'status' => $status,
            'ranging' => $ranging
        ]);
        foreach ($products as $key => $product) {
            if($product->prod_tags){
                $ingArray = $product->prod_tags;
                $tags = DB::table('product_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $products[$key]->prod_tags = implode(', ', $tags);
            }else{
                $products[$key]->prod_tags = "";
            }
        }  

        $totalCount = Product::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->count();
        $PageCount = $request->input('page',1);
        $start = ($PageCount - 1) * $perPage + 1;
        $end = min($PageCount * $perPage, $totalCount);
        if ($request->filled('search') || $request->filled('category') || $request->filled('status') || $request->filled('ranging') || $request->filled('tags')) {
            if ($products->total() > 0) {
                $foot_note =  "Showing {$start} to {$end} of {$products->total()} entries (filtered from {$totalCount} total entries)";
            }else{
                $foot_note =  "No entries found.";
            }
        }else{
            if ($totalCount > 0) {
                $foot_note =  "Showing {$start} to {$end} of {$totalCount} entries";
            } else {
                $foot_note =  "No entries found.";
            }    
        }
    
        $paginationLinks = $products->onEachSide(1)->links('backend.product.pagination')->render();
        if ($request->ajax()) {
            // Return the product list view and pagination as part of the response
            return response()->json([
                'data' => view('backend.product-views.partials.product-view', compact('products'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
                'foot_note' => $foot_note
            ]);
        }
    }

    public function rawmaterial_views(Request $request){
        $prod_status = get_rawmaterial_status_array();
        $prod_ranging = get_rawmaterial_range_array();
        $categories = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $tags = Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();

        $perPage = $request->input('perPage', 10); // Default value is 10
        $viewType = $request->input('view', 'grid');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $user_role = $this->role_id;
        $permission = [];
        // Fetch products with pagination
        $lists = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('archive',0)->orderBy('favorite','desc')->with('supplier')->latest('updated_at')->paginate($perPage);
        $paginationLinks = $lists->appends(['view' => $viewType])->onEachSide(1)->links('backend.ingredient.pagination')->render();
        return view('backend.product-views.rawmaterial-grid-view', compact('lists', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','tags'));
    }

    public function rawmaterial_search(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10); // Default to 10 if not provided
        $viewType = $request->input('view', 'list');
        // $session = $request->session()->all();
        $clientID = $this->clientID;
        $ws_id = $this->ws_id; 
        $user_role = $this->role_id;
        $permission = [];

        $category = $request->input('category');
        $status = $request->input('status');
        $ranging = $request->input('ranging');
        $tags = $request->input('tags');

        $query = Ingredient::where('client_id', $this->clientID)
        ->where('workspace_id', $this->ws_id)
        ->where('archive', 0)
        ->with(['raw_category']);

        
        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($status)) {
            $query->where('raw_material_status', $status);
        }

        if (!empty($ranging)) {
            $query->where('raw_material_ranging', $ranging);
        }

        if (!empty($tags) && is_array($tags)) {
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('ing_tags', $tag);
                }
            });
        }
        $lists = $query->orderBy('favorite', 'desc')
        ->latest('updated_at')
        ->paginate($perPage)
        ->appends([
            'search' => $search,
            'perPage' => $perPage,
            'view' => $viewType,
            'category' => $category,
            'status' => $status,
            'ranging' => $ranging
        ]);
        foreach ($lists as $key => $ingredient) {
            if($ingredient->ing_tags){
                $ingArray = json_decode($ingredient->ing_tags);
                $tags = DB::table('rawmaterial_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $lists[$key]->ing_tags = implode(', ', $tags);
            }else{
                $lists[$key]->ing_tags = "";
            }
        }
        $totalCount = Ingredient::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->count();
        $PageCount = $request->input('page',1);
        $start = ($PageCount - 1) * $perPage + 1;
        $end = min($PageCount * $perPage, $totalCount);
        if ($request->filled('search') || $request->filled('category') || $request->filled('status') || $request->filled('ranging') || $request->filled('tags')) {
            if ($lists->total() > 0) {
                $foot_note =  "Showing {$start} to {$end} of {$lists->total()} entries (filtered from {$totalCount} total entries)";
            }else{
                $foot_note =  "No entries found.";
            }
        }else{
            if ($totalCount > 0) {
                $foot_note =  "Showing {$start} to {$end} of {$totalCount} entries";
            } else {
                $foot_note =  "No entries found.";
            }    
        }

        $paginationLinks = $lists->onEachSide(1)->links('backend.ingredient.pagination')->render();
        if ($request->ajax()) {
            // Return the product list view and pagination as part of the response
            return response()->json([
                'data' => view('backend.product-views.partials.rawmaterial-view', compact('lists'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
                'foot_note' => $foot_note
            ]);
        }
    }


    // public function product_search(Request $request)
    // {
    //     $search = $request->input('search', '');
    //     $perPage = $request->input('perPage', 25); // Default to 25 if not provided
    //     $viewType = $request->input('view', 'list');
    //     $value = $request->input('id');
    //     $category = $request->input('category');
    //     $status = $request->input('status');
    //     $ranging = $request->input('ranging');
    //     $tags = $request->input('tags');
        
    //     // Fetch paginated results using the search term
    //     $query = Product::where('client_id', $this->clientID)
    //     ->where('workspace_id', $this->ws_id)
    //     ->where('archive', 0)
    //     ->with(['product_category']);

    //     if (!empty($category)) {
    //         $query->where('prod_category', $category);
    //     }

    //     if (!empty($status)) {
    //         $query->where('product_status', $status);
    //     }

    //     if (!empty($ranging)) {
    //         $query->where('product_ranging', $ranging);
    //     }

    //     if (!empty($tags) && is_array($tags)) {
    //         $query->where(function ($q) use ($tags) {
    //             foreach ($tags as $tag) {
    //                 $q->orWhereJsonContains('prod_tags', $tag);
    //             }
    //         });
    //     }

    //     // Apply dynamic filter based on module
    //     // switch ($module) {
    //     //     case 'category':
    //     //         $query->where('prod_category', $value);
    //     //         break;

    //     //     case 'status':
    //     //         $query->where('product_status', $value);
    //     //         break;

    //     //     case 'ranging':
    //     //         $query->where('product_ranging', $value);
    //     //         break;

    //     //     case 'tags':
    //     //         $query->where(function ($q) use ($value) {
    //     //             foreach ($value as $tag) {
    //     //                 $q->orWhereJsonContains('prod_tags', $tag);
    //     //             }
    //     //         });
    //     //         break;
    //     // }

    //     $products = $query->orderBy('favorite', 'desc')
    //     ->latest('created_at')
    //     ->paginate($perPage)
    //     ->appends([
    //         'search' => $search,
    //         'perPage' => $perPage,
    //         'view' => $viewType,
    //     ]);
    //     foreach ($products as $key => $product) {
    //         if($product->prod_tags){
    //             $ingArray = $product->prod_tags;
    //             $tags = DB::table('product_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
    //             $products[$key]->prod_tags = implode(', ', $tags);
    //         }else{
    //             $products[$key]->prod_tags = "";
    //         }
    //     }
    //     $paginationLinks = $products->onEachSide(1)->links('backend.product.pagination')->render();
    //     if ($request->ajax()) {
    //         // Return the product list view and pagination as part of the response
    //         return response()->json([
    //             'data' => view('backend.product-views.partials.product-view', compact('products'))->render(),
    //             'pagination' => $paginationLinks, // Include pagination links for AJAX
    //         ]);
    //     }
    // }

    // public function ingredient_search(Request $request)
    // {
    //     $search = $request->input('search', '');
    //     $perPage = $request->input('perPage', 25); // Default to 25 if not provided
    //     $viewType = $request->input('view', 'list');
    //     $value = $request->input('id');
    //     $category = $request->input('category');
    //     $status = $request->input('status');
    //     $ranging = $request->input('ranging');
    //     $tags = $request->input('tags');
        
    //     // Fetch paginated results using the search term
    //     $query = Ingredient::where('client_id', $this->clientID)
    //     ->where('workspace_id', $this->ws_id)
    //     ->where('archive', 0)
    //     ->with(['raw_category']);

    //     if (!empty($category)) {
    //         $query->where('category', $category);
    //     }

    //     if (!empty($status)) {
    //         $query->where('raw_material_status', $status);
    //     }

    //     if (!empty($ranging)) {
    //         $query->where('raw_material_ranging', $ranging);
    //     }

    //     if (!empty($tags) && is_array($tags)) {
    //         $query->where(function ($q) use ($tags) {
    //             foreach ($tags as $tag) {
    //                 $q->orWhereJsonContains('ing_tags', $tag);
    //             }
    //         });
    //     }
    //     $lists = $query->orderBy('favorite', 'desc')
    //     ->latest('created_at')
    //     ->paginate($perPage)
    //     ->appends([
    //         'search' => $search,
    //         'perPage' => $perPage,
    //         'view' => $viewType,
    //     ]);
    //     foreach ($lists as $key => $ingredient) {
    //         if($ingredient->ing_tags){
    //             $ingArray = json_decode($ingredient->ing_tags);
    //             $tags = DB::table('rawmaterial_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
    //             $lists[$key]->ing_tags = implode(', ', $tags);
    //         }else{
    //             $lists[$key]->ing_tags = "";
    //         }
    //     }
    //     $paginationLinks = $lists->onEachSide(1)->links('backend.ingredient.pagination')->render();
    //     if ($request->ajax()) {
    //         // Return the product list view and pagination as part of the response
    //         return response()->json([
    //             'data' => view('backend.product-views.partials.rawmaterial-view', compact('lists'))->render(),
    //             'pagination' => $paginationLinks, // Include pagination links for AJAX
    //         ]);
    //     }
    // }




}