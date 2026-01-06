<?php

namespace App\Http\Controllers;

use App\Models\{Product,Ingredient,Labour,Packaging,Machinery,Fsanz,Freight,Ing_country,company,Product_category,Product_tag,Client_company,Rawmaterial_tag,Rawmaterial_category,Client_contact_tag,Client_contact,Client_contact_category,Client_company_tag,Client_company_category,Specification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{LabourExport,ProductExport,IngredientExport,MachineryExport,PackagingExport,FreightExport,FSANZExport,CompanyExport,ContactExport,SpecificationExport};
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
    private $labourArray = ['Labour ID','Labour Type','Hourly Rate ($)'];
    private $machineryArray = ['Machinery ID','Machine Name','Cost per Hour ($)'];
    private $packagingArray = ['Name','SKU','Supplier','Type','Purchase Price','Purchase Units','Price per Unit'];
    private $freightArray = ['Name','Freight ID','Supplier','Price ($)','Unit','Parcel Weight (g)','Description'];
    private $productArray = ['Product Name','SKU','Product Catagory','Product Tags','Status','Ranging','GS1 Barcode','Description','Long Description','Weight-Ind Unit','Weight-Sell Unit','Weight-Carton','Weight-Pallet','Unit-Sell Unit','Unit-Carton','Unit-Pallet','Recipe Method','Recipe Notes','Oven Time','Oven Temperature','Oven Temperature Unit','Weight Gain or Loss','Serving Per Package','Serving Size (g)','Direct Cost Contigency','Retailer Charges','Wholesale Price (ex GST)','Distributor Price (ex GST)','RRP (ex GST)','Wholesale Margin (%)','Distributor Margin (%)','Retailer Margin (%)','RRP-Ind Unit','RRP-Sell Unit','RRP-Carton','RRP-Pallet'];
    private $ingredientArray =['Ingredient SKU','Name by Kitchen','Name by Supplier','Supplier Name','Supplier Spec URL','Raw Material Category','Raw Material Tags','Status','Ranging','GTIN','Supplier Code','Ingredients List','Allergens','Price Per Item','Units Per Item','Ingredient Units','Is Liquid (Yes/No)','Price Per KG/L','Country of Origin','Australian %','Specific Gravity','Energy (kJ)','Protein (g)','Total Fat (g)','Saturated Fat (g)','Carbohydrate (g)','Sugars (g)','Sodium (mg)','Shelf Life','Description'];
    private $companyArray = ['Company Name','Website','ABN','ACN','Billing Address','Delivery Address','Company Category','Company Tags','Notes','First Name','Last Name','Email','Phone'];
    private $contactArray = ['First Name','Last Name','Email','Phone','Company','Contact Category','Contact Tags','Notes','Primary Contact'];
    private $specificationArray = [
        'Spec Name','Spec Sku','Spec Type', 'Status', 'Australian Regulatory Status', 'Description', 'Supplier Name', 'Manufacturer Name', 'Manufacturer Address', 'Manufacturer Contact', 'Distributor Name', 'Distributor Contact', 'Compliance Officer', 'Lot Number Format', 'Serving Size (g)', 'Servings per Container', 'Nutrition Basis', 'Energy (kJ)', 'Protein (g)', 'Carbohydrate (g)', 'Sodium (mg)', 'Total Fat (g)', 'Saturated Fat (g)', 'Trans Fat (g)', 'Sugars (g)', 'Added Sugars (g)', 'Dietary Fibre (g)', 'Cholesterol (mg)', 'Calcium (mg)', 'Iron (mg)', 'Potassium (mg)', 'Vitamin D (mcg)', 'Gluten Content', 'Primary Country of Origin', 'Origin Declaration', 'Australian Content (%)', 'FSANZ Standard Reference', 'Date Marking Requirement', 'Label Type','Calculation Method','Australian Made', 'Australian Owned', 'Australian Grown','Ingredient List', 'Allergen Statement', 'FSANZ Allergen Declaration', 'Percentage Labelling', 'Appearance', 'Colour', 'Odour', 'Texture', 'Density (g/mL)', 'Specific Gravity', 'Moisture (%)', 'pH Level', 'Water Activity (aw)', 'Viscosity (cP)', 'Aerobic Plate Count Max', 'Yeast & Mould Max', 'Coliforms Max', 'E. coli Max', 'Salmonella Absent in (g)', 'Listeria Absent in (g)', 'Staphylococcus Max', 'Primary Pack Type', 'Primary Pack Material', 'Primary Pack Dimensions (mm)', 'Primary Pack Weight (g)', 'Secondary Pack Type', 'Secondary Pack Material', 'Secondary Pack Dimensions (mm)', 'Units per Secondary', 'Case Dimensions (mm)', 'Case Weight (g)', 'Units per Case', 'Pallet Type', 'Pallet Dimensions (mm)', 'Pallet Height (mm)', 'Pallet Weight (kg)', 'Cases per Layer (Ti)', 'Layers per Pallet (Hi)', 'Total Cases per Pallet', 'GTIN-13 (Retail)', 'GTIN-14 (Case)', 'SSCC (Logistics)', 'Batch Code Format', 'Barcode Type', 'Advisory Statements', 'Warning Statements', 'Health Claims', 'Nutrition Content Claims', 'Organic (Certified)', 'Halal (Certified)', 'Kosher (Certified)', 'Gluten Free (Certified)', 'Non-GMO', 'Fair Trade', 'Certificate Details', 'Storage Temp Min (C)', 'Storage Temp Max (C)', 'Storage RH Min (%)', 'Storage RH Max (%)', 'Storage Conditions', 'Shelf Life Type', 'Shelf Life Value', 'Shelf Life Unit', 'Best Before Days','Use By Days','Handling Instructions', 'Disposal Instructions', 'GLN', 'Traceability System', 'Recall Procedure', 'Trace Documents Required','Lead (Pb)', 'Cadmium (Cd)','Mercury (Hg)',  'Arsenic (As)', 'Tin (Sn)', 'Chlorpyrifos', 'Glyphosate','Malathion','Permethrin','Imazalil','Residues', 'Aflatoxin B1','Aflatoxin Total','Ochratoxin A','Deoxynivalenol (DON)','Zearalenone','Patulin','Tartrazine', 'Cochineal','Sunset Yellow','Citric Acid','Ascorbic Acid','Monosodium Glutamate (MSG)','Sodium Benzoate', 'Potassium Sorbate','Calcium Propionate','Sulfur Dioxide','Sodium Nitrite','Sodium Metabisulfite'
        ];


    public function save_download_attribute(Request $request){
        try {
            $model = $request->input('model');
            $select_cols = $request->input('selectedCols');
            if($model == "products"){
                $search_term = ['Name / SKU', 'Category','Tags','Contingency', ];
                $replace     = ['SKU', 'Product Catagory','Product Tags','Direct Cost Contigency'];
                $finalArray = array_map(function ($value) use ($search_term, $replace) {
                    // Find exact match (case-sensitive)
                    $index = array_search($value, $search_term, true);
                    return $index !== false ? $replace[$index] : $value;
                }, $select_cols);
            }elseif ($model == "ingredient") {
                $search_term = ['Name', 'Supplier','Category','Tags', 'Spec'];
                $replace     = ['Name by Kitchen', 'Supplier Name','Raw Material Category','Raw Material Tags','Supplier Spec URL'];
                $finalArray = array_map(function ($value) use ($search_term, $replace) {
                    // Find exact match (case-sensitive)
                    $index = array_search($value, $search_term, true);
                    return $index !== false ? $replace[$index] : $value;
                }, $select_cols);
            }else{
                $finalArray = $select_cols;
            }
            $ex_col = implode(',',$finalArray);
            session()->put($model, $ex_col); 
            $result['status'] = true;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    /**
     * Download the data in csv format
     */
    public function download_csv(Request $request, $slug)
    {
        try {
            $SessionData = Session::all();
            $clientID = (int)$SessionData['client'];
            $workspaceID = (int)$SessionData['workspace'];
            $excludeArray = [];
            switch ($slug) {
                case 'product':
                    $finalArray = $this->product_download_csv($SessionData,$clientID,$workspaceID);
                    $fileName = 'Product_data.csv';
                    break;
                    case 'ingredient':
                        $finalArray = $this->rawmaterial_download_csv($SessionData,$clientID,$workspaceID);
                        $fileName = 'ingredient_data.csv';
                        break;
                        case 'labour':
                            $finalArray = $this->labour_download_csv($SessionData,$clientID,$workspaceID);
                            $fileName = 'labour_data.csv';
                            break;
                            case 'machinery':
                                $finalArray = $this->machinery_download_csv($SessionData,$clientID,$workspaceID);
                                $fileName = 'machinery_data.csv';
                                break;
                                case 'packaging':
                                    $finalArray = $this->packaging_download_csv($SessionData,$clientID,$workspaceID);
                                    $fileName = 'package_data.csv';
                                    break;
                                    case 'freight':
                                        $finalArray = $this->freight_download_csv($SessionData,$clientID,$workspaceID);
                                        $fileName = 'freight_data.csv';
                                        break;
                                        case 'companies':
                                            $finalArray = $this->company_download_csv($SessionData,$clientID);
                                            $fileName = 'company_data.csv';
                                            break;
                                            case 'contacts':
                                                $finalArray = $this->contact_download_csv($SessionData,$clientID);
                                                $fileName = 'contact_data.csv';
                                                break;
                                                case 'specifications':
                                                    $finalArray = $this->specification_download_csv($SessionData,$clientID);
                                                    $fileName = 'Specification_data.csv';
                                                    break;
                                                    case 'fsanz':
                                                        $excludeArray = ['id'];
                                                        $lists = Fsanz::all()->toArray();
                                                        $finalArray = self::create_csv($lists, $excludeArray);
                                                        $fileName = 'FSANZ_data.csv';
                                                        break;
                                                        default:
                                                            break;
            }
            ob_start();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $fileName . '');
            ob_end_clean();
            $output = fopen('php://output', 'w');
            fputcsv($output, $finalArray['headers']);
            foreach ($finalArray['rows'] as $data_item) {
                $flattened_row = $this->flatten_array($data_item);
                fputcsv($output, $flattened_row);
            }
            fclose($output);
            exit;
        } catch (\Exception $e) {
            return;
        }
    }

    public function product_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('products',$SessionData)){
            $session_details = explode(',',$SessionData['products']);
            $product_array = $this->productArray;
            $custom_heading = array_unique(array_merge($product_array,$session_details)); 
        }else{
            $custom_heading = $this->productArray;
        }
        $db_header = $this->convertProductHeadersToDbColumns($custom_heading);
        $collection = Product::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {
            if($item['prod_category']){
                $category = Product_category::where('client_id', $clientID)->where('workspace_id',$workspaceID)->where('id',$item['prod_category'])->pluck('name');
                if(sizeof($category) >0){
                    $item['prod_category'] = $category[0];
                }
            }
            if($item['prod_tags']){
                $ingArray = $item['prod_tags'];
                $tags = Product_tag::whereIn('id', $ingArray)->pluck('name')->toArray();
                $item['prod_tags'] = implode(', ', $tags);
            }
            return $item;
        });
        $lists = $updated->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function rawmaterial_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('ingredient',$SessionData)){
            $session_details = explode(',',$SessionData['ingredient']);
            $ing_array = $this->ingredientArray;
            $custom_heading = array_unique(array_merge($ing_array,$session_details)); 
        }else{
            $custom_heading = $this->ingredientArray;
        }
        $db_header = $this->convertHeadersToDbColumns($custom_heading);
        $collection = Ingredient::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {

            if($item['category']){
                $category = Rawmaterial_category::where('client_id', $clientID)->where('workspace_id',$workspaceID)->where('id',$item['category'])->pluck('name');
                if(sizeof($category) >0){
                    $item['category'] = $category[0];
                }
            }
            
            if($item['ing_tags']){
                $ingArray = json_decode($item['ing_tags']);
                $tags = Rawmaterial_tag::whereIn('id', $ingArray)->pluck('name')->toArray();
                $item['ing_tags'] = implode(', ', $tags);
            }

            if($item['country_of_origin']){
                $country = Ing_country::where('COID',$item['country_of_origin'])->pluck('full_name');
                if(sizeof($country) >0){
                    $item['country_of_origin'] = $country[0];
                }
            }
            
            if($item['supplier_name']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['supplier_name'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['supplier_name'] = $supplier[0];
                }
            }
            return $item;
        });
        $lists = $updated->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function labour_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('labour',$SessionData)){
            $session_details = explode(',',$SessionData['labour']);
            $labour_array = $this->labourArray;
            $custom_heading = array_unique(array_merge($labour_array,$session_details)); 
        }else{
            $custom_heading = $this->labourArray;
        }
        $db_header = $this->convertLabourHeadersToDbColumns($custom_heading);
        $lists = Labour::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($db_header)->get()->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function machinery_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('machinery',$SessionData)){
            $session_details = explode(',',$SessionData['machinery']);
            $machinery_array = $this->machineryArray;
            $custom_heading = array_unique(array_merge($machinery_array,$session_details)); 
        }else{
            $custom_heading = $this->machineryArray;
        }
        $db_header = $this->convertMachineryHeadersToDbColumns($custom_heading);
        $collection = Machinery::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {
            if($item['manufacturer']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['manufacturer'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['manufacturer'] = $supplier[0];
                }
            }
            return $item;
        });
        $lists = $updated->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function packaging_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('packaging',$SessionData)){
            $session_details = explode(',',$SessionData['packaging']);
            $package_array = $this->packagingArray;
            $custom_heading = array_unique(array_merge($package_array,$session_details)); 
        }else{
            $custom_heading = $this->packagingArray;
        }
        $db_header = $this->convertPackageHeadersToDbColumns($custom_heading);
        $collection = Packaging::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->with('supplier')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {
            if($item['supplier_id']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['supplier_id'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['supplier_id'] = $supplier[0];
                }
            }
            return $item;
        });
        $lists = $updated->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function freight_download_csv($SessionData,$clientID,$workspaceID){
        if(array_key_exists('freight',$SessionData)){
            $session_details = explode(',',$SessionData['freight']);
            $freight_array = $this->freightArray;
            $custom_heading = array_unique(array_merge($freight_array,$session_details)); 
        }else{
            $custom_heading = $this->freightArray;
        }
        $db_header = $this->convertFreightHeadersToDbColumns($custom_heading);
        $collection = Freight::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {
            if($item['freight_supplier']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['freight_supplier'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['freight_supplier'] = $supplier[0];
                }
            }
            return $item;
        });
        $lists = $updated->toArray();
        return self::create_csv($lists, $custom_heading);
    }

    public function company_download_csv($SessionData,$clientID){
        if(array_key_exists('companies',$SessionData)){
            $session_details = explode(',',$SessionData['companies']);
            $company_array = $this->companyArray;
            $custom_heading = array_unique(array_merge($company_array,$session_details)); 
        }else{
            $custom_heading = $this->companyArray;
        }
        $db_header = $this->convertCompanyHeadersToDbColumns($custom_heading);
        $db_header[] = 'id';
        $collection = Client_company::where('client_id', $clientID)->with(['primaryContact','Category'])->latest('created_at')->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) use($clientID) {
            if($item->Category){
                $item['company_category'] = $item->Category->name;
            }
            if($item['company_tags']){
                $cmpArray = json_decode($item['company_tags']);
                $tags = Client_company_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
                $item['company_tags'] = implode(', ', $tags);
            }
            if($item->primaryContact){
                $item['first_name'] = $item->primaryContact->first_name;
                $item['last_name'] = $item->primaryContact->last_name;
                $item['email'] = $item->primaryContact->email;
                $item['phone'] = $item->primaryContact->phone;
            }
            unset($item['id']);
            return $item;
        });  
        $lists = $updated->toArray();
        $keysToRemove = ['primary_contact', 'category']; //Remove unwanted index
        foreach ($lists as $index => $item) {
            foreach ($keysToRemove as $key) {
                if (array_key_exists($key, $item)) {
                    unset($lists[$index][$key]);
                }
            }
        }  
        return self::create_csv($lists, $custom_heading);
    }

    public function contact_download_csv($SessionData,$clientID){
        if(array_key_exists('contacts',$SessionData)){
            $session_details = explode(',',$SessionData['contacts']);
            $contact_array = $this->contactArray;
            $custom_heading = array_unique(array_merge($contact_array,$session_details)); 
        }else{
            $custom_heading = $this->contactArray;
        }
        $db_header = $this->convertContactHeadersToDbColumns($custom_heading);
        $collection = Client_contact::where('client_id', $clientID)->with(['ClientCompany','Category'])->latest('created_at')->select($db_header)->get();
        $updated = $collection->map(function ($item) {
            if($item->Category){
                $item['contact_category'] = $item->Category->name;
            }
            if($item['contact_tags']){
                $cmpArray = json_decode($item['contact_tags']);
                $tags = Client_contact_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
                $item['contact_tags'] = implode(', ', $tags);
            } 
            if($item->ClientCompany){
                $item['company'] = $item->ClientCompany->company_name;
            }
            return $item;
        });
        $lists = $updated->toArray();
        $keysToRemove = ['client_company', 'category']; //Remove unwanted index
        foreach ($lists as $index => $item) {
            foreach ($keysToRemove as $key) {
                if (array_key_exists($key, $item)) {
                    unset($lists[$index][$key]);
                }
            }
        }      
        return self::create_csv($lists, $custom_heading);
    }

    public function specification_download_csv($SessionData,$clientID){
        if(array_key_exists('specification',$SessionData)){
            $session_details = explode(',',$SessionData['specification']);
            $spec_array = $this->specificationArray;
            $custom_heading = array_unique(array_merge($spec_array,$session_details)); 
        }else{
            $custom_heading = $this->specificationArray;
        }        
        $db_header = $this->convertSpecificationHeadersToDbColumns($custom_heading);
        $collection = Specification::where('client_id', $clientID)->latest('created_at')->select($db_header)->get();
        $yesNoFields = [
            'cool_aus_made_claim',
            'cool_aus_owned_claim',
            'cool_aus_grown_claim',
            'trace_document_required',
            'cert_is_organic',
            'cert_is_halal',
            'cert_is_kosher',
            'cert_is_gluten_free',
            'cert_is_non_gmo',
            'cert_is_fair_trade',
        ];

        $updated = $collection->map(function ($item) use ($yesNoFields) {
            if ($item->cool_percentage_australia) {
                $item['cool_percentage_australia'] = $item->cool_percentage_australia * 100;
            }

            foreach ($yesNoFields as $field) {
                $item[$field] = isset($item->$field) && $item->$field == 1 ? 'Yes' : 'No';
            }

            if ($item->spec_type) {
                switch ($item->spec_type) {
                    case 'raw_material':
                        $type = "Raw Material";
                        break;
                    case 'product':
                        $type = "Finished Product";
                        break;
                    case 'package_material':
                        $type = "Packaging Material";
                        break;
                    default:
                        $type = "Raw Material";
                        break;
                }
                $item['spec_type'] = $type;
            }

            if ($item->nutritional_basis) {
                switch ($item->nutritional_basis) {
                    case 'g':
                        $nutrition = "Per 100g";
                        break;
                    case 'ml':
                        $nutrition = "Per 100ml";
                        break;
                    default:
                        $nutrition = "Per 100g";
                        break;
                }
                $item['nutritional_basis'] = $nutrition;
            }

            if ($item->id_barcode_type) {
                switch ($item->id_barcode_type) {
                    case '1d':
                        $bar = "1D";
                        break;
                    case '2d':
                        $bar = "2D";
                        break;
                    case 'qr':
                        $bar = "QR Type";
                        break;
                    default:
                        $bar = "1D";
                        break;
                }
                $item['id_barcode_type'] = $bar;
            }

            return $item;
        });
        $lists = $collection->toArray();  
        return self::create_csv($lists, $custom_heading);
    }


    
    /**
     * Create a CSV output from an array of records.
     */
    public function create_csv($lists,$fetchheader)
    {    
        $final['headers'] = $fetchheader;
        $final['rows'] = array_map(function ($row) {
            return array_values($row);
        }, $lists);
        return $final;
    }

    /**
     * Preprocess an array by converting any sub-arrays into comma-separated strings.
     */
    public function preprocess_array($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Convert array to comma-separated string
                $array[$key] = implode(',', $value);
            }
        }
        return $array;
    }

    /**
     * Flatten an array by converting any sub-arrays into a single-level array.
     */
    public function flatten_array($array)
    {
        $array = $this->preprocess_array($array); // Preprocess the array first
        $flattened = [];
        array_walk_recursive($array, function ($value) use (&$flattened) {
            $flattened[] = $value; // Add each value to the flattened array
        });
        return $flattened;
    }


    /**
     * Download data as an Excel file based on the provided slug.
     */
    public function download_excel($slug)
    {
        $SessionData = Session::all();
        switch ($slug) {
            case 'product':
                if(array_key_exists('products',$SessionData)){
                    $session_details = explode(',',$SessionData['products']);
                    $product_array = $this->productArray;
                    $custom_heading = array_unique(array_merge($product_array,$session_details));
                }else{
                    $custom_heading = $this->productArray;
                }
                $db_header = $this->convertProductHeadersToDbColumns($custom_heading);
                return Excel::download(new ProductExport($custom_heading,$db_header), 'Product_data.xlsx');
                break;
            case 'ingredient':
                if(array_key_exists('ingredient',$SessionData)){
                    $session_details = explode(',',$SessionData['ingredient']);
                    $ing_array = $this->ingredientArray;
                    $custom_heading = array_unique(array_merge($ing_array,$session_details)); 
                }else{
                    $custom_heading = $this->ingredientArray;
                }
                $db_header = $this->convertHeadersToDbColumns($custom_heading);
                return Excel::download(new IngredientExport($custom_heading,$db_header), 'Ingredient_data.xlsx');
                break;
            case 'labour':
                if(array_key_exists('labour',$SessionData)){
                    $session_details = explode(',',$SessionData['labour']);
                    $labour_array = $this->labourArray;
                    $custom_heading = array_unique(array_merge($labour_array,$session_details)); 
                }else{
                    $custom_heading = $this->labourArray;
                }
                $db_header = $this->convertLabourHeadersToDbColumns($custom_heading);
                return Excel::download(new LabourExport($custom_heading,$db_header), 'labour_data.xlsx');
                break;
            case 'machinery':
                if(array_key_exists('machinery',$SessionData)){
                    $session_details = explode(',',$SessionData['machinery']);
                    $machinery_array = $this->machineryArray;
                    $custom_heading = array_unique(array_merge($machinery_array,$session_details)); 
                }else{
                    $custom_heading = $this->machineryArray;
                }
                $db_header = $this->convertMachineryHeadersToDbColumns($custom_heading);
                return Excel::download(new MachineryExport($custom_heading,$db_header), 'Machinery_data.xlsx');
                break;
            case 'packaging':
                if(array_key_exists('packaging',$SessionData)){
                    $session_details = explode(',',$SessionData['packaging']);
                    $package_array = $this->packagingArray;
                    $custom_heading = array_unique(array_merge($package_array,$session_details)); 
                }else{
                    $custom_heading = $this->packagingArray;
                }
                $db_header = $this->convertPackageHeadersToDbColumns($custom_heading);
                return Excel::download(new PackagingExport($custom_heading,$db_header), 'Packaging_data.xlsx');
                break;
            case 'freight':
                $custom_heading =$this->freightArray;
                $db_header = $this->convertFreightHeadersToDbColumns($custom_heading);
                return Excel::download(new FreightExport($custom_heading,$db_header), 'freight_data.xlsx');
                break;
            case 'companies':
                if(array_key_exists('companies',$SessionData)){
                    $session_details = explode(',',$SessionData['companies']);
                    $company_array = $this->companyArray;
                    $custom_heading = array_unique(array_merge($company_array,$session_details)); 
                }else{
                    $custom_heading = $this->companyArray;
                }
                $db_header = $this->convertCompanyHeadersToDbColumns($custom_heading);
                return Excel::download(new CompanyExport($custom_heading,$db_header), 'Company_data.xlsx');
                break;
            case 'contacts':
                if(array_key_exists('contacts',$SessionData)){
                    $session_details = explode(',',$SessionData['contacts']);
                    $contact_array = $this->contactArray;
                    $custom_heading = array_unique(array_merge($contact_array,$session_details)); 
                }else{
                    $custom_heading = $this->contactArray;
                }
                $db_header = $this->convertContactHeadersToDbColumns($custom_heading);
                return Excel::download(new ContactExport($custom_heading,$db_header), 'Contacts_data.xlsx');
                break;
            case 'fsanz':
                return Excel::download(new FSANZExport, 'FSANZ_data.xlsx');
                break;
            case 'specifications':
                    if(array_key_exists('specification',$SessionData)){
                        $session_details = explode(',',$SessionData['specification']);
                        $spec_array = $this->specificationArray;;
                        $custom_heading = array_unique(array_merge($spec_array,$session_details)); 
                    }else{
                        $custom_heading = $this->specificationArray;
                    }
                    $db_header = $this->convertSpecificationHeadersToDbColumns($custom_heading);
                    return Excel::download(new SpecificationExport($custom_heading,$db_header), 'Specification_data.xlsx');
                break;
            default:
                break;
        }   
    }
    
    private function convertProductHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Product Name' => 'prod_name',
            'SKU' => 'prod_sku',
            'Name / SKU' => 'prod_sku',
            'Product Catagory' => 'prod_category',
            'Category' => 'prod_category',
            'Product Tags' => 'prod_tags',
            'Tags' => 'prod_tags',
            'Status' => 'product_status',
            'Ranging' => 'product_ranging',
            'GS1 Barcode' => 'barcode_gs1',
            'Description' => 'description_short',
            'Long Description' => 'description_long',
            'Weight-Ind Unit' => 'weight_ind_unit_g',
            'Weight-Sell Unit' => 'weight_retail_unit_g',
            'Weight-Carton' => 'weight_carton_g',
            'Weight-Pallet' => 'weight_pallet_g',
            'Unit-Sell Unit' => 'count_ind_units_per_retail',
            'Unit-Carton' => 'count_retail_units_per_carton',
            'Unit-Pallet' => 'count_cartons_per_pallet',
            'Recipe Method' => 'recipe_method',
            'Recipe Notes' => 'recipe_notes',
            'Oven Time' => 'recipe_oven_time',
            'Oven Temperature' => 'recipe_oven_temp',
            'Oven Temperature Unit' => 'recipe_oven_temp_unit',
            'Weight Gain or Loss' => 'batch_baking_loss_percent',
            'Serving Per Package' => 'serv_per_package',
            'Serving Size (g)' => 'serv_size_g',
            'Direct Cost Contigency' => 'contingency',
            'Contingency' => 'contingency',
            'Retailer Charges' => 'retailer_charges',
            'Wholesale Price (ex GST)' => 'wholesale_price_sell',
            'Distributor Price (ex GST)' => 'distributor_price_sell',
            'RRP (ex GST)' => 'rrp_ex_gst_sell',
            'Wholesale Margin (%)' => 'wholesale_margin',
            'Distributor Margin (%)' => 'distributor_margin',
            'Retailer Margin (%)' => 'retailer_margin',
            'RRP-Ind Unit' => 'price_ind_unit',
            'RRP-Sell Unit' => 'price_retail_unit',
            'RRP-Carton' => 'price_carton',
            'RRP-Pallet' => 'price_pallet'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }


    private function convertHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Ingredient SKU*' => 'ing_sku',
            'Ingredient SKU' => 'ing_sku',
            'Name by Kitchen' => 'name_by_kitchen',
            'Raw Material Name' => 'name_by_kitchen',
            'Name' => 'name_by_kitchen',
            'Name by Supplier' => 'name_by_supplier',
            'Supplier Name' => 'supplier_name',
            'Supplier' => 'supplier_name',
            'Supplier Spec URL' => 'supplier_spec_url',
            'Spec' => 'supplier_spec_url',
            'Status' => 'raw_material_status',
            'Ranging' => 'raw_material_ranging',
            'Raw Material Category' => 'category',
            'Category' => 'category',
            'Raw Material Tags' => 'ing_tags',
            'Tags' => 'ing_tags',
            'GTIN' => 'gtin',
            'Supplier Code' => 'supplier_code',
            'Supplier Spec URL Code' => 'supplier_code',
            'Ingredients List' => 'ingredients_list_supplier',
            'Allergens' => 'allergens',
            'Price Per Item' => 'price_per_item',
            'Units Per Item' => 'units_per_item',
            'Ingredient Units' => 'ingredient_units',
            'Is Liquid (Yes/No)' => 'purchase_units',
            'Price Per KG/L' => 'price_per_kg_l',
            'Country of Origin' => 'country_of_origin',
            'Australian %' => 'australian_percent',
            'Specific Gravity' => 'specific_gravity',
            'Energy (kJ)' => 'energy_kj',
            'Protein (g)' => 'protein_g',
            'Total Fat (g)' => 'fat_total_g',
            'Saturated Fat (g)' => 'fat_saturated_g',
            'Carbohydrate (g)' => 'carbohydrate_g',
            'Sugars (g)' => 'sugars_g',
            'Sodium (mg)' => 'sodium_mg',
            'Shelf Life' => 'shelf_life',
            'Description' => 'raw_material_description',
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }

    private function convertFreightHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Name' => 'name',
            'Description' => 'description',
            'Freight ID' => 'freight_id',
            'Supplier' => 'freight_supplier',
            'Price ($)' => 'freight_price',
            'Unit' => 'freight_unit',
            'Parcel Weight (g)' => 'parcel_weight'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }

    private function convertPackageHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Name' => 'name',
            'SKU' => 'pack_sku',
            'Purchase Price' => 'purchase_price',
            'Purchase Units' => 'purchase_units',
            'Price per Unit' => 'price_per_unit',
            'Type' => 'type',
            'Supplier'=>'supplier_id',
            'Supplier SKU' => 'supplier_sku',
            'Sales Channel' => 'sales_channel',
            'Environmental' => 'environmental',
            'Description' => 'description'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }

    public function convertLabourHeadersToDbColumns($headers){
        $headerMap = [
            'Labour ID' => 'labour_id',
            'Labour Type' => 'labour_type',
            'Hourly Rate ($)' => 'hourly_rate',
            'Category' => 'labour_category',
            'Notes' => 'notes'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }

    public function convertMachineryHeadersToDbColumns($headers){
        $headerMap = [
            'Machinery ID' => 'machinery_id',
            'Machine Name' => 'name',
            'Cost per Hour ($)' => 'cost_per_hour_aud',
            'Serial Number' => 'serial_number',
            'Model Number' => 'model_number',
            'Manufacturer'=>'manufacturer',
            'Energy Efficiency' => 'energy_efficiency',
            'Power Consumption (kW)' => 'power_consumption_kw',
            'Machine Condition' => 'condition',
            'Location' => 'location',
            'Year of Manufacture' => 'year_of_manufacture',
            'Maintenance Frequency' => 'maintenance_frequency',
            'Production Rate (Units/Hour)' => 'production_rate_units_hr',
            'Setup Time (Minutes)' => 'setup_time_minutes',
            'Downtime Impact ($/Hour)' => 'downtime_impact_aud_hr',
            'Wear and Tear Factor' => 'wear_and_tear_factor',
            'Last Maintenance Date' => 'last_maintenance_date',
            'Depreciation Rate (%/Year)' => 'depreciation_rate_percent_yr',
            'Additional Notes' => 'notes'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }


    public function convertCompanyHeadersToDbColumns($headers){
        $headerMap = [
            'Company Name' => 'company_name',
            'Website' => 'website',
            'ABN' => 'ABN',
            'ACN' => 'ACN',
            'Billing Address' => 'billing_address',
            'Delivery Address' => 'delivery_address',
            'Company Category' => 'company_category',
            'Company Tags' => 'company_tags',
            'Notes' => 'notes',
            // 'First Name' => 'first_name',
            // 'Last Name' => 'last_name',
            // 'Email' => 'email',
            // 'Phone' => 'phone'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }

    public function convertContactHeadersToDbColumns($headers){
        $headerMap = [
            'First Name' => 'first_name',
            'Last Name' => 'last_name',
            'Email' => 'email',
            'Phone' => 'phone',
            'Company' => 'company',
            'Contact Category' => 'contact_category',
            'Contact Tags' => 'contact_tags',
            'Notes' => 'notes',
            'Primary Contact' => 'primary_contact'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }


    public function convertSpecificationHeadersToDbColumns($headers){
        $headerMap = [
            'Spec Name' => 'spec_name',
            'Spec Sku' => 'spec_sku',
            'Spec Type' => 'spec_type',
            'Status' => 'spec_status',
            'Australian Regulatory Status' => 'aus_regulatory_status',
            'Description' => 'description',
            'Supplier Name' => 'supplier_name',
            'Manufacturer Name' => 'manufacturer_name',
            'Manufacturer Address' => 'manufacturer_address',
            'Manufacturer Contact' => 'manufacturer_contact',
            'Distributor Name' => 'distributor_name',
            'Distributor Contact' => 'distributor_contact',
            'Compliance Officer' => 'compliance_officer',
            'Lot Number Format' => 'lot_number_format',
            'Serving Size (g)' => 'nutr_serving_size_g',
            'Servings per Container' => 'nutr_servings_per_container',
            'Nutrition Basis' => 'nutritional_basis',
            'Energy (kJ)' => 'nutr_energy_kj',
            'Protein (g)' => 'nutr_protein_g',
            'Carbohydrate (g)' => 'nutr_carbohydrate_g',
            'Sodium (mg)' => 'nutr_sodium_mg',
            'Total Fat (g)' => 'nutr_fat_total_g',
            'Saturated Fat (g)' => 'nutr_fat_saturated_g',
            'Trans Fat (g)' => 'nutr_fat_trans_g',
            'Sugars (g)' => 'nutr_sugars_g',
            'Added Sugars (g)' => 'nutr_added_sugars_g',
            'Dietary Fibre (g)' => 'nutr_dietary_fiber_g',
            'Cholesterol (mg)' => 'nutr_cholesterol_mg',
            'Calcium (mg)' => 'nutr_calcium_mg',
            'Iron (mg)' => 'nutr_iron_mg',
            'Potassium (mg)' => 'nutr_potassium_mg',
            'Vitamin D (mcg)' => 'nutr_vitamin_d_mcg',
            'Gluten Content' => 'nutr_gluten_content',
            'Primary Country of Origin' => 'cool_primary_country',
            'Origin Declaration' => 'cool_origin_declaration',
            'Australian Content (%)' => 'cool_percentage_australia',
            'FSANZ Standard Reference' => 'cool_fsanz_standard_ref',
            'Date Marking Requirement' => 'cool_date_marking_requirement',
            'Label Type' => 'cool_label_type',
            'Calculation Method' => 'cool_calculation_method',
            'Australian Made' => 'cool_aus_made_claim',
            'Australian Owned' => 'cool_aus_owned_claim',
            'Australian Grown' => 'cool_aus_grown_claim',
            'Ingredient List' => 'ing_ingredient_list',
            'Allergen Statement' => 'allergen_statement',
            'FSANZ Allergen Declaration' => 'allergen_fsanz_declaration',
            'Percentage Labelling' => 'ing_percentage_labelling',
            'Appearance' => 'phys_appearance',
            'Colour' => 'phys_color',
            'Odour' => 'phys_odor',
            'Texture' => 'phys_texture',
            'Density (g/mL)' => 'phys_density_g_ml',
            'Specific Gravity' => 'phys_specific_gravity',
            'Moisture (%)' => 'phys_moisture_percent',
            'pH Level' => 'phys_ph_level',
            'Water Activity (aw)' => 'phys_water_activity',
            'Viscosity (cP)' => 'phys_viscosity_cps',
            'Aerobic Plate Count Max' => 'micro_total_plate_count_cfu_g_max',
            'Yeast & Mould Max' => 'micro_yeast_mold_cfu_g_max',
            'Coliforms Max' => 'micro_coliforms_cfu_g_max',
            'E. coli Max' => 'micro_e_coli_cfu_g_max',
            'Salmonella Absent in (g)' => 'micro_salmonella_absent_in_g',
            'Listeria Absent in (g)' => 'micro_listeria_absent_in_g',
            'Staphylococcus Max' => 'micro_staphylococcus_cfu_g_max',
            'Primary Pack Type' => 'pack_primary_type',
            'Primary Pack Material' => 'pack_primary_material',
            'Primary Pack Dimensions (mm)' => 'pack_primary_dimensions_mm',
            'Primary Pack Weight (g)' => 'pack_primary_weight_g',
            'Secondary Pack Type' => 'pack_secondary_type',
            'Secondary Pack Material' => 'pack_secondary_material',
            'Secondary Pack Dimensions (mm)' => 'pack_secondary_dimensions_mm',
            'Units per Secondary' => 'pack_units_per_secondary',
            'Case Dimensions (mm)' => 'pack_case_dimensions_mm',
            'Case Weight (g)' => 'pack_case_weight_g',
            'Units per Case' => 'pack_units_per_case',
            'Pallet Type' => 'pack_pallet_type',
            'Pallet Dimensions (mm)' => 'pack_pallet_dimensions_mm',
            'Pallet Height (mm)' => 'pack_pallet_height_mm',
            'Pallet Weight (kg)' => 'pack_pallet_weight_kg',
            'Cases per Layer (Ti)' => 'pack_cases_per_layer',
            'Layers per Pallet (Hi)' => 'pack_layers_per_pallet',
            'Total Cases per Pallet' => 'pack_total_cases_per_pallet',
            'GTIN-13 (Retail)' => 'id_gtin_13',
            'GTIN-14 (Case)' => 'id_gtin_14',
            'SSCC (Logistics)' => 'id_sscc',
            'Batch Code Format' => 'id_batch_code_format',
            'Barcode Type' => 'id_barcode_type',
            'Advisory Statements' => 'aus_advisory_statements',
            'Warning Statements' => 'aus_warning_statements',
            'Health Claims' => 'aus_health_claims',
            'Nutrition Content Claims' => 'aus_nutrition_content_claims',
            'Organic (Certified)' => 'cert_is_organic',
            'Halal (Certified)' => 'cert_is_halal',
            'Kosher (Certified)' => 'cert_is_kosher',
            'Gluten Free (Certified)' => 'cert_is_gluten_free',
            'Non-GMO' => 'cert_is_non_gmo',
            'Fair Trade' => 'cert_is_fair_trade',
            'Certificate Details' => 'cert_certificate_details',
            'Storage Temp Min (C)' => 'storage_temp_min_c',
            'Storage Temp Max (C)' => 'storage_temp_max_c',
            'Storage RH Min (%)' => 'storage_humidity_min_percent',
            'Storage RH Max (%)' => 'storage_humidity_max_percent',
            'Storage Conditions' => 'storage_conditions',
            'Shelf Life Type' => 'shelf_life_type',
            'Shelf Life Value' => 'shelf_life_value',
            'Shelf Life Unit' => 'shelf_life_unit',
            'Best Before Days' => 'best_before_days',
            'Use By Days' => 'use_by_days', 
            'Handling Instructions' => 'handling_instructions',
            'Disposal Instructions' => 'disposal_instructions',
            'GLN' => 'trace_gln',
            'Traceability System' => 'trace_system',
            'Recall Procedure' => 'trace_recall_procedure',
            'Trace Documents Required' => 'trace_document_required',
            'Lead (Pb)' => 'chem_metal_lead',
            'Cadmium (Cd)' => 'chem_metal_cadmium',
            'Mercury (Hg)' => 'chem_metal_mercury',
            'Arsenic (As)' => 'chem_metal_arsenic',
            'Tin (Sn)' => 'chem_metal_tin',
            'Glyphosate' => 'chem_pest_glyphosate',
            'Chlorpyrifos' => 'chem_pest_chlorpyrifos',
            'Malathion' => 'chem_pest_malathion',
            'Permethrin' => 'chem_pest_permethrin',
            'Imazalil' => 'chem_pest_imazalil',
            'Residues' => 'chem_pesticide_residues',
            'Aflatoxin B1' => 'chem_mycotoxin_aflatoxin_b1',
            'Aflatoxin Total' => 'chem_mycotoxin_aflatoxin_total',
            'Ochratoxin A' => 'chem_mycotoxin_ochratoxin_a',
            'Deoxynivalenol (DON)' => 'chem_mycotoxin_deoxynivalenol',
            'Zearalenone' => 'chem_mycotoxin_zearalenone',
            'Patulin' => 'chem_mycotoxin_patulin',
            'Tartrazine' => 'chem_add_tartrazine',
            'Cochineal' => 'chem_add_cochineal',
            'Sunset Yellow' => 'chem_add_sunset_yellow',
            'Citric Acid' => 'chem_add_citric_acid',
            'Ascorbic Acid' => 'chem_add_ascorbic_acid',
            'Monosodium Glutamate (MSG)' => 'chem_add_monosodium_glutamate',
            'Sodium Benzoate' => 'chem_pres_sodium_benzoate',
            'Potassium Sorbate' => 'chem_pres_potassium_sorbate',
            'Calcium Propionate' => 'chem_pres_calcium_propionate',
            'Sulfur Dioxide' => 'chem_pres_sulfur_dioxide',
            'Sodium Nitrite' => 'chem_pres_sodium_nitrite',
            'Sodium Metabisulfite' => 'chem_pres_sodium_metabisulfite'
        ];
        return $this->dbColumnFilter($headers,$headerMap);
    }




    public function dbColumnFilter($headers,$headerMap){
        $dbHeaders = [];
        foreach ($headers as $index => $header) {
            if (isset($headerMap[trim($header)])) {
                $dbHeaders[$index] = $headerMap[trim($header)];
            }
        }
        return $dbHeaders;
    }
}
