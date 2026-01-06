<?php

namespace App\Http\Controllers;

use League\Csv\Reader;
use App\Models\{Product,Ingredient,ProdIngredient,ClientSubscription,Product_category,Product_tag};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Excel as ExcelType;
use App\Imports\CommonImport;

class ProductImportController extends Controller
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

    private $importResults = [
        'success' => [],
        'errors' => [
            'missing_ingredients' => [],
            'invalid_ingredient_skus' => [],
            'missing_master_ingredients' => [],
            'data_validation' => []
        ]
    ];

    private const MAX_DECIMAL_10_2 = 99999999.99;
    private const MAX_DECIMAL_5_2 = 999.99;
    private const MAX_DECIMAL_5_1 = 999.9;
    private const MAX_INT = 2147483647;
    private const MAX_VARCHAR_LENGTH = 255;


    /**
     * Display the product import form view.
     */
    public function showImportForm()
    {
        return view('backend.product-import.index');
    }

    public function export_columns(Request $request){
        $expArray = $request->input('ex_xol');
        if($expArray[0] == "ALL"){
            array_shift($expArray);
        }
        $ex_col = implode(',',$expArray);
        session()->put('products', $ex_col);
        $result['status'] = true;
        return response()->json($result);
    }

    private $numericFields = [
        'weight_ind_unit_g' => ['type' => 'decimal', 'precision' => 2],
        'weight_retail_unit_g' => ['type' => 'decimal', 'precision' => 2],
        'weight_carton_g' => ['type' => 'decimal', 'precision' => 2],
        'weight_pallet_g' => ['type' => 'decimal', 'precision' => 2],
        'count_ind_units_per_retail' => ['type' => 'int','precision' => 0],
        'count_retail_units_per_carton' => ['type' => 'int','precision' => 0],
        'count_cartons_per_pallet' => ['type' => 'int','precision' => 0],
        'price_ind_unit' => ['type' => 'decimal', 'precision' => 2],
        'price_retail_unit' => ['type' => 'decimal', 'precision' => 2],
        'price_carton' => ['type' => 'decimal', 'precision' => 2],
        'price_pallet' => ['type' => 'decimal', 'precision' => 2],
        'recipe_oven_temp' => ['type' => 'decimal','precision' => 0],
        'serv_per_package' => ['type' => 'int','precision' => 0],
        'serv_size_g' => ['type' => 'decimal','precision' => 1],
        'batch_baking_loss_percent'=> ['type' => 'decimal','precision' => 2],
        'contingency'=> ['type' => 'decimal','precision' => 2],
        'retailer_charges'=> ['type' => 'decimal','precision' => 2],
        'wholesale_price_sell'=> ['type' => 'decimal','precision' => 2],
        'distributor_price_sell'=> ['type' => 'decimal','precision' => 2],
        'rrp_ex_gst_sell'=> ['type' => 'decimal','precision' => 2],
        'wholesale_margin'=> ['type' => 'decimal','precision' => 2],
        'distributor_margin'=> ['type' => 'decimal','precision' => 2],
        'retailer_margin'=> ['type' => 'decimal','precision' => 2],
    ];

    /**
     * Generates and downloads an Excel template for product import.
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'prod_name' => 'Product Name',
                'prod_sku' => 'SKU',
                'prod_category' => 'Product Catagory',
                'prod_tags' => 'Product Tags',
                'product_status' => 'Status',
                'product_ranging' => 'Ranging',
                'barcode_gs1' => 'GS1 Barcode',
                'description_short' => 'Description',
                'description_long' => 'Long Description',
                'weight_ind_unit_g' => 'Weight-Ind Unit',
                'count_ind_units_per_retail' => 'Unit-Sell Unit',
                'count_retail_units_per_carton' => 'Unit-Carton',
                'count_cartons_per_pallet' => 'Unit-Pallet',
                'recipe_method' => 'Recipe Method',
                'recipe_notes' => 'Recipe Notes',
                'recipe_oven_time' => 'Oven Time',
                'recipe_oven_temp' => 'Oven Temperature',
                'recipe_oven_temp_unit' => 'Oven Temperature Unit',
                'batch_baking_loss_percent' => 'Weight Gain or Loss',
                'serv_per_package' => 'Serving Per Package',
                'serv_size_g' =>'Serving Size (g)',
                'contingency' => 'Direct Cost Contigency',
                'retailer_charges' => 'Retailer Charges',
                'wholesale_price_sell' => 'Wholesale Price (ex GST)',
                'distributor_price_sell' => 'Distributor Price (ex GST)',
                'rrp_ex_gst_sell' => 'RRP (ex GST)',
                'wholesale_margin' => 'Wholesale Margin (%)',
                'distributor_margin' => 'Distributor Margin (%)',
                'retailer_margin' => 'Retailer Margin (%)',                
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


            // Add data validation for category column
            $categoryName = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->pluck('name')->toArray();
            if(sizeof($categoryName) > 0){
                $cat_dropdownSheet = $spreadsheet->createSheet();
                $cat_dropdownSheet->setTitle('CountryDropdownData');
                foreach ($categoryName as $index => $name) {
                    $cat_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $cat_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $categoryRange = "'CountryDropdownData'!\$A\$1:\$A\$" . count($categoryName);
            }else{
                $categoryRange = '""';
            }
            $categoryColumn = array_search('prod_category', array_keys($headers)) + 1;
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

            // Add data validation for Is Active column
            $statusArray = get_products_status_array();
            if(sizeof($statusArray) > 0){
                $status_dropdownSheet = $spreadsheet->createSheet();
                $status_dropdownSheet->setTitle('activeDropdownData');
                // Insert supplier names into column A of 'DropdownData' sheet
                foreach ($statusArray as $index => $name) {
                    $status_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $status_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                // Define the range where the supplier names are stored
                $statusRange = "'activeDropdownData'!\$A\$1:\$A\$" . count($statusArray);
            }else{
                $statusRange = '""';
            }
            $statusColumn = array_search('product_status', array_keys($headers)) + 1;
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

            // Add data validation for category column
            $rangeArray = get_products_range_array();
            if(sizeof($rangeArray) > 0){
                $range_dropdownSheet = $spreadsheet->createSheet();
                $range_dropdownSheet->setTitle('categoryDropdownData');
                foreach ($rangeArray as $index => $name) {
                    $range_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $range_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $categoryRange = "'categoryDropdownData'!\$A\$1:\$A\$" . count($rangeArray);
            }else{
                $categoryRange = '""';
            }
            $rangeColumn = array_search('product_ranging', array_keys($headers)) + 1;
            $rangeColLetter = Coordinate::stringFromColumnIndex($rangeColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($rangeColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($categoryRange);
                $sheet->getCell($rangeColLetter . $row)->setDataValidation($validation);
            }

            $prTagsColumn = array_search('prod_tags', array_keys($headers)) + 1;
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

            $oventimeColumn = array_search('recipe_oven_time', array_keys($headers)) + 1;
            $oventimeColLetter = Coordinate::stringFromColumnIndex($oventimeColumn);
            $secondsColIndex = $oventimeColumn + 14;
            $secondsColLetter = Coordinate::stringFromColumnIndex($secondsColIndex);
            $sheet->setCellValue($secondsColLetter . '1', 'Oven Time (Seconds)');
            $sheet->getColumnDimension($secondsColLetter)->setVisible(false);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $ovenCell = $oventimeColLetter . $row;
                $validation = $sheet->getCell($ovenCell)->getDataValidation();
                $validation->setType(DataValidation::TYPE_CUSTOM)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(true)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Invalid Time Format')
                    ->setError('Please enter time in hh:mm:ss format.')
                    ->setFormula1('=ISNUMBER(TIMEVALUE("' . $ovenCell . '"))');
                $sheet->getStyle($ovenCell)->getNumberFormat()->setFormatCode('hh:mm:ss');
                $formula = "=IF(ISNUMBER($ovenCell), (HOUR($ovenCell)*3600 + MINUTE($ovenCell)*60 + SECOND($ovenCell)), \"\")";
                $sheet->setCellValue($secondsColLetter . $row, $formula);
            }
            

            $unitColumn = array_search('recipe_oven_temp_unit', array_keys($headers)) + 1;
            $unitColLetter = Coordinate::stringFromColumnIndex($unitColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($unitColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"°C,°F"');
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
            header('Content-Disposition: attachment; filename="products_template.xlsx"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            // Save with explicit options
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate template'], 500);
        }
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
                if(array_key_exists('recipe_oven_time',$mappedRow) && $mappedRow['recipe_oven_time'] != null){
                    $mappedRow['recipe_oven_time'] = (int) round($mappedRow['recipe_oven_time']* 86400);
                }
                $mappedData[] = $mappedRow;
            }            
            $errors = $this->validateData($mappedData);
            return response()->json(['success' => true, 'data' => $mappedData, 'errors' => $errors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function convertHeadersToDbColumns($headers)
    {
        $headerMap = [
            'Product Name' => 'prod_name',
            'SKU' => 'prod_sku',
            'Product Category' => 'prod_category',
            'GS1 Barcode' => 'barcode_gs1',
            'Description' => 'description_short',
            'Long Description' => 'description_long',
            'Product Tags' => 'prod_tags',
            'Status' => 'product_status',
            'Ranging' => 'product_ranging',
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
            'Serving Size' => 'serv_size_g',
            'Direct Cost Contigency' => 'contingency',
            'Retailer Charges' => 'retailer_charges',
            'Wholesale Price (ex GST)' => 'wholesale_price_sell',
            'Distributor Price (ex GST)' => 'distributor_price_sell',
            'RRP (ex GST)' => 'rrp_ex_gst_sell',
            'Wholesale Margin (%)' => 'wholesale_margin',
            'Distributor Margin (%)' => 'distributor_margin',
            'Retailer Margin (%)' => 'retailer_margin', 
        ];
        $dbHeaders = [];
        foreach ($headers as $index => $header) {
            if (isset($headerMap[trim($header)])) {
                $dbHeaders[$index] = $headerMap[trim($header)];
            }
        }
        return $dbHeaders;
    }

    private function validateData($data)
    {
        $errors = [];
        $checkArray = [];
        $nameArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            // Required fields
            if (empty($row['prod_sku'])) {
                $errors[] = "Row {$rowNum}: Product SKU is required";
            }
            if($row['prod_sku']){
                if(in_array($row['prod_sku'],$checkArray)){
                    $errors[] = "Row {$rowNum}: {$row['prod_sku']} SKU Duplicate";
                }else{
                    $checkArray[] = $row['prod_sku'];
                }
            }

            // Validate numeric fields
            foreach ($this->numericFields as $field => $config) {
                if (isset($row[$field]) && $row[$field] !== null && !is_numeric($row[$field])) {
                    $errors[] = "Row {$rowNum}: {$field} must be numeric";
                }
            }
            // Validate field lengths based on database constraints
            if (isset($row['prod_sku']) && strlen($row['prod_sku']) > 255) {
                $errors[] = "Row {$rowNum}: Product SKU exceeds maximum length of 255 characters";
            }
        }
        return $errors;
    }

    private function formatValue($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
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


    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $file = $request->file('file');    
            $data = Excel::toArray(new CommonImport, $file);
            if (empty($data) || count($data[0]) == 0) {
                return redirect()->back()->with('error', 'No data found in the uploaded file.');
            }
            // $session = $request->session()->all();
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;

            $ex_clt_plan = ClientSubscription::where('client_id',$clientID)->with(['plan'])->first();
            if($ex_clt_plan == null){
                return redirect()->back()->with('error', 'The company does not have an active subscription plan. Uploading is not allowed without a valid plan.');
            }

            if($ex_clt_plan){
                $product_count = Product::where('client_id',$clientID)->count();
                $filtered = array_filter($data[0], function ($subArray) {
                    // Check if the sub-array has at least one non-null value
                    return collect($subArray)->filter()->isNotEmpty();
                });
                $upload_count = sizeof($filtered);
                $total_count = $product_count + $upload_count;
                $allcated = $ex_clt_plan->plan->max_skus;
                if($allcated < $total_count){
                    return redirect()->back()->with('error', 'Already product limit reached. Contact batchbase admin');
                }
            }
            // Extract headers & rows
            $headers = array_shift($data[0]); // Get headers
            $dbHeaders = $this->convertHeadersToDbColumns($headers);
            DB::beginTransaction();
            foreach ($data[0] as $rowIndex => $row) {
                $productData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $productData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                // Skip if no SKU
                 if (empty($productData['prod_sku'])) {
                    continue;
                }
                $product = Product::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('prod_sku', $productData['prod_sku'])->first();
 
                if(array_key_exists('recipe_oven_time',$productData) && $productData['recipe_oven_time'] != null){
                    $productData['recipe_oven_time'] = (int) round($productData['recipe_oven_time'] * 86400); // 24*60*60
                }

                if(array_key_exists('recipe_oven_temp_unit',$productData) && $productData['recipe_oven_temp_unit'] != null){
                    $productData['recipe_oven_temp_unit'] = ($productData['recipe_oven_temp_unit'] == "°C") ? "C" : "F";
                }
        
                if(array_key_exists('prod_category',$productData)){
                    $pr_cate = Product_category::where('client_id', $this->clientID)->where('workspace_id',$this->ws_id)->where('name',$productData['prod_category'] )->first();
                    if($pr_cate){
                        $productData['prod_category'] = $pr_cate->id;
                    }else{
                        $productData['prod_category'] = null;
                    }
                }

                if(array_key_exists('prod_tags',$productData)){
                    if($productData['prod_tags']){
                        $ProdnameArray = array_map('trim', explode(',', $productData['prod_tags']));
                        $ingArray = Product_tag::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->whereIn('name',$ProdnameArray)->pluck('id');
                        if(count($ingArray) > 0){
                            $productData['prod_tags'] = $ingArray;
                        }else{
                            $productData['prod_tags'] = null;
                        }
                        
                    }else{
                        $productData['prod_tags'] = null;
                    }
                }

                if ($product) {
                    // Ensure required values are float/int and avoid division by zero
                    $weightIndUnitG = (float) ($productData['weight_ind_unit_g'] ?? $product->weight_ind_unit_g);
                    $countIndUnitsPerRetail = (float) ($productData['count_ind_units_per_retail'] ?? $product->count_ind_units_per_retail);
                    $countRetailUnitsPerCarton = (float) ($productData['count_retail_units_per_carton'] ?? $product->count_retail_units_per_carton);
                    $countCartonsPerPallet = (float) ($productData['count_cartons_per_pallet'] ?? $product->count_cartons_per_pallet);
                    $priceRetailUnit = (float) ($productData['rrp_ex_gst_sell'] ?? $product->rrp_ex_gst_sell);
                }else{
                    // Ensure required values are float/int and avoid division by zero
                    $weightIndUnitG = (float) ($productData['weight_ind_unit_g'] ?? 100);
                    $countIndUnitsPerRetail = (float) ($productData['count_ind_units_per_retail'] ?? 1);
                    $countRetailUnitsPerCarton = (float) ($productData['count_retail_units_per_carton'] ?? 0);
                    $countCartonsPerPallet = (float) ($productData['count_cartons_per_pallet'] ?? 0);
                    $priceRetailUnit = (float) ($productData['rrp_ex_gst_sell'] ?? 0);

                }

                // Perform calculations for weight
                $weightRetailUnitG = $weightIndUnitG * $countIndUnitsPerRetail;
                $weightCartonG = $weightRetailUnitG * $countRetailUnitsPerCarton;
                $weightPalletG = $weightCartonG * $countCartonsPerPallet;

                // Format and assign weight values
                $productData['weight_ind_unit_g'] = $weightIndUnitG > 0 ? round($weightIndUnitG, 1) : 0;
                $productData['weight_retail_unit_g'] = $weightRetailUnitG > 0 ? round($weightRetailUnitG, 1) : 0;
                $productData['weight_carton_g'] = $weightCartonG > 0 ? round($weightCartonG, 1) : 0;
                $productData['weight_pallet_g'] = $weightPalletG > 0 ? round($weightPalletG, 1) : 0;

                // Perform calculations for price
                $priceIndUnit = $countIndUnitsPerRetail > 0 
                    ? $priceRetailUnit / $countIndUnitsPerRetail 
                    : $priceRetailUnit;
                $priceCarton = $priceRetailUnit * $countRetailUnitsPerCarton;
                $pricePallet = $priceCarton * $countCartonsPerPallet;

                // Format and assign price values
                $productData['price_retail_unit'] = $priceRetailUnit > 0 ? round($priceRetailUnit, 2) : 0;
                $productData['price_ind_unit'] = $priceIndUnit > 0 ? round($priceIndUnit, 2) : 0;
                $productData['price_carton'] = $priceCarton > 0 ? round($priceCarton, 2) : 0;
                $productData['price_pallet'] = $pricePallet > 0 ? round($pricePallet, 2) : 0;

                // Format and assign count values
                $productData['count_ind_units_per_retail'] = $countIndUnitsPerRetail > 0 ? (int) round($countIndUnitsPerRetail) : 0;
                $productData['count_retail_units_per_carton'] = $countRetailUnitsPerCarton > 0 ? (int) round($countRetailUnitsPerCarton) : 0;
                $productData['count_cartons_per_pallet'] = $countCartonsPerPallet > 0 ? (int) round($countCartonsPerPallet) : 0;

                if ($product) {
                    $productData['serv_size_g'] = ($productData['serv_size_g'] ?? $product->serv_size_g);
                    $productData['serv_per_package'] = ($productData['serv_per_package'] ?? $product->serv_per_package);
                    $productData['batch_baking_loss_percent'] = ($productData['batch_baking_loss_percent'] ?? $product->batch_baking_loss_percent);
                    $productData['contingency'] = ($productData['contingency'] ?? $product->contingency);
                    $productData['retailer_charges'] = ($productData['retailer_charges'] ?? $product->retailer_charges);
                    $productData['wholesale_margin'] = ($productData['wholesale_margin'] ?? $product->wholesale_margin);
                    $productData['distributor_margin'] = ($productData['distributor_margin'] ?? $product->distributor_margin);
                    $productData['retailer_margin'] = ($productData['retailer_margin'] ?? $product->retailer_margin);

                    $Wholesale_price = ($productData['wholesale_price_sell'] ?? $product->wholesale_price_sell);
                    $Distributor_price = ($productData['distributor_price_sell'] ?? $product->distributor_price_sell);
                    $RRP_ex_gst = ($productData['rrp_ex_gst_sell'] ?? $product->rrp_ex_gst_sell);

                    $RRP_ex_gst_price = ($RRP_ex_gst > 0)? $RRP_ex_gst * (1000 / $weightIndUnitG):0.00;
                    $RRP_inc = ($RRP_ex_gst > 0)? $RRP_ex_gst * (1 + 0.1):0.00;
                    $RRP_inc_price = ($RRP_inc > 0)? $RRP_inc * (1000 / $weightIndUnitG):0.00; 
                    $Wholesale_price_kg = ($Wholesale_price > 0)? $Wholesale_price * (1000 / $weightIndUnitG):0.00; 
                    $Distributor_price_kg = ($Distributor_price > 0)? $Distributor_price * (1000 / $weightIndUnitG):0.00; 

                }else{
                    $productData['serv_size_g'] = ($productData['serv_size_g'] ?? 100);
                    $productData['serv_per_package'] = ($productData['serv_per_package'] ?? 1);
                    $productData['batch_baking_loss_percent'] = ($productData['batch_baking_loss_percent'] ?? 0);
                    $productData['contingency'] = ($productData['contingency'] ?? 0.00);
                    $productData['retailer_charges'] = ($productData['retailer_charges'] ?? 0.00);
                    $productData['wholesale_margin'] = ($productData['wholesale_margin'] ?? 0.00);
                    $productData['distributor_margin'] = ($productData['distributor_margin'] ?? 0.00);
                    $productData['retailer_margin'] = ($productData['retailer_margin'] ?? 0.00);

                    $Wholesale_price = ($productData['wholesale_price_sell'] ?? 0.00);
                    $Distributor_price = ($productData['distributor_price_sell'] ?? 0.00);
                    $RRP_ex_gst = ($productData['rrp_ex_gst_sell'] ?? 0.00);

                    $RRP_ex_gst_price = ($RRP_ex_gst > 0)? $RRP_ex_gst * (1000 / $weightIndUnitG):0.00;
                    $RRP_inc = ($RRP_ex_gst > 0)? $RRP_ex_gst * (1 + 0.1):0.00;
                    $RRP_inc_price = ($RRP_inc > 0)? $RRP_inc * (1000 / $weightIndUnitG):0.00; 
                    $Wholesale_price_kg = ($Wholesale_price > 0)? $Wholesale_price * (1000 / $weightIndUnitG):0.00; 
                    $Distributor_price_kg = ($Distributor_price > 0)? $Distributor_price * (1000 / $weightIndUnitG):0.00; 
                }
                
                $productData['wholesale_price_sell'] = $Wholesale_price;
                $productData['wholesale_price_kg_price'] = $Wholesale_price_kg;
                $productData['distributor_price_sell'] = $Distributor_price;
                $productData['distributor_price_kg_price'] = $Distributor_price_kg;
                $productData['rrp_ex_gst_sell'] = $RRP_ex_gst;
                $productData['rrp_ex_gst_price'] = $RRP_ex_gst_price;
                $productData['rrp_inc_gst_sell'] = $RRP_inc;
                $productData['rrp_inc_gst_price'] = $RRP_inc_price;

                // Try to find existing ingredient by SKU
                
                if ($product) {
                    $product->update($productData);
                } else {
                    $productData['client_id'] = $clientID;
                    $productData['workspace_id'] = $workspaceID;
                    $productData['created_by'] = $this->user_id;
                    $productData['updated_by'] = $this->user_id;
                    Product::create($productData);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Products imported successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }
}
