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
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function download_specs(Product $product, $slug)
    {
        // Eager load relationships to avoid N+1 queries
        $product->load([
            'creator',
            'updater',
            'productClient',
            'prodLabels',
            'ingredients' => function ($query) {
                $query->orderBy('ingredient_order');
            }
        ]);
        $allergen = Ing_allergen::pluck('name')->toArray(); 
        $prod_ings = ProdIngredient::where('product_id',$product->id)->get();
        $prod_ingredient = ProductCalculationController::calculate_nutrition_information_components($product,$prod_ings);
        $product->australian_percent = ($prod_ingredient)? $prod_ingredient['totals']['australian_percent']:0;
        $product = $this->update_company_profile_details($product);
        
        //load export pdf common details
        $response = $this->create_all_images($product,$slug);
        $client_logo = $response['client_logo'];
        $product_image = $response['product_image'];
        $batchbase_logo = $response['batchbase_logo'];
        $colors = $response['colors'];
        $ABN = $response['ABN'];
        $type = $slug;

        $pdf = Pdf::loadView('exports.spec', compact('product','client_logo','product_image','allergen','batchbase_logo','type','colors','ABN'));
        // return $pdf->stream();  
        $filename = "{$product->prod_name} ".date('d F Y'); 
        return $pdf->download("{$filename}.pdf");  
    }

    public function download_recipe(Product $product, $slug)
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
        // Group ingredients by component
        $batchTotalArray = []; 
        $groupedIngredients = $product->productIngredients->groupBy('component');
        foreach ($groupedIngredients as $component => $ingredients) {
            foreach ($ingredients as $ingredient) {
                if (in_array($ingredient->units_g_ml, ['kg', 'L', 'l'])) {
                    $temp = $ingredient->quantity_g * 1000;
                    $ingredient->quantity_g = round((float) $temp, 2);;   
                }
                $batchTotalArray[] = round((float) $ingredient->quantity_g, 2);
            }
        }
        $batchTotal = array_sum($batchTotalArray);

        //load export pdf common details
        $product = $this->update_company_profile_details($product);
        $response = $this->create_all_images($product,$slug);
        $client_logo = $response['client_logo'];
        $product_image = $response['product_image'];
        $batchbase_logo = $response['batchbase_logo'];
        $colors = $response['colors'];
        $ABN = $response['ABN'];
        $type = $slug;
        
        $pdf = Pdf::loadView('exports.recipe', compact('product','client_logo','product_image','batchbase_logo','type','colors','ABN','groupedIngredients','batchTotal'));
        // return $pdf->stream();    
        $filename = "{$product->prod_name} ".date('d F Y'); 
        return $pdf->download("{$filename}.pdf");  

    }   

    public function download_labelling(Product $product, $slug)
    {
        $product->load([
            'prodLabels',
            'ingredients' => function ($query) {
                $query->orderBy('ingredient_order');
            }
        ]);
        $prod_ings = ProdIngredient::where('product_id',$product->id)->get();
        $prod_ingredient = ProductCalculationController::calculate_nutrition_information_components($product,$prod_ings);
        $product->australian_percent = ($prod_ingredient)? $prod_ingredient['totals']['australian_percent']:0;     
        $prod_packaging = ProdPackaging::where('product_id', $product->id)->get()->toArray();
        $packaging = (count($prod_packaging) > 0) ? implode(',', array_column($prod_packaging,'packaging_name')) :"No Packaging selected.";
        $health_claims = "Health Claims not selected";
        if($product->prodLabels){
            $labelArray = [
                'Halal' => 'rm_halal_yn',
                'Kosher' => 'rm_kosher_yn',
                'Organic' => 'rm_organic_yn',
                'Biodynamic' => 'rm_bio_yn',
                'Octo-lacto-vegetarian' => 'rm_octo_yn',
                'Lacto-vegetarian' => 'rm_lacto_yn',
                'Vegan' => 'rm_vegan_yn'
            ];
            $main = [];
            foreach ($labelArray as $key => $value) {
                if (isset($product->prodLabels->$value) && strcasecmp($product->prodLabels->$value, "Yes") === 0) {
                    $main[] = $key;
                }
            }
            $health_claims = (count($main) > 0 )? implode(',',$main) : "Health Claims not selected";
        }
        $allergen = Ing_allergen::pluck('name')->toArray(); 

        //load export pdf common details
        $product = $this->update_company_profile_details($product);
        $response = $this->create_all_images($product,$slug);
        $client_logo = $response['client_logo'];
        $product_image = $response['product_image'];
        $batchbase_logo = $response['batchbase_logo'];
        $colors = $response['colors'];
        $ABN = $response['ABN'];
        $type = $slug;

        $pdf = Pdf::loadView('exports.labelling', compact('product','client_logo','product_image','batchbase_logo','type','colors','ABN','allergen','packaging','health_claims'));
        // return $pdf->stream();  
        $filename = "{$product->prod_name} ".date('d F Y'); 
        return $pdf->download("{$filename}.pdf");  

    }

    public function download_costing(Product $product, $slug)
    {
        //load export pdf common details
        $product = $this->update_company_profile_details($product);
        $response = $this->create_all_images($product,$slug);
        $client_logo = $response['client_logo'];
        $product_image = $response['product_image'];
        $batchbase_logo = $response['batchbase_logo'];
        $colors = $response['colors'];
        $ABN = $response['ABN'];
        $type = $slug;

        $pr_ing = ProdIngredient::where('product_id',$product->id)->get();
        $nutrition = ProductCalculationController::calculate_nutrition_information_components($product,$pr_ing);
        $directcost =  ProductCalculationController::calculete_product_directcost($product);
        $rawmaterial_cost_html = view('components.costing.rawmaterial-costing', ['nutrition' => $nutrition,'directcost'=>$directcost ])->render();
        $costingData = ProductCalculationController::calculate_direct_cost_componenet($product,$pr_ing);
        $weightTotal = $product->batch_after_waste_g ?? 1;
        $direct_cost_html = view('components.directcost', ['product' => $product,'costingData' => $costingData,'weightTotal'=>$weightTotal ])->render();
        $details = ProductCalculationController::calculate_price_analysis_componenet($product);
        $prince_analysis_html = view('components.price-analysis', ['details' => $details,'weightTotal'=>$weightTotal ])->render();
        $pdf = Pdf::loadView('exports.costing', compact('product','client_logo','product_image','batchbase_logo','type','colors','ABN','direct_cost_html','prince_analysis_html','rawmaterial_cost_html'));
        // return $pdf->stream();  
        $filename = "{$product->prod_name} ".date('d F Y'); 
        return $pdf->download("{$filename}.pdf"); 
    }

    public function update_company_profile_details($product){
        //update company factory
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

        //update company keyperson
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

    public function create_all_images($product,$slug){
        $spath = get_client_logo();
        $stype = pathinfo($spath, PATHINFO_EXTENSION);
        $simage = file_get_contents($spath);
        $response['client_logo'] = 'data:image/' . $stype . ';base64,' . base64_encode($simage);

        $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
        $prImgpath = getModuleImage('product', $product->id, $sequenceNumber);
        $prImgtype = pathinfo($prImgpath, PATHINFO_EXTENSION);
        $primage = file_get_contents($prImgpath);
        $response['product_image'] = 'data:image/' . $prImgtype . ';base64,' . base64_encode($primage);

        if($slug == "custom"){
            $bppath = "https://app.batchbase.com.au/assets/img/powered.png";
        }else{
            $bppath = "https://app.batchbase.com.au/assets/img/black&white.png";
        }    
        $bptype = pathinfo($bppath, PATHINFO_EXTENSION);
        $bpimage = file_get_contents($bppath);
        $response['batchbase_logo'] = 'data:image/' . $bptype . ';base64,' . base64_encode($bpimage);
        
        $type="black";
        $response['colors'] = [];
        $profile = ClientProfile::where('client_id', $product->client_id)->first();
        $response['ABN'] = ($profile) ? $profile->abn : "N/A";
        if($slug == "custom"){
            $type="custom";
            if($profile){
                $response['colors']['primaryColor'] = $profile->primaryColor;
                $response['colors']['secondaryColor'] = $profile->secondaryColor;
                $response['colors']['accentColor'] = $profile->accentColor;
            }
        }
        return $response;
    }

}