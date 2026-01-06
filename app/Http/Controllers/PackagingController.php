<?php

namespace App\Http\Controllers;

use App\Models\{Packaging,Client_company,ProdPackaging,Product,Company};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Excel as ExcelType;
use Illuminate\Support\Facades\DB;
use App\Imports\CommonImport;

class PackagingController extends Controller
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientID = $this->clientID;
        $workspaceID = $this->ws_id;
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - Packaging','Resources - Packaging Read']);
        }else{
            $permission = [];
        }
        $packagings = Packaging::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('updated_at')->with('supplier')->get();
        $suppliers = Client_company::where('client_id', $this->clientID)->orderBy('company_name','asc')->get();
        return view('backend.packaging.packagings', compact('packagings', 'suppliers','permission','user_role'));
    }

    /**
     * Store a newly created packaging in database.
     */
    public function store(Request $request)
    {
        try {
            $validationRules = Packaging::validationRules();
            $validationMessages = Packaging::validationMessages();
            if (!$request->filled('pack_sku')) {
                $request->merge(['pack_sku' => 'PKG-' . Str::random(6)]);
            }
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;
            
            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $clientID;
            $data['workspace_id'] = $workspaceID;
            $packaging = Packaging::create($data);
            return response()->json(['success' => true, 'message' => 'Packaging created successfully', 'packaging' => $packaging]);
        } catch (\Exception $e) {
            Log::error('Packaging creation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified packaging resource in storage.
     */
    public function update(Request $request, Packaging $packaging)
    {
        try {
            $validationRules = Packaging::validationRules($packaging->id);
            $validationMessages = Packaging::validationMessages();

            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;
            $packaging->update($data);

            /**
             * Product Packaging Details Update
            */
            $this->update_productpackaging($packaging->id);


            // $ext = number_format($packaging->price_per_unit,2);
            // $new = number_format($data['price_per_unit'],2);
            // if($ext != $new){
            //     $this->update_productpackaging($packaging->id,$new);
            // }
            return response()->json(['success' => true, 'message' => 'Packaging updated successfully', 'packaging' => $packaging]);
        } catch (\Exception $e) {
            Log::error('Packaging update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function update_productpackaging($pacID){
        $packaging = Packaging::with('supplier')->findOrFail($pacID);
        $prod_packaging = ProdPackaging::where('packaging_id',$pacID)->get();
        if ($prod_packaging->isNotEmpty()){
            foreach ($prod_packaging as $key => $value) {
                $product = Product::findOrFail($value->product_id);
                switch ($packaging->type) {
                    case 'Ind Unit':
                        $pack_weight = $product->weight_ind_unit_g;
                        break;
                    case 'Sell Unit':
                        $pack_weight = $product->weight_retail_unit_g;
                        break;
                    case 'Carton':
                        $pack_weight = $product->weight_carton_g;
                        break;
                    case 'Pallet':
                        $pack_weight = $product->weight_pallet_g;
                        break;
                    default:
                        break;
                }

                // Prevent division by zero
                if (!$pack_weight || $pack_weight == 0) {
                    $total = 0;
                } else {
                    $total = ($packaging->price_per_unit / $pack_weight)*1000;
                }
                                
                $update_data = [
                    'packaging_type'        => $packaging->type??null,
                    'cost_per_sell_unit'    => $packaging->price_per_unit??null,
                    'weight_per_sell_unit'  => $pack_weight,
                    'cost_per_kg'           => round($total,2),
                ];
                ProdPackaging::where('id',$value->id)->update($update_data);
            }
        }
        return;
    }

    /**
     * Remove the specified packaging from the database.
     */
    public function destroy(Packaging $packaging)
    {
        try {
            $status = true;
            if($packaging->archive == 0){
                if(ProdPackaging::where('packaging_id',$packaging->id)->count() == 0){
                    $packaging->update(['archive' => 1]);
                    $message = 'Packaging moved to archive status';
                }else{
                    $status = false;
                    $message = 'Packaging not archive because these assigned some products.';
                }
            }else{
                if(ProdPackaging::where('packaging_id',$packaging->id)->count() == 0){
                    $packaging->delete();
                    $message = 'Packaging deleted successfully';
                }else{
                    $status = false;
                    $message = 'Packaging not delete because these assigned some products.';
                }
                
            }
            return response()->json(['success' => $status,'message' => $message]);
        } catch (\Exception $e) {
            // Log::error('Packaging deletion error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function unarchive(Packaging $packaging)
    {
        try {
            $packaging->update(['archive' => 0]);
            return response()->json(['success' => true,'message' => 'Packaging unarchived']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    /**
     * Generate a unique SKU for a packaging, based on the provided name.
     * If the generated SKU already exists in the database, it will be modified
     * by appending a random 3-digit number to the end, until a unique one is found.
     */
    public function generateSKU(Request $request)
    {
        $packName = $request->name;
        $packId = $request->id;
        $sku = $this->cleanPackName($packName);
        $query = Packaging::where('pack_sku', $sku);
        if ($packId) {
            $query->where('id', '!=', $packId);
        }
        if ($query->exists()) {
            do {
                $randomSku = $sku . '_' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                $exists = Packaging::where('pack_sku', $randomSku)->when($packId, function ($q) use ($packId) {
                    return $q->where('id', '!=', $packId);
                })->exists();
            } while ($exists);
            $sku = $randomSku;
        }
        return response()->json(['sku' => $sku]);
    }

    /**
     * Cleans a packaging name to generate a SKU-friendly string.
     *
     * This function converts the name to lowercase, replaces spaces with underscores,
     * removes special characters except for underscores, and ensures no consecutive
     * underscores exist. It also trims any leading or trailing underscores.
     */
    private function cleanPackName($name)
    {
        $clean = Str::lower($name);
        $clean = str_replace(' ', '_', $clean);
        $clean = preg_replace('/[^a-z0-9_]/', '', $clean);
        $clean = preg_replace('/_+/', '_', $clean);
        return trim($clean, '_');
    }

    
    public function import_form()
    {
        return view('backend.packaging.import-form');
    }

    private $numericFields = [
        'purchase_price' => ['type' => 'decimal', 'precision' => 2],
        'purchase_units' => ['type' => 'decimal', 'precision' => 1],
        'price_per_unit' => ['type' => 'decimal', 'precision' => 2]
    ];
    
    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'name' => 'Name',
                'pack_sku' => 'SKU',
                'purchase_price' => 'Purchase Price',
                'purchase_units' => 'Purchase Units',
                'price_per_unit' => 'Price per Unit',
                'type' => 'Type',
                'supplier_id'=>'Supplier',
                'supplier_sku' =>'Supplier SKU',
                'sales_channel' => 'Sales Channel',
                'environmental' => 'Environmental',         
                'description' => 'Description'
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

            $channelColumn = array_search('sales_channel', array_keys($headers)) + 1;
            $channelColLetter = Coordinate::stringFromColumnIndex($channelColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($channelColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Retail,Major Wholesale,Ind Wholesale,Food Services"');
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
            $categoryColumn = array_search('supplier_id', array_keys($headers)) + 1;
            $categoryColLetter = Coordinate::stringFromColumnIndex($categoryColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($categoryColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($supplierRange);
                $sheet->getCell($categoryColLetter . $row)->setDataValidation($validation);
            }

            $typeColumn = array_search('type', array_keys($headers)) + 1;
            $typeColLetter = Coordinate::stringFromColumnIndex($typeColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($typeColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Ind Unit,Sell Unit,Carton,Pallet"');
            }


            $stageColumn = array_search('environmental', array_keys($headers)) + 1;
            $stageColLetter = Coordinate::stringFromColumnIndex($stageColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($stageColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"PET (Polyethylene Terephthalate),HDPE (High-Density Polyethylene),PVC (Polyvinyl Chloride),LDPE (Low-Density Polyethylene),PP (Polypropylene),PS (Polystyrene),Other (Mixed Plastics),Biodegradable Plastics (PLA, PHA),Flexible Plastic Pouches,Cling Film (Stretch Wrap),Corrugated Cardboard,Cartonboard (Folding Cartons),Paper Bags & Wrapping,Wax-Coated Paper,Moulded Pulp Packaging,Aluminium Cans,Tinplate Steel Cans,Aluminium Foil Packaging,Metal Drums & Barrels,Clear Glass Bottles,Coloured Glass (Green/Brown),Glass Jars,Tetra Pak (Liquid Cartons),Foil Laminates,Paper-Based Blister Packs,Cloth/Fabric Bags,Jute & Hemp Packaging,Wooden Crates & Boxes,Bamboo Packaging,Sugarcane Bagasse,Edible Packaging,Cornstarch Packaging,Mushroom-Based Packaging,Aerogel Insulated Packaging,EVacuum Packaging,Bubble Wrap (Plastic or Paper-Based),Foam Packaging (EPE, EPS),Metalized Plastic Films,Tyvek (Synthetic Paper),Plant-Based Film Wraps"');
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
            header('Content-Disposition: attachment; filename="packaging_template.xlsx"');
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
        $dbHeaders = [];
        foreach ($headers as $index => $header) {
            if (isset($headerMap[trim($header)])) {
                $dbHeaders[$index] = $headerMap[trim($header)];
            }
        }
        return $dbHeaders;
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

    private function validateData($data)
    {
        $errors = [];
        $checkArray = [];
        $nameArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            // Required fields
            if (empty($row['pack_sku'])) {
                $errors[] = "Row {$rowNum}: SKU is required";
            }

            if($row['name'] == null){
                $errors[] = "Row {$rowNum}: Name is required";
            }

            if($row['purchase_price'] == null){
                $errors[] = "Row {$rowNum}: Purchase price is required";
            }

            if($row['purchase_units'] == null){
                $errors[] = "Row {$rowNum}: Purchase units is required";
            }

            if($row['type'] == null){
                $errors[] = "Row {$rowNum}: Packaging type is required";
            }

            if($row['supplier_id'] == null){
                $errors[] = "Row {$rowNum}: Supplier is required";
            }

            if($row['pack_sku']){
                if(in_array($row['pack_sku'],$checkArray)){
                    $errors[] = "Row {$rowNum}: {$row['pack_sku']} SKU Duplicate";
                }else{
                    $checkArray[] = $row['pack_sku'];
                }
            }
            
            if($row['name']){
                if(in_array(strtolower($row['name']),$nameArray)){
                    $errors[] = "Row {$rowNum}: {$row['name']} Package Name Duplicate";
                }else{
                    $nameArray[] = strtolower($row['name']);
                }
            }

            // if (isset($row['supplier_id']) && $row['supplier_id'] != null) {
            //     $suppliersName = Company::where('company_name', $row['supplier_id'])->pluck('id')->toArray();
            //     if(sizeof($suppliersName) == 0){
            //         $errors[] = "Row {$rowNum}: {$row['supplier_id']} supplier name not available.";
            //     }
            // }

            // Validate numeric fields
            foreach ($this->numericFields as $field => $config) {
                if (isset($row[$field]) && $row[$field] !== null && !is_numeric($row[$field])) {
                    $errors[] = "Row {$rowNum}: {$field} must be numeric";
                }
            }
        }
        return $errors;
    }

    public function store_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $file = $request->file('file');
            $data = Excel::toArray(new CommonImport, $file);
            // $data = Excel::toArray([], $file, null, \Maatwebsite\Excel\Excel::XLSX)[0];

            // Check if the uploaded file is empty
            if (empty($data[0])) {
                return redirect()->back()->with('error', 'No data found in the uploaded file.');
            }

            $headers = array_shift($data[0]);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);
            DB::beginTransaction();
            $session = $request->session()->all();
            foreach ($data[0] as $rowIndex => $row) {
                $packageData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $packageData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                // Skip if no SKU
                if (empty($packageData['pack_sku'])) {
                    continue;
                }
                if($packageData['supplier_id']){
                    $suppliersName = Client_company::where('client_id', $this->clientID)->where('company_name', $packageData['supplier_id'])->pluck('id')->toArray();
                    if(sizeof($suppliersName) > 0){
                        $packageData['supplier_id'] = $suppliersName[0];
                    }else{
                        $item = new Client_company;
                        $item->company_name = trim($packageData['supplier_id']);
                        $item->client_id = $this->clientID;
                        $item->save();
                        $packageData['supplier_id'] = $item->id;
                    } 
                }


                $packageData['client_id'] = (int)$session['client'];
                $packageData['workspace_id'] = (int)$session['workspace'];
                $packageData['created_by'] = $this->user_id;
                $packageData['updated_by'] = $this->user_id;
                $packageData['price_per_unit'] =  $packageData['purchase_price'] / $packageData['purchase_units'];

                // Try to find existing ingredient by SKU
                $packaging = Packaging::where('name', $packageData['name'])->where('client_id', $packageData['client_id'])->where('workspace_id', $packageData['workspace_id'])->first();
                if($packaging){
                    if($packageData['pack_sku'] != null && $packageData['pack_sku'] == $packaging->pack_sku){
                        $packaging->update($packageData);
                    }
                    /**
                     * Product Packagin details 
                     */
                    $this->update_productpackaging($packaging->id);
                }else{
                    Packaging::create($packageData);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Packaging imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing packaging: ' . $e->getMessage());
        }
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            Packaging::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Packaging Favorite." : "Packaging Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function packaging_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $packageArray = json_decode($request->input('packageobj'));
            $packageName = [];
            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($packageArray as $key => $value) {
                    if(ProdPackaging::where('packaging_id',$value)->count() == 0){
                        Packaging::where('id',$value)->update(['archive'=> 1]);
                    }else{
                        $packaging = Packaging::where('id', $value)->select('name')->first();
                        $packageName[] = $packaging->name;
                    }
                }

                if(sizeof($packageName) > 0 ){
                    $result['status'] = false;
                    $undeletePackaging = implode(',',$packageName);
                    $message = "Follwing Packaging not archive because these assigned some products.: {$undeletePackaging}";
                }else{
                    $result['status'] = true;
                    $message = "Packaging archive successfully";
                }
                $result['message'] = $message;
                return response()->json($result);
            }
            
            foreach ($packageArray as $key => $value) {
                if(ProdPackaging::where('packaging_id',$value)->count() == 0){
                    Packaging::where('id', $value)->delete();
                }else{
                    $packaging = Packaging::where('id', $value)->select('name')->first();
                    $packageName[] = $packaging->name;
                }
            }
            if(sizeof($packageName) > 0 ){
                $result['status'] = false;
                $undeletePackaging = implode(',',$packageName);
                $message = "Follwing Packaging not delete because these assigned some products.: {$undeletePackaging}";
            }else{
                $result['status'] = true;
                $message = "Packaging delete successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }
}
