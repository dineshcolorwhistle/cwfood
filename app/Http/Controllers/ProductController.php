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

class ProductController extends Controller
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

        return view('backend.product.grid-view', compact('products', 'search', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','tags'));
    }

    /**
     * Search for products based on a search term.
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 25); // Default to 25 if not provided
        $viewType = $request->input('view', 'list');
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Products','Products Read']);
        }else{
            $permission = [];
        }

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
            $tags = array_filter(array_map('intval', $tags));
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('prod_tags', $tag); // integer
                    $q->orWhereJsonContains('prod_tags', (string) $tag); // string
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
        ])->appends(request()->only('tags'));
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
                'data' => view('backend.product.partials.product-view', compact('products', 'viewType','clientID','ws_id','permission','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
                'foot_note' => $foot_note
            ]);
        }

        return view('backend.product.index', compact('products', 'search', 'viewType', 'perPage','clientID','ws_id','permission','user_role'));
    }

    /**
     * Delete a product and its related data.
     */
    public function destroy(Product $product)
    {
        try {
            if($product->archive == 0){
                $product->update(['archive' => 1]);
                return response()->json(['success' => true,'message' => 'Product moved to archive status']);
            }

            DB::beginTransaction();
            // Delete related data
            $product->productIngredients()->delete();
            $product->prodLabours()->delete();
            $product->prodMachinery()->delete();
            $product->prodPackaging()->delete();
            $product->productLabels()->delete();
            $product->prodFreights()->delete();
            
            // Delete images from image_library
            $images = $product->imageLibrary()->get();

            foreach ($images as $image) {
                // Delete physical file
                $imageName = $image->image_name;
                if ($image->folder_path && !empty($imageName) && $imageName != '.') {
                    $fullPath = public_path($image->folder_path . '/' . $image->image_name);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }

                    // Delete database record
                    $image->delete();
                }
            }

            // Delete the product
            $product->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product and all related data deleted successfully.'
                ]);
            }

            return redirect()->route('products.index')
                ->with('success', 'Product and all related data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Product deletion error: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting product: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    public function unarchive(Product $product)
    {
        try {
            $product->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Product unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $ingredients = Ingredient::all();
        $labours = Labour::all();
        $machinery = Machinery::all();
        $packaging = Packaging::all();
        $ingredients = Ingredient::all();
        $product = new Product();
        $categories = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        return view('backend.product_v2.form', [
            'step' => 1,
            'product' => $product,
            'ingredients' => $ingredients,
            'labours' => $labours,
            'machinery' => $machinery,
            'packaging' => $packaging,
            'categories' => $categories,
            'prod_status' => get_products_status_array(),
            'prod_ranging' => get_products_range_array(),
        ]);
    }

    /**
     * Preprocess numeric input fields to remove commas and ensure they are numeric.
     */
    private function preprocessNumericInput(array $input, array $fieldsToClean): array
    {
        foreach ($fieldsToClean as $field) {
            // Check if the field contains a wildcard for array processing
            if (str_contains($field, '.*')) {
                // Split the field name into array parts
                [$arrayName, $arrayField] = explode('.*', $field);

                // Check if the array exists in input
                if (isset($input[$arrayName]) && is_array($input[$arrayName])) {
                    // Process each item in the array
                    foreach ($input[$arrayName] as $key => &$item) {
                        // Remove the leading dot from arrayField
                        $fieldName = ltrim($arrayField, '.');

                        if (isset($item[$fieldName])) {
                            // Remove commas from the value
                            $cleanValue = str_replace(',', '', $item[$fieldName]);
                            // Check if the cleaned value is numeric, if not set to 0
                            $item[$fieldName] = is_numeric($cleanValue) && !empty($cleanValue)
                                ? $cleanValue
                                : 0;
                        } else {
                            $item[$fieldName] = 0;
                        }
                    }
                }
            } else {
                // Handle regular non-array fields
                if (isset($input[$field])) {
                    // Remove commas from the value
                    $cleanValue = str_replace(',', '', $input[$field]);
                    // Check if the cleaned value is numeric, if not set to 0
                    $input[$field] = is_numeric($cleanValue) && !empty($cleanValue)
                        ? $cleanValue
                        : 0;
                } else {
                    $input[$field] = 0;
                }
            }
        }

        return $input;
    }

    /**
     * Store a newly created product in Database.
     */
    public function store(Request $request)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        $numericFields = [
            'weight_ind_unit_g',
            'weight_retail_unit_g',
            'weight_carton_g',
            'weight_pallet_g',
            'count_retail_units_per_carton',
            'count_cartons_per_pallet',
            'price_ind_unit',
            'price_retail_unit',
            'price_carton',
            'price_pallet',
        ];

        $cleanedInput = $this->preprocessNumericInput($request->all(), $numericFields);

        $validator = Validator::make($cleanedInput, [
            'prod_name' => 'required|max:50',
            'prod_sku' => [
                'required',
                'string',
                Rule::unique('products', 'prod_sku')
                    ->where(function ($query) {
                        return $query->where('client_id', session('client'))
                                    ->where('workspace_id', session('workspace'));
                    })
                ],
            'description_short' => 'nullable|max:300',
            'description_long' => 'nullable|max:2000',
            'barcode_gs1' => 'nullable|max:50',
            'barcode_gtin14' => 'nullable|max:50',
            'weight_ind_unit_g' => 'required|numeric|gt:0',
            'weight_retail_unit_g' => 'required|numeric|gt:0',
            'weight_carton_g' => 'nullable|numeric|min:0',
            'weight_pallet_g' => 'nullable|numeric|min:0',
            'count_ind_units_per_retail' => 'required|integer|min:1',
            'count_retail_units_per_carton' => 'nullable|integer|min:0',
            'count_cartons_per_pallet' => 'nullable|integer|min:0',
            'price_ind_unit' => 'nullable|numeric|min:0',
            'price_retail_unit' => 'nullable|numeric|min:0',
            'price_carton' => 'nullable|numeric|min:0',
            'price_pallet' => 'nullable|numeric|min:0',
            'prod_tags' => 'nullable|array',
            'prod_tags.*' => 'string|max:255',
            'prod_category' => 'nullable|numeric',
            'image_file' => 'nullable|array',
            'image_file.*' => 'nullable|mimes:jpg,jpeg,png,bmp|max:5120',
            'productDefault' => 'nullable',
            'product_status' => 'nullable|string',
            'product_ranging' => 'nullable|string',

        ], [
            // Custom error messages
            'prod_name.required' => 'Product name is required.',
            'prod_name.max' => 'Product name cannot be longer than 255 characters.',
            'prod_sku.required' => 'SKU is required.',
            'prod_sku.unique' => 'The SKU has already been taken for another product.',
            'description_short.max' => 'Short description cannot be longer than 1000 characters.',
            'description_long.max' => 'Long description cannot be longer than 2000 characters.',
            'weight_ind_unit_g.required' => 'Weight per individual unit is required.',
            'weight_ind_unit_g.numeric' => 'Weight per individual unit must be a number.',
            'weight_ind_unit_g.min' => 'Weight per individual unit must be greater than 0.',
            'weight_retail_unit_g.required' => 'Retail unit weight is required.',
            'weight_retail_unit_g.numeric' => 'Retail unit weight must be a number.',
            'weight_retail_unit_g.min' => 'Retail unit weight must be greater than 0.',
            'count_ind_units_per_retail.required' => 'Individual units per retail unit is required.',
            'price_ind_unit.required' => 'Price per individual unit is required.',
            'price_ind_unit.numeric' => 'Price per individual unit must be a number.',
            'price_ind_unit.min' => 'Price per individual unit must be at least 0.',
            'price_retail_unit.required' => 'Price per retail unit is required.',
            'price_retail_unit.numeric' => 'Price per retail unit must be a number.',
            'price_retail_unit.min' => 'Price per retail unit must be at least 0.',
            'prod_tags.array' => 'Product tags must be an array.',
            'prod_tags.*.string' => 'Each tag must be a string.',
            'prod_tags.*.max' => 'Each tag must not exceed 255 characters.',
            'image_file.array' => 'Image files must be an array.',
            'image_file.*.mimes' => 'Each image file must be of type: jpg, jpeg, png, bmp.',
            'image_file.*.max' => 'Each image file must not exceed 5MB in size.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $session = $request->session()->all();
        $clientID = (int)$session['client'];
        $workspaceID = (int)$session['workspace'];
        $product_count = Product::where('client_id',$clientID)->count();
        $client_plan = ClientSubscription::where('client_id',$clientID)->with(['plan'])->first();
        if($client_plan && $client_plan->plan->max_skus > $product_count){
            $validated = $validator->validated();
            $validated['created_by'] = $this->user_id;
            $validated['updated_by'] = $this->user_id;
            $validated['prod_tags'] = $request->input('prod_tags', []);
            $validated['sub_receipe'] = (int) $request->input('sub_receipe');
            $validated['client_id'] = $clientID;
            $validated['workspace_id'] = $workspaceID;
            $product = Product::create($validated);
            if (isset($_FILES['image_file']) && count($_FILES['image_file']['name']) > 0 && !empty($_FILES['image_file']['name'][0]) && $_FILES['image_file']['name'][0] != '.') {
                $filepath = "assets/$clientID/$workspaceID/product/$product->id";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    $defaultImage = ($request->input('default_image'))? (int)$request->input('default_image') : 1;
                    $this->insert_product_images($product->prod_sku, $filepath, $imageArray, $defaultImage, $product->id);
                    $product->update(['prod_image' => $defaultImage]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'next_url' => route('products.edit', ['product' => $product->id, 'step' => 2]),
                'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 1]),
                // 'next_url'     => $isProductV2 
                //                     ? route('product_v2.edit', ['product' => $product->id]) 
                //                     : route('products.edit', ['product' => $product->id, 'step' => 2]),
                // 'redirect_url' => $isProductV2 
                //                 ? route('product_v2.edit', ['product' => $product->id]) 
                //                 : route('products.edit', ['product' => $product->id, 'step' => 1]),
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => ($client_plan) ? 'Already product limit reached. Contact batchbase admin' : 'Your company does not have an active subscription plan. Please contact your administrator.'
            ]);
        }
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

    public function get_allergen_summary(Product $product){
        
    }

    /**
     * Update the specified product in Database(Step 1).
     */
    public function update(Request $request, Product $product)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        $numericFields = [
            'weight_ind_unit_g',
            'weight_retail_unit_g',
            'weight_carton_g',
            'weight_pallet_g',
            'count_retail_units_per_carton',
            'count_cartons_per_pallet',
            'price_ind_unit',
            'price_retail_unit',
            'price_carton',
            'price_pallet',
        ];

        $cleanedInput = $this->preprocessNumericInput($request->all(), $numericFields);
        $validator = Validator::make(
            $cleanedInput,
            [
                'prod_name' => 'required|max:255',
                'prod_sku' => [
                                'required',
                                'string',
                                Rule::unique('products', 'prod_sku')
                                    ->ignore( $product->id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', session('client'))
                                                    ->where('workspace_id', session('workspace'));
                                    })
                                ],
                'description_short' => 'nullable|max:1000',
                'description_long' => 'nullable|max:2000',
                'barcode_gs1' => 'nullable|max:255',
                'barcode_gtin14' => 'nullable|max:255',
                'weight_ind_unit_g' => 'required|numeric|gt:0',
                'weight_retail_unit_g' => 'required|numeric|gt:0',
                'weight_carton_g' => 'nullable|numeric|min:0',
                'weight_pallet_g' => 'nullable|numeric|min:0',
                'count_ind_units_per_retail' => 'required|integer|min:1',
                'count_retail_units_per_carton' => 'required|integer|min:0',
                'count_cartons_per_pallet' => 'required|integer|min:0',
                'price_ind_unit' => 'nullable|numeric|min:0',
                'price_retail_unit' => 'required|numeric|min:0',
                'price_carton' => 'nullable|numeric|min:0',
                'price_pallet' => 'nullable|numeric|min:0',
                'prod_tags' => 'nullable|array',
                'prod_tags.*' => 'string|max:255',
                'prod_category' => 'nullable|numeric',
                'image_file' => 'nullable|array',
                'image_file.*' => 'nullable|mimes:jpg,jpeg,png,bmp|max:5120',
                'productDefault' => 'nullable',
                'product_status' => 'nullable|string',
                'product_ranging' => 'nullable|string',
            ],
            [
                // Custom error messages
                'prod_name.required' => 'Product name is required.',
                'prod_name.max' => 'Product name cannot be longer than 255 characters.',
                'prod_sku.required' => 'SKU is required.',
                'prod_sku.unique' => 'The SKU has already been taken for another product.',
                'description_short.max' => 'Short description cannot be longer than 1000 characters.',
                'description_long.max' => 'Long description cannot be longer than 2000 characters.',
                'weight_ind_unit_g.required' => 'Weight per individual unit is required.',
                'weight_ind_unit_g.numeric' => 'Weight per individual unit must be a number.',
                'weight_ind_unit_g.min' => 'Weight per individual unit must be greater than 0.',
                'weight_retail_unit_g.required' => 'Retail unit weight is required.',
                'weight_retail_unit_g.numeric' => 'Retail unit weight must be a number.',
                'weight_retail_unit_g.min' => 'Retail unit weight must be greater than 0.',

                'count_ind_units_per_retail.required' => 'Individual units per retail unit is required.',
                'price_ind_unit.required' => 'Price per individual unit is required.',
                'price_ind_unit.numeric' => 'Price per individual unit must be a number.',
                'price_ind_unit.min' => 'Price per individual unit must be at least 0.',
                'price_retail_unit.required' => 'Price per retail unit is required.',
                'price_retail_unit.numeric' => 'Price per retail unit must be a number.',
                'price_retail_unit.min' => 'Price per retail unit must be at least 0.',

                'prod_tags.array' => 'Product tags must be an array.',
                'prod_tags.*.string' => 'Each tag must be a string.',
                'prod_tags.*.max' => 'Each tag must not exceed 255 characters.',
                'image_file.array' => 'Image files must be an array.',
                'image_file.*.mimes' => 'Each image file must be of type: jpg, jpeg, png, bmp.',
                'image_file.*.max' => 'Each image file must not exceed 5MB in size.',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();  
        $validated['updated_by'] = $this->user_id;
        $validated['prod_tags'] = $request->input('prod_tags', []);
        $validated['sub_receipe'] = (int) $request->input('sub_receipe');
        $product->update($validated);
        Product_edit_lock::where('sku_id',$product->id)->delete();  

        // Handle image uploads
        $defaultImage = !empty((int)$request->input('default_image')) ? (int)$request->input('default_image') : 1;

        // Check if files were uploaded
        if ($request->hasFile('image_file')) {
            $session = $request->session()->all();
            $clientID = (int)$session['client'];
            $workspaceID = (int)$session['workspace'];
            $filepath = "assets/$clientID/$workspaceID/product/$product->id";

            // Prepare files array for upload_multiple_files function
            $files = [];
            foreach ($request->file('image_file') as $key => $file) {
                $files['name'][] = $file->getClientOriginalName();
                $files['size'][] = $file->getSize();
                $files['tmp_name'][] = $file->getPathname();
                $files['type'][] = $file->getClientMimeType();
            }
            $image_response = upload_multiple_files($files, $filepath);
            if ($image_response['status'] == true) {

                $imageArray = $image_response['final_array'];
                $this->update_product_images($product->prod_sku, $filepath, $imageArray, $defaultImage, $product->id);
                $product->update(['prod_image' => $defaultImage]);
            }
        } else if ($product->prod_image != null && $product->prod_image != $defaultImage) {
            // Update default image selection
            image_library::where('module', 'product')
                ->where('module_id', $product->id)
                ->whereIn('default_image', [1])
                ->update(['default_image' => 0]);

            image_library::where('module', 'product')
                ->where('module_id', $product->id)
                ->where('image_number', $defaultImage)
                ->update(['default_image' => 1]);

            $product->update(['prod_image' => $defaultImage]);
        }

        self::create_subreceipe($product);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'next_url' => route('products.edit', ['product' => $product->id, 'step' => 2]),
            'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 1])
            // 'next_url' => $isProductV2
            //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 2])
            //     : route('products.edit', ['product' => $product->id, 'step' => 2]),
            // 'redirect_url' => $isProductV2
            //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 1])
            //     : route('products.edit', ['product' => $product->id, 'step' => 1]),
        ]);
    }

    /**
     * Insert product images into the database.
     */
    private function insert_product_images($sku, $filepath, $imageArray, $defaultImage, $productId)
    {
        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = "product";
            $item->module_id = $productId;
            $item->image_number = ++$key;
            $item->image_name = $value['name'];
            if(sizeof($imageArray) == 1){
                $item->default_image = true;
            }else{
                $item->default_image = ((int)$key == $defaultImage) ? true : false;
            }
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
    }

    /**
     * Update product images in the database.
     */
    private function update_product_images($sku, $filepath, $imageArray, $defaultImage, $productId)
    {
        $image_count = image_library::where('module', 'product')
            ->where('module_id', $productId)
            ->count();

        if ($defaultImage > $image_count) {
            image_library::where('module', 'product')
                ->where('module_id', $productId)
                ->whereIn('default_image', [1])
                ->update(['default_image' => 0]);
        }

        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = "product";
            $item->module_id = $productId;
            $item->image_number = ++$image_count;
            $item->image_name = $value['name'];
            $item->default_image = ((int)$image_count == $defaultImage) ? true : false;
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
    }

    /**
     * Get tags for a product.
     */
    public function getTags(Request $request)
    {
        $product = $request->input('product_id') ? Product::find($request->input('product_id')) : new Product();
        $existingTags = Product_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $selectedTags = $product->prod_tags ?? []; // Fetch tags linked to this product, default to an empty array if null
        return response()->json([
            'tags' => $existingTags,
            'selectedTags' => $selectedTags
        ]);
    }

    /**
     * Save tags to the database.
     */
    private function saveTags(array $tagNames)
    {
        $tags = collect();
        foreach ($tagNames as $tagName) {
            $tag = ProdTag::firstOrCreate(['name' => $tagName]);
            $tags->push($tag);
        }
        return $tags;
    }

    /**
     * Create a new tag.
     */
    public function createTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:prod_tags',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $tag = ProdTag::create(['name' => $request->input('name')]);
        return response()->json($tag);
    }


    /**
     * Update step 2 of the product creation process.
     */
    public function updateStep2(Request $request, Product $product)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        // if (!$request->has('IngFields') || empty($request->input('IngFields'))) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['Please provide at least one set of ingredient details.'],
        //     ], 422);
        // }
        $numericFields = [
            'recipe_oven_temp',
            'batch_baking_loss_percent',
            'batch_waste_percent',
            'serv_per_package',
            'serv_size_g',
            'energy_kJ_per_100g',
            'protein_g_per_100g',
            'fat_total_g_per_100g',
            'fat_saturated_g_per_100g',
            'carbohydrate_g_per_100g',
            'sugar_g_per_100g',
            'sodium_mg_per_100g',
            'energy_kJ_per_serve',
            'protein_g_per_serve',
            'fat_total_g_per_serve',
            'fat_saturated_g_per_serve',
            'carbohydrate_g_per_serve',
            'sugar_g_per_serve',
            'sodium_mg_per_serve',
            'batch_after_waste_g',
            'IngFields.*.quantity_weight'
        ];

        $cleanedInput = $this->preprocessNumericInput($request->all(), $numericFields);
        
        $validator = Validator::make($cleanedInput, [
            'product_id' => 'required|exists:products,id',
            'recipe_method' => 'nullable',
            'recipe_notes' => 'nullable',
            'recipe_oven_temp' => 'nullable|numeric|min:0',
            'recipe_oven_temp_unit' => 'nullable|in:C,F,K',
            'recipe_oven_time' => [
                'nullable',
                'regex:/^\d{2}:\d{2}:\d{2}$/',
            ],
            'batch_baking_loss_percent' => 'required|numeric',
            'serv_per_package' => 'required|integer|min:1',
            'serv_size_g' => 'required|numeric|min:0',
            'IngFields.*.ing_id' => 'nullable|exists:ingredients,id',
            'IngFields.*.quantity_weight' => 'nullable|numeric|min:0',
            'IngFields.*.component' => 'nullable',
            'IngFields.*.kitchen_comments' => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'The selected product ID is invalid.',
            'recipe_oven_temp.numeric' => 'Oven temperature must be a valid number.',
            'recipe_oven_temp.min' => 'Oven temperature must be at least 0.',
            'recipe_oven_temp_unit.in' => 'Oven temperature unit must be one of the following: C, F, K.',
            'recipe_oven_time.regex' => 'The time must be in HH:MM:SS format (e.g., 01:05:30).',
            'batch_baking_loss_percent.required' => 'Blended loss (%) is required.',
            'batch_baking_loss_percent.numeric' => 'Blended loss (%) must be a valid number.',
            'batch_baking_loss_percent.min' => 'Blended loss (%) must be at least 0.',

            'serv_per_package.required' => 'Serving per package is required.',
            'serv_per_package.integer' => 'Serving per package must be a valid integer.',
            'serv_per_package.min' => 'Serving per package must be at least 1.',
            'serv_size_g.required' => 'Serving size (g) is required.',
            'serv_size_g.numeric' => 'Serving size (g) must be a valid number.',
            'serv_size_g.min' => 'Serving size (g) must be at least 0.',
            'IngFields.*.ing_id.required' => 'Ingredient selection is required.',
            'IngFields.*.ing_id.exists' => 'The selected ingredient is invalid.',
            'IngFields.*.quantity_weight.required' => 'Quantity weight is required.',
            'IngFields.*.quantity_weight.numeric' => 'Quantity weight must be a valid number.',
            'IngFields.*.quantity_weight.min' => 'Quantity weight must be at least 0.',
            'IngFields.*.component.required' => 'Component is required.',
        ]);


        //  dd($validator);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $uniqueErrors = array_unique($errors);
            return response()->json([
                'success' => false,
                'errors' => $uniqueErrors,
            ], 422);
        }

        DB::beginTransaction();
        try {

            if ($request->has('IngFields') && !empty($request->input('IngFields'))) {
                $recipevalidator = Validator::make($cleanedInput, [
                    'IngFields.*.ing_id' => 'required|exists:ingredients,id',
                    'IngFields.*.quantity_weight' => 'required|numeric|min:0',
                    'IngFields.*.component' => 'required',
                ], [
                    'IngFields.*.ing_id.required' => 'Ingredient selection is required.',
                    'IngFields.*.ing_id.exists' => 'The selected ingredient is invalid.',
                    'IngFields.*.quantity_weight.required' => 'Quantity weight is required.',
                    'IngFields.*.quantity_weight.numeric' => 'Quantity weight must be a valid number.',
                    'IngFields.*.quantity_weight.min' => 'Quantity weight must be at least 0.',
                    'IngFields.*.component.required' => 'Please complete quantity, units and component.',
                ]);

                if ($recipevalidator->fails()) {
                    $errors = $recipevalidator->errors()->all();
                    $uniqueErrors = array_unique($errors);
                    return response()->json([
                        'success' => false,
                        'errors' => $uniqueErrors,
                    ], 422);
                }
            }

            // Process product-specific fields
            if ($request->has('IngFields') && !empty($request->input('IngFields'))) {
                $beforelossArray = array_column($request->input('IngFields'),'quantity_weight');
                $beforelossAmount = array_sum(array_map(fn($value) => (int) str_replace(',', '', $value), $beforelossArray));
            }else{
                $beforelossAmount = null;
            }
            
            $productData = [
                // Non-numeric fields remain unchanged
                'recipe_method' => $request->recipe_method,
                'recipe_notes' => $request->recipe_notes,
                'recipe_oven_temp_unit' => $request->recipe_oven_temp_unit,
                'labelling_ingredients' => $request->labelling_ingredients,
                'labelling_allergens' => $request->labelling_allergens,
                'labelling_may_contain' => $request->labelling_may_contain,
                'updated_by' => $this->user_id,

                // Numeric fields use cleaned values
                'recipe_oven_temp' => $cleanedInput['recipe_oven_temp'] ?? null,
                'batch_baking_loss_percent' => $cleanedInput['batch_baking_loss_percent'] ?? null,
                'batch_waste_percent' => $cleanedInput['batch_waste_percent'] ?? null,
                'serv_per_package' => $cleanedInput['serv_per_package'] ?? null,
                'serv_size_g' => $cleanedInput['serv_size_g'] ?? null,

                // Nutritional values per 100g
                'energy_kJ_per_100g' => $cleanedInput['energy_kJ_per_100g'] ?? null,
                'protein_g_per_100g' => $cleanedInput['protein_g_per_100g'] ?? null,
                'fat_total_g_per_100g' => $cleanedInput['fat_total_g_per_100g'] ?? null,
                'fat_saturated_g_per_100g' => $cleanedInput['fat_saturated_g_per_100g'] ?? null,
                'carbohydrate_g_per_100g' => $cleanedInput['carbohydrate_g_per_100g'] ?? null,
                'sugar_g_per_100g' => $cleanedInput['sugar_g_per_100g'] ?? null,
                'sodium_mg_per_100g' => $cleanedInput['sodium_mg_per_100g'] ?? null,

                // Nutritional values per serve
                'energy_kJ_per_serve' => $cleanedInput['energy_kJ_per_serve'] ?? null,
                'protein_g_per_serve' => $cleanedInput['protein_g_per_serve'] ?? null,
                'fat_total_g_per_serve' => $cleanedInput['fat_total_g_per_serve'] ?? null,
                'fat_saturated_g_per_serve' => $cleanedInput['fat_saturated_g_per_serve'] ?? null,
                'carbohydrate_g_per_serve' => $cleanedInput['carbohydrate_g_per_serve'] ?? null,
                'sugar_g_per_serve' => $cleanedInput['sugar_g_per_serve'] ?? null,
                'sodium_mg_per_serve' => $cleanedInput['sodium_mg_per_serve'] ?? null,

                'batch_after_waste_g' => $cleanedInput['batch_after_waste_g'] ?? null,
                'batch_initial_weight_g' =>$beforelossAmount ?? null
            ];

            // Remove null values if database doesn't accept them
            $productData = array_filter($productData, function ($value) {
                return $value !== null;
            });

            // Convert time format to seconds before saving
            if (!empty($request->recipe_oven_time)) {
                try {
                    list($hours, $minutes, $seconds) = explode(':', $request->recipe_oven_time);
                    $productData['recipe_oven_time'] = (intval($hours) * 3600) + (intval($minutes) * 60) + intval($seconds);
                } catch (\Exception $e) {
                    $productData['recipe_oven_time'] = null;
                }
            } else {
                $productData['recipe_oven_time'] = null;
            }
            
            // dd($productData['recipe_oven_time']);

            // Initialize nutritional totals
            $totalNutrition = [
                'energy_kj' => 0,
                'protein_g' => 0,
                'fat_total_g' => 0,
                'fat_saturated_g' => 0,
                'carbohydrate_g' => 0,
                'sugars_g' => 0,
                'sodium_mg' => 0
            ];

            $totalWeight = 0;
            $ingr_order = 1;

            if ($request->has('IngFields') && !empty($request->input('IngFields'))) {
                // Process ingredients and calculate nutritional values
                foreach ($request->IngFields as $key => $ingredientData) {
                    $ingredient = Ingredient::findOrFail($ingredientData['ing_id']);

                    // Calculate quantity after losses
                    $quantity = floatval(str_replace(',', '', $ingredientData['quantity_weight']));
                    $lossPercent = floatval(str_replace(',', '', $request->batch_baking_loss_percent ?? 0));
                    $wastePercent = floatval(str_replace(',', '', $request->batch_waste_percent ?? 0));
                    $quantityAfterLoss = $quantity * (1 - ($lossPercent + $wastePercent) / 100);

                    $totalWeight += $quantityAfterLoss;

                    // Calculate nutritional values for this ingredient
                    foreach ($totalNutrition as $nutrient => $value) {
                        $totalNutrition[$nutrient] += ($ingredient->$nutrient * $quantityAfterLoss / 100);
                    }

                    // Update or create product ingredients
                    $prod_ing = ProdIngredient::updateOrCreate(
                        [
                            'id' => $ingredientData['id']
                        ],
                        [
                            'product_id' => $product->id,
                            'ing_id' => $ingredientData['ing_id'],
                            'product_sku' => $product->product_sku,
                            'ing_sku' => $ingredient->ing_sku,
                            'ing_name' => $ingredient->name_by_kitchen,
                            'quantity_weight' => $quantity,
                            'units_g_ml' => $ingredientData['units_g_ml'] ?? 'g',
                            'component' => $ingredientData['component'],
                            'kitchen_comments' => $ingredientData['kitchen_comments'] ?? null,
                            'quantity_g' => $quantity,
                            'quantity_loss_g' => $quantity * ($lossPercent / 100),
                            'quantity_waste_g' => $quantity * ($wastePercent / 100),
                            'ingredient_order' => $ingr_order,
                            'spec_grav' => $ingredient->specific_gravity,
                            'allergens' => $ingredient->allergens,
                            'peel_name' => $ingredient->ingredients_list_supplier
                        ]
                    );
                    $ingr_order++;
                }
            }
            // Update product
            $product->update($productData);
            Product_edit_lock::where('sku_id',$product->id)->delete();

            DB::commit();

            $ingrs = ProdIngredient::with('ingredient:id,ingredients_list_supplier')
                    ->where('product_id', $product->id)->orderByDesc('quantity_weight')->get()
                    ->pluck('ingredient.ingredients_list_supplier')->toArray();
            if(count($ingrs) > 0){
                $filteredIngredients = implode(', ', array_filter($ingrs));
                $product->update(['labelling_ingredients' => $filteredIngredients, 'labelling_ingredients_override' => $filteredIngredients]);
            }

            self::create_subreceipe($product); // Create sub receipe
            
            return response()->json([
                'success' => true,
                'message' => 'Step 2 completed successfully.',
                'next_url' => route('products.edit', ['product' => $product->id, 'step' => 3]),
                'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 2])
                // 'next_url' => $isProductV2
                //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 3])
                //     : route('products.edit', ['product' => $product->id, 'step' => 3]),
                // 'redirect_url' => $isProductV2
                //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 2])
                //     : route('products.edit', ['product' => $product->id, 'step' => 2]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error in updateStep2: ' . $e->getMessage());
            // Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create_subreceipe($product){
        $product = $product->toArray();
        if($product['sub_receipe'] == 1 && $product['sub_receipe_id'] == 0){
            $profile = ClientProfile::where('client_id', $product['client_id'])->first(); 
            $prcountry = ($profile['country'])? $profile['country'] : "Australia";
            $country = Ing_country::where('name',$prcountry)->pluck('COID');
            $sku = "RM_".$product['prod_sku'];
            $item = new Ingredient;
            $item->client_id = $product['client_id'];
            $item->workspace_id = $product['workspace_id'];
            $item->ing_sku = $sku;
            $item->name_by_kitchen = $product['prod_name'];
            $item->desc_by_kitchen = strip_tags($product['description_short']);
            $item->raw_material_description = strip_tags($product['description_long']);
            $item->name_by_supplier = $profile['company_name'];
            $item->ing_image = ((int)$product['prod_image'])? $product['prod_image'] : null;
            $item->ingredients_list_supplier  = ($product['labelling_ingredients'])? $product['labelling_ingredients'] : null;
            $item->allergens  = ($product['labelling_allergens'])? $product['labelling_allergens'] : null; 
            $item->country_of_origin = (sizeof($country) > 0)?$country[0] : 1;
            $item->purchase_units = "g";
            $item->energy_kj = $product['energy_kJ_per_100g'];
            $item->protein_g = $product['protein_g_per_100g'];
            $item->fat_total_g = $product['fat_total_g_per_100g'];
            $item->fat_saturated_g = $product['fat_saturated_g_per_100g'];
            $item->carbohydrate_g = $product['carbohydrate_g_per_100g'];
            $item->sugars_g = $product['sugar_g_per_100g'];
            $item->sodium_mg = $product['sodium_mg_per_100g'];
            $item->created_by = $this->user_id;
            $item->updated_by = $this->user_id;
            $item->save();
            Product::where('id',$product['id'])->update(['sub_receipe_id'=>$item->id]);
            if($product['prod_image']){
                $source = "assets/{$product['client_id']}/{$product['workspace_id']}/product/{$product['id']}";
                $dest =  "assets/{$product['client_id']}/{$product['workspace_id']}/raw_material/{$item->id}";
                copy_files($source, $dest);
                $imgDetails  = image_library::where('module', 'product')->where('module_id', $product['id'])->get()->toArray();
                $filepath = "assets/{$product['client_id']}/{$product['workspace_id']}/raw_material/{$item->id}";
                foreach($imgDetails as $img){
                    $img_item = new image_library;
                    $img_item->SKU = $sku;
                    $img_item->module =  "raw_material";
                    $img_item->module_id = $item->id;
                    $img_item->image_number =  $img['image_number'];
                    $img_item->image_name =  $img['image_name'];
                    $img_item->default_image =  $img['default_image'];
                    $img_item->file_format =  $img['file_format'];
                    $img_item->file_size =  $img['file_size'];
                    $img_item->folder_path = $filepath;
                    $img_item->uploaded_by = $this->user_id;
                    $img_item->last_modified_by = $this->user_id;
                    $img_item->save();
                }
            }
        }elseif ($product['sub_receipe'] == 1 && $product['sub_receipe_id'] != 0){
            $rw_id = $product['sub_receipe_id'];
            $update_data['energy_kj'] = $product['energy_kJ_per_100g'];
            $update_data['protein_g'] = $product['protein_g_per_100g'];
            $update_data['fat_total_g'] = $product['fat_total_g_per_100g'];
            $update_data['fat_saturated_g'] = $product['fat_saturated_g_per_100g'];
            $update_data['carbohydrate_g'] = $product['carbohydrate_g_per_100g'];
            $update_data['sugars_g'] = $product['sugar_g_per_100g'];
            $update_data['sodium_mg'] = $product['sodium_mg_per_100g'];
            Ingredient::where('id',$rw_id)->update($update_data);
        }elseif ($product['sub_receipe'] == 0 && $product['sub_receipe_id'] != 0){
            $rw_id = $product['sub_receipe_id'];
            $ingDetails = Ingredient::where('id', $rw_id)->first()->toArray();
            $dirPath = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$rw_id}";
            $response = all_image_remove($dirPath);
            Ingredient::where('id', $rw_id)->delete();
            image_library::where('module', 'raw_material')->where('module_id', $rw_id)->delete();
            Product::where('id',$product['id'])->update(['sub_receipe_id'=>0, 'sub_receipe_id'=>0]);
        }
        return;
    }

    /**
     * Update step 3 of the product creation process.
     */
    public function updateStep3(Request $request, Product $product)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        $validator = Validator::make($request->all(), [
            'labelling_ingredients' => 'nullable|string|max:2000',
            'labelling_allergens' => 'nullable|string|max:2000',
            'labelling_may_contain' => 'nullable|string|max:2000',
            'labelling_ingredients_override' => 'nullable|string|max:2000',
            'labelling_allergens_override' => 'nullable|string|max:2000',
            'labelling_may_contain_override' => 'nullable|string|max:2000',
            'company_factory' => 'nullable|numeric',
            'company_keyperson' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); // HTTP status 422 Unprocessable Entity
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();
            $validated['updated_by'] = $this->user_id;
            $product->update($validated);
            Product_edit_lock::where('sku_id',$product->id)->delete();

            $checkLabel = ProdLabel::where('product_id', $product->id)->first();
            if($checkLabel){
                $updated_data['rm_halal_yn'] = $request->input('rm_halal_yn');
                $updated_data['rm_halal_validated'] = $request->input('rm_halal_validated');
                $updated_data['rm_halal_certification_yn'] = $request->input('rm_halal_certification_yn');
                $updated_data['rm_kosher_yn'] = $request->input('rm_kosher_yn');
                $updated_data['rm_kosher_validated'] = $request->input('rm_kosher_validated');
                $updated_data['rm_kosher_certification_yn'] = $request->input('rm_kosher_certification_yn');
                $updated_data['rm_organic_yn'] = $request->input('rm_organic_yn');
                $updated_data['rm_organic_validated'] = $request->input('rm_organic_validated');
                $updated_data['rm_organic_certification_yn'] = $request->input('rm_organic_certification_yn');
                $updated_data['rm_bio_yn'] = $request->input('rm_bio_yn');
                $updated_data['rm_bio_validated'] = $request->input('rm_bio_validated');
                $updated_data['rm_bio_certification_yn'] = $request->input('rm_bio_certification_yn');
                $updated_data['rm_octo_yn'] = $request->input('rm_octo_yn');
                $updated_data['rm_octo_validated'] = $request->input('rm_octo_validated');
                $updated_data['rm_octo_certification_yn'] = $request->input('rm_octo_certification_yn');
                $updated_data['rm_lacto_yn'] = $request->input('rm_lacto_yn');
                $updated_data['rm_lacto_validated'] = $request->input('rm_lacto_validated');
                $updated_data['rm_lacto_certification_yn'] = $request->input('rm_lacto_certification_yn');
                $updated_data['rm_vegan_yn'] = $request->input('rm_vegan_yn');
                $updated_data['rm_vegan_validated'] = $request->input('rm_vegan_validated');
                $updated_data['rm_vegan_certification_yn'] = $request->input('rm_vegan_certification_yn');
                $updated_data['rm_supplied_shelf_life_num'] = $request->input('rm_supplied_shelf_life_num');
                $updated_data['rm_supplied_shelf_life_units'] = $request->input('rm_supplied_shelf_life_units');
                $updated_data['rm_suppied_temp_control_storage_num'] = $request->input('rm_suppied_temp_control_storage_num');
                $updated_data['rm_suppied_temp_control_storage_degrees'] = $request->input('rm_suppied_temp_control_storage_degrees');
                $updated_data['rm_supplied_temp_control_transport_yn'] = $request->input('rm_supplied_temp_control_transport_yn');
                $updated_data['rm_supplied_temp_control_transport_degrees'] = $request->input('rm_supplied_temp_control_transport_degrees');
                $updated_data['rm_inuse_shelf_life_num'] = $request->input('rm_inuse_shelf_life_num');
                $updated_data['rm_inuse_shelf_life_units'] = $request->input('rm_inuse_shelf_life_units');
                $updated_data['rm_inuse_temp_control_storage_num'] = $request->input('rm_inuse_temp_control_storage_num');
                $updated_data['rm_inuse_temp_control_storage_degrees'] = $request->input('rm_inuse_temp_control_storage_degrees');
                $updated_data['rm_storage_requirement'] = $request->input('rm_storage_requirement');
                $updated_data['rm_indended_use'] = $request->input('rm_indended_use');
                $updated_data['rm_date_mark'] = $request->input('rm_date_mark');
                $updated_data['rm_hazard_yn'] = $request->input('rm_hazard_yn');
                ProdLabel::where('id', $checkLabel->id)->update($updated_data);
            }else{
                $item = new ProdLabel;
                $item->product_id = $product->id;
                $item->rm_halal_yn = $request->input('rm_halal_yn');
                $item->rm_halal_validated = $request->input('rm_halal_validated');
                $item->rm_halal_certification_yn = $request->input('rm_halal_certification_yn');
                $item->rm_kosher_yn = $request->input('rm_kosher_yn');
                $item->rm_kosher_validated = $request->input('rm_kosher_validated');
                $item->rm_kosher_certification_yn = $request->input('rm_kosher_certification_yn');
                $item->rm_organic_yn = $request->input('rm_organic_yn');
                $item->rm_organic_validated = $request->input('rm_organic_validated');
                $item->rm_organic_certification_yn = $request->input('rm_organic_certification_yn');
                $item->rm_bio_yn = $request->input('rm_bio_yn');
                $item->rm_bio_validated = $request->input('rm_bio_validated');
                $item->rm_bio_certification_yn = $request->input('rm_bio_certification_yn');
                $item->rm_octo_yn = $request->input('rm_octo_yn');
                $item->rm_octo_validated = $request->input('rm_octo_validated');
                $item->rm_octo_certification_yn = $request->input('rm_octo_certification_yn');
                $item->rm_lacto_yn = $request->input('rm_lacto_yn');
                $item->rm_lacto_validated = $request->input('rm_lacto_validated');
                $item->rm_lacto_certification_yn = $request->input('rm_lacto_certification_yn');
                $item->rm_vegan_yn = $request->input('rm_vegan_yn');
                $item->rm_vegan_validated = $request->input('rm_vegan_validated');
                $item->rm_vegan_certification_yn = $request->input('rm_vegan_certification_yn');
                $item->rm_supplied_shelf_life_num = $request->input('rm_supplied_shelf_life_num');
                $item->rm_supplied_shelf_life_units = $request->input('rm_supplied_shelf_life_units');
                $item->rm_suppied_temp_control_storage_num = $request->input('rm_suppied_temp_control_storage_num');
                $item->rm_suppied_temp_control_storage_degrees = $request->input('rm_suppied_temp_control_storage_degrees');
                $item->rm_supplied_temp_control_transport_yn = $request->input('rm_supplied_temp_control_transport_yn');
                $item->rm_supplied_temp_control_transport_degrees = $request->input('rm_supplied_temp_control_transport_degrees');
                $item->rm_inuse_shelf_life_num = $request->input('rm_inuse_shelf_life_num');
                $item->rm_inuse_shelf_life_units = $request->input('rm_inuse_shelf_life_units');
                $item->rm_inuse_temp_control_storage_num = $request->input('rm_inuse_temp_control_storage_num');
                $item->rm_inuse_temp_control_storage_degrees = $request->input('rm_inuse_temp_control_storage_degrees');
                $item->rm_storage_requirement = $request->input('rm_storage_requirement');
                $item->rm_indended_use = $request->input('rm_indended_use');
                $item->rm_date_mark = $request->input('rm_date_mark');
                $item->rm_hazard_yn = $request->input('rm_hazard_yn');
                $item->created_by = $this->user_id;
                $item->updated_by = $this->user_id;
                $item->save();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Step 3 completed successfully.',
                'next_url' => route('products.edit', ['product' => $product->id, 'step' => 4]),
                'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 3])
                // 'next_url' => $isProductV2
                //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 4])
                //     : route('products.edit', ['product' => $product->id, 'step' => 4]),
                // 'redirect_url' => $isProductV2
                //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 3])
                //     : route('products.edit', ['product' => $product->id, 'step' => 3]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in updateStep3: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the product: ' . $e->getMessage()
            ], 500); // HTTP status 500 Internal Server Error
        }
    }

    /**
     * Remove an ingredient from a product.
     */
    public function removeIngredient(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'prod_ingredient_id' => 'required|exists:prod_ingredients,id'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid ingredient entry.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the ingredient entry
            $prodIngredient = ProdIngredient::findOrFail($request->input('prod_ingredient_id'));

            // Delete the ingredient entry
            $prodIngredient->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Ingredient entry removed successfully.'
            ]);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing ingredient entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate the nutrition table values for a product.
    */
    
    public function create_nutrition_information(Request $request){

        $ingredients = collect($request->ingredients)->map(function ($item) {
            return [
                'id' => $item['id'],
                'index' => $item['index'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'component' => $item['component']
            ];
        });            
        $ingredientModels = Ingredient::whereIn('id', $ingredients->pluck('id'))->get()->keyBy('id');

        // $batchLossPercent = abs($request->batch_baking_loss_percent);
        $batchLossPercent = $request->batch_baking_loss_percent; //Loss or gain

        //dd($batchLossPercent);
        $totalNetQuantity = 0;
        $nutritionData = $peelNameData = [];
        $totalAmount = 0;
        foreach ($ingredients as $ingredient) {
            $model = $ingredientModels[$ingredient['id']];
            $specific_gravity = $model->specific_gravity > 0 ? $model->specific_gravity : 1;
            // $quantity = $ingredient['quantity'] * $specific_gravity;
            // dd($quantity);
            $quantity = $ingredient['quantity'];
            $netQuantity = round($quantity * (1 + ($batchLossPercent / 100))); //Loss or gain
            $totalNetQuantity += $netQuantity;
            $quantityBefore = round($ingredient['quantity'], 1);
            $amount = round($model->price_per_kg_l, 2);
            $tot_amount = $quantityBefore * $amount/ 1000;
            $ingredientUnit = $ingredient['unit'];
            if(in_array($ingredient['unit'],['ml','mL','l','L'])){
                $tot_amount = $tot_amount * $specific_gravity;
            }
            $totalAmount += $tot_amount;
            $nutritionData[] = [
                'id' => $model->id,
                'name' => $model->name_by_kitchen,
                'cost_per_kg' => (float) $model->price_per_kg_l,
                'sku' => $model->ing_sku,
                'image' => $model->ing_image ?: asset('assets/img/ing_default.png'),
                'component' => $ingredient['component'],
                'quantity' => $quantityBefore,
                'net_quantity' => $netQuantity,
                'original_index' => $ingredient['index'],
                'amount' => $tot_amount,
                'energy_kj' => round($model->energy_kj),
                'protein_g' => round($model->protein_g, 1),
                'fat_total_g' => round($model->fat_total_g, 1),
                'fat_saturated_g' => round($model->fat_saturated_g, 1),
                'carbohydrate_g' => round($model->carbohydrate_g, 1),
                'sugars_g' => round($model->sugars_g, 1),
                'sodium_mg' => round($model->sodium_mg),
                'australian_percent' => $model->australian_percent ?? 0
            ];

            $peelNameData[] = [
                'quantity' => $quantity,
                'peel_name' => $model->ingredients_list_supplier ?? '',
                'original_index' => $ingredient['index']
            ];
        }
        // Calculate mix percentages after all quantities are known
        foreach ($nutritionData as &$item) {
            $item['mix_percent'] = $totalNetQuantity > 0 ? ($item['net_quantity'] / $totalNetQuantity * 100): 0;
        }

        $totals = array_reduce($nutritionData, function ($carry, $item) {
            $carry['quantity'] += $item['quantity'];
            $carry['net_quantity'] += $item['net_quantity'];
            $carry['amount'] += $item['amount'];
            $carry['energy_kj'] += $item['energy_kj'] * ($item['mix_percent'] / 100);
            $carry['protein_g'] += $item['protein_g'] * ($item['mix_percent'] / 100);
            $carry['fat_total_g'] += $item['fat_total_g'] * ($item['mix_percent'] / 100);
            $carry['fat_saturated_g'] += $item['fat_saturated_g'] * ($item['mix_percent'] / 100);
            $carry['carbohydrate_g'] += $item['carbohydrate_g'] * ($item['mix_percent'] / 100);
            $carry['sugars_g'] += $item['sugars_g'] * ($item['mix_percent'] / 100);
            $carry['sodium_mg'] += $item['sodium_mg'] * ($item['mix_percent'] / 100);
            $carry['australian_percent'] += $item['australian_percent'] * $item['mix_percent'];
            return $carry;
        }, [
            'quantity' => 0,
            'net_quantity' => 0,
            'amount' => 0,
            'energy_kj' => 0,
            'protein_g' => 0,
            'fat_total_g' => 0,
            'fat_saturated_g' => 0,
            'carbohydrate_g' => 0,
            'sugars_g' => 0,
            'sodium_mg' => 0,
            'australian_percent' => 0
        ]);

        // Calculate total Australian percentage
        $totalAusPercent = array_sum(array_map(fn($item) => $item['australian_percent'] * $item['mix_percent'], $nutritionData));

        // Sort by original index to maintain order
        usort($peelNameData, fn($a, $b) => $a['original_index'] <=> $b['original_index']);
        $labellingIngredients = implode(', ', array_filter(array_column($peelNameData, 'peel_name')));
        
        $final['nutritionData'] = $nutritionData;
        $final['totals'] = $totals;
        $final['totalAmount'] = $totalAmount;
        $final['totalAusPercent'] = $totalAusPercent;
        $final['labellingIngredients'] = $labellingIngredients;
        $final['ingredientModels'] = $ingredientModels;
        return $final;
    } 

    public function calculateNutritionTable(Request $request)
    {
        try {
            $ingredient = $this->create_nutrition_information($request);
            $allergenData = $this->processAllergens($ingredient['ingredientModels']);
            return response()->json([
                'success' => true,
                'data' => [
                    'rows' => $ingredient['nutritionData'],
                    'totals' => $ingredient['totals'],
                    'total_final_value' => round($ingredient['totalAmount'], 2),
                    'total_aus_percent' => round($ingredient['totalAusPercent'], 2),
                    'labelling_ingredients' => $ingredient['labellingIngredients'],
                    'labelling_allergens' => $allergenData['allergens'],
                    'labelling_may_contain' => $allergenData['may_contain']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating nutrition data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function displayNutritionTable(Request $request)
    {
        try {
            $pid = $request->input('product_id');
            $ingredient = $this->create_nutrition_information($request);
            $costing_html = $this->generate_rawmaterial_costing($ingredient['nutritionData'],$ingredient['totals'],$pid);
            $analysis_html = $this->generate_nutrition_analysis($ingredient['nutritionData'],$ingredient['totals'],$pid);            
            return response()->json([
                'success' => true,
                'data' => [
                    'costing_html' =>$costing_html,
                    'analysis_html' => $analysis_html
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating nutrition data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generate_rawmaterial_costing($nutritionData,$totals,$product_id){
        $product = Product::findOrFail($product_id);
        $directcost =  ProductCalculationController::calculete_product_directcost($product); 
        // $directcost =  0.00; 
        $final['nutritionData'] = $nutritionData;
        $final['totals'] = $totals;
        $final['loss_percent'] = $product->batch_baking_loss_percent;
        $html = Blade::render('<x-rawmaterial-costing :nutrition="$nutrition" :directcost="$directcost"/>', ['nutrition' => $final,'directcost'=>$directcost ]);
        return $html;  
    }

    public function generate_nutrition_analysis($nutritionData,$totals,$product_id){
        $product = Product::findOrFail($product_id);
        $final['nutrition'] = $nutritionData;
        $final['totals'] = $totals;
        $html = Blade::render('<x-nutrition-analysis :product="$product" :prodIngredient="$prodIngredient" />', ['product' => $product,'prodIngredient'=>$final ]);
        return $html;
    }

    /**
     * Process allergens for a product.
     */
    private function processAllergens($ingredients)
    {
        // Standard allergen order
        $standardOrder = [
            'gluten',
            'wheat',
            'fish',
            'crustacean',
            'mollusc',
            'egg',
            'milk',
            'lupin',
            'peanut',
            'soy',
            'sesame',
            'almond',
            'brazil nut',
            'cashew',
            'hazelnut',
            'macadamia',
            'pecan',
            'pistachio',
            'pine nut',
            'walnut',
            'barley',
            'oats',
            'rye',
            'sulphites'
        ];

        // Define allergens that require special handling
        $glutenGrains = ['barley', 'oats', 'rye'];
        $soyVariants = ['soy'];

        // Define default "may contain" allergens
        $mayContainOrder = [
            'wheat',
            'milk',
            'egg',
            'soy',
            'sesame',
            'peanut',
            'almond',
            'cashew',
            'hazelnut',
            'macadamia',
            'pecan',
            'pistachio',
            'walnut',
            'oats'
        ];

        // Extract and normalize allergens from ingredients
        $presentAllergens = $ingredients->flatMap(function ($ingredient) {
            return collect(explode(',', strtolower($ingredient->allergens)))
                ->map(fn($allergen) => trim($allergen))
                ->filter(fn($allergen) => !empty($allergen) && $allergen !== '-');
        })->unique()->values();

        // Collect special allergens that meet their specific conditions
        $specialAllergens = collect();

        // Process allergens according to rules
        $processedAllergens = collect($standardOrder)
            ->filter(function ($allergen) use ($presentAllergens, $ingredients, $glutenGrains, $soyVariants, &$specialAllergens) {
                // Handle gluten grains
                if (in_array($allergen, $glutenGrains)) {
                    $hasGlutenGrain = $ingredients->contains(function ($ingredient) use ($allergen) {
                        $ingredientAllergens = collect(explode(',', strtolower($ingredient->allergens)))
                            ->map(fn($a) => trim($a))
                            ->filter(fn($a) => !empty($a) && $a !== '-');

                        // Check if the grain is present in allergens OR explicitly specified
                        $hasGrain = $ingredientAllergens->contains($allergen);
                        $hasGluten = ($ingredient->contains_gluten ?? false) === true;

                        return $hasGrain || $hasGluten;
                    });

                    if ($hasGlutenGrain) {
                        return true;
                    }
                    return false;
                }

                // Handle sulphites
                if ($allergen === 'sulphites') {
                    $hasSulphites = $ingredients->contains(function ($ingredient) {
                        // Check if sulphites are present in allergens OR meet content threshold
                        $ingredientAllergens = collect(explode(',', strtolower($ingredient->allergens)))
                            ->map(fn($a) => trim($a))
                            ->filter(fn($a) => !empty($a) && $a !== '-');

                        $hasSulphitesAllergen = $ingredientAllergens->contains('sulphites');
                        $meetsContentThreshold = ($ingredient->sulphite_content ?? 0) >= 10;

                        return $hasSulphitesAllergen || $meetsContentThreshold;
                    });

                    if ($hasSulphites) {
                        return true;
                    }
                    return false;
                }

                // Handle soy variants
                if (in_array($allergen, $soyVariants)) {
                    return $presentAllergens->contains(function ($present) use ($soyVariants) {
                        return in_array($present, $soyVariants);
                    });
                }

                // Handle regular allergens
                return $presentAllergens->contains($allergen);
            })
            ->map(function ($allergen) use ($glutenGrains) {
                // Add asterisk for gluten grains and sulphites based on processing rules
                /*
                if (in_array($allergen, $glutenGrains)) {
                    return ucfirst($allergen) . '*';
                }
                if ($allergen === 'sulphites') {
                    return ucfirst($allergen) . '**';
                }
                */
                return ucfirst($allergen);
            })
            ->unique()
            ->values();

        // Generate "May contain" list
        $normalizedPresentAllergens = $presentAllergens->map(function ($allergen) use ($soyVariants) {
            // Normalize soy variants to 'soy'
            if (in_array($allergen, $soyVariants)) {
                return 'soy';
            }
            return $allergen;
        });

        $mayContain = collect($mayContainOrder)
            ->filter(function ($allergen) use ($normalizedPresentAllergens, $soyVariants) {
                if ($allergen === 'soy') {
                    return !$normalizedPresentAllergens->contains(function ($present) use ($soyVariants) {
                        return in_array($present, $soyVariants);
                    });
                }
                return !$normalizedPresentAllergens->contains($allergen);
            })
            ->map(fn($allergen) => ucfirst($allergen));

        // Comprehensive debugging information
        // Log::info('Allergen Processing Details', [
        //     'presentAllergens' => $presentAllergens->toArray(),
        //     'processedAllergens' => $processedAllergens->toArray(),
        //     'mayContain' => $mayContain->toArray()
        // ]);

        return [
            'allergens' => $processedAllergens->implode(', '),
            'may_contain' => $mayContain->implode(', ')
        ];
    }

    /**
     * Get ingredient units for a product.
     */
    public function getIngredientUnits(Request $request)
    {
        $id = $request->input('id');
        $ingredient = Ingredient::find($id);
        if ($ingredient) {
            return response()->json([
                'units_g_ml' => $ingredient->purchase_units
            ]);
        }

        return response()->json([
            'units_g_ml' => null
        ], 404);
    }

    /**
     * Generate a SKU for a product.
     */
    public function generateSKU(Request $request)
    {
        // Get the product name and ID from request
        $productName = $request->product_name;
        $productId = $request->product_id;

        // Clean the product name
        $sku = $this->cleanProductName($productName);

        // Check if SKU exists (excluding the current product if editing)
        $query = Product::where('prod_sku', $sku);
        if ($productId) {
            $query->where('id', '!=', $productId);
        }

        // Only add random number if SKU exists
        if ($query->exists()) {
            do {
                $randomSku = $sku . '_' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

                // Check if the new random SKU exists
                $exists = Product::where('prod_sku', $randomSku)
                    ->when($productId, function ($q) use ($productId) {
                        return $q->where('id', '!=', $productId);
                    })
                    ->exists();
            } while ($exists); // Keep trying until we find a unique SKU

            $sku = $randomSku;
        }

        return response()->json(['sku' => $sku]);
    }

    /**
     * Clean a product name to generate a SKU.
     */
    private function cleanProductName($name)
    {
        // Convert to lowercase and replace spaces with underscore
        $clean = Str::lower($name);
        // Replace spaces with underscores
        $clean = str_replace(' ', '_', $clean);
        // Remove special characters except underscore
        $clean = preg_replace('/[^a-z0-9_]/', '', $clean);
        // Replace multiple underscores with single underscore
        $clean = preg_replace('/_+/', '_', $clean);
        // Trim underscores from start and end
        return trim($clean, '_');
    }

    /**
     * List all products.
     */
    public function list()
    {
        $products = Product::with(['creator', 'updater'])->get();
        return view('backend.product.list', compact('products'));
    }

    /**
     * Show the recipe for a product.
     */
    public function recipe(Product $product)
    {
        // Load necessary relationships
        $product->load([
            'creator:id,name',
            'updater:id,name',
            'productIngredients' => function ($query) {
                $query->with('ingredient:id,name_by_kitchen,name_by_supplier')
                    ->orderBy('ingredient_order');
            }
        ]);
        $product = $this->update_companydetails($product); //update factory and keyperson
        // Group ingredients by component
        $batchTotalArray = []; 
        $groupedIngredients = $product->productIngredients->groupBy('component');
        foreach ($groupedIngredients as $component => $ingredients) {
            foreach ($ingredients as $ingredient) {
                if (in_array($ingredient->units_g_ml, ['kg', 'L', 'l'])) {
                    $temp = $ingredient->quantity_g * 1000;
                    $ingredient->quantity_g = round((float) $temp, 2);
                }
                $batchTotalArray[] = round((float) $ingredient->quantity_g, 2);
            }
        }
        $batchTotal = array_sum($batchTotalArray);
        return view('backend.product.recipe', compact('product', 'groupedIngredients','batchTotal'));
    }

    
    /**
     * Show the specification for a product.
     */
    public function spec(Product $product)
    {
        // Eager load relationships to avoid N+1 queries
        $product->load([
            'creator',
            'updater',
            'prodLabels',
            'ingredients' => function ($query) {
                $query->orderBy('ingredient_order');
            },
            'ingredients.ingredient' //nested relation
        ]);

        if($product->ingredients){
            //Sort ascending based on weight
            $sorted = $product->ingredients->sortByDesc(function ($item) {
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
                        $allgs[] = trim($value);
                    }
                }
            });
            $product->labelling_ingredients = (count($ingrs))?implode(', ', $ingrs):'';
            $product->labelling_allergens   = (count($allgs))?implode(', ', array_unique($allgs)):'';
        }
        $product = $this->update_companydetails($product); //update factory and keyperson
        $allergen = Ing_allergen::pluck('name')->toArray();
        $prod_ings = ProdIngredient::where('product_id',$product->id)->get()->toArray();
        $prod_ingredient = $this->labelling_nutrition($prod_ings,$product->batch_baking_loss_percent);
        $product->australian_percent = ($prod_ingredient)? $prod_ingredient['totals']['australian_percent']:0;
        return view('backend.product.spec', compact('product', 'allergen'));  
    }

    public function update_companydetails($product){

        if($product->labelling_may_contain == null){
            $profile = ClientProfile::where('client_id', $product->client_id)->first();
            $product->labelling_may_contain = $profile->commonAllergens??null;
        }

        if($product->company_factory){
            $factory = client_factory_location::find($product->company_factory);
            if ($factory) {
                $factoryDetails = json_decode($factory->factory_locations, true);
                if (is_array($factoryDetails)) {
                    $facArray = [
                        $factoryDetails['street_address'] ?? '',
                        $factoryDetails['suburb'] ?? '',
                        $factoryDetails['state'] ?? '',
                        $factoryDetails['zip_code'] ?? '',
                        $factoryDetails['country'] ?? '',
                    ];
                    // Remove empty values & join with comma
                    $product->factoryAddress = implode(', ', array_filter($facArray));
                }
            }
        }

        if ($product->company_keyperson) {
            $comp_keyperson = client_key_personnel::find($product->company_keyperson);
            if ($comp_keyperson) {
                $keyDetails = json_decode($comp_keyperson->key_personnel, true);
                if (is_array($keyDetails)) {
                    $keyArray = [
                        $keyDetails['full_name'] ?? '',
                        $keyDetails['email'] ?? '',
                        $keyDetails['phone'] ?? '',
                    ];
                    // Remove empty values & join with comma
                    $product->keyPerson = implode(', ', array_filter($keyArray));
                }
            }
        }
        return $product;
    }
    /**
     * Show the labelling for a product.
     */
    public function labelling(Product $product)
    {
        // Eager load relationships to avoid N+1 queries
        $product->load([
            'creator',
            'updater',
            'ingredients' => function ($query) {
                $query->orderBy('ingredient_order');
            },
            'ingredients.ingredient'
        ]);
        if($product->ingredients){ //Assign allergens and ingredient lists
            $sorted = $product->ingredients->sortByDesc(function ($item) {
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
                        $allgs[] = trim($value);
                    }
                }
            });
            $product->labelling_ingredients = (count($ingrs))?implode(', ', $ingrs):'';
            $product->labelling_allergens   = (count($allgs))?implode(', ', array_unique($allgs)):'';
        }

        $product = $this->update_companydetails($product); //update factory and keyperson
        $prod_ingres = $product->ingredients()->with('ingredient')->get();      
        $prodingNutrition = $this->costing_nutrition($prod_ingres, $product->batch_baking_loss_percent);        
        $prodIngs = $prod_ingres->map(function ($item) use ($prodingNutrition) {
            $pr_name = $item->ing_name;     
            $key = array_search($pr_name, array_column($prodingNutrition['nutrition'], 'name'));
            if ($key === false) {
                $item->mix_percent = null; // or some default value
            } else {
                $item->mix_percent = $prodingNutrition['nutrition'][$key]['mix_percent'] ?? null;
            }
            return $item;
        });
        $prod_ings = ProdIngredient::where('product_id',$product->id)->get()->toArray();
        $prod_ingredient = $this->labelling_nutrition($prod_ings,$product->batch_baking_loss_percent);
        $allergen = Ing_allergen::pluck('name')->toArray();
        $prodLabel = ProdLabel::where('product_id',$product->id)->first();
        $labelling_information = view('components.labelling.information', ['product' => $product])->render();
        return view('backend.product.labelling', compact('product','prod_ingredient','allergen','prodLabel','prodIngs','labelling_information'));
    }

    public function labelling_nutrition($ingredients,$batchLossPercent){
        $totalNetQuantity = 0;
        $nutritionData = $peelNameData = [];
        $totalAmount = 0;
        foreach ($ingredients as $ingredient) {
            $model = Ingredient::where('id',$ingredient['ing_id'])->first();
            $specific_gravity = $model->specific_gravity > 0 ? $model->specific_gravity : 1;
            // $quantity = $ingredient['quantity_weight'] * $specific_gravity;
            $quantity = $ingredient['quantity_weight'];
            $netQuantity = round($quantity * (1 + $batchLossPercent / 100)); //Loss or gain
            $totalNetQuantity += $netQuantity;
            $quantityBefore = round($ingredient['quantity_weight'], 1);
            $amount = round($model->price_per_kg_l, 2);
            $tot_amount = $quantityBefore * $amount/ 1000;
            $ingredientUnit = $ingredient['units_g_ml'];
            if ($ingredient['units_g_ml'] == 'ml' || $ingredient['units_g_ml'] == 'l') {
                $tot_amount = $tot_amount * $specific_gravity;
            }
            $totalAmount += $tot_amount;
            $nutritionData[] = [
                'name' => $model->name_by_kitchen,
                'quantity' => $quantityBefore,
                'net_quantity' => $netQuantity,
                'energy_kj' => round($model->energy_kj),
                'protein_g' => round($model->protein_g, 1),
                'fat_total_g' => round($model->fat_total_g, 1),
                'fat_saturated_g' => round($model->fat_saturated_g, 1),
                'carbohydrate_g' => round($model->carbohydrate_g, 1),
                'sugars_g' => round($model->sugars_g, 1),
                'sodium_mg' => round($model->sodium_mg),
                'australian_percent' => $model->australian_percent ?? 0             
            ];
        }

        foreach ($nutritionData as &$item) {
            $item['mix_percent'] = $totalNetQuantity > 0
                ? ($item['net_quantity'] / $totalNetQuantity * 100)
                : 0;   
        }
        $totals = array_reduce($nutritionData, function ($carry, $item) {
            $carry['energy_kj'] += $item['energy_kj'] * ($item['mix_percent'] / 100);
            $carry['protein_g'] += $item['protein_g'] * ($item['mix_percent'] / 100);
            $carry['fat_total_g'] += $item['fat_total_g'] * ($item['mix_percent'] / 100);
            $carry['fat_saturated_g'] += $item['fat_saturated_g'] * ($item['mix_percent'] / 100);
            $carry['carbohydrate_g'] += $item['carbohydrate_g'] * ($item['mix_percent'] / 100);
            $carry['sugars_g'] += $item['sugars_g'] * ($item['mix_percent'] / 100);
            $carry['sodium_mg'] += $item['sodium_mg'] * ($item['mix_percent'] / 100);
            $carry['australian_percent'] += $item['australian_percent'] * $item['mix_percent'];
            return $carry;
        }, [
            'energy_kj' => 0,
            'protein_g' => 0,
            'fat_total_g' => 0,
            'fat_saturated_g' => 0,
            'carbohydrate_g' => 0,
            'sugars_g' => 0,
            'sodium_mg' => 0,
            'australian_percent' => 0
        ]);

        $final['nutrition'] = $nutritionData;
        $final['totals'] = $totals;
        return $final;
    }

    /**
     * Show the costing for a product.
     */
    public function costing(Product $product)
    {
        $product = $this->update_companydetails($product); //update factory and keyperson
        $pr_ing = ProdIngredient::where('product_id',$product->id)->get();
        $costingData = ProductCalculationController::calculate_direct_cost_componenet($product,$pr_ing);
        $weightTotal = ($product->batch_after_waste_g) ?? 1;
        // $nutrition = $this->costing_nutrition($pr_ing, $product->batch_baking_loss_percent);
        $nutrition = ProductCalculationController::calculate_nutrition_information_components($product,$pr_ing);
        $directcost =  ProductCalculationController::calculete_product_directcost($product);
        $details = ProductCalculationController::calculate_price_analysis_componenet($product);
        $prince_analysis_html = view('components.price-analysis', ['details' => $details,'weightTotal'=>$weightTotal ])->render();
        $costing_information = view('components.costing.information', ['product' => $product])->render();
        return view('backend.product.costing', compact('product', 'costingData', 'weightTotal','nutrition','directcost','prince_analysis_html','costing_information'));
    }


    public function costing_nutrition($ingredients,$batchLossPercent){
        $totalNetQuantity = 0;
        $nutritionData = $peelNameData = [];
        $totalAmount = 0;
        foreach ($ingredients as $ingredient) {
            $model = Ingredient::where('id',$ingredient['ing_id'])->first();
            if($model == null){
                continue;
            }
            $specific_gravity = $model->specific_gravity > 0 ? $model->specific_gravity : 1;
            if(in_array($ingredient['units_g_ml'], ['kg','l','L'])){
                $quantity = $ingredient['quantity_weight'] * 1000;
            }else{
                $quantity = $ingredient['quantity_weight'];
            }
            $netQuantity = round($quantity * (1 + $batchLossPercent / 100)); //Loss or gain
            $totalNetQuantity += $netQuantity;
            $quantityBefore = round($ingredient['quantity_weight'], 1);
            $amount = round($model->price_per_kg_l, 2);
            $tot_amount = $quantityBefore * $amount/ 1000;
            if(in_array($ingredient['units_g_ml'], ['ml','mL','l','L'] )) {
                $tot_amount = $tot_amount * $specific_gravity;
            }
            $totalAmount += $tot_amount;
            $nutritionData[] = [
                'name' => $model->name_by_kitchen,
                'cost_per_kg' => $model->price_per_kg_l,
                'quantity' => $quantityBefore,
                'net_quantity' => $netQuantity,
                'amount' => $tot_amount                
            ];
            $peelNameData[] = [
                'quantity' => $quantity,
                'peel_name' => $model->ingredients_list_supplier ?? ''
            ];
        }

        foreach ($nutritionData as &$item) {
            $item['mix_percent'] = $totalNetQuantity > 0
                ? ($item['net_quantity'] / $totalNetQuantity * 100)
                : 0;   
        }
        $totals = array_reduce($nutritionData, function ($carry, $item) {
            $carry['quantity'] += $item['quantity'];
            $carry['net_quantity'] += $item['net_quantity'];
            $carry['amount'] += $item['amount'];
            return $carry;
        }, [
            'quantity' => 0,
            'net_quantity' => 0,
            'amount' => 0
        ]);

        $final['nutrition'] = $nutritionData;
        $final['totals'] = $totals;
        $final['loss_percent'] = $batchLossPercent;
        return $final;
    }

    /**
     * Update step 4 of the product creation process (Labour).
     */
    public function updateStep4(Request $request, Product $product)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        // if (!$request->has('LabourFields') || empty($request->input('LabourFields'))) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['Please provide at least one set of labour details.'],
        //     ], 422);
        // }

        // if (!$request->has('MachineryFields') || empty($request->input('MachineryFields'))) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['Please provide at least one set of machinery details.'],
        //     ], 422);
        // }

        // if (!$request->has('PackagingFields') || empty($request->input('PackagingFields'))) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['Please provide at least one set of packaging details.'],
        //     ], 422);
        // }

        // if (!$request->has('FreightFields') || empty($request->input('FreightFields'))) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['Please provide at least one set of freight details.'],
        //     ], 422);
        // }

        if ($request->has('LabourFields') || !empty($request->input('LabourFields'))) {
            $numericFields = [
                'LabourFields.*.people_count',
                'LabourFields.*.hours_per_person',
                'LabourFields.*.hourly_rate',
                'LabourFields.*.cost_per_kg'
            ];
            $cleanedInput = $this->preprocessNumericInput($request->all(), $numericFields);
            $validator = Validator::make($cleanedInput, [
                'LabourFields.*.labour_id' => 'required|exists:labours,id',
                'LabourFields.*.people_count' => 'required|integer|min:1',
                'LabourFields.*.hours_per_person' => 'required|numeric|min:0',
                'LabourFields.*.hourly_rate' => 'required|numeric|min:0',
                'LabourFields.*.labour_units' => 'required|string|min:1',
                'LabourFields.*.cost_per_kg' => 'nullable|numeric|min:0',
            ], [
                'LabourFields.*.labour_id.required' => 'Labour selection is required.',
                'LabourFields.*.people_count.required' => 'Number of people is required.',
                'LabourFields.*.hours_per_person.required' => 'Hours per person is required.',
                'LabourFields.*.hourly_rate.required' => 'Hourly rate is required.',
                'LabourFields.*.labour_units.required' => 'Product units is required.',

            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $uniqueErrors = array_unique($errors);
                return response()->json([
                    'success' => false,
                    'errors' => $uniqueErrors,
                ], 422);
            }

            //Execute product labour 
            $validated = $validator->validated();
            ProdLabour::where('product_id', $product->id)->delete();
            $labourRecords = [];
            foreach ($validated['LabourFields'] as $labourData) {
                $labourRecords[] = [
                    'product_id' => $product->id,
                    'labour_id' => $labourData['labour_id'],
                    'people_count' => $labourData['people_count'],
                    'hours_per_person' => $labourData['hours_per_person'],
                    'hourly_rate' => $labourData['hourly_rate'],
                    'product_units' => $labourData['labour_units'],
                    'cost_per_kg' => $labourData['cost_per_kg'],
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id,
                ];
            }
            ProdLabour::insert($labourRecords);
            
        }

        if ($request->has('MachineryFields') || !empty($request->input('MachineryFields'))) {
            //Machinery validation
            $Machinery_numericFields = [
                'MachineryFields.*.hours',
                'MachineryFields.*.cost_per_hour',
                'MachineryFields.*.weight',
                'MachineryFields.*.cost_per_kg'
            ];
            $machinery_cleanedInput = $this->preprocessNumericInput($request->all(), $Machinery_numericFields);
            $machinery_validator = Validator::make($machinery_cleanedInput, [
                'MachineryFields.*.machinery_id' => 'required|exists:machinery,id',
                'MachineryFields.*.hours' => 'required|numeric|min:0',
                'MachineryFields.*.cost_per_hour' => 'required|numeric|min:0',
                'MachineryFields.*.machine_units' => 'required|string|min:1',
                'MachineryFields.*.weight' => 'nullable|numeric|min:0',
                'MachineryFields.*.cost_per_kg' => 'nullable|numeric|min:0'
            ], [
                'MachineryFields.*.machinery_id.required' => 'Machinery selection is required.',
                'MachineryFields.*.hours.required' => 'Hours is required.',
                'MachineryFields.*.cost_per_hour.required' => 'Cost per hour is required.',
                'MachineryFields.*.machine_units.required' => 'Product units is required.',
                'MachineryFields.*.weight.numeric' => 'Weight must be a number.',
                'MachineryFields.*.cost_per_kg.numeric' => 'Cost per kg must be a number.',
                'MachineryFields.*.weight.min' => 'Weight must be at least 0.',
                'MachineryFields.*.cost_per_kg.min' => 'Cost per kg must be at least 0.',
            ]);
            if ($machinery_validator->fails()) {
                $errors = $machinery_validator->errors()->all();
                $uniqueErrors = array_unique($errors);
                return response()->json([
                    'success' => false,
                    'errors' => $uniqueErrors,
                ], 422);
            }

            //Execute Product Machinery
            $machinery_validated = $machinery_validator->validated();
            ProdMachinery::where('product_id', $product->id)->delete();
            $machineryRecords = [];
            foreach ($machinery_validated['MachineryFields'] as $machineryData) {
                $machineryRecords[] = [
                    'product_id' => $product->id,
                    'machinery_id' => $machineryData['machinery_id'],
                    'machine_type' => Machinery::find($machineryData['machinery_id'])->name,
                    'hours' => $machineryData['hours'],
                    'cost_per_hour' => $machineryData['cost_per_hour'],
                    'product_units' => $machineryData['machine_units'],
                    'weight' => $machineryData['weight'] ?? 0,
                    'cost_per_kg' => $machineryData['cost_per_kg'] ?? 0,
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id,
                ];
            }
            ProdMachinery::insert($machineryRecords);
            
        }

        if ($request->has('PackagingFields') || !empty($request->input('PackagingFields'))) {
            //Packaging validation
            $packaging_numericFields = [
                'PackagingFields.*.cost_per_sell_unit',
                'PackagingFields.*.weight_per_sell_unit',
                'PackagingFields.*.cost_per_kg'
            ];
            $packaging_cleanedInput = $this->preprocessNumericInput($request->all(), $packaging_numericFields);
            $packaging_validator = Validator::make($packaging_cleanedInput, [
                'PackagingFields.*.packaging_id' => 'required|exists:packaging,id',
                'PackagingFields.*.cost_per_sell_unit' => 'required|numeric|min:0',
                'PackagingFields.*.weight_per_sell_unit' => 'nullable|numeric|min:0',
                'PackagingFields.*.cost_per_kg' => 'nullable|numeric|min:0',
                'PackagingFields.*.product_units' => 'nullable',
            ], [
                'PackagingFields.*.packaging_id.required' => 'Packaging selection is required.',
                'PackagingFields.*.cost_per_sell_unit.required' => 'Cost per sell unit is required.',
                'PackagingFields.*.cost_per_sell_unit.numeric' => 'Cost per sell unit must be a number.',
                'PackagingFields.*.weight_per_sell_unit.numeric' => 'Weight per sell unit must be a number.',
                'PackagingFields.*.cost_per_kg.numeric' => 'Cost per kg must be a number.'
            ]);

            if ($packaging_validator->fails()) {
                $errors = $packaging_validator->errors()->all();
                $uniqueErrors = array_unique($errors);
                return response()->json([
                    'success' => false,
                    'errors' => $uniqueErrors,
                ], 422);
            }

            //Execute Product Packaging
            $packaging_validated = $packaging_validator->validated();
            ProdPackaging::where('product_id', $product->id)->delete();
            $packagingRecords = [];
            foreach ($packaging_validated['PackagingFields'] as $packagingData) {
                $packaging = Packaging::find($packagingData['packaging_id']);
                $packagingRecords[] = [
                    'product_id' => $product->id,
                    'packaging_id' => $packagingData['packaging_id'],
                    'packaging_name' => $packaging->name,
                    'packaging_type' => $packagingData['product_units'],
                    'cost_per_sell_unit' => $packagingData['cost_per_sell_unit'],
                    'weight_per_sell_unit' => $packagingData['weight_per_sell_unit'] ?? 0,
                    'cost_per_kg' => $packagingData['cost_per_kg'] ?? 0,
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ProdPackaging::insert($packagingRecords);
        }

        if ($request->has('FreightFields') || !empty($request->input('FreightFields'))) {
            $freights_numericFields = [
                'FreightFields.*.freight_cost',
                'FreightFields.*.freight_weight',
                'FreightFields.*.cost_per_kg'
            ];
            $freight_cleanedInput = $this->preprocessNumericInput($request->all(), $freights_numericFields);
            $freight_validator = Validator::make($freight_cleanedInput, [
                'FreightFields.*.freight_id' => 'required|exists:freights,id',
                'FreightFields.*.freight_supplier' => 'nullable|string',
                'FreightFields.*.freight_cost' => 'required|numeric|min:0',
                'FreightFields.*.freight_weight' => 'required|numeric|min:0',
                'FreightFields.*.freight_units' => 'required|string',
                'FreightFields.*.cost_per_kg' => 'required|numeric|min:0',
            ], [
                'FreightFields.*.freight_id.required' => 'Freight selection is required.',            
                'FreightFields.*.freight_cost.required' => 'Freight Cost is required.',
                'FreightFields.*.freight_weight.required' => 'Freight Weight is required.',
                'FreightFields.*.freight_units.required' => 'Freight Unit is required.',
                'FreightFields.*.cost_per_kg.required' => 'Cost per kg must is required.'
            ]);

            if ($freight_validator->fails()) {
                $errors = $freight_validator->errors()->all();
                $uniqueErrors = array_unique($errors);
                return response()->json([
                    'success' => false,
                    'errors' => $uniqueErrors,
                ], 422);
            }

            // Execute Product Freight
            $freight_validated = $freight_validator->validated();
            ProdFreight::where('product_id', $product->id)->delete();
            $freightRecords = [];
            foreach ($freight_validated['FreightFields'] as $freightData) {
                $freightRecords[] = [
                    'product_id' => $product->id,
                    'freight_id' => $freightData['freight_id'],
                    'freight_supplier' => $freightData['freight_supplier'],
                    'freight_cost' => $freightData['freight_cost'],
                    'freight_weight' => $freightData['freight_weight'],
                    'freight_units' => $freightData['freight_units'],
                    'cost_per_kg' => $freightData['cost_per_kg'],
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ProdFreight::insert($freightRecords);
        }
        $updated_data['contingency'] = $request->input('contingency');
        $updated_data['retailer_charges'] = $request->input('retailer_charges');
        Product::where('id',$product->id)->update($updated_data);
        Product_edit_lock::where('sku_id',$product->id)->delete();  
        return response()->json([
            'success' => true,
            'message' => 'Costing information saved successfully.',
            'next_url' => route('products.edit', ['product' => $product->id, 'step' => 5]),
            'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 4])
            // 'next_url' => $isProductV2
            //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 5])
            //     : route('products.edit', ['product' => $product->id, 'step' => 5]),
            // 'redirect_url' => $isProductV2
            //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 4])
            //     : route('products.edit', ['product' => $product->id, 'step' => 4]),
        ]);
    }

    /**
     * Remove a labour entry from a product.
     */
    public function removeLabour(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'prod_labour_id' => 'required|exists:prod_labours,id' // Make sure 'prod_labour' is the correct table name
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid labour entry.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the labour entry
            $prodLabour = ProdLabour::findOrFail($request->input('prod_labour_id'));

            // Delete the labour entry
            $prodLabour->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Labour entry removed successfully.'
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing labour entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get labour details for a product.
     */
    public function getLabourDetails(Request $request)
    {
        $labourId = $request->input('labour_id');

        $labour = Labour::findOrFail($labourId);

        return response()->json([
            'hourly_rate' => $labour->hourly_rate,
        ]);
    }

    /**
     * Get the product weight for a product.
     */
    public function getProductWeight(Request $request)
    {
        $productId = $request->input('product_id');
        $productUnits = $request->input('product_units');

        $product = Product::findOrFail($productId);
        // dd($product);
        // Determine weight based on product units
        
        switch ($productUnits) {
            case '1':
                $weight = $product->weight_ind_unit_g;
                break;
            case '2':
                $weight = $product->weight_retail_unit_g;
                break;
            case '3':
                $weight = $product->batch_after_waste_g;
                break;
            case '4':
                $weight = $product->weight_carton_g;
                break;
            case '5':
                $weight = $product->weight_pallet_g;
                break;
            default:
                $weight = 0;
        }

        return response()->json([
            'weight' => $weight ?? 0
        ]);
    }


    // Machinery Module
    /**
     * Get machinery details for a product.
     */
    public function getMachineryDetails(Request $request)
    {
        $machineryId = $request->input('machinery_id');
        $machinery = Machinery::findOrFail($machineryId);

        return response()->json([
            'cost_per_hour' => $machinery->cost_per_hour_aud,
        ]);
    }

    /**
     * Get the machinery weight for a product.
     */
    public function getMachineryWeight(Request $request)
    {
        $productId = $request->input('product_id');
        $productUnits = $request->input('product_units');
        $product = Product::findOrFail($productId);

        switch ($productUnits) {
            case '1':
                $weight = $product->weight_ind_unit_g;
                break;
            case '2':
                $weight = $product->weight_retail_unit_g;
                break;
            case '3':
                $weight = $product->batch_after_waste_g;
                break;
            case '4':
                $weight = $product->weight_carton_g;
                break;
            case '5':
                $weight = $product->weight_pallet_g;
                break;
            default:
                $weight = 0;
        }

        return response()->json([
            'weight' => $weight ?? 0
        ]);
    }



    public function getPackagingWeight(Request $request)
    {
        $productId = $request->input('product_id');
        $productUnits = $request->input('product_units');
        $product = Product::findOrFail($productId);
        // dd($productUnits);
        switch ($productUnits) {
            case 'Ind Unit':
                $weight = $product->weight_ind_unit_g;
                break;
            case 'Sell Unit':
                $weight = $product->weight_retail_unit_g;
                break;
            case 'Carton':
                $weight = $product->weight_carton_g;
                break;
            case 'Pallet':
                $weight = $product->weight_pallet_g;
                break;
            default:
                $weight = 0;
        }
        return response()->json([
            'weight' => $weight ?? 0
        ]);
    }

    /**
     * Update step 5 of the product creation process (Machinery).
     */
    public function updateStep5(Request $request, Product $product)
    {
        $isProductV2 = $request->input('tab_variant') === 'product_v2';
        $updated_data['wholesale_margin'] = $request->input('wholesale_margin');
        $updated_data['distributor_margin'] = $request->input('distributor_margin');
        $updated_data['retailer_margin'] = $request->input('retailer_margin');
        //Advanced costing datas
        $updated_data['wholesale_price_sell'] = $request->input('wholesale_price_sell');
        $updated_data['wholesale_price_kg_price'] = $request->input('wholesale_price_kg_price');
        $updated_data['distributor_price_sell'] = $request->input('distributor_price_sell');
        $updated_data['distributor_price_kg_price'] = $request->input('distributor_price_kg_price');
        $updated_data['rrp_ex_gst_sell'] = $request->input('rrp_ex_gst_sell');
        $updated_data['rrp_ex_gst_price'] = $request->input('rrp_ex_gst_price');
        $updated_data['rrp_inc_gst_sell'] = $request->input('rrp_inc_gst_sell');
        $updated_data['rrp_inc_gst_price'] = $request->input('rrp_inc_gst_price');

        $retail_price = number_format($request->input('rrp_ex_gst_sell'),2) ;
        $count_retail = ($product->count_ind_units_per_retail > 0) ?$product->count_ind_units_per_retail : 1;
        $count_carton = $product->count_retail_units_per_carton;
        $count_pallet = $product->count_cartons_per_pallet;

        $updated_data['price_ind_unit'] = $retail_price / ( $count_retail);
        $updated_data['price_retail_unit'] = $retail_price;
        $updated_data['price_carton'] = $retail_price * $count_carton;
        $updated_data['price_pallet'] = $retail_price * $count_carton * $count_pallet;
        Product::where('id',$product->id)->update($updated_data);
        Product_edit_lock::where('sku_id',$product->id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Pricing information saved successfully.',
            'next_url' => route('products.index'),
            'redirect_url' => route('products.edit', ['product' => $product->id, 'step' => 5]),
            'manage_url' => route('products.index'),
            // 'next_url' => $isProductV2
            //     ? route('product_v2.manage')
            //     : route('products.index'),   
            // 'redirect_url' => $isProductV2
            //     ? route('product_v2.edit', ['product' => $product->id, 'step' => 5])
            //     : route('products.edit', ['product' => $product->id, 'step' => 5]),
            // 'manage_url' => $isProductV2
            //     ? route('product_v2.manage')
            //     : route('products.index'),
        ]);
    }

    /**
     * Remove a machinery entry from a product.
     */
    public function removeMachinery(Request $request)
    {

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'prod_machinery_id' => 'required|exists:prod_machinery,id'
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid machinery entry.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the machinery entry
            $prodMachinery = ProdMachinery::findOrFail($request->input('prod_machinery_id'));

            // Delete the machinery entry
            $prodMachinery->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Machinery entry removed successfully.'
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing machinery entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update step 6 of the product creation process (Packaging).
     */
    public function updateStep6(Request $request, Product $product)
    {
        if (!$request->has('PackagingFields') || empty($request->input('PackagingFields'))) {
            return response()->json([
                'success' => false,
                'errors' => ['Please provide at least one set of packaging details.'],
            ], 422);
        }

        // Define numeric fields that need comma cleaning
        $numericFields = [
            'PackagingFields.*.cost_per_sell_unit',
            'PackagingFields.*.weight_per_sell_unit',
            'PackagingFields.*.cost_per_kg'
        ];

        // Clean the numeric input
        $cleanedInput = $this->preprocessNumericInput($request->all(), $numericFields);

        $validator = Validator::make($cleanedInput, [
            'PackagingFields.*.packaging_id' => 'required|exists:packaging,id',
            'PackagingFields.*.cost_per_sell_unit' => 'required|numeric|min:0',
            'PackagingFields.*.weight_per_sell_unit' => 'nullable|numeric|min:0',
            'PackagingFields.*.cost_per_kg' => 'nullable|numeric|min:0',
            'PackagingFields.*.product_units' => 'nullable',
        ], [
            'PackagingFields.*.packaging_id.required' => 'Packaging selection is required.',
            'PackagingFields.*.cost_per_sell_unit.required' => 'Cost per sell unit is required.',
            'PackagingFields.*.cost_per_sell_unit.numeric' => 'Cost per sell unit must be a number.',
            'PackagingFields.*.weight_per_sell_unit.numeric' => 'Weight per sell unit must be a number.',
            'PackagingFields.*.cost_per_kg.numeric' => 'Cost per kg must be a number.'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $uniqueErrors = array_unique($errors);
            return response()->json([
                'success' => false,
                'errors' => $uniqueErrors,
            ], 422);
        }

        $validated = $validator->validated();

        // Delete existing packaging records for this product
        ProdPackaging::where('product_id', $product->id)->delete();

        // Prepare packaging records for bulk insert
        $packagingRecords = [];
        foreach ($validated['PackagingFields'] as $packagingData) {
            $packaging = Packaging::find($packagingData['packaging_id']);
            $packagingRecords[] = [
                'product_id' => $product->id,
                'packaging_id' => $packagingData['packaging_id'],
                'packaging_name' => $packaging->name,
                'packaging_type' => $packagingData['product_units'],
                'cost_per_sell_unit' => $packagingData['cost_per_sell_unit'],
                'weight_per_sell_unit' => $packagingData['weight_per_sell_unit'] ?? 0,
                'cost_per_kg' => $packagingData['cost_per_kg'] ?? 0,
                'created_by' => $this->user_id,
                'updated_by' => $this->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert packaging records
        ProdPackaging::insert($packagingRecords);
        /*
        // Calculate total packaging cost 
        $totalPackagingCost = collect($packagingRecords)->sum('cost_per_sell_unit');

        // Update product with total packaging cost 
        $product->update([
            'total_packaging_cost' => $totalPackagingCost
        ]);
*/
        return response()->json([
            'success' => true,
            'message' => 'Packaging information saved successfully.',
            'next_url' => route('products.index'),
            'redirect_url' => route('products.index'),
        ]);
    }

    /**
     * Get packaging details for a product.
     */
    public function getPackagingDetails(Request $request)
    {
        $packagingId = $request->input('packaging_id');
        $pr_id = $request->input('product_id');
        $packaging = Packaging::findOrFail($packagingId);
        $packaging->supplier = $packaging->supplier->company_name;
        $product = Product::findOrFail($pr_id);
        $pr_int_unit = $product->weight_ind_unit_g;
        return response()->json([
            'cost_per_sell_unit' => $pr_int_unit,
            'packagings' => $packaging,
            'pack_total' => ($packaging->price_per_unit / $pr_int_unit) * 1000
        ]);
    }


    public function getFreightDetails(Request $request)
    {
        $freight_id = $request->input('freight_id');
        $pr_id = $request->input('product_id');
        $freights = Freight::findOrFail($freight_id);    
        $freights->load('supplier');
        $product = Product::findOrFail($pr_id);
        $fr_cost = $freights->freight_price;
        if(in_array($freights->freight_unit,['Parcel','Kg'])){
            $fr_weight = $freights->parcel_weight;
        }else{
            switch ($freights->freight_unit) {
                case 'Ind Unit':
                    $fr_weight = $product->weight_ind_unit_g;
                    break;
                case 'Sell Unit':
                    $fr_weight = $product->weight_retail_unit_g;
                    break;
                case 'Carton':
                    $fr_weight = $product->weight_carton_g;
                    break;
                case 'Pallet':
                    $fr_weight = $product->weight_pallet_g;
                    break;
                default:
                    $fr_weight = 0;
                    break;
            }
        }

        // Guard against zero or missing weight to avoid division by zero
        $fr_weight = (float) ($fr_weight ?? 0);
        $fr_cost = (float) ($fr_cost ?? 0);
        if ($fr_weight <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Freight weight is zero or missing for the selected unit.',
                'cost_per_kg' => 0,
                'freights' => $freights,
                'fr_weight' => $fr_weight
            ], 400);
        }

        $cost_per_kg = ($fr_cost / $fr_weight) * 1000; 
        return response()->json([
            'success' => true,
            'cost_per_kg' => $cost_per_kg,
            'freights' => $freights,
            'fr_weight' => $fr_weight
        ]);
    }

    /**
     * Get packaging details for a product.
     */
    public function getSupplierDetails(Request $request)
    {
        $session = $request->session()->all();
        $clientID = (int)$session['client'];
        $workspaceID = (int)$session['workspace'];
        $packagings = Packaging::where('client_id', $clientID)->where('workspace_id', $workspaceID)->with('supplier')->get();
        $suppliers = Client_company::where('client_id', $this->clientID)->orderBy('company_name','asc')->get();
        return view('backend.product.partials.form_step6', compact('packagings', 'suppliers'));
    }

    /**
     * Remove a packaging entry from a product.
     */
    public function removePackaging(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'prod_packaging_id' => 'required|exists:prod_packaging,id'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid packaging entry.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the packaging entry
            $prodPackaging = ProdPackaging::findOrFail($request->input('prod_packaging_id'));

            // Delete the packaging entry
            $prodPackaging->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Packaging entry removed successfully.'
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing packaging entry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeFreights(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'prod_freight_id' => 'required|exists:prod_freights,id'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid packaging entry.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the packaging entry
            $prodPackaging = ProdFreight::findOrFail($request->input('prod_freight_id'));

            // Delete the packaging entry
            $prodPackaging->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Freights entry removed successfully.'
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing packaging entry: ' . $e->getMessage()
            ], 500);
        }
    }



    public function duplicate(Request $request,$id)
    {
        try {

            $product_count = Product::where('client_id',$this->clientID)->count();
            $client_plan = ClientSubscription::where('client_id',$this->clientID)->with(['plan'])->first();
            if($client_plan && $client_plan->plan->max_skus > $product_count){
                // $product = $product->toArray();
                $product = product::where('id',$id)->first();
                $pid = $product->id;
                $new_productID = self::duplicate_product($product);
                if($product->prod_image != null){
                    self::duplicate_product_image($product,$pid,$new_productID);
                }
                self::duplicate_product_ingredients($pid,$new_productID);
                self::duplicate_product_labor($pid,$new_productID);
                self::duplicate_product_machinery($pid,$new_productID);
                self::duplicate_product_packaging($pid,$new_productID);
                self::duplicate_product_freight($pid,$new_productID);
                $result['status'] = true;
                $result['message'] = "Product Duplicated.";
                $result['url'] = "/products";
            }else{
                $result['status'] = false;
                $result['message'] = ($client_plan) ? 'Already product limit reached. Contact batchbase admin' : 'Your company does not have an active subscription plan. Please contact your administrator.';
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function generate_duplicate_product_sku($product){
        $baseSku = $product->prod_sku;
        $baseName = $product->prod_name;

        // If SKU already contains 'copy', keep it as base
        if (!Str::contains($baseSku, 'copy')) {
            $baseSku .= '_copy';
            $baseName .= '_copy';
        }

        // If baseSku doesn't exist → return directly
        if (!Product::where('prod_sku', $baseSku)->exists()) {
            return [
                'sku'  => $baseSku,
                'name' => $baseName,
            ];
        }

        // Otherwise append increment number until unique
        for ($i = 1; $i <= 100; $i++) {
            $checkSku  = "{$baseSku}_{$i}";
            $checkName = "{$baseName} ({$i})";
            if (!Product::where('prod_sku', $checkSku)->exists()) {
                return [
                    'sku'  => $checkSku,
                    'name' => $checkName,
                ];
            }
        }
    }

    public function duplicate_product($product){
        
        $final_sku = self::generate_duplicate_product_sku($product);
        $data = $product->toArray();
        $filtered = Arr::except($data, ['id', 'created_by', 'updated_by','updated_at','created_at']);
        $filtered['prod_sku'] = $final_sku['sku'];
        $filtered['prod_name'] = $final_sku['name'];
        $filtered['created_by'] = $this->user_id;
        $filtered['updated_by'] = $this->user_id;
        $newProduct = Product::create($filtered);
        return $newProduct->id;

        

        // $item = new product;
        // $item->prod_sku= $product->prod_sku."_copy";
        // $item->prod_name= $product->prod_name."_copy";
        // $item->prod_custom_name= $product->prod_custom_name;
        // $item->prod_image= $product->prod_image;
        // $item->barcode_gs1= $product->barcode_gs1;
        // $item->barcode_gtin14= $product->barcode_gtin14;
        // $item->description_long= $product->description_long;
        // $item->description_short= $product->description_short;
        // $item->prod_tags= $product->prod_tags;
        // $item->product_status= $product->product_status;
        // $item->product_ranging= $product->product_ranging;
        // $item->weight_ind_unit_g= $product->weight_ind_unit_g;
        // $item->weight_retail_unit_g=$product->weight_retail_unit_g;
        // $item->weight_carton_g=$product->weight_carton_g;
        // $item->weight_pallet_g= $product->weight_pallet_g;
        // $item->count_ind_units_per_retail=$product->count_ind_units_per_retail;
        // $item->count_retail_units_per_carton=$product->count_retail_units_per_carton;
        // $item->count_cartons_per_pallet=$product->count_cartons_per_pallet;
        // $item->price_ind_unit=$product->price_ind_unit;
        // $item->price_retail_unit=$product->price_retail_unit;
        // $item->price_carton=$product->price_carton;
        // $item->price_pallet=$product->price_pallet;
        // $item->price_sell_unit_wholesale=$product->price_sell_unit_wholesale;
        // $item->price_sell_unit_wholesale_freight=$product->price_sell_unit_wholesale_freight;
        // $item->recipe_method=$product->recipe_method;
        // $item->recipe_notes=$product->recipe_notes;
        // $item->recipe_oven_temp=$product->recipe_oven_temp;
        // $item->recipe_oven_temp_unit=$product->recipe_oven_temp_unit;
        // $item->recipe_oven_time=$product->recipe_oven_time;
        // $item->recipe_mould_type=$product->recipe_mould_type;
        // $item->recipe_baking_instructions=$product->recipe_baking_instructions;
        // $item->batch_initial_weight_g=$product->batch_initial_weight_g;
        // $item->batch_baking_loss_percent=$product->batch_baking_loss_percent;
        // $item->batch_after_baking_loss_g=$product->batch_after_baking_loss_g;
        // $item->batch_waste_percent=$product->batch_waste_percent;
        // $item->batch_after_waste_g=$product->batch_after_waste_g;
        // $item->serv_per_package=$product->serv_per_package;
        // $item->serv_size_g=$product->serv_size_g;
        // $item->status=$product->status;
        // $item->created_by=$this->user_id;
        // $item->updated_by=$this->user_id;
        // $item->updated_reason=$product->updated_reason;
        // $item->updated_version=$product->updated_version;
        // $item->energy_kJ_per_100g=$product->energy_kJ_per_100g;
        // $item->protein_g_per_100g=$product->protein_g_per_100g;
        // $item->fat_total_g_per_100g=$product->fat_total_g_per_100g;
        // $item->fat_saturated_g_per_100g=$product->fat_saturated_g_per_100g;
        // $item->carbohydrate_g_per_100g=$product->carbohydrate_g_per_100g;
        // $item->sugar_g_per_100g=$product->sugar_g_per_100g;
        // $item->sodium_mg_per_100g=$product->sodium_mg_per_100g;
        // $item->energy_kJ_per_serve=$product->energy_kJ_per_serve;
        // $item->protein_g_per_serve=$product->protein_g_per_serve;
        // $item->fat_total_g_per_serve=$product->fat_total_g_per_serve;
        // $item->fat_saturated_g_per_serve=$product->fat_saturated_g_per_serve;
        // $item->carbohydrate_g_per_serve=$product->carbohydrate_g_per_serve;
        // $item->sugar_g_per_serve=$product->sugar_g_per_serve;
        // $item->sodium_mg_per_serve=$product->sodium_mg_per_serve;
        // $item->labelling_ingredients=$product->labelling_ingredients;
        // $item->labelling_allergens=$product->labelling_allergens;
        // $item->labelling_may_contain=$product->labelling_may_contain;
        // $item->labelling_ingredients_override=$product->labelling_ingredients_override;
        // $item->labelling_allergens_override=$product->labelling_allergens_override;
        // $item->labelling_may_contain_override=$product->labelling_may_contain_override;
        // $item->supplied_shelf_life_num=$product->supplied_shelf_life_num;
        // $item->supplied_temp_control_storage_yn=$product->supplied_temp_control_storage_yn;
        // $item->supplied_temp_control_transport_yn=$product->supplied_temp_control_transport_yn;
        // $item->supplied_shelf_life_units=$product->supplied_shelf_life_units;
        // $item->supplied_temp_control_storage_degrees=$product->supplied_temp_control_storage_degrees;
        // $item->supplied_temp_control_transport_degrees=$product->supplied_temp_control_transport_degrees;
        // $item->inuse_shelf_life_num=$product->inuse_shelf_life_num;
        // $item->inuse_temp_control_storage_yn=$product->inuse_temp_control_storage_yn;
        // $item->inuse_shelf_life_units=$product->inuse_shelf_life_units;
        // $item->inuse_temp_control_storage_degrees=$product->inuse_temp_control_storage_degrees;
        // $item->ing_storage_requirement=$product->ing_storage_requirement;
        // $item->ing_intended_use=$product->ing_intended_use;
        // $item->ing_date_mark=$product->ing_date_mark;
        // $item->country_of_origin=$product->country_of_origin;
        // $item->pack_packaging1=$product->pack_packaging1;
        // $item->client_id=$product->client_id;
        // $item->workspace_id=$product->workspace_id;
        // $item->favorite=$product->favorite;
        // $item->save();
        // return $item->id;
    }

    public function duplicate_product_image($product,$pid,$new_productID){
        $imgDetails  = image_library::where('module', 'product')->where('module_id', $pid)->get()->toArray();
        $filepath = "assets/{$product->client_id}/{$product->workspace_id}/product/{$new_productID}";
        foreach($imgDetails as $img){
            $img_item = new image_library;
            $img_item->SKU = $img['SKU']."_copy";
            $img_item->module =  $img['module'];
            $img_item->module_id = $new_productID;
            $img_item->image_number =  $img['image_number'];
            $img_item->image_name =  $img['image_name'];
            $img_item->default_image =  $img['default_image'];
            $img_item->file_format =  $img['file_format'];
            $img_item->file_size =  $img['file_size'];
            $img_item->folder_path = $filepath;
            $img_item->uploaded_by = $this->user_id;
            $img_item->last_modified_by = $this->user_id;
            $img_item->save();
        }
        $source = "assets/{$product->client_id}/{$product->workspace_id}/product/{$pid}";
        $dest =  "assets/{$product->client_id}/{$product->workspace_id}/product/{$new_productID}";
        return copy_files($source, $dest);
    }

    public function duplicate_product_ingredients($pid,$new_productID){
        $prod_ings = ProdIngredient::where('product_id',$pid)->get()->toArray();
        foreach ($prod_ings as $key => $value) {
            $item = new ProdIngredient;
            $item->product_id= $new_productID;
            $item->ing_id=$value['ing_id'];
            $item->product_sku=$value['product_sku'];
            $item->ing_sku=$value['ing_sku'];
            $item->ing_name=$value['ing_name'];
            $item->quantity_weight=$value['quantity_weight'];
            $item->units_g_ml=$value['units_g_ml'];
            $item->component=$value['component'];
            $item->kitchen_comments=$value['kitchen_comments'];
            $item->spec_grav=$value['spec_grav'];
            $item->quantity_g=$value['quantity_g'];
            $item->quantity_loss_g=$value['quantity_loss_g'];
            $item->quantity_waste_g=$value['quantity_waste_g'];
            $item->cost_per_kg=$value['cost_per_kg'];
            $item->cost_per_batch=$value['cost_per_batch'];
            $item->allergens=$value['allergens'];
            $item->peel_name=$value['peel_name'];
            $item->ingredient_order=$value['ingredient_order'];
            $item->ingredient_order_weight=$value['ingredient_order_weight'];
            $item->save();
        }
        return;
    }

    public function duplicate_product_labor($pid,$new_productID){
        $prod_ings = ProdLabour::where('product_id',$pid)->get()->toArray();
        foreach ($prod_ings as $key => $value) {
            $item = new ProdLabour;
            $item->product_id=$new_productID;
            $item->labour_id=$value['labour_id'];
            $item->labour_type=$value['labour_type'];
            $item->people_count=$value['people_count'];
            $item->hours_per_person=$value['hours_per_person'];
            $item->hourly_rate=$value['hourly_rate'];
            $item->product_units=$value['product_units'];
            $item->created_by=$this->user_id;
            $item->updated_by=$this->user_id;
            $item->save();
        }
        return;
    }

    public function duplicate_product_machinery($pid,$new_productID){
        $prod_ings = ProdMachinery::where('product_id',$pid)->get()->toArray();
        foreach ($prod_ings as $key => $value) {
            $item = new ProdMachinery;
            $item->product_id=$new_productID;
            $item->machinery_id=$value['machinery_id'];
            $item->machine_type=$value['machine_type'];
            $item->hours=$value['hours'];
            $item->cost_per_hour=$value['cost_per_hour'];
            $item->product_units=$value['product_units'];
            $item->weight=$value['weight'];
            $item->cost_per_kg=$value['cost_per_kg'];
            $item->created_by=$this->user_id;
            $item->updated_by=$this->user_id;
            $item->save();
        }
        return;
    }

    public function duplicate_product_packaging($pid,$new_productID){
        $prod_ings = ProdPackaging::where('product_id',$pid)->get()->toArray();
        foreach ($prod_ings as $key => $value) {
            $item = new ProdPackaging;
            $item->product_id=$new_productID;
            $item->packaging_id=$value['packaging_id'];
            $item->packaging_name=$value['packaging_name'];
            $item->packaging_type=$value['packaging_type'];
            $item->cost_per_sell_unit=$value['cost_per_sell_unit'];
            $item->weight_per_sell_unit=$value['weight_per_sell_unit'];
            $item->cost_per_kg=$value['cost_per_kg'];
            $item->created_by=$this->user_id;
            $item->updated_by=$this->user_id;
            $item->save();
        }
        return;
    }

    public function duplicate_product_freight($pid,$new_productID){
        $prod_freights = ProdFreight::where('product_id',$pid)->get()->toArray();
        foreach ($prod_freights as $key => $value) {
            $item = new ProdFreight;
            $item->product_id=$new_productID;
            $item->freight_id=$value['freight_id'];
            $item->freight_supplier=$value['freight_supplier'];
            $item->freight_cost=$value['freight_cost'];
            $item->freight_units=$value['freight_units'];
            $item->freight_weight=$value['freight_weight'];
            $item->cost_per_kg=$value['cost_per_kg'];
            $item->created_by=$this->user_id;
            $item->updated_by=$this->user_id;
            $item->save();
        }
        return;
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            product::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Product Favorite." : "Product Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function product_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $prodArray = json_decode($request->input('productobj'));
            if($archiveVal == "all" || $archiveVal == "0"){
                Product::whereIn('id',$prodArray)->update(['archive'=> 1]);
                $result['status'] = true;
                $result['message'] = "Archived all selected items";
                return response()->json($result);
            }
            $proName = [];
            foreach ($prodArray as $key => $value) {
                $product = Product::findOrFail($value);

                // Delete related data
                $product->productIngredients()->delete();
                $product->prodLabours()->delete();
                $product->prodMachinery()->delete();
                $product->prodPackaging()->delete();
                $product->prodFreights()->delete();
                $product->productLabels()->delete();
                // Delete images from image_library
                $images = $product->imageLibrary()->get();
                foreach ($images as $image) {
                    // Delete physical file
                    $imageName = $image->image_name;
                    if ($image->folder_path && !empty($imageName) && $imageName != '.') {
                        $fullPath = public_path($image->folder_path . '/' . $image->image_name);
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }

                        // Delete database record
                        $image->delete();
                    }
                }
                // Delete the product
                $product->delete();  
            }
            $result['status'] = true;
            $result['message'] = "All selected products deleted successfully";
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }


    public function custom_search(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 25); // Default to 25 if not provided
        $viewType = $request->input('view', 'list');
        $value = $request->input('id');
        $module = $request->input('module');
        $user_role = $this->role_id;
        // Fetch paginated results using the search term
           // Fetch paginated results using the search term
        $query = Product::where('client_id', $this->clientID)
        ->where('workspace_id', $this->ws_id)
        ->where('archive', 0);

        // Apply dynamic filter based on module
        switch ($module) {
            case 'category':
                $query->where('prod_category', $value);
                break;

            case 'status':
                $query->where('product_status', $value);
                break;

            case 'ranging':
                $query->where('product_ranging', $value);
                break;

            case 'tags':
                $query->where(function ($q) use ($value) {
                    foreach ($value as $tag) {
                        $q->orWhereJsonContains('prod_tags', $tag);
                    }
                });
                break;
        }

        $products = $query->orderBy('favorite', 'desc')
        ->latest('created_at')
        ->paginate($perPage)
        ->appends([
            'search' => $search,
            'perPage' => $perPage,
            'view' => $viewType,
        ]);
        $paginationLinks = $products->onEachSide(1)->links('backend.product.pagination')->render();
        if ($request->ajax()) {
            // Return the product list view and pagination as part of the response
            return response()->json([
                'data' => view('backend.product.partials.product-view', compact('products', 'viewType','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
            ]);
        }
    }


    public function price_analysis_componenet(Product $product){
        $weightTotal = $product->batch_after_waste_g;
        $cost_details = ProductCalculationController::calculate_advanced_cost_modelling_componenet($product);
        $details = ProductCalculationController::calculate_price_analysis_componenet($product);
        $cost_html = view('components.new-costing', ['cost_details' => $cost_details,'weightTotal' => $weightTotal ])->render();
        $prince_analysis_html = view('components.price-analysis', ['details' => $details,'weightTotal'=>$weightTotal ])->render();
        return response()->json([
            'success' => true,
            'prince_analysis_html' =>$prince_analysis_html,
            'cost_html' => $cost_html
        ]);
    }  

    public function direct_cost_componenet(Product $product){
        $pr_ing = ProdIngredient::where('product_id',$product->id)->get();
        $ingredient = ProductCalculationController::calculate_nutrition_information_components($product,$pr_ing);
        if($ingredient['status']){
            $rawmaterial_costing_html = $this->generate_rawmaterial_costing($ingredient['nutritionData'],$ingredient['totals'],$product->id);
        }else{
            $rawmaterial_costing_html = "";
        }
        $costingData = ProductCalculationController::calculate_direct_cost_componenet($product,$pr_ing);
        $weightTotal = ($product->batch_after_waste_g) ?? 1;
        $direct_cost_html = view('components.directcost', ['product' => $product,'costingData' => $costingData,'weightTotal'=>$weightTotal ])->render();
        return response()->json([
            'success' => true,
            'direct_cost_html' =>$direct_cost_html,
            'rawmaterial_costing_html' => $rawmaterial_costing_html
        ]);
    }  

    public function inactivity_update(Product $product){
        try {
            Product_edit_lock::where('sku_id',$product->id)->delete();
            $result['status'] = true;
        }catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function edit_lock_update(Product $product){
        try {
            if(Product_edit_lock::where('sku_id',$product->id)->where('user_id',$this->user_id)->first() == null){
                Product_edit_lock::create([
                    'sku_id' => $product->id,
                    'user_id' => $this->user_id,
                    'locked_at' => now(),
                    'expires_at' => now()->addMinutes(1),
                ]);
            }
            $result['status'] = true;
        }catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }
}

//  
