<?php

namespace App\Http\Controllers;

use App\Models\{Freight,ProdFreight,Client_company,Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Excel as ExcelType;

class FreightController extends Controller
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

    public function index(Request $request)
    {   
        $clientID = $this->clientID;
        $workspaceID = $this->ws_id;
        $user_role = $this->role_id;
        if($user_role == 4){
            $user_id = $this->user_id;
            $permission = get_member_permission($user_id,$clientID,['Resources - Freight','Resources - Freight Read']);
        }else{
            $permission = [];
        }
        $freights = Freight::where('client_id', $clientID)->where('workspace_id', $workspaceID)->with(['supplier'])->orderBy('favorite','desc')->latest('updated_at')->get()->toArray();
        $suppliers = Client_company::where('client_id',$this->clientID)->get();
        return view('backend.freights.manage', compact('freights','permission','user_role','suppliers'));


    }


    public function store(Request $request)
    {
        try {
            $validationRules = Freight::validationRules();
            $validationMessages = Freight::validationMessages();
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            if(!in_array($data['freight_unit'],['Parcel','Kg'])){
                unset($data['parcel_weight']);
            }
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $clientID;
            $data['workspace_id'] = $workspaceID;
            $freights = Freight::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Freights created successfully',
                'labour' => $freights
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function update(Request $request, Freight $freight)
    {
        try {
            $validationRules = Freight::validationRules($freight->id);
            $validationMessages = Freight::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();

            if(!in_array($data['freight_unit'],['Parcel','Kg'])){
                $data['parcel_weight'] = 0.00;
            }
            $data['updated_by'] = $this->user_id;
            $freight->update($data);

            /** 
             * Update Product Freights Details
            */
            $this->updateProdFreights($freight->id);

            return response()->json([
                'success' => true,
                'message' => 'Freight updated successfully',
                'labour' => $freight
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updateProdFreights($id){
        $prodFreights = ProdFreight::where('freight_id',$id)->get();
        $freights = Freight::with('supplier')->findOrFail($id);
        if ($prodFreights->isNotEmpty()){
            foreach ($prodFreights as $key => $value) {
                $product = Product::findOrFail($value->product_id);
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
                            break;
                    }
                }
                $cost_per_kg = ($freights->freight_price / $fr_weight) * 1000;
                $update_data = [
                    'freight_supplier' => $freights->supplier? $freights->supplier->company_name:null,
                    'freight_cost'     => $freights->freight_price??null,
                    'freight_units'    => $freights->freight_unit??null,
                    'freight_weight'   => $fr_weight,
                    'cost_per_kg'      => $cost_per_kg,
                ];
                ProdFreight::where('id',$value->id)->update($update_data);
            }
        }
        return;
    }

    public function destroy(Freight $freight)
    {
        try {
            $status = true;
            if($freight->archive == 0){
                if(ProdFreight::where('freight_id',$freight->id)->count() == 0){
                    $freight->update(['archive' => 1]);
                    $message = 'Freight moved to archive status';
                }else{
                    $status = false;
                    $message = 'Freight not archive because these assigned some products.'; 
                }
            }else{
                if(ProdFreight::where('freight_id',$freight->id)->count() == 0){
                    $freight->delete();
                    $message = 'Freight deleted successfully';
                }else{
                    $status = false;
                    $message = 'Freight not delete because these assigned some products.';
                }
            }
            return response()->json(['success' => $status,'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }
    public function unarchive(Freight $freight)
    {
        try {
            $freight->update(['archive' => 0]);
            return response()->json(['success' => true,'message' => 'Packaging unarchived']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    public function show_import()
    {
        return view('backend.freights.import-form');
    }

    private $numericFields = [
        'freight_price' => ['type' => 'decimal', 'precision' => 2],
        'parcel_weight' => ['type' => 'decimal', 'precision' => 2]
    ];
    
    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'name' => 'Name',
                'description' => 'Description',
                'freight_id' => 'Freight ID',
                'freight_supplier' => 'Supplier',
                'freight_price' => 'Price ($)',
                'freight_unit' => 'Unit',
                'parcel_weight' => 'Parcel Weight (g)',
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
            $supplierColumn = array_search('freight_supplier', array_keys($headers)) + 1;
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



            $unitColumn = array_search('freight_unit', array_keys($headers)) + 1;
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
                    ->setFormula1('"Ind Unit,Sell Unit,Carton,Pallet,Parcel,Kg"');
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
            header('Content-Disposition: attachment; filename="freight_template.xlsx"');
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
            $data = Excel::toArray([], $file->getRealPath(), null, $readerType)[0];
            if (empty($data) || count($data) <= 1) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found in the uploaded file.'
                ], 400);
            }

            // Remove headers
            $headers = array_shift($data);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            // Filter out rows that are completely empty (all `null` values)
            $data = array_filter($data, function ($row) {
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
            'Description' => 'description',
            'Freight ID' => 'freight_id',
            'Supplier' => 'freight_supplier',
            'Price ($)' => 'freight_price',
            'Unit' => 'freight_unit',
            'Parcel Weight (g)' => 'parcel_weight'
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
        $freightNameArray = [];
        $labourTypeArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            if($row['name']){
                if(in_array($row['name'],$freightNameArray)){
                    $errors[] = "Row {$rowNum}: {$row['name']} Freight Name Duplicate";
                }else{
                    $freightNameArray[] = $row['name'];
                }
            }
            if($row['name'] == null){
                $errors[] = "Row {$rowNum}: Freight Name field mandatory";
            }

            if($row['freight_price'] == null){
                $errors[] = "Row {$rowNum}: Freight Price field mandatory";
            }
            if($row['description'] == null){
                $errors[] = "Row {$rowNum}: Freight Description field mandatory";
            }
            if($row['freight_unit'] == null){
                $errors[] = "Row {$rowNum}: Freight Unit field mandatory";
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

    public function store_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file, null, \Maatwebsite\Excel\Excel::XLSX)[0];

            // Check if the uploaded file is empty
            if (empty($data)) {
                return redirect()->back()->with('error', 'No data found in the uploaded file.');
            }


            $headers = array_shift($data);
            $dbHeaders = $this->convertHeadersToDbColumns($headers);

            DB::beginTransaction();
            $session = $request->session()->all();
            foreach ($data as $rowIndex => $row) {
                $freightData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $freightData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }

                if(array_key_exists('freight_supplier',$freightData)){ 
                    $suppliersName = Client_company::where('client_id', $this->clientID)->where('company_name', trim($freightData['freight_supplier']))->pluck('id')->toArray();
                    if(sizeof($suppliersName) > 0){
                        $freightData['freight_supplier'] = $suppliersName[0];
                    }else{
                        $item = new Client_company;
                        $item->company_name = trim($freightData['freight_supplier']);
                        $item->client_id = $this->clientID;
                        $item->created_by = $this->user_id;
                        $item->updated_by = $this->user_id;
                        $item->save();
                        $freightData['freight_supplier'] = $item->id;
                    } 
                }
                $freightData['client_id'] = (int)$session['client'];
                $freightData['workspace_id'] = (int)$session['workspace'];
                $freightData['created_by'] = $this->user_id;
                $freightData['updated_by'] = $this->user_id;
                // Try to find existing ingredient by SKU
                $checkname = Freight::where('client_id',$freightData['client_id'])->where('workspace_id',$freightData['workspace_id'])->where('name', $freightData['name'])->first();
                if($checkname){
                    $checkname->update($freightData);
                    $this->updateProdFreights($checkname->id);
                }else{
                    Freight::create($freightData);
                } 
            }
            DB::commit();
            return redirect()->back()->with('success', 'Freight imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing Freight: ' . $e->getMessage());
        }
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            Freight::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Freight Favorite." : "Freight Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function freight_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $freightArray = json_decode($request->input('freightobj'));
            $freightName = [];
            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($freightArray as $key => $value) {
                    if(ProdFreight::where('freight_id',$value)->count() == 0){
                        Freight::where('id',$value)->update(['archive'=> 1]);
                    }else{
                        $freight = Freight::where('id', $value)->select('name')->first();
                        $freightName[] = $freight->name;
                    }
                }
                if(sizeof($freightName) > 0 ){
                    $result['status'] = false;
                    $undeleteFreight = implode(',',$freightName);
                    $message = "Follwing Freight not archive because these assigned some products.: {$undeleteFreight}";
                }else{
                    $result['status'] = true;
                    $message = "Freight archive successfully";
                }
                $result['message'] = $message;
                return response()->json($result);
            }

            foreach ($freightArray as $key => $value) {
                if(ProdFreight::where('freight_id',$value)->count() == 0){
                    Freight::where('id', $value)->delete();
                }else{
                    $freight = Freight::where('id', $value)->select('name')->first();
                    $freightName[] = $freight->name;
                }
            }
            if(sizeof($freightName) > 0 ){
                $result['status'] = false;
                $undeleteFreight = implode(',',$freightName);
                $message = "Follwing Freight not delete because these assigned some products.: {$undeleteFreight}";
            }else{
                $result['status'] = true;
                $message = "Freight delete successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

}