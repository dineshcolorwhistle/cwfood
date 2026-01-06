<?php

namespace App\Http\Controllers;

use App\Models\{Labour,ProdLabour,Product};
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
use Carbon\Carbon;

class LabourController extends Controller
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
            $permission = get_member_permission($user_id,$clientID,['Resources - Labour','Resources - Labour Read']);
        }else{
            $permission = [];
        }

        $labours = Labour::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('updated_at')->get()->toArray();
        if(sizeof($labours) > 0){
            // $lastArray = end($labours);
            // $part = explode('_',$lastArray['labour_id']);
            // if(isset($part[1])){
            //     $nextNum = increment_numbers($part[1]);
            // }else{
            //     $nextNum = "001";
            // }
            $exist_labour = array_column($labours, 'labour_id');
            $max = collect($exist_labour)
                    ->map(function ($item) {
                        return (int) explode('_', $item)[1];
                    })
                    ->max();
            $nextNum = increment_numbers($max);
            $lab_code = "{$clientID}{$workspaceID}_{$nextNum}";
        }else{
            $lab_code = "{$clientID}{$workspaceID}_001";
        }
        return view('backend.labour.labours', compact('labours','lab_code','permission','user_role'));
    }
    /**
     * Store a newly created labour in database.
     */
    public function store(Request $request)
    {
        try {
            $validationRules = Labour::validationRules();
            $validationMessages = Labour::validationMessages();
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
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $clientID;
            $data['workspace_id'] = $workspaceID;
            $labour = Labour::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Labour created successfully',
                'labour' => $labour
            ]);
        } catch (\Exception $e) {
            Log::error('Labour creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    /**
     * Update the specified labour resource in the database.
     */
    public function update(Request $request, Labour $labour)
    {
        try {
            $validationRules = Labour::validationRules($labour->id);
            $validationMessages = Labour::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;
            $ext = number_format($labour->hourly_rate,2);
            $new = number_format($data['hourly_rate'],2);
            if($ext != $new){
                $this->update_productlabours($labour->id,$new);
            }
            $labour->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Labour updated successfully',
                'labour' => $labour
            ]);
        } catch (\Exception $e) {
            Log::error('Labour update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }


    public function update_productlabours($labID,$hrRate){
        $pr_labour = ProdLabour::where('labour_id',$labID)->get()->toArray();
        foreach ($pr_labour as $key => $value) {
            $product = Product::findOrFail($value['product_id']);
            switch ($value['product_units']) {
                case '1':
                    $weight = $product->weight_ind_unit_g;
                    break;
                case '2':
                    $weight = $product->weight_retail_unit_g;
                    break;
                case '3':
                    $weight = $product->batch_after_waste_g;
                    break;
                case '4':
                    $weight = $product->weight_carton_g;
                    break;
                case '5':
                    $weight = $product->weight_pallet_g;
                    break;
                default:
                    $weight = 0;
            }
            $total = $value['people_hours'] * $hrRate;
            $cost = $total / ($weight/1000);
            $update_data['hourly_rate'] = $hrRate;
            $update_data['cost_per_kg'] = $cost;
            ProdLabour::where('id',$value['id'])->update($update_data);
        }
        return;
    }

    /**
     * Remove the specified labour from the database.
     */
    public function destroy(Labour $labour)
    {
        try {
            $status = true;
            if($labour->archive == 0){
                if(ProdLabour::where('labour_id',$labour->id)->count() == 0){
                    $labour->update(['archive' => 1]);
                    $message = 'Labour moved to archive status';                    
                }else{
                    $status = false;
                    $message = 'Labour not archive because these assigned some products.';
                }
            }else{
                if(ProdLabour::where('labour_id',$labour->id)->count() == 0){
                    $labour->delete();
                    $message = 'Labour deleted successfully';
                }else{
                    $status = false;
                    $message = 'Labour not delete because these assigned some products.';
                }
            }
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            // Log::error('Labour deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function unarchive(Labour $labour)
    {
        try {
            $labour->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Labour unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function show_import()
    {
        return view('backend.labour.import-form');
    }

    private $numericFields = [
        'hourly_rate' => ['type' => 'decimal', 'precision' => 2]
    ];
    
    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'labour_id' => 'Labour ID',
                'labour_type' => 'Labour Type',
                'hourly_rate' => 'Hourly Rate ($)',
                'labour_category' => 'Category',
                'notes' => 'Notes'
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

            $categoryColumn = array_search('labour_category', array_keys($headers)) + 1;
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
                    ->setFormula1('"Kitchen,Packing,Logistics,Cleaning,Miscellaneous"');
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
            header('Content-Disposition: attachment; filename="labour_template.xlsx"');
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
            'Labour ID' => 'labour_id',
            'Labour Type' => 'labour_type',
            'Hourly Rate ($)' => 'hourly_rate',
            'Category' => 'labour_category',
            'Notes' => 'notes'
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
        $labourIDArray = [];
        $labourTypeArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            // Required fields
            // if (empty($row['labour_id'])) {
            //     $errors[] = "Row {$rowNum}: Labour ID is required";
            // }
            if($row['labour_id']){
                if(in_array($row['labour_id'],$labourIDArray)){
                    $errors[] = "Row {$rowNum}: {$row['labour_id']} Labour ID Duplicate";
                }else{
                    $labourIDArray[] = $row['labour_id'];
                }
            }

            if($row['labour_type'] == null){
                $errors[] = "Row {$rowNum}: Labour Type field mandatory";
            }

            if($row['labour_type'] != null){
                if(in_array(strtolower($row['labour_type']),$labourTypeArray)){
                    $errors[] = "Row {$rowNum}: {$row['labour_type']} Labour Type Duplicate";
                }else{
                    $labourTypeArray[] = strtolower($row['labour_type']);
                }
            }

            if($row['hourly_rate'] == null){
                $errors[] = "Row {$rowNum}: Hourly rate field mandatory";
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
                $labourData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $labourData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }
                $labourData['client_id'] = $this->clientID;
                $labourData['workspace_id'] = $this->ws_id;
                $labourData['created_by'] = $this->user_id;
                $labourData['updated_by'] = $this->user_id;
                // Try to find existing ingredient by SKU
                $checkname = Labour::where('labour_type', $labourData['labour_type'])->where('client_id',$labourData['client_id'])->where('workspace_id',$labourData['workspace_id'])->first();
                if($checkname){
                    $checkID = $checkname->labour_id;
                    if($labourData['labour_id'] != null && $labourData['labour_id'] == $checkID){
                        $checkname->update($labourData);
                    }
                    
                    // Product Labour details update
                    $hrRate = $labourData['hourly_rate'];
                    $prodlabours = ProdLabour::where('labour_id',$checkname->id)->get();
                    if ($prodlabours->isNotEmpty()) {
                        foreach ($prodlabours as $key => $value) {
                            $product = Product::findOrFail($value->product_id);
                            switch ($value->product_units) {
                                case '1':
                                    $weight = $product->weight_ind_unit_g;
                                    break;
                                case '2':
                                    $weight = $product->weight_retail_unit_g;
                                    break;
                                case '3':
                                    $weight = $product->batch_after_waste_g;
                                    break;
                                case '4':
                                    $weight = $product->weight_carton_g;
                                    break;
                                case '5':
                                    $weight = $product->weight_pallet_g;
                                    break;
                                default:
                                    $weight = 0;
                            }
                            $total = $value->people_hours * $hrRate;
                            $cost = $total / ($weight/1000);

                            $update_data = [
                                'hourly_rate' => $hrRate,
                                'cost_per_kg' => $cost
                            ];
                            ProdLabour::where('id',$value->id)->update($update_data);
                        }
                    }

                }else{
                    $machArray = Labour::where('client_id', $labourData['client_id'])->where('workspace_id', $labourData['workspace_id'])->get()->toArray();
                    if(sizeof($machArray) > 0){
                        $lastArray = end($machArray);
                        $part = explode('_',$lastArray['labour_id']);
                        $nextNum = increment_numbers($part[1]);
                        $lab_code = "{$labourData['client_id']}{$labourData['workspace_id']}_{$nextNum}";
                    }else{
                        $lab_code = "{$labourData['client_id']}{$labourData['workspace_id']}_001";
                    }
                    $labourData['labour_id'] = $lab_code;
                    Labour::create($labourData);
                } 
            }
            DB::commit();
            return redirect()->back()->with('success', 'Labour imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing labour: ' . $e->getMessage());
        }
    }

    public function make_favorite(Request $request,$id){
        try {
            $fav_val = ((int) $request->input('favor') == 0) ? 1 : 0;
            Labour::where('id', $id)->update(['favorite' => $fav_val]);
            $result['status'] = true;
            $result['message'] = ((int) $request->input('favor') == 0) ? "Labour Favorite." : "Labour Unfavorite.";
            $result['val'] = $fav_val;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function labour_delete(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $labourArray = json_decode($request->input('labourobj'));
            if($archiveVal == "all" || $archiveVal == "0"){
                foreach ($labourArray as $key => $value) {
                    if(ProdLabour::where('labour_id',$value)->count() == 0){
                        Labour::where('id',$value)->update(['archive'=> 1]);
                    }else{
                        $labour = Labour::where('id', $value)->select('labour_type')->first();
                        $labourName[] = $labour->labour_type;
                    }
                }
                if(sizeof($labourName) > 0 ){
                    $result['status'] = false;
                    $undeleteLabour = implode(',',$labourName);
                    $message = "Follwing Labour not archive because these assigned some products.: {$undeleteLabour}";
                }else{
                    $result['status'] = true;
                    $message = "Archived all selected items";
                }
                // Labour::whereIn('id',$labourArray)->update(['archive'=> 1]);
                $result['message'] = $message;
                return response()->json($result);
            }
            $labourName = [];
            foreach ($labourArray as $key => $value) {
                if(ProdLabour::where('labour_id',$value)->count() == 0){
                    Labour::where('id', $value)->delete();
                }else{
                    $labour = Labour::where('id', $value)->select('labour_type')->first();
                    $labourName[] = $labour->labour_type;
                }
            }
            if(sizeof($labourName) > 0 ){
                $result['status'] = false;
                $undeleteLabour = implode(',',$labourName);
                $message = "Follwing Labour not delete because these assigned some products.: {$undeleteLabour}";
            }else{
                $result['status'] = true;
                $message = "Labour delete successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

}
