<?php

namespace App\Http\Controllers;

use App\Models\{ Specification,IngLabel,Product,Ingredient,Ing_category,Ing_subcategory,company,Ing_country,Ing_allergen,image_library,ProdIngredient,ClientSubscription,Client_company,Rawmaterial_tag,Rawmaterial_category,Ingredient_edit_lock};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class IngredientController extends Controller
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
     * Display a listing of the ingredient.
     */
    public function index(Request $request)
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
                'data' => view('backend.ingredient.grid', compact('lists', 'viewType','permission','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
            ]);
        }
        // dd($lists);

        return view('backend.ingredient.grid', compact('lists', 'viewType', 'perPage','clientID','ws_id','permission','user_role','prod_status','prod_ranging','categories','tags'));
    }


    /**
     * Searches for ingredients based on the provided search term and returns the results with pagination.
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10); // Default to 10 if not provided
        $viewType = $request->input('view', 'grid');
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
            $tags = array_filter(array_map('intval', $tags));
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('ing_tags', $tag); // integer
                    $q->orWhereJsonContains('ing_tags', (string) $tag); // string
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
        ])->appends(request()->only('tags'));
        
        foreach ($lists as $key => $ingredient) {
            if($ingredient->ing_tags){
                $ingArray = json_decode($ingredient->ing_tags);
                $tags = DB::table('rawmaterial_tags')->whereIn('id', $ingArray)->pluck('name')->toArray();
                $lists[$key]->ing_tags = implode(', ', $tags);
            }else{
                $lists[$key]->ing_tags = "";
            }
        }
        $totalCount = Ingredient::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->where('archive', 0)->count();
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
                'data' => view('backend.ingredient.partials.product-view', compact('lists', 'viewType','permission','user_role'))->render(),
                'pagination' => $paginationLinks, // Include pagination links for AJAX
                'foot_note' => $foot_note
            ]);
        }
        return view('backend.ingredient.manage', compact('lists', 'search', 'viewType', 'perPage','permission','user_role'));
    }

    /**
     * Renders the create ingredient view
     * */
    public function add()
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
        
        // return view('backend.rawmaterial_v2.form', [
        //     'statusArray' => get_rawmaterial_status_array(),
        //     'rangeArray' => get_rawmaterial_range_array(),
        //     'supplier' => Client_company::where('client_id', $this->clientID)->get()->toArray(),
        //     'country' => Ing_country::all()->toArray(),
        //     'allergen' => Ing_allergen::all()->toArray(),
        //     'categories' => Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray(),
        //     'Tags'  => Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray()
        // ]);
    }

    /**
     * Validate if an ingredient SKU exists in the database.
     */
    public function validate_sku(Request $request)
    {
        $result = Ingredient::where('ing_sku', $request->input('final_val'))->exists();
        return response()->json($result);
    }

    /**
     * Saves the dynamic source of an ingredient to the database.
     */
    public function save(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ing_sku' => [
                                'required',
                                'string',
                                Rule::unique('ingredients', 'ing_sku')
                                    ->where(function ($query) {
                                        return $query->where('client_id', session('client'))
                                                    ->where('workspace_id', session('workspace'));
                                    })
                            ],
                'ing_name' => 'required',
            ], [
                'ing_sku.required' => 'SKU is required.',
                'ing_sku.unique' => 'The SKU has already been taken for another rawmaterial.',
                'ing_name.required' => 'Ingredient name is required.',
            ]);
            if ($validator->fails()) {
                $errorMessages = $validator->errors()->all();
                $result['status'] = false;
                $result['message'] = implode(', ', $errorMessages);
                $result['message_type'] = "Validation";
            } else {
                
                $session = $request->session()->all();
                $clientID = (int)$session['client'];
                $workspaceID = (int)$session['workspace'];
                $ingredient_count = Ingredient::where('client_id',$clientID)->count();
                $client_plan = ClientSubscription::where('client_id',$clientID)->with(['plan'])->first();
                if($client_plan && $client_plan->plan->max_raw_materials > $ingredient_count){
                    $sku = $request->input('ing_sku');
                    $item = new Ingredient;
                    $item->client_id = $clientID;
                    $item->workspace_id = $workspaceID;
                    $item->ing_sku = $sku;
                    $item->name_by_kitchen = $request->input('ing_name');
                    $item->desc_by_kitchen = $request->input('ing_description');
                    $item->raw_material_description = $request->input('ing_description');
                    $item->name_by_supplier = $request->input('ing_supplier_name');
                    $item->raw_material_status = $request->input('raw_material_status');
                    $item->raw_material_ranging = $request->input('raw_material_ranging');
                    $item->supplier_name =  ($request->input('ing_supplier')) ? $request->input('ing_supplier') : null;
                    $item->supplier_code = $request->input('ing_supplier_code');
                    $item->gtin = $request->input('ing_gtin');
                    $item->shelf_life = $request->input('ing_shelf');
                    $item->category = ($request->input('ing_category')) ? $request->input('ing_category') : null;
                    $item->ing_tags = ($request->input('ing_tags')) ? json_encode($request->input('ing_tags')) : null;
                    $item->country_of_origin = ($request->input('ing_country')) ? $request->input('ing_country') : null;
                    $item->ingredients_list_supplier = $request->input('ing_ing_list');
                    $item->allergens = ($request->input('ing_allergen')) ? implode(',', $request->input('ing_allergen')) : null;
                    $item->ingredient_units = ($request->input('ing_quantity_unit')) ? $request->input('ing_quantity_unit') : null;
                    $item->purchase_units = ($request->input('ing_spec_unit')) ? $request->input('ing_spec_unit') : null;
                    // Helper function to safely convert numeric values
                    $safeConvertDecimal = function($value, $precision) {
                        if (empty($value) || $value === '' || $value === null) {
                            return null;
                        }
                        $cleaned = str_replace(',', '', (string)$value);
                        if ($cleaned === '' || !is_numeric($cleaned)) {
                            return null;
                        }
                        return convert_decimal($cleaned, $precision);
                    };

                    $item->energy_kj = $safeConvertDecimal($request->input('ing_energy'), 1);
                    $item->protein_g = $safeConvertDecimal($request->input('ing_protein'), 1);
                    $item->fat_total_g = $safeConvertDecimal($request->input('ing_total_fat'), 1);
                    $item->fat_saturated_g = $safeConvertDecimal($request->input('ing_saturated_fat'), 1);
                    $item->carbohydrate_g = $safeConvertDecimal($request->input('ing_avail_corb'), 1);
                    $item->sugars_g = $safeConvertDecimal($request->input('ing_total_sugar'), 1);
                    $item->sodium_mg = $safeConvertDecimal($request->input('ing_sodium'), 1);
                    $item->price_per_item = $safeConvertDecimal($request->input('ing_total_price'), 2);
                    $item->units_per_item = $safeConvertDecimal($request->input('ing_quantity'), 2);
                    $item->price_per_kg_l = $safeConvertDecimal($request->input('ing_unit_kg_price'), 2);
                    
                    // Handle australian_percent - need to divide by 100, but clean first
                    $ausPerValue = $request->input('ing_aus_per');
                    if (!empty($ausPerValue) && $ausPerValue !== '' && $ausPerValue !== null) {
                        $cleaned = str_replace(',', '', (string)$ausPerValue);
                        if ($cleaned !== '' && is_numeric($cleaned)) {
                            $item->australian_percent = convert_decimal((float)$cleaned / 100, 4);
                        } else {
                            $item->australian_percent = null;
                        }
                    } else {
                        $item->australian_percent = null;
                    }
                    
                    $item->specific_gravity = $safeConvertDecimal($request->input('ing_spec_gravity'), 4);
                    $item->supplier_spec_url =  ($request->input('supplier_spec_url')) ? $request->input('supplier_spec_url') : null;
                    $item->created_by = $this->user_id;
                    $item->updated_by = $this->user_id;
                    $item->save();
                    $this->save_rawmaterial_labels($request, $item->id);
                    if (isset($_FILES['image_file'])) {
                        $filepath = "assets/$clientID/$workspaceID/raw_material/$item->id";
                        $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                        if ($image_response['status'] == true) {
                            $imageArray = $image_response['final_array'];
                            $defaultImage = ($request->input('default_image')) ?(int)$request->input('default_image') : 1;
                            self::insert_images($sku, $filepath, $imageArray, $defaultImage, $item->id);
                            Ingredient::where('id', $item->id)->update(['ing_image' => $defaultImage]);
                        }
                    }
                    // $formSource = $request->input('form_source');
                    // $isRawMaterialV2 = ($formSource === 'rawmaterial_v2');
                    // if ($isRawMaterialV2) {
                    //     $result['url'] = route('rawmaterial_v2.manage');
                    //     $result['edit_url'] = route('rawmaterial_v2.edit', $item->id);
                    // } else {
                    //     $result['url'] = env('APP_URL') . "/data/raw-materials";
                    //     $result['edit_url'] = env('APP_URL') . "/data/raw-materials/edit/{$item->id}";
                    // }

                    $result['url'] = env('APP_URL') . "/data/raw-materials";
                    $result['edit_url'] = env('APP_URL') . "/data/raw-materials/edit/{$item->id}";
                    $result['status'] = true;
                    $result['message'] = "Form Saved";
                    $result['message_type'] = "Success";
                    $result['id'] = $item->id;
                }else{
                    $result['status'] = false;
                    $result['message'] = ($client_plan) ? 'Already rawmaterial limit reached. Contact nutriflow admin' : 'Your company does not have an active subscription plan. Please contact your administrator.';;
                }

            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
            $result['message_type'] = "Error";
        }
        return response()->json($result);
    }

    public function save_rawmaterial_labels($request,$ingid){
        $item = new IngLabel;
        $item->rawmaterial_id = $ingid;
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
        return;
    }

    public function insert_images($sku, $filepath, $imageArray, $defaultImage, $ingID)
    {
        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = "raw_material";
            $item->module_id = $ingID;
            $item->image_number = ++$key;
            $item->image_name =  $value['name'];
            $item->default_image = ((int)$key == $defaultImage) ? true : false;
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
        return;
    }

    public function edit($id)
    {
        $lock = Ingredient_edit_lock::where('sku_id',$id)->first();
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

        // $ingr = Ingredient::where('id', $id)->first()->toArray();

        // if(!empty($ingr['australian_percent'])){
        //     $ingr['australian_percent'] = $ingr['australian_percent'] *100;
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

        // return view('backend.ingredient.edit', [
        //     'statusArray' => get_rawmaterial_status_array(),
        //     'rangeArray' => get_rawmaterial_range_array(),
        //     'categories' => Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray(),
        //     'Tags'  => Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray(),
        //     'supplier' => Client_company::where('client_id', $this->clientID)->get()->toArray(),
        //     'country' => Ing_country::all()->toArray(),
        //     'allergen' => Ing_allergen::all()->toArray(),
        //     'details' => $ingr,
        //     'prod_labels' => IngLabel::where('rawmaterial_id', $id)->first(),
        // ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ing_sku' => [
                                'required',
                                'string',
                                Rule::unique('ingredients', 'ing_sku')
                                    ->ignore( $id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', session('client'))
                                                    ->where('workspace_id', session('workspace'));
                                    })
                            ],
                'ing_name' => 'required',
            ], [
                'ing_sku.required' => 'SKU is required.',
                'ing_sku.unique' => 'The SKU has already been taken for another rawmaterial.',
                'ing_name.required' => 'Ingredient name is required.',
            ]);
            if ($validator->fails()) {
                $errorMessages = $validator->errors()->all();
                $result['status'] = false;
                $result['message'] = implode(', ', $errorMessages);
                $result['message_type'] = "Validation";
                return response()->json($result);
            }
            $sku = $request->input('ing_sku');
            $form = $request->input('ing_form');    
            $update_data['ing_sku'] = $sku;
            $update_data['name_by_kitchen'] = $request->input('ing_name');
            $update_data['desc_by_kitchen'] = $request->input('ing_description');
            $update_data['raw_material_description'] = $request->input('ing_description');
            $update_data['name_by_supplier'] = $request->input('ing_supplier_name');
            $update_data['raw_material_status'] = $request->input('raw_material_status');
            $update_data['raw_material_ranging'] = $request->input('raw_material_ranging');
            $update_data['supplier_name'] =  ($request->input('ing_supplier')) ? $request->input('ing_supplier') : null;
            $update_data['supplier_code'] = $request->input('ing_supplier_code');
            $update_data['gtin'] = $request->input('ing_gtin');
            $update_data['category'] = ($request->input('ing_category')) ? $request->input('ing_category') : null;
            $update_data['ing_tags'] = ($request->input('ing_tags')) ? json_encode($request->input('ing_tags')) : null;
            $update_data['shelf_life'] = $request->input('ing_shelf');
            $update_data['country_of_origin'] = ($request->input('ing_country')) ? $request->input('ing_country') : null;
            $update_data['ingredients_list_supplier'] = $request->input('ing_ing_list');
            $update_data['ingredient_units'] = ($request->input('ing_quantity_unit')) ? $request->input('ing_quantity_unit') : null;
            $update_data['purchase_units'] = ($request->input('ing_spec_unit')) ? $request->input('ing_spec_unit') : null;
            $update_data['allergens'] = ($request->input('ing_allergen')) ? implode(',', $request->input('ing_allergen')) : null;

            // Helper function to safely convert numeric values
            $safeConvertDecimal = function($value, $precision) {
                if (empty($value) || $value === '' || $value === null) {
                    return null;
                }
                $cleaned = str_replace(',', '', (string)$value);
                if ($cleaned === '' || !is_numeric($cleaned)) {
                    return null;
                }
                return convert_decimal($cleaned, $precision);
            };

            $update_data['energy_kj'] = $safeConvertDecimal($request->input('ing_energy'), 1);
            $update_data['protein_g'] = $safeConvertDecimal($request->input('ing_protein'), 1);
            $update_data['fat_total_g'] = $safeConvertDecimal($request->input('ing_total_fat'), 1);
            $update_data['fat_saturated_g'] = $safeConvertDecimal($request->input('ing_saturated_fat'), 1);
            $update_data['carbohydrate_g'] = $safeConvertDecimal($request->input('ing_avail_corb'), 1);
            $update_data['sugars_g'] = $safeConvertDecimal($request->input('ing_total_sugar'), 1);
            $update_data['sodium_mg'] = $safeConvertDecimal($request->input('ing_sodium'), 1);
            $update_data['price_per_item'] = $safeConvertDecimal($request->input('ing_total_price'), 2);
            $update_data['units_per_item'] = $safeConvertDecimal($request->input('ing_quantity'), 2);
            $update_data['price_per_kg_l'] = $safeConvertDecimal($request->input('ing_unit_kg_price'), 2);
            
            // Handle australian_percent - need to divide by 100, but clean first
            $ausPerValue = $request->input('ing_aus_per');
            if (!empty($ausPerValue) && $ausPerValue !== '' && $ausPerValue !== null) {
                $cleaned = str_replace(',', '', (string)$ausPerValue);
                if ($cleaned !== '' && is_numeric($cleaned)) {
                    $update_data['australian_percent'] = convert_decimal((float)$cleaned / 100, 4);
                } else {
                    $update_data['australian_percent'] = null;
                }
            } else {
                $update_data['australian_percent'] = null;
            }
            
            $update_data['specific_gravity'] = $safeConvertDecimal($request->input('ing_spec_gravity'), 4);
            $update_data['supplier_spec_url'] = ($request->input('supplier_spec_url')) ? $request->input('supplier_spec_url') : null;            
            $update_data['updated_by'] = $this->user_id;
            Ingredient::where('id', $id)->update($update_data);

            // Product Ingredient details update
            $prodIngs_IDs = ProdIngredient::where('ing_id',$id)->pluck('id');
            if (!empty($prodIngs_IDs)) {
                    $Produpdate_data =[
                    'cost_per_kg' => $update_data['price_per_kg_l'],
                    'allergens'   => $update_data['allergens'],
                    'peel_name'   => $update_data['ingredients_list_supplier']
                ];
                ProdIngredient::whereIn('id', $prodIngs_IDs)->update($Produpdate_data); 
            }

            // Product Nutrition details updated
            $prodIngs_Collection = ProdIngredient::where('ing_id',$id)->pluck('product_id'); 
            if (!empty($prodIngs_Collection)) {
                $productIDs = $prodIngs_Collection->toArray();
                // Trigger Nutritional information Details
                updateNutritional_value($productIDs);
            }
            
            $defaultImage = ($request->input('default_image'))? (int)$request->input('default_image'): 1;
            if (isset($_FILES['image_file'])) {
                $clientID = $request->input('client_id');
                $workspaceID = $request->input('workspace');
                $filepath = "assets/$clientID/$workspaceID/raw_material/$id";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    self::update_images($sku, $filepath, $imageArray, $defaultImage, $id);
                    Ingredient::where('id', $id)->update(['ing_image' => $defaultImage]);
                }
            } else {
                $ingIamge = Ingredient::where('id', $id)->select('ing_image')->first();
                if ($ingIamge->ing_image != null) {
                    $ingCount = (int) $ingIamge->ing_image;
                    if ($ingCount != $defaultImage) {
                        image_library::where('module', 'raw_material')->where('module_id', $id)->whereIn('default_image', [1])->update(['default_image' => 0]);
                        image_library::where('module', 'raw_material')->where('module_id', $id)->where('image_number', $defaultImage)->update(['default_image' => 1]);
                        Ingredient::where('id', $id)->update(['ing_image' => $defaultImage]);
                    }
                }
            }
            $this->update_rawmaterial_labels($request,$id); //UPdate Labels
            Ingredient_edit_lock::where('sku_id',$id)->delete();
            $result['status'] = true;
            $result['form'] = $form;
            //$formSource = $request->input('form_source');
            // $isRawMaterialV2 = ($formSource === 'rawmaterial_v2');
            // if ($isRawMaterialV2) {
            //     $result['url'] = route('rawmaterial_v2.manage');
            // } else {
            //     $result['url'] = env('APP_URL') . "/data/raw-materials";   
            // }
            $result['url'] = env('APP_URL') . "/data/raw-materials";
            $result['message'] = "Form Updated";
            $result['message_type'] = "Success";
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
            $result['message_type'] = "Error";
        }
        return response()->json($result);
    }


    public function update_rawmaterial_labels($request,$id){
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

        $exists = IngLabel::where('rawmaterial_id', $id)->exists();
        if ($exists) {
            IngLabel::where('rawmaterial_id', $id)->update($updated_data);
        } else {
            $updated_data['rawmaterial_id'] = $id;
            IngLabel::insert($updated_data);
        }
        return;
    }


    public function update_images($sku, $filepath, $imageArray, $defaultImage, $ingID)
    {
        $image_count = image_library::where('module', 'raw_material')->where('module_id', $ingID)->count();
        if ($defaultImage > $image_count) {
            image_library::where('module', 'raw_material')->where('module_id', $ingID)->whereIn('default_image', [1])->update(['default_image' => 0]);
        }
        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = "raw_material";
            $item->module_id = $ingID;
            $item->image_number = ++$image_count;
            $item->image_name =  $value['name'];
            $item->default_image = ((int)$image_count == $defaultImage) ? true : false;
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
        return;
    }

    public function destroy($id)
    {
        try {            
            $ingDetails = Ingredient::where('id', $id)->first()->toArray();
            if($ingDetails['archive'] == 0){
                $prodArray = ProdIngredient::where('ing_id',$id)->pluck('product_id');
                if ($prodArray->isNotEmpty()) {
                    $pr = $prodArray->toArray();
                    $pr_count = count($pr);
                    $productsArray = Product::whereIn('id',$pr)->pluck('prod_name')->toArray();
                    $pr_names = implode(',', $productsArray);
                    $productLabel = $pr_count > 1 ? 'products' : 'product';
                    $errorMsg = "<p>This raw material is used as an ingredient in <strong>{$pr_count}</strong> {$productLabel}.
                                Reassign or remove the ingredient from these {$productLabel} before archiving.</p>
                                <p>Affected {$productLabel}: <strong>{$pr_names}</strong>.</p>";
                    return response()->json(['status' => false,'message' => $errorMsg]);
                }
                Ingredient::where('id', $id)->update(['archive' => 1]);
                return response()->json(['status' => true,'message' => 'Ingredient moved to archive status']);
            }

            if($ingDetails['ing_image']){
                $dirPath = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$id}";
                $response = all_image_remove($dirPath);
                if ($response == "success") {
                    Ingredient::where('id', $id)->delete();
                    IngLabel::where('rawmaterial_id', $id)->delete();
                    image_library::where('module', 'raw_material')->where('module_id', $id)->delete();
                    $result['status'] = true;
                } else {
                    $result['status'] = false;
                    $result['message'] = $response;
                    $result['message_type'] = "Error";
                }
            }else{
                Ingredient::where('id', $id)->delete();
                IngLabel::where('rawmaterial_id', $id)->delete();
                $result['status'] = true;
                $result['message'] = "Raw material deleted";
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
            $result['message_type'] = "Error";
        }
        return response()->json($result);
    }

    public function unarchive(Ingredient $ingredient)
    {
        try {
            $ingredient->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Ingredient unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function inactivity_update(Ingredient $ingredient){
        try {
            Ingredient_edit_lock::where('sku_id',$ingredient->id)->delete();
            $result['status'] = true;
        }catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }


    public function generate_duplicate_rawmaterial_sku($ingredient){
        $baseSku = $ingredient['ing_sku'];
        $baseName = $ingredient['name_by_kitchen'];

        // If SKU already contains 'copy', keep it as base
        if (!Str::contains($baseSku, 'copy')) {
            $baseSku .= '_copy';
            $baseName .= '_copy';
        }

        // If baseSku doesn't exist → return directly
        if (!Ingredient::where('ing_sku', $baseSku)->exists()) {
            return [
                'sku'  => $baseSku,
                'name' => $baseName,
            ];
        }

        // Otherwise append increment number until unique
        for ($i = 1; $i <= 100; $i++) {
            $checkSku  = "{$baseSku}_{$i}";
            $checkName = "{$baseName} ({$i})";
            if (!Ingredient::where('ing_sku', $checkSku)->exists()) {
                return [
                    'sku'  => $checkSku,
                    'name' => $checkName,
                ];
            }
        }
    }


    public function duplicate($id)
    {
        try {
            $ingredient_count = Ingredient::where('client_id',$this->clientID)->count();
            $client_plan = ClientSubscription::where('client_id',$this->clientID)->with(['plan'])->first();
            if($client_plan && $client_plan->plan->max_raw_materials > $ingredient_count){
                $ingDetails = Ingredient::where('id', $id)->first()->toArray();
                $final_sku = self::generate_duplicate_rawmaterial_sku($ingDetails);

                $item = new Ingredient;
                $item->client_id= $ingDetails['client_id'];
                $item->workspace_id= $ingDetails['workspace_id'];
                $item->favorite=$ingDetails['favorite'];
                $item->ing_sku= $final_sku['sku'];
                $item->name_by_kitchen=$final_sku['name'];
                $item->name_by_supplier=$ingDetails['name_by_supplier'];
                $item->ing_image=$ingDetails['ing_image'];
                $item->raw_material_status = $ingDetails['raw_material_status'];
                $item->raw_material_ranging = $ingDetails['raw_material_ranging'];
                $item->gtin=$ingDetails['gtin'];
                $item->supplier_code=$ingDetails['supplier_code'];
                $item->supplier_name=$ingDetails['supplier_name'];
                $item->category=$ingDetails['category'];
                $item->ing_tags=$ingDetails['ing_tags'];
                $item->ingredients_list_supplier=$ingDetails['ingredients_list_supplier'];
                $item->allergens=$ingDetails['allergens'];
                $item->price_per_item=$ingDetails['price_per_item'];
                $item->units_per_item=$ingDetails['units_per_item'];
                $item->ingredient_units=$ingDetails['ingredient_units'];
                $item->purchase_units=$ingDetails['purchase_units'];
                $item->price_per_kg_l=$ingDetails['price_per_kg_l'];
                $item->country_of_origin=$ingDetails['country_of_origin'];
                $item->australian_percent=$ingDetails['australian_percent'];
                $item->specific_gravity=$ingDetails['specific_gravity'];
                $item->energy_kj=$ingDetails['energy_kj'];
                $item->protein_g=$ingDetails['protein_g'];
                $item->fat_total_g=$ingDetails['fat_total_g'];
                $item->fat_saturated_g=$ingDetails['fat_saturated_g'];
                $item->carbohydrate_g=$ingDetails['carbohydrate_g'];
                $item->sugars_g=$ingDetails['sugars_g'];
                $item->sodium_mg=$ingDetails['sodium_mg'];
                $item->shelf_life=$ingDetails['shelf_life'];
                $item->raw_material_description=$ingDetails['raw_material_description'];
                $item->supplier_spec_url=$ingDetails['supplier_spec_url'];
                $item->ai_predicted_allergence=$ingDetails['ai_predicted_allergence'];
                $item->ingredients_peal=$ingDetails['ingredients_peal'];
                $item->desc_by_kitchen=$ingDetails['desc_by_kitchen'];
                $item->created_by=$this->user_id;
                $item->updated_by=$this->user_id;
                $item->save();
                if($ingDetails['ing_image'] != null){
                    $imgDetails  = image_library::where('module', 'raw_material')->where('module_id', $id)->get()->toArray();
                    $filepath = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$item->id}";
                    foreach($imgDetails as $img){
                        $img_item = new image_library;
                        $img_item->SKU = $img['SKU']."_copy";
                        $img_item->module =  $img['module'];
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
                    $source = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$id}";
                    $dest =  "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$item->id}";
                    $response = self::copy_files($source, $dest);
                }
                $result['status'] = true;
                $result['message'] = "Ingredient Duplicated.";
                $result['url'] = "/data/raw-materials";
            }else{
                $result['status'] = false;
                $result['message'] = ($client_plan) ? 'Already rawmaterial limit reached. Contact nutriflow admin' : 'Your company does not have an active subscription plan. Please contact your administrator.';;
            }
            
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    
    public function copy_files($source,$dest){
        try {
            if (!is_dir($dest)) {
                mkdir('./'. $dest, 0777, TRUE);
            }

            if (substr($dest, strlen($dest) - 1, 1) != '/') {
                $dest .= '/';
            }
            if (substr($source, strlen($source) - 1, 1) != '/') {
                $source .= '/';
            }

            $files = glob($source . '*', GLOB_MARK);
            foreach ($files as $key => $value) {
                $fileArray = explode('/',$value);
                $file = end($fileArray);
                copy("$source/$file", "$dest/$file");
            }
            $message = true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
       return $message;
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            Ingredient::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Raw material Favorite." : "Raw material Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function export_columns(Request $request){

        $expArray = $request->input('ex_xol');
        if($expArray[0] == "ALL"){
            array_shift($expArray);
        }
        $ex_col = implode(',',$expArray);
        session()->put('raw_material', $ex_col);
        $result['status'] = true;
        return response()->json($result);
    }

    public function rawmaterial_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $IngArray = json_decode($request->input('ingobj'));
            $IngName = [];

            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($IngArray as $key => $value) {
                    if(ProdIngredient::where('ing_id',$value)->count() == 0){
                        Ingredient::where('id',$value)->update(['archive'=> 1]);
                    }else{
                        $ingr = Ingredient::where('id', $value)->select('name_by_kitchen')->first();
                        $IngName[] = $ingr->name_by_kitchen;
                    }
                }

                if(sizeof($IngName) > 0 ){
                    $result['status'] = false;
                    $undeleteIng = implode(',',$IngName);
                    $message = "Follwing Raw material not archive because these assigned some products.: {$undeleteIng}";
                }else{
                    $result['status'] = true;
                    $message = "Archived all selected items";
                }
                // Ingredient::whereIn('id',$IngArray)->update(['archive'=> 1]);
                $result['message'] = $message;
                return response()->json($result);
            }
            
            foreach ($IngArray as $key => $value) {
                if(ProdIngredient::where('ing_id',$value)->count() == 0){
                    $ingDetails = Ingredient::where('id', $value)->first()->toArray();
                    if($ingDetails['ing_image']){
                        $dirPath = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$value}";
                        $response = all_image_remove($dirPath);
                        if ($response == "success") {
                            Ingredient::where('id', $value)->delete();
                        }
                    }else{
                        Ingredient::where('id', $value)->delete();
                    }
                }else{
                    $ingr = Ingredient::where('id', $value)->select('name_by_kitchen')->first();
                    $IngName[] = $ingr->name_by_kitchen;
                }
            }
            if(sizeof($IngName) > 0 ){
                $result['status'] = false;
                $undeleteIng = implode(',',$IngName);
                $message = "Follwing Raw material not delete because these assigned some products.: {$undeleteIng}";
            }else{
                $result['status'] = true;
                $message = "Raw material delete successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function storeSpec(Request $request)
    {
        try {
            // Validate
            $validated = $request->validate([
                'specification_id' => 'required|string',
            ]);

            $spec = Specification::findOrFail($request->specification_id);

            $validator = Validator::make($request->all(), [
                'code' => [
                    'required',
                    'string',
                    Rule::unique('ingredients', 'ing_sku')
                        ->where(function ($query) {
                            return $query->where('client_id', session('client'))
                                        ->where('workspace_id', session('workspace'));
                        })
                ]
            ], [
                'code.required' => 'SKU is required.',
                'code.unique' => 'The SKU has already been taken for another rawmaterial.',
            ]);

            if ($validator->fails()) {
                $errorMessages = $validator->errors()->all();

                return response()->json([
                    'success' => false,
                    'message' => implode(', ', $errorMessages),  
                ], 422);
            }

            // Derive unit pricing from request inputs
            $unitPricing = $this->calculateUnitPricing(
                (float) $request->purchase_price_ex_gst,
                (float) $request->minimum_order_quantity,
                $request->pricing_unit,
                (float) ($request->specific_gravity ?? 1)
            );

            $item = new Ingredient;
            $item->client_id = $this->clientID;
            $item->workspace_id = $this->ws_id;
            $item->ing_sku = $request->code?? 'FSANZ';
            $item->name_by_kitchen = $request->name?? $spec->spec_name;
            $item->desc_by_kitchen = $request->description?? $spec->description;
            $item->supplier_name =  $request->supplier??null;
            $item->ingredients_list_supplier = $spec->ing_ingredient_list??null;
            $item->allergens = $spec->allergen_statement?? null;
            $item->energy_kj = $spec->nutr_energy_kj;
            $item->protein_g = $spec->nutr_protein_g;
            $item->fat_total_g = $spec->nutr_fat_total_g;
            $item->fat_saturated_g = $spec->nutr_fat_saturated_g;
            $item->carbohydrate_g = $spec->nutr_carbohydrate_g;
            $item->sugars_g = $spec->nutr_sugars_g;
            $item->sodium_mg = $spec->nutr_sodium_mg;
            $item->price_per_item = $request->purchase_price_ex_gst;
            $item->price_per_kg_l = $unitPricing['price_per_100g'];
            $item->ingredient_units = $request->pricing_unit ? strtolower($request->pricing_unit) : null;
            $item->units_per_item = $request->minimum_order_quantity??null;
            $item->specific_gravity = $request->specific_gravity??null;
            $item->country_of_origin = $spec->cool_primary_country
                                    ? Ing_country::where('name', $spec->cool_primary_country)->value('COID')
                                    : null;
            $item->australian_percent = $spec->cool_percentage_australia??null;
            $item->shelf_life = $spec->storage_conditions??null;
            $item->source = "Specification";
            $item->created_by = $this->user_id;
            $item->updated_by = $this->user_id;
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Raw material created successfully.'
            ]); // <— send 201 Created status

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Calculate price per kg and per 100g based on total price, quantity, unit, and specific gravity.
     */
    private function calculateUnitPricing(float $totalPrice, float $qty, ?string $unit, float $specGravity = 1.0): array
    {
        $unit = strtolower($unit ?? '');
        $sg = $specGravity > 0 ? $specGravity : 1;

        // Convert to mass in kg
        $massKg = match ($unit) {
            'g'  => $qty / 1000,
            'kg' => $qty,
            'ml' => ($qty * $sg) / 1000,
            'l'  => $qty * $sg,
            default => 0,
        };

        $pricePerKg = ($massKg > 0) ? $totalPrice / $massKg : 0;
        $pricePer100g = $pricePerKg / 10;

        return [
            'price_per_kg' => round($pricePerKg, 2),
            'price_per_100g' => round($pricePer100g * 10, 2)
        ];
    }

}


