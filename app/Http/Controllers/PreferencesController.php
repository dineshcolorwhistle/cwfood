<?php

namespace App\Http\Controllers;
use App\Models\{Product_tag,Product_category,Rawmaterial_tag,Rawmaterial_category,Recipe_component,Ingredient,Product,ProdIngredient};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Validator
};
use Illuminate\Validation\Rule;

class PreferencesController extends Controller
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
        $product_categories = Product_category::with(['creator','products'])->withCount('products')->where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $produc_tags = $this->get_product_tag_lists();
        $rawmaterial_categories = Rawmaterial_category::with(['creator','raw_materials'])->withCount('raw_materials')->where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $rawmaterial_tags = $this->get_rawmaterial_tag_lists();
        $recipe_components = $this->recipe_component_count();
        return view('backend.preferences.index', compact('product_categories', 'produc_tags', 'rawmaterial_categories','rawmaterial_tags','recipe_components'));
    }

    public function get_product_tag_lists(){
        $ingredients = Product::select('id', 'prod_name', 'prod_tags')->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get();
        // 2. Build a tag-to-ingredient mapping from ing_tags JSON field
        $tagMap = [];
        foreach ($ingredients as $ingredient) {
            $tags = $ingredient->prod_tags ?? [];
            foreach ($tags as $tagId) {
                $tagMap[$tagId][] = $ingredient->prod_name;
            }
        }
        // 3. Fetch all tags and attach count + sample product names
        $product_tags = Product_tag::with(['creator'])->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get();
        foreach ($product_tags as $tag) {
            $tagId = $tag->id;
            $associatedItems = $tagMap[$tagId] ?? [];
            $tag->product_count = count($associatedItems);
            $tooltipHtml = '<ol>';
            foreach ($associatedItems as $item) {
                $tooltipHtml .= '<li>' . htmlspecialchars($item) . '</li>';
            }
            $tooltipHtml .= '</ol>';  
            $tag->products_list = $tooltipHtml; // show up to 5 names
        }
        return $product_tags->toArray();
    }


    public function get_rawmaterial_tag_lists(){
        // 1. Fetch all raw materials (ingredients) with relevant tag info
        $ingredients = Ingredient::select('id', 'name_by_kitchen', 'ing_tags')->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get();
        // 2. Build a tag-to-ingredient mapping from ing_tags JSON field
        $tagMap = [];
        foreach ($ingredients as $ingredient) {
            $tags = json_decode($ingredient->ing_tags, true) ?? [];
            foreach ($tags as $tagId) {
                $tagMap[$tagId][] = $ingredient->name_by_kitchen;
            }
        }
        // 3. Fetch all tags and attach count + sample product names
        $rawmaterial_tags = Rawmaterial_tag::with(['creator'])->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->get();
        foreach ($rawmaterial_tags as $tag) {
            $tagId = $tag->id;
            $associatedItems = $tagMap[$tagId] ?? [];
            $tag->raw_materials_count = count($associatedItems);
            $tooltipHtml = '<ol>';
            foreach ($associatedItems as $item) {
                $tooltipHtml .= '<li>' . htmlspecialchars($item) . '</li>';
            }
            $tooltipHtml .= '</ol>';
            $tag->raw_material_list = $tooltipHtml; // show up to 5 names
        }
        return $rawmaterial_tags->toArray();
    }

    public function recipe_component_count(){
        $component_list = Recipe_component::with(['creator'])->where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $prducts = Product::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->pluck('id')->toArray();
        foreach ($component_list as $key => $value) {
            $component_list[$key]['component_count'] = ProdIngredient::whereIn('product_id',$prducts)->where('component',$value['name'])->count();
        }
        return $component_list;
    }




    public function store(Request $request)
    {
       try {
            $module = $request->input('module');
            if($module == "Product Category"){
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('product_categories')
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $this->clientID;
                $data['workspace_id'] = $this->ws_id;
                Product_category::create($data);
            }elseif ($module == "Product Tags") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('product_tags')
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $this->clientID;
                $data['workspace_id'] = $this->ws_id;
                Product_tag::create($data);
            }elseif ($module == "Rawmaterial Category") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('rawmaterial_categories')
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $this->clientID;
                $data['workspace_id'] = $this->ws_id;
                Rawmaterial_category::create($data);
            }elseif ($module == "Rawmaterial Tags") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('rawmaterial_tags')
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $this->clientID;
                $data['workspace_id'] = $this->ws_id;
                Rawmaterial_tag::create($data);
            }elseif ($module == "Recipe Component") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('recipe_components')
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $data['client_id'] = $this->clientID;
                $data['workspace_id'] = $this->ws_id;
                Recipe_component::create($data);
            }
            return response()->json(['success' => true, 'message' =>"$module Added"]);
       } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' =>$e->getMessage()]);
       }
    }

    public function update(Request $request)
    {
       try {
            $module = $request->input('module');
            $id = $request->input('id');
            if($module == "Product Category"){
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('product_categories')
                                    ->ignore($id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                Product_category::where('id',$id)->update($data);
            }elseif ($module == "Product Tags") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('product_tags')
                                    ->ignore($id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                Product_tag::where('id',$id)->update($data);
            }elseif ($module == "Rawmaterial Category") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('rawmaterial_categories')
                                    ->ignore($id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                Rawmaterial_category::where('id',$id)->update($data);
            }elseif ($module == "Rawmaterial Tags") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('rawmaterial_tags')
                                    ->ignore($id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                Rawmaterial_tag::where('id',$id)->update($data);
            }elseif ($module == "Recipe Component") {
                $validator = Validator::make($request->all(), [
                            'name' => [
                                'required',
                                'string',
                                Rule::unique('recipe_components')
                                    ->ignore($id)
                                    ->where(function ($query) {
                                        return $query->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id);
                                    }),
                            ],
                        ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                }
                $data = $validator->validated();
                $exist = Recipe_component::where('id',$id)->pluck('name');
                if($exist && $exist['0'] != $data['name']){
                    $ingredientIds = ProdIngredient::where('component',$exist['0'])->whereIn('product_id', function ($query) {
                                            $query->select('id')
                                                ->from('products')
                                                ->where('client_id', $this->clientID)
                                                ->where('workspace_id', $this->ws_id);
                                        })->pluck('id')->toArray();
                    if(count($ingredientIds) > 0){
                        ProdIngredient::whereIn('id',$ingredientIds)->update(['component' => $data['name']]);
                    }
                }
                Recipe_component::where('id',$id)->update($data);
            }
            return response()->json(['success' => true, 'message' =>"$module Updated."]);
       } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' =>$e->getMessage()]);
       }
    }

    public function delete(Request $request)
    {
       try {
            $module = $request->input('module');
            $id = $request->input('id');
            if($module == "Product Category"){
                Product_category::where('id',$id)->delete();
            }elseif ($module == "Product Tags") {
                Product_tag::where('id',$id)->delete();
            }elseif ($module == "Rawmaterial Category") {
                Rawmaterial_category::where('id',$id)->delete();
            }elseif ($module == "Rawmaterial Tags") {
                Rawmaterial_tag::where('id',$id)->delete();
            }elseif ($module == "Recipe Component") {
                Recipe_component::where('id',$id)->delete();
            }            
            return response()->json(['success' => true, 'message' =>"$module Deleted."]);
       } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' =>$e->getMessage()]);
       }
    }

    public function make_default(Request $request,Recipe_component $preference)
    {
       try {        
             // Use a transaction to ensure data consistency
        DB::transaction(function () use ($preference) {
            // Reset all components for the same client and workspace
            Recipe_component::where('client_id', $preference->client_id)
                ->where('workspace_id', $preference->workspace_id)
                ->update(['default' => 0]);

            // Mark the selected one as default
            $preference->update(['default' => 1]);
        });
            return response()->json(['success' => true, 'message' =>"Default Component updated"]);
       } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' =>$e->getMessage()]);
       }
    }


}