<?php

namespace App\Http\Controllers;

use App\Models\{Machinery,Client_company,ProdMachinery};
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

class MachineryController extends Controller
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
            $permission = get_member_permission($user_id,$clientID,['Resources - Machinery','Resources - Machinery Read']);
        }else{
            $permission = [];
        }

        $machinery = Machinery::where('client_id', $clientID)->where('workspace_id', $workspaceID)->with(['supplier'])->orderBy('favorite','desc')->latest('updated_at')->get()->toArray();
        if(sizeof($machinery) > 0){
            $machines = array_column($machinery, 'machinery_id');
            $max = collect($machines)
                    ->map(function ($item) {
                        return (int) explode('_', $item)[1];
                    })
                    ->max();
            // $lastArray = end($last_machinery);
            // $part = explode('_',$lastArray['machinery_id']);
            // if(isset($part[1])){
            //     $nextNum = increment_numbers($part[1]);
            // }else{
            //     $nextNum = "001";
            // }
            $nextNum = increment_numbers($max);
            $lab_code = "{$clientID}{$workspaceID}_{$nextNum}";
        }else{
            $lab_code = "{$clientID}{$workspaceID}_001";
        }
        $suppliers = Client_company::where('client_id',$this->clientID)->get();
        return view('backend.machinery.machinery', compact('machinery','lab_code','permission','user_role','suppliers'));
    }

    /**
     * Store a newly created machinery in database.
     * */
    public function store(Request $request)
    {
        try {
            $validationRules = Machinery::validationRules();
            $validationMessages = Machinery::validationMessages();

            if (!$request->filled('serial_number')) {
                $request->merge(['serial_number' => 'MCH-' . Str::random(6)]);
            }

            $validator = Validator::make($request->all(), $validationRules, $validationMessages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $clientID = $this->clientID;
            $workspaceID = $this->ws_id;
            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $clientID;
            $data['workspace_id'] = $workspaceID;
            $machinery = Machinery::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Machinery created successfully',
                'machinery' => $machinery
            ]);
        } catch (\Exception $e) {
            Log::error('Machinery creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified machinery resource in storage.
     */
    public function update(Request $request, Machinery $machinery)
    {
        try {
            $validationRules = Machinery::validationRules($machinery->id);
            $validationMessages = Machinery::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;

            $ext = number_format($machinery->cost_per_hour_aud,2);
            $new = number_format($data['cost_per_hour_aud'],2);
            if($ext != $new){
                $this->update_productmachinery($machinery->id,$new);
            }
            $machinery->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Machinery updated successfully',
                'machinery' => $machinery
            ]);
        } catch (\Exception $e) {
            Log::error('Machinery update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    public function update_productmachinery($macID,$costhr){
        $pr_machinery = ProdMachinery::where('machinery_id',$macID)->get()->toArray();
        foreach ($pr_machinery as $key => $value) {
            $weight = $value['weight'];
            $total = $value['hours'] * $costhr;
            $cost = $total / ($weight/1000);
            $update_data['cost_per_hour'] = $costhr;
            $update_data['cost_per_kg'] = $cost;
            ProdMachinery::where('id',$value['id'])->update($update_data);
        }
        return;
    }


    /**
     * Remove the specified machinery from the database.
     */
    public function destroy(Machinery $machinery)
    {
        try {
            $status = true;
            if($machinery->archive == 0){
                if(ProdMachinery::where('machinery_id',$machinery->id)->count() == 0){
                    $machinery->update(['archive' => 1]);
                    $message = 'Machinery moved to archive status';
                }else{
                    $status = false;
                    $message = 'Machinery not archive because these assigned some products.';
                }
            }else{
                if(ProdMachinery::where('machinery_id',$machinery->id)->count() == 0){
                    $machinery->delete();
                    $message = 'Machinery deleted successfully';
                }else{
                    $status = false;
                    $message = 'Machinery not delete because these assigned some products.';
                }
            }
            return response()->json([
                'success' => $status,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            // Log::error('Machinery deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unarchive(Machinery $machinery)
    {
        try {
            $machinery->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Machinery unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function import_form()
    {
        return view('backend.machinery.import-form');
    }

    private $numericFields = [
        'power_consumption_kw' => ['type' => 'decimal', 'precision' => 1],
        'cost_per_hour_aud' => ['type' => 'decimal', 'precision' => 2],
        'production_rate_units_hr' => ['type' => 'decimal', 'precision' => 2],
        'downtime_impact_aud_hr' => ['type' => 'decimal', 'precision' => 2],
        'wear_and_tear_factor' => ['type' => 'decimal', 'precision' => 2],
        'depreciation_rate_percent_yr' => ['type' => 'decimal', 'precision' => 2]
    ];
    
    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'machinery_id' => 'Machinery ID',
                'name' => 'Machine Name',
                'cost_per_hour_aud' => 'Cost per Hour ($)',
                'serial_number' => 'Serial Number',
                'model_number' => 'Model Number',
                'manufacturer'=>'Manufacturer',
                'energy_efficiency' => 'Energy Efficiency',
                'power_consumption_kw' => 'Power Consumption (kW)',
                'condition' => 'Machine Condition',
                'location' => 'Location',
                'year_of_manufacture' => 'Year of Manufacture',
                'maintenance_frequency' => 'Maintenance Frequency',
                'production_rate_units_hr' => 'Production Rate (Units/Hour)',
                'setup_time_minutes' => 'Setup Time (Minutes)',
                'downtime_impact_aud_hr' => 'Downtime Impact ($/Hour)',
                'wear_and_tear_factor' => 'Wear and Tear Factor',
                'last_maintenance_date' => 'Last Maintenance Date',
                'depreciation_rate_percent_yr' => 'Depreciation Rate (%/Year)',
                'notes' => 'Additional Notes',
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

            // Add data validation for Is Active column
            $isActiveColumn = array_search('energy_efficiency', array_keys($headers)) + 1;
            $isActiveColLetter = Coordinate::stringFromColumnIndex($isActiveColumn);

            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isActiveColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"High,Low,A+,A,B+,B,C,D"');
            }

            $categoryColumn = array_search('maintenance_frequency', array_keys($headers)) + 1;
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
                    ->setFormula1('"Daily,Weekly,Monthly,Quarterly,Biannually,Annually"');
            }

            $conditionColumn = array_search('condition', array_keys($headers)) + 1;
            $conditionColLetter = Coordinate::stringFromColumnIndex($conditionColumn);
            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($conditionColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Good,Excellent,Fair,New,Operational,Under Repair,Decommissioned"');
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
            $supplierColumn = array_search('manufacturer', array_keys($headers)) + 1;
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

            $startDateColumn = array_search('last_maintenance_date', array_keys($headers)) + 1;
            $startDateColLetter = Coordinate::stringFromColumnIndex($startDateColumn);
            // Apply date validation for Start Date and End Date
            for ($row = 2; $row <= 1000; $row++) {
                foreach ([$startDateColLetter] as $colLetter) {
                    $validation = $sheet->getCell($colLetter . $row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_DATE)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Invalid Date')
                        ->setError('Please enter a valid date in YYYY-MM-DD format.')
                        ->setPromptTitle('Date Entry')
                        ->setPrompt('Enter a date in YYYY-MM-DD format.');
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
            header('Content-Disposition: attachment; filename="machinery_template.xlsx"');
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
                if(isset($mappedRow['last_maintenance_date'])){
                    $mappedRow['last_maintenance_date'] = date('Y-m-d', ($mappedRow['last_maintenance_date'] - 25569) * 86400);
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
            // if (empty($row['machinery_id'])) {
            //     $errors[] = "Row {$rowNum}: Machinery ID is required";
            // }

            if($row['machinery_id']){
                if(in_array($row['machinery_id'],$checkArray)){
                    $errors[] = "Row {$rowNum}: {$row['machinery_id']} Machinery ID Duplicate";
                }else{
                    $checkArray[] = $row['machinery_id'];
                }
            }
            if($row['name'] == null){
                $errors[] = "Row {$rowNum}: Machine Name field mandatory";
            }
            
            if($row['name'] != null){
                if(in_array(strtolower($row['name']),$nameArray)){
                    $errors[] = "Row {$rowNum}: {$row['name']} Machinery Name Duplicate";
                }else{
                    $nameArray[] = strtolower($row['name']);
                }
            }

            if($row['cost_per_hour_aud'] == null){
                $errors[] = "Row {$rowNum}: Cost per Hour field mandatory";
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
            foreach ($data as $rowIndex => $row) {
                $machineData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $machineData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                // Skip if no SKU
                // if (empty($machineData['machinery_id'])) {
                //     continue;
                // }

                if(array_key_exists('manufacturer',$machineData)){ 
                    $suppliersName = Client_company::where('client_id', $this->clientID)->where('company_name', trim($machineData['manufacturer']))->pluck('id')->toArray();
                    if(sizeof($suppliersName) > 0){
                        $machineData['manufacturer'] = $suppliersName[0];
                    }else{
                        $item = new Client_company;
                        $item->company_name = trim($machineData['manufacturer']);
                        $iten->client_id = $this->clientID;
                        $category->created_by = $this->user_id;
                        $category->updated_by = $this->user_id;
                        $item->save();
                        $machineData['manufacturer'] = $item->id;
                    } 
                }

                if(isset($machineData['last_maintenance_date'])){
                    $machineData['last_maintenance_date'] = date('Y-m-d', ($machineData['last_maintenance_date'] - 25569) * 86400);
                }
                $machineData['client_id'] = $this->clientID;
                $machineData['workspace_id'] = $this->ws_id;
                $machineData['created_by'] = $this->user_id;
                $machineData['updated_by'] = $this->user_id;

                // Try to find existing ingredient by SKU
                $checkname = Machinery::where('name', $machineData['name'])->where('client_id', $machineData['client_id'])->where('workspace_id', $machineData['workspace_id'])->first();
                if($checkname){
                    $checkID = $checkname->machinery_id;
                    if($machineData['machinery_id'] != null && $machineData['machinery_id'] == $checkID){
                        $checkname->update($machineData);
                    }

                    // Product Machinery details update
                    $costhr = $machineData['cost_per_hour_aud'];
                    $prodMachinery = ProdMachinery::where('machinery_id',$checkname->id)->get();
                    if ($prodMachinery->isNotEmpty()) {
                        foreach ($prodMachinery as $key => $value) {
                            $weight = $value->weight;
                            $total = $value->hours * $costhr;
                            $cost = $total / ($weight/1000);
                            $update_data = [
                                'cost_per_hour' => $costhr,
                                'cost_per_kg' => $cost
                            ];
                            ProdMachinery::where('id',$value->id)->update($update_data);
                        }
                    }
                }else{
                    $machArray = Machinery::where('client_id', $machineData['client_id'])->where('workspace_id', $machineData['workspace_id'])->get()->toArray();
                    if(sizeof($machArray) > 0){
                        $lastArray = end($machArray);
                        $part = explode('_',$lastArray['machinery_id']);
                        $nextNum = increment_numbers($part[1]);
                        $lab_code = "{$machineData['client_id']}{$machineData['workspace_id']}_{$nextNum}";
                    }else{
                        $lab_code = "{$machineData['client_id']}{$machineData['workspace_id']}_001";
                    }
                    $machineData['machinery_id'] = $lab_code;
                    Machinery::create($machineData);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Machinery imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing machinery: ' . $e->getMessage());
        }
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            Machinery::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Machinery Favorite." : "Machinery Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }
    
    public function machine_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $machineName = [];
            $machineArray = json_decode($request->input('machineobj'));

            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($machineArray as $key => $value) {
                    if(ProdMachinery::where('machinery_id',$value)->count() == 0){
                        Machinery::where('id',$value)->update(['archive'=> 1]);
                    }else{
                        $machine = Machinery::where('id', $value)->select('name')->first();
                        $machineName[] = $machine->name;
                    }
                }
                if(sizeof($machineName) > 0 ){
                    $result['status'] = false;
                    $undeleteMachine = implode(',',$machineName);
                    $message = "Follwing Machine not archive because these assigned some products.: {$undeleteMachine}";
                }else{
                    $result['status'] = true;
                    $message = "Machine archive successfully";
                }
                $result['message'] = $message;
                return response()->json($result);
            }
            
            foreach ($machineArray as $key => $value) {
                if(ProdMachinery::where('machinery_id',$value)->count() == 0){
                    Machinery::where('id', $value)->delete();
                }else{
                    $machine = Machinery::where('id', $value)->select('name')->first();
                    $machineName[] = $machine->name;
                }
            }
            if(sizeof($machineName) > 0 ){
                $result['status'] = false;
                $undeleteMachine = implode(',',$machineName);
                $message = "Follwing Machine not delete because these assigned some products.: {$undeleteMachine}";
            }else{
                $result['status'] = true;
                $message = "Machine delete successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }
}
