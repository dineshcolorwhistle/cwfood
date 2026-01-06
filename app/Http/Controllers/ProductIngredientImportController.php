<?php

namespace App\Http\Controllers;

use League\Csv\Reader;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\ProdIngredient;
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
use Maatwebsite\Excel\Excel as ExcelType;
use App\Imports\CommonImport;

class ProductIngredientImportController extends Controller
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
        return view('backend.product-import.ingredient-import');
    }

    private $numericFields = [
        'quantity_weight' => ['type' => 'integer', 'precision' => 0],
    ];

    /**
     * Generates and downloads an Excel template for product import.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            // $session = $request->session()->all();
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'product_id' => 'Products',
                'ingredient_id' => 'Ingredients',
                'quantity_weight' => 'Quantity',
                'units_g_ml' => 'Unit',
                'component' => 'Component',
                'kitchen_comments' => 'Kitchen Comments',
                'ingredient_order' => 'Ingredient Order'
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

            // PRODUCT DROPDOWN
            $products = Product::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->get()->toArray();

            if (sizeof($products) > 0) {
                $dropdownSheetProduct = $spreadsheet->createSheet();
                $dropdownSheetProduct->setTitle('DropdownProduct');

                foreach ($products as $index => $product) {
                    $rowIndex = $index + 1;
                    $dropdownSheetProduct->setCellValue('A' . $rowIndex, $product['prod_name']);
                    $dropdownSheetProduct->setCellValue('B' . $rowIndex, $product['id']);
                }

                $dropdownSheetProduct->getColumnDimension('A')->setAutoSize(true);
                $productRange = "'DropdownProduct'!\$A\$1:\$A\$" . count($products);
            } else {
                $productRange = '""';
            }

            $productColumn = array_search('product_id', array_keys($headers)) + 1;
            $productColLetter = Coordinate::stringFromColumnIndex($productColumn);

            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($productColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($productRange);
                $sheet->getCell($productColLetter . $row)->setDataValidation($validation);
            }


            // INGREDIENT DROPDOWN
            $ingredients = Ingredient::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->get()->toArray();

            if (sizeof($ingredients) > 0) {
                $dropdownSheetIngredient = $spreadsheet->createSheet();
                $dropdownSheetIngredient->setTitle('DropdownIngredient');

                foreach ($ingredients as $index => $ingredient) {
                    $rowIndex = $index + 1;
                    $dropdownSheetIngredient->setCellValue('A' . $rowIndex, $ingredient['name_by_kitchen']);
                    $dropdownSheetIngredient->setCellValue('B' . $rowIndex, $ingredient['id']);
                }

                $dropdownSheetIngredient->getColumnDimension('A')->setAutoSize(true);
                $ingredientRange = "'DropdownIngredient'!\$A\$1:\$A\$" . count($ingredients);
            } else {
                $ingredientRange = '""';
            }

            $ingredientColumn = array_search('ingredient_id', array_keys($headers)) + 1;
            $ingredientColLetter = Coordinate::stringFromColumnIndex($ingredientColumn);

            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($ingredientColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($ingredientRange);
                $sheet->getCell($ingredientColLetter . $row)->setDataValidation($validation);
            }

            $unitColumn = array_search('units_g_ml', array_keys($headers)) + 1;
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
                    ->setFormula1('"g,kg,mL,L"');
            }

            $componentColumn = array_search('component', array_keys($headers)) + 1;
            $componentColLetter = Coordinate::stringFromColumnIndex($componentColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($componentColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"dough,batter,pre-bake-topping,post-bake-dec,icing,syrup,filling,sub-recipe"');
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
            header('Content-Disposition: attachment; filename="product_ingredient_template.xlsx"');
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
            'Products' => 'product_id',
            'Ingredients' => 'ingredient_id',
            'Quantity' => 'quantity_weight',
            'Unit'=> 'units_g_ml',
            'Component' => 'component',
            'Kitchen Comments' => 'kitchen_comments',
            'Ingredient Order' => 'ingredient_order'
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
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            // Required fields
            if (empty($row['product_id'])) {
                $errors[] = "Row {$rowNum}: Product is required";
            }
            if (empty($row['ingredient_id'])) {
                $errors[] = "Row {$rowNum}: Ingredient is required";
            }

            if (empty($row['quantity_weight'])) {
                $errors[] = "Row {$rowNum}: Quantity is required";
            }

            if (empty($row['units_g_ml'])) {
                $errors[] = "Row {$rowNum}: Unit is required";
            }

            // Validate numeric fields
            foreach ($this->numericFields as $field => $config) {
                if (isset($row[$field]) && $row[$field] !== null && !is_numeric($row[$field])) {
                    $errors[] = "Row {$rowNum}: {$field} must be numeric";
                }
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
            // Extract headers & rows
            $headers = array_shift($data[0]); // Get headers
            $dbHeaders = $this->convertHeadersToDbColumns($headers);
            DB::beginTransaction();
            $session = $request->session()->all();
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;

            $productIDs = [];
            foreach ($data[0] as $rowIndex => $row) {
                $productIngData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $productIngData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                // Skip if no SKU
                 if (empty($productIngData['product_id'])) {
                    continue;
                }

                // Try to find existing ingredient by SKU
                $product = Product::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('prod_name', $productIngData['product_id'])->first();
                $ingredient = Ingredient::where('client_id', $clientID)->where('workspace_id', $workspaceID)->where('name_by_kitchen', $productIngData['ingredient_id'])->first();
                if($product == null || $ingredient == null){
                    continue;
                }
                $productIDs[] = $product->id;
                $productIngData['quantity_g'] = $productIngData['quantity_weight'];
                $productIngData['allergens'] = $ingredient->allergens??null;
                $productIngData['peel_name'] = $ingredient->ingredients_list_supplier??null;
                $productIngData['cost_per_kg'] = $ingredient->price_per_kg_l??null;
                $prod_ing = ProdIngredient::where('product_id', $product->id)->where('ing_id', $ingredient->id)->first();
                unset($productIngData['ingredient_order'], $productIngData['product_id'],$productIngData['ingredient_id']);
                if ($prod_ing){
                    $prod_ing->update($productIngData);
                } else {
                    $pring_order = ProdIngredient::where('product_id', $product->id)->orderBy('ingredient_order','desc')->first();
                    if($pring_order){
                        $productIngData['ingredient_order'] = $pring_order->ingredient_order + 1;
                    }else{
                        $productIngData['ingredient_order'] = 1;
                    }
                    $productIngData['product_id'] = $product->id;
                    $productIngData['ing_id'] = $ingredient->id;
                    $productIngData['product_sku'] = $product->prod_sku;
                    $productIngData['ing_sku'] = $ingredient->ing_sku;
                    $productIngData['ing_name'] = $ingredient->name_by_kitchen;
                    $productIngData['spec_grav'] = $ingredient->specific_gravity;
                    $productIngData['cost_per_kg'] = $ingredient->price_per_kg_l;
                    ProdIngredient::create($productIngData);
                }
            }
            DB::commit();

            // Trigger Nutritional information Details
            $this->updateNutritional_value($productIDs);

            return redirect()->back()->with('success', 'Product Ingredient imported successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing ingredients: ' . $e->getMessage());
        }
    }

    public function updateNutritional_value(array $productIDs)
    {
        $uniqueIDs = array_unique($productIDs);

        foreach ($uniqueIDs as $value) {
            $product = Product::findOrFail($value);
            $ingredients = ProdIngredient::where('product_id', $value)->get();

            $NutritionResponse = ProductCalculationController::calculate_nutrition_information_components($product, $ingredients);
            $total = $NutritionResponse['totals'];

            $serv_size_g = $product->serv_size_g ?? 0;

            // Helper closure for safe formatting
            $format = function ($value, $decimals = 1) {
                return isset($value) ? (float) number_format($value, $decimals, '.', '') : null;
            };

            // Build update array
            $updateData = [
                'energy_kJ_per_100g'       => $format($total['energy_kj'], 0),
                'protein_g_per_100g'      => $format($total['protein_g'], 1),
                'fat_total_g_per_100g'    => $format($total['fat_total_g'], 1),
                'fat_saturated_g_per_100g'=> $format($total['fat_saturated_g'], 1),
                'carbohydrate_g_per_100g' => $format($total['carbohydrate_g'], 1),
                'sugar_g_per_100g'        => $format($total['sugars_g'], 1),
                'sodium_mg_per_100g'      => $format($total['sodium_mg'], 0),
                'batch_after_waste_g'    => $format($total['net_quantity'], 0),
            ];

            // Per-serve calculations (scaled by serving size)
            $updateData += [
                'energy_kJ_per_serve'       => $format($total['energy_kj'] * $serv_size_g / 100, 0),
                'protein_g_per_serve'      => $format($total['protein_g'] * $serv_size_g / 100, 1),
                'fat_total_g_per_serve'    => $format($total['fat_total_g'] * $serv_size_g / 100, 1),
                'fat_saturated_g_per_serve'=> $format($total['fat_saturated_g'] * $serv_size_g / 100, 1),
                'carbohydrate_g_per_serve' => $format($total['carbohydrate_g'] * $serv_size_g / 100, 1),
                'sugar_g_per_serve'        => $format($total['sugars_g'] * $serv_size_g / 100, 1),
                'sodium_mg_per_serve'      => $format($total['sodium_mg'] * $serv_size_g / 100, 0),
            ];

            $product->update($updateData);
        }
    }


}
