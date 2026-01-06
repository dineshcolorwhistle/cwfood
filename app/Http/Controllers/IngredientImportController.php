<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\{Ing_country,Client_company,Rawmaterial_tag,Rawmaterial_category,ClientSubscription,Ing_allergen,ProdIngredient};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel as ExcelType;
use App\Imports\CommonImport;

class IngredientImportController extends Controller
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
     * Display the ingredient import page.
     */
    public function index()
    {
        return view('backend.ingredient-import.index');
    }


    private $numericFields = [
        'price_per_item' => ['type' => 'decimal', 'precision' => 2],
        'units_per_item' => ['type' => 'decimal', 'precision' => 2],
        'price_per_kg_l' => ['type' => 'decimal', 'precision' => 2],
        'australian_percent' => ['type' => 'decimal', 'precision' => 1],
        'specific_gravity' => ['type' => 'decimal', 'precision' => 2],
        'energy_kj' => ['type' => 'decimal', 'precision' => 1],
        'protein_g' => ['type' => 'decimal', 'precision' => 1],
        'fat_total_g' => ['type' => 'decimal', 'precision' => 1],
        'fat_saturated_g' => ['type' => 'decimal', 'precision' => 1],
        'carbohydrate_g' => ['type' => 'decimal', 'precision' => 1],
        'sugars_g' => ['type' => 'decimal', 'precision' => 1],
        'sodium_mg' => ['type' => 'decimal', 'precision' => 1],
    ];

    /**
     * Generates and downloads an Excel template for ingredient import.
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'ing_sku' => 'Ingredient SKU',
                'name_by_kitchen' => 'Name by Kitchen',
                'name_by_supplier' => 'Name by Supplier',
                'supplier_name' => 'Supplier Name',
                'supplier_spec_url' => 'Supplier Spec URL',
                'category' => 'Raw Material Catagory',
                'ing_tags' => 'Raw Material Tags',
                'raw_material_status' => 'Status',
                'raw_material_ranging' => 'Ranging',
                'gtin' => 'GTIN',
                'supplier_code' => 'Supplier Code',
                'ingredients_list_supplier' => 'Ingredients List',
                'allergens' => 'Allergens',
                'price_per_item' => 'Price Per Item',
                'units_per_item' => 'Units Per Item',
                'ingredient_units' => 'Ingredient Units',
                'purchase_units' => 'Is Liquid (Yes/No)',
                'price_per_kg_l' => 'Price Per KG/L',
                'country_of_origin' => 'Country of Origin',
                'australian_percent' => 'Australian %',
                'specific_gravity' => 'Specific Gravity',
                'energy_kj' => 'Energy (kJ)',
                'protein_g' => 'Protein (g)',
                'fat_total_g' => 'Total Fat (g)',
                'fat_saturated_g' => 'Saturated Fat (g)',
                'carbohydrate_g' => 'Carbohydrate (g)',
                'sugars_g' => 'Sugars (g)',
                'sodium_mg' => 'Sodium (mg)',
                'shelf_life' => 'Shelf Life',
                'raw_material_description' => 'Description'
            ];

            // Write headers
            $col = 1;
            foreach ($headers as $key => $header) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($col) . '1';
                $sheet->setCellValue($cellCoordinate, $header);
                // Style headers
                $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
                $sheet->getStyle($cellCoordinate)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E0E0');
                // Set column width
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                $col++;
            }

            $suppliersName = Client_company::where('client_id', $this->clientID)->orderBy('company_name','asc')->pluck('company_name')->toArray();
            if(sizeof($suppliersName) > 0){
                $dropdownSheet = $spreadsheet->createSheet();
                $dropdownSheet->setTitle('DropdownData');
                foreach ($suppliersName as $index => $name) {
                    $dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $supplierRange = "'DropdownData'!\$A\$1:\$A\$" . count($suppliersName);
            }else{
                $supplierRange = '""';
            }
            $supplierColumn = array_search('supplier_name', array_keys($headers)) + 1;
            $supplierColLetter = Coordinate::stringFromColumnIndex($supplierColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($supplierColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($supplierRange);
                $sheet->getCell($supplierColLetter . $row)->setDataValidation($validation);
            }

            // Add data validation for Country column
            $countryName = Ing_country::pluck('full_name')->toArray();
            if(sizeof($countryName) > 0){
                $cou_dropdownSheet = $spreadsheet->createSheet();
                $cou_dropdownSheet->setTitle('CountryDropdownData');
                foreach ($countryName as $index => $name) {
                    $cou_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $cou_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $countryRange = "'CountryDropdownData'!\$A\$1:\$A\$" . count($countryName);
            }else{
                $countryRange = '""';
            }
            $countryColumn = array_search('country_of_origin', array_keys($headers)) + 1;
            $countryColLetter = Coordinate::stringFromColumnIndex($countryColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($countryColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($countryRange);
                $sheet->getCell($countryColLetter . $row)->setDataValidation($validation);
            }
        
            // Add data validation for Ing Unit column
            $ingUnitColumn = array_search('ingredient_units', array_keys($headers)) + 1;
            $ingUnitColLetter = Coordinate::stringFromColumnIndex($ingUnitColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ingUnitColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"g,kg,mL,L"');
            }

            // Add data validation for Unit column
            $purUnitColumn = array_search('purchase_units', array_keys($headers)) + 1;
            $purUnitColLetter = Coordinate::stringFromColumnIndex($purUnitColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($purUnitColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
            }
            $allergensColumn = array_search('allergens', array_keys($headers)) + 1;
            $allergensColLetter = Coordinate::stringFromColumnIndex($allergensColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($allergensColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST) // Use LIST type for comma-separated values
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Invalid Input')
                        ->setError('Please enter values separated by commas')
                        ->setPromptTitle('Comma-Separated Values')
                        ->setPrompt('Enter multiple values separated by commas (e.g., Apple, Banana, Orange)');
            }

            // Add data validation for Status column
            $StatusArray = get_rawmaterial_status_array();
            if(sizeof($StatusArray) > 0){
                $status_dropdownSheet = $spreadsheet->createSheet();
                $status_dropdownSheet->setTitle('statusDropdownData');
                foreach ($StatusArray as $index => $name) {
                    $status_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $status_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $statusRange = "'statusDropdownData'!\$A\$1:\$A\$" . count($StatusArray);
            }else{
                $statusRange = '""';
            }
            $statusColumn = array_search('raw_material_status', array_keys($headers)) + 1;
            $statusColLetter = Coordinate::stringFromColumnIndex($statusColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($statusColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($statusRange);
                $sheet->getCell($statusColLetter . $row)->setDataValidation($validation);
            }

            // Add data validation for Is Active column
            $ActiveArray = get_rawmaterial_range_array();
            if(sizeof($ActiveArray) > 0){
                $active_dropdownSheet = $spreadsheet->createSheet();
                $active_dropdownSheet->setTitle('activeDropdownData');
                // Insert supplier names into column A of 'DropdownData' sheet
                foreach ($ActiveArray as $index => $name) {
                    $active_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $active_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                // Define the range where the supplier names are stored
                $activeRange = "'activeDropdownData'!\$A\$1:\$A\$" . count($ActiveArray);
            }else{
                $activeRange = '""';
            }
            $activeColumn = array_search('raw_material_ranging', array_keys($headers)) + 1;
            $activeColLetter = Coordinate::stringFromColumnIndex($activeColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($activeColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($activeRange);
                $sheet->getCell($activeColLetter . $row)->setDataValidation($validation);
            }

            // Add data validation for category column
            $categoryArray = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->pluck('name')->toArray();
            if(sizeof($categoryArray) > 0){
                $category_dropdownSheet = $spreadsheet->createSheet();
                $category_dropdownSheet->setTitle('categoryDropdownData');
                foreach ($categoryArray as $index => $name) {
                    $category_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $category_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $categoryRange = "'categoryDropdownData'!\$A\$1:\$A\$" . count($categoryArray);
            }else{
                $categoryRange = '""';
            }
            $categoryColumn = array_search('category', array_keys($headers)) + 1;
            $categoryColLetter = Coordinate::stringFromColumnIndex($categoryColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($categoryColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($categoryRange);
                $sheet->getCell($categoryColLetter . $row)->setDataValidation($validation);
            }


            // Add data validation for tags column
           $prTagsColumn = array_search('ing_tags', array_keys($headers)) + 1;
            $prTagColLetter = Coordinate::stringFromColumnIndex($prTagsColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($prTagColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST) // Use LIST type for comma-separated values
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Invalid Input')
                        ->setError('Please enter values separated by commas')
                        ->setPromptTitle('Comma-Separated Values')
                        ->setPrompt('Enter multiple values separated by commas (e.g., Apple, Banana, Orange)');
            }

            // Add numeric validation for numeric fields
            foreach ($this->numericFields as $field => $config) {
                $col = array_search($field, array_keys($headers)) + 1;
                if ($col) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    for ($row = 2; $row <= 1000; $row++) {
                        $validation = $sheet->getCell($colLetter . $row)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_DECIMAL)
                            ->setErrorStyle(DataValidation::STYLE_STOP)
                            ->setAllowBlank(true)
                            ->setShowInputMessage(true)
                            ->setShowErrorMessage(true)
                            ->setErrorTitle('Invalid Value')
                            ->setError('Please enter a numeric value')
                            ->setPromptTitle('Numeric Value')
                            ->setPrompt("Enter a number with up to {$config['precision']} decimal places");
                    }
                }
            }

            // Freeze the header row
            $sheet->freezePane('A2');

            // Set auto filter
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->setAutoFilter("A1:{$lastColumn}1");

            // Create the Excel file
            $writer = new Xlsx($spreadsheet);

            // Set more explicit headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="ingredient_template.xlsx"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Save with explicit options
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Format a value according to the given field name and value.
     */
    private function formatValue($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle is_active field
        if ($field === 'is_active') {
            return strtolower(trim($value)) === 'active' ? 1 : 0;
        }

        // Handle numeric fields
        if (isset($this->numericFields[$field])) {
            if (is_numeric($value)) {
                return round((float)$value, $this->numericFields[$field]['precision']);
            }
            return null;
        }

        // Handle text fields - trim and clean
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Handles the preview of uploaded Excel files, validating and parsing the data.
     */
    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {

            $file = $request->file('file');

            // Explicitly check file existence and type
            if (!$file->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls'])) {
                throw new \Exception('Invalid file type. Only XLSX and XLS files are allowed.');
            }

            // Explicitly specify the reader type based on extension
            $readerType = $extension === 'xlsx' ? ExcelType::XLSX : ExcelType::XLS;
            $data = Excel::toArray(new CommonImport, $file);
            // $data = Excel::toArray([], $file->getRealPath(), null, $readerType)[0];

            if (empty($data[0]) || count($data[0]) <= 1) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found in the uploaded file.'
                ], 400);
            }

            // Remove headers
            $headers = array_shift($data[0]);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            // Filter out rows that are completely empty (all `null` values)
            $data = array_filter($data[0], function ($row) {
                return array_filter($row) !== [];
            });

            // If no rows remain after filtering, return "No data found" error
            if (empty($data)) {
                return response()->json(['success' => false, 'error' => 'No data found in the uploaded file.'], 400);
            }

            $mappedData = [];
            foreach ($data as $row) {
                $mappedRow = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $mappedRow[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                $mappedData[] = $mappedRow;
            }
            $total_errors = $this->validateData($mappedData);
            return response()->json(['success' => true, 'data' => $mappedData, 'errors' => $total_errors['errors'], 'mandatory' => $total_errors['mandatory']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Handles the import of ingredients from an uploaded Excel file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            // $path = $request->file('file')->getRealPath();
            // $data = Excel::toArray([], $path)[0];
            $file = $request->file('file');
            $data = Excel::toArray(new CommonImport, $file);

            // Check if the uploaded file is empty
            if (empty($data[0])) {
                return redirect()->back()->with('error', 'No data found in the uploaded file.');
            }

            $client_id = $this->clientID;
            $workspace_id = $this->ws_id;
            $ex_clt_plan = ClientSubscription::where('client_id',$client_id)->with(['plan'])->first();
            if($ex_clt_plan == null){
                return redirect()->back()->with('error', 'The company does not have an active subscription plan. Uploading is not allowed without a valid plan.');
            }

            if($ex_clt_plan){
                $ing_count = Ingredient::where('client_id',$client_id)->count();
                $filtered = array_filter($data[0], function ($subArray) {
                    // Check if the sub-array has at least one non-null value
                    return collect($subArray)->filter()->isNotEmpty();
                });
                $upload_count = sizeof($filtered);
                $total_count = $ing_count + $upload_count;
                $allcated = $ex_clt_plan->plan->max_raw_materials;
                if($allcated < $total_count){
                    return redirect()->back()->with('error', 'Already rawmaterial limit reached. Contact batchbase admin');
                }
            }


            $headers = array_shift($data[0]);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            DB::beginTransaction();
            $activeArray = get_active_array();
            $Allergens = Ing_allergen::pluck('name')->toArray();
            foreach ($data[0] as $rowIndex => $row) {
                $ingredientData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $ingredientData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }

                // Skip if no SKU
                if (empty($ingredientData['ing_sku'])) {
                    continue;
                }

                //Supplier Details
                if(array_key_exists('supplier_name',$ingredientData)){
                    $suppliersName = Client_company::where('client_id', $this->clientID)->where('company_name', trim($ingredientData['supplier_name']))->pluck('id')->toArray();
                    if(sizeof($suppliersName) > 0){
                        $ingredientData['supplier_name'] = $suppliersName[0];
                    }else{
                        $item = new Client_company;
                        $item->company_name = trim($ingredientData['supplier_name']);
                        $item->client_id = $this->clientID;
                        $item->created_by = $this->user_id;
                        $item->updated_by = $this->user_id;
                        $item->save();
                        $ingredientData['supplier_name'] = $item->id;
                    } 
                }

                //Country Details
                if(array_key_exists('country_of_origin',$ingredientData)){
                    $countryName = Ing_country::where('full_name', $ingredientData['country_of_origin'])->pluck('COID')->toArray();
                    if(sizeof($countryName) > 0){
                        $ingredientData['country_of_origin'] = $countryName[0];
                    }else{
                        
                        $ingredientData['country_of_origin'] = null;
                    } 
                }

                //Category Details
                if(array_key_exists('category',$ingredientData)){
                    $category = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->where('name', trim($ingredientData['category']))->pluck('id')->toArray();
                    if(sizeof($category) > 0){
                        $ingredientData['category'] = $category[0];
                    }else{
                        $category = new Rawmaterial_category;
                        $category->name = trim($ingredientData['category']);
                        $category->created_by = $this->user_id;
                        $category->updated_by = $this->user_id;
                        $category->client_id = $this->clientID;
                        $category->workspace_id = $this->ws_id;
                        $category->save();
                        $ingredientData['category'] = $category->id;
                    }
                }

                //Tags Details
                if(array_key_exists('ing_tags',$ingredientData)){
                    if($ingredientData['ing_tags']){
                        $ingnameArray = array_map('trim', explode(',', $ingredientData['ing_tags']));
                        $ingArray = Rawmaterial_tag::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->whereIn('name',$ingnameArray)->pluck('id');
                        if(count($ingArray) > 0){
                            $ingredientData['ing_tags'] = json_encode($ingArray);
                        }else{
                            $ingredientData['ing_tags'] = null;
                        }
                    }else{
                        $ingredientData['ing_tags'] = null;
                    }
                }


                //Allrgens Details
                if(array_key_exists('allergens',$ingredientData)){
                    if($ingredientData['allergens']){
                        $allergensArray = array_map('trim', explode(",", $ingredientData['allergens']));
                        $matched = array_intersect($allergensArray, $Allergens);
                        if(count($matched) > 0){
                            $ingredientData['allergens'] = implode(",",$matched);
                        }else{
                            $ingredientData['allergens'] = null;
                        }
                    }else{
                        $ingredientData['allergens'] = null;
                    }
                }

                $ingredientData = $this->calculate_priceperKG($ingredientData); // Calculate Price per KG value

                // Try to find existing ingredient by SKU
                $ingredient = Ingredient::where('ing_sku', $ingredientData['ing_sku'])->where('client_id',$client_id)->where('workspace_id',$workspace_id)->first();
                if ($ingredient) {
                    $ingredient->update($ingredientData);

                    // Product Ingredient details update
                    $prodIngs_IDs = ProdIngredient::where('ing_id',$ingredient->id)->pluck('id');
                    if ($prodIngs_IDs->isNotEmpty()){
                            $Produpdate_data =[
                                'cost_per_kg' => $ingredientData['price_per_kg_l'],
                                'allergens'   => $ingredientData['allergens'],
                                'peel_name'   => $ingredientData['ingredients_list_supplier']
                            ];
                            ProdIngredient::whereIn('id', $prodIngs_IDs)->update($Produpdate_data); 
                    }

                    // Product Nutrition details updated
                    $prodIngs_Collection = ProdIngredient::where('ing_id',$ingredient->id)->pluck('product_id'); 
                    if ($prodIngs_Collection->isNotEmpty()){
                        $productIDs = $prodIngs_Collection->toArray();
                        // Trigger Nutritional information Details
                        updateNutritional_value($productIDs);
                    }

                } else {
                    $ingredientData['client_id'] = $client_id;
                    $ingredientData['workspace_id'] = $workspace_id;
                    $ingredientData['created_by'] = $this->user_id;
                    $ingredientData['updated_by'] = $this->user_id;
                    Ingredient::create($ingredientData);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Ingredients imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing ingredients: ' . $e->getMessage());
        }
    }

    /**
     * Calculate price per kg using uploaded datas
    **/

    public function calculate_priceperKG($ingredientData){
        $pricePerItem = $ingredientData['price_per_item']??null;
        $unitPerItem = $ingredientData['units_per_item']??null;
        $ingredientUnits = $ingredientData['ingredient_units']??null;
        $specificGravity = $ingredientData['specific_gravity']??1;
        $pricePerKg = 0;
        if($pricePerItem && $unitPerItem && $ingredientUnits){
            switch ($ingredientUnits) {
                case 'g':
                    $pricePerKg = ($pricePerItem / $unitPerItem ) * 100;
                    break;
                case 'kg':
                    $pricePerKg = ($pricePerItem / $unitPerItem ) * 0.1;
                    break;
                case 'ml':
                    $pricePerKg = ($pricePerItem / ($unitPerItem * $specificGravity) ) * 100;
                    break;
                case 'l':
                    $pricePerKg = ($pricePerItem / ($unitPerItem * $specificGravity) ) * 0.1;
                    break;
                default:
                    $pricePerKg = ($pricePerItem / $unitPerItem ) * 100;
                    break;
            }
            $ingredientData['price_per_kg_l'] = round(($pricePerKg * 10), 2);
        }else{
            $ingredientData['price_per_kg_l '] = null;
        }
        return $ingredientData;
    }

    /**
     * Converts the header names from the uploaded Excel file to the corresponding
     * database column names for the ingredients table.
     **/
    private function convertHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Ingredient SKU*' => 'ing_sku',
            'Ingredient SKU' => 'ing_sku',
            'Name by Kitchen' => 'name_by_kitchen',
            'Name by Supplier' => 'name_by_supplier',
            'Status' => 'raw_material_status',
            'Ranging' => 'raw_material_ranging',
            'GTIN' => 'gtin',
            'Supplier Code' => 'supplier_code',
            'Supplier Name' => 'supplier_name',
            'Raw Material Catagory' => 'category',
            'Raw Material Tags' => 'ing_tags',
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
            'Raw Material Description' => 'raw_material_description',
            'Supplier Spec URL' => 'supplier_spec_url'
        ];

        $dbHeaders = [];
        foreach ($headers as $index => $header) {
            if (isset($headerMap[trim($header)])) {
                $dbHeaders[$index] = $headerMap[trim($header)];
            }
        }
        return $dbHeaders;
    }

    /**
     * Validates the given data array for ingredient import, ensuring required fields
     * are present and conform to expected formats and constraints.
     */

    private function validateData($data)
    {
        $errors = [];
        $mandatory_errors = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            // Required fields
            if (empty($row['ing_sku'])) {
                $errors[] = "Row {$rowNum}: Ingredient SKU is required";
                $mandatory_errors[] = "Row {$rowNum}: Ingredient SKU is required";
            }

            // Validate numeric fields
            foreach ($this->numericFields as $field => $config) {
                if (isset($row[$field]) && $row[$field] !== null && !is_numeric($row[$field])) {
                    $errors[] = "Row {$rowNum}: {$field} must be numeric";
                }
            }

            // Validate URLs
            if (!empty($row['supplier_spec_url']) && !filter_var($row['supplier_spec_url'], FILTER_VALIDATE_URL)) {
                $errors[] = "Row {$rowNum}: Invalid URL format for Supplier Spec URL";
            }

            // Validate field lengths based on database constraints
            if (isset($row['ing_sku']) && strlen($row['ing_sku']) > 255) {
                $errors[] = "Row {$rowNum}: Ingredient SKU exceeds maximum length of 255 characters";
            }

            if (isset($row['supplier_name']) && $row['supplier_name'] != null) {
                $suppliersName = Client_company::where('client_id', $this->clientID)->where('company_name', $row['supplier_name'])->pluck('id')->toArray();
                if(sizeof($suppliersName) == 0){
                    $errors[] = "Row {$rowNum}: {$row['supplier_name']} supplier name not available.";
                }
            }

            if (isset($row['country_of_origin']) && $row['country_of_origin'] != null) {
                $countName = Ing_country::where('full_name', $row['country_of_origin'])->pluck('COID')->toArray();
                if(sizeof($countName) == 0){
                    $errors[] = "Row {$rowNum}: {$row['country_of_origin']} country not available.";
                }
            }

            if (isset($row['category']) && $row['category'] != null) {
                $categaryName = Rawmaterial_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->where('name', $row['category'])->pluck('id')->toArray();
                if(sizeof($categaryName) == 0){
                    $errors[] = "Row {$rowNum}: {$row['category']} category not available.";
                }
            }

            // Add more specific validations as needed
        }
        $final['errors'] = $errors;
        $final['mandatory'] = $mandatory_errors;
        return $final;
    }
}
