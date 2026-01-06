<?php

namespace App\Http\Controllers;
use App\Models\{Client_company,Client_company_tag,Client_contact,Client_company_category};
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
  
class CompanyImportController extends Controller
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

    public function import_form()
    {
        return view('backend.client-company.import-form');
    }

    private $numericFields = [
        'phone' => ['type' => 'int','precision' => 0]
    ];

    public function download_template()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'company_name' => 'Company Name',
                'website' => 'Website',
                'ABN' => 'ABN',
                'ACN' => 'ACN',
                'billing_address' => 'Billing Address',
                'delivery_address' => 'Delivery Address',
                'company_category' => 'Company Category',
                'company_tags' => 'Company Tags',
                'notes' => 'Notes',
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'email' => 'Email',
                'phone' => 'Phone'
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
            $categoryName = Client_company_category::where('client_id', $this->clientID)->pluck('name')->toArray();
            if(sizeof($categoryName) > 0){
                $cat_dropdownSheet = $spreadsheet->createSheet();
                $cat_dropdownSheet->setTitle('CategoryDropdownData');
                foreach ($categoryName as $index => $name) {
                    $cat_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $cat_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $categoryRange = "'CategoryDropdownData'!\$A\$1:\$A\$" . count($categoryName);
            }else{
                $categoryRange = '""';
            }
            $categoryColumn = array_search('company_category', array_keys($headers)) + 1;
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

            $prTagsColumn = array_search('company_tags', array_keys($headers)) + 1;
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
            header('Content-Disposition: attachment; filename="company_template.xlsx"');
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
            'Company Name' => 'company_name',
            'Website' => 'website',
            'ABN' => 'ABN',
            'ACN' => 'ACN',
            'Billing Address' => 'billing_address',
            'Delivery Address' => 'delivery_address',
            'Company Category' => 'company_category',
            'Company Tags' => 'company_tags',
            'Notes' => 'notes',
            'First Name' => 'first_name',
            'Last Name' => 'last_name',
            'Email' => 'email',
            'Phone' => 'phone'
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
        $companynameArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            if($row['company_name'] == null){
                $errors[] = "Row {$rowNum}: Company Name field mandatory";
            }

            if($row['company_name']){
                if(in_array($row['company_name'],$companynameArray)){
                    $errors[] = "Row {$rowNum}: {$row['company_name']} Company Name Duplicate";
                }else{
                    $companynameArray[] = $row['company_name'];
                }
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
            $keysToRemove = ['first_name', 'last_name', 'email', 'phone'];
            foreach ($data as $rowIndex => $row) {
                $companyData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $companyData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }

                // Skip if no SKU
                if (empty($companyData['company_name'])) {
                    continue;
                }
            
                if(array_key_exists('company_category',$companyData)){
                    $pr_cate = Client_company_category::where('client_id', $this->clientID)->where('name',$companyData['company_category'] )->first();
                    if($pr_cate){
                        $companyData['company_category'] = $pr_cate->id;
                    }else{
                        $companyData['company_category'] = null;
                    }
                }

                if(array_key_exists('company_tags',$companyData)){
                    if($companyData['company_tags']){
                        $ProdnameArray = array_map('trim', explode(',', $companyData['company_tags']));
                        $ingArray = Client_company_tag::where('client_id', $this->clientID)->whereIn('name',$ProdnameArray)->pluck('id');
                        if(count($ingArray) > 0){
                            $companyData['company_tags'] = $ingArray;
                        }else{
                            $companyData['company_tags'] = null;
                        }
                    }else{
                        $companyData['company_tags'] = null;
                    }
                }
                $companyData['client_id'] = $this->clientID;
                $companyData['created_by'] = $this->user_id;
                $companyData['updated_by'] = $this->user_id;
                $company = Client_company::where('client_id',$this->clientID)->where('company_name', $companyData['company_name'])->first();
                if ($company) {
                    Client_contact::where('client_id', $this->clientID)->where('company', $company->id)->update(['primary_contact' => 0]);
                    if(array_key_exists('email',$companyData)){
                        $exists = Client_contact::where('client_id', $this->clientID)->where('company',$company->id)->where('email', $companyData['email'])->first();
                        if($exists){
                            $companyData['contact_id'] = $exists->id;
                            $exists->update(['primary_contact' => 1 ]);
                        }else{
                            if (!empty($companyData['first_name'] ?? null) &&!empty($companyData['last_name'] ?? null) &&!empty($companyData['email'] ?? null)){ 
                                $item = new Client_contact;
                                $item->client_id = $this->clientID;
                                $item->company = $company->id;
                                $item->first_name = $companyData['first_name'];
                                $item->last_name = $companyData['last_name'];
                                $item->email = $companyData['email'];
                                $item->phone = $companyData['phone']?? null;
                                $item->primary_contact = 1;
                                $item->save();
                                $companyData['contact_id'] = $item->id;
                            }
                        }
                    }
                    foreach ($keysToRemove as $key) {
                        if (array_key_exists($key, $companyData)) {
                            unset($companyData[$key]);
                        }
                    }
                    $company->update($companyData);
                } else {

                    $first_name = $companyData['first_name']?? null;
                    $last_name = $companyData['last_name']??null;
                    $email = $companyData['email']??null;
                    $phone = $companyData['phone']?? null;
                    
                    foreach ($keysToRemove as $key) {
                        if (array_key_exists($key, $companyData)) {
                            unset($companyData[$key]);
                        }
                    }

                    $new_company = Client_company::create($companyData);
                    if ($first_name && $last_name && $email){ 
                        $item = new Client_contact;
                        $item->client_id = $this->clientID;
                        $item->first_name = $first_name;
                        $item->last_name = $last_name;
                        $item->email = $email;
                        $item->phone = $phone;
                        $item->primary_contact = 1;
                        $item->company = $new_company->id;
                        $item->save();
                        Client_company::where('id', $new_company->id)->update(['contact_id' => $item->id]);
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Company imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing company: ' . $e->getMessage());
        }
    }

}
