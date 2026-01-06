<?php

namespace App\Http\Controllers;
use App\Models\{Client_contact,Client_contact_tag,Client_company,Client_contact_category};
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

class ContactImportController extends Controller
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
        return view('backend.client-contact.import-form');
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
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'company' => 'Company',
                'contact_category' => 'Contact Category',
                'contact_tags' => 'Contact Tags',
                'notes' => 'Notes',
                'primary_contact' => 'Primary Contact'
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

            $companyName = Client_company::where('client_id', $this->clientID)->pluck('company_name')->toArray();
            if(sizeof($companyName) > 0){
                $comp_dropdownSheet = $spreadsheet->createSheet();
                $comp_dropdownSheet->setTitle('CompanyDropdownData');
                foreach ($companyName as $index => $name) {
                    $comp_dropdownSheet->setCellValue('A' . ($index + 1), $name);
                }
                $comp_dropdownSheet->getColumnDimension('A')->setAutoSize(true);
                $companyRange = "'CompanyDropdownData'!\$A\$1:\$A\$" . count($companyName);
            }else{
                $companyRange = '""';
            }
            $companyColumn = array_search('company', array_keys($headers)) + 1;
            $companyColLetter = Coordinate::stringFromColumnIndex($companyColumn);
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($companyColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1($companyRange);
                $sheet->getCell($companyColLetter . $row)->setDataValidation($validation);
            }


            // Add data validation for company column
            $categoryName = Client_contact_category::where('client_id', $this->clientID)->pluck('name')->toArray();
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
            $categoryColumn = array_search('contact_category', array_keys($headers)) + 1;
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

            $prTagsColumn = array_search('contact_tags', array_keys($headers)) + 1;
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


            // Add data validation for Is Active column
            $isPrimaryColumn = array_search('primary_contact', array_keys($headers)) + 1;
            $isPrimaryColLetter = Coordinate::stringFromColumnIndex($isPrimaryColumn);

            // Add dropdown for first 1000 rows (you can adjust this number)
            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getCell($isPrimaryColLetter . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"Yes,No"');
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
            header('Content-Disposition: attachment; filename="contact_template.xlsx"');
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
        $emailArray = [];
        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            if($row['first_name'] == null){
                $errors[] = "Row {$rowNum}: First Name field mandatory";
            }

            if($row['last_name'] == null){
                $errors[] = "Row {$rowNum}: Last Name field mandatory";
            }

            if($row['email'] == null){
                $errors[] = "Row {$rowNum}: Email field mandatory";
            }

            if($row['email']){
                if(in_array($row['email'],$emailArray)){
                    $errors[] = "Row {$rowNum}: {$row['email']} Email Duplicate";
                }else{
                    $emailArray[] = $row['email'];
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
            foreach ($data as $rowIndex => $row) {
                $clientData = [];
                foreach ($row as $index => $value) {
                    if (isset($dbHeaders[$index])) {
                        $clientData[$dbHeaders[$index]] = $this->formatValue($dbHeaders[$index], $value);
                    }
                }

                // Skip if no details
                if (empty($clientData['first_name']) || empty($clientData['last_name']) || empty($clientData['email'])) {
                    continue;
                }
            
                if(array_key_exists('contact_category',$clientData)){
                    $pr_cate = Client_contact_category::where('client_id', $this->clientID)->where('name',$clientData['contact_category'] )->first();
                    if($pr_cate){
                        $clientData['contact_category'] = $pr_cate->id;
                    }else{
                        $clientData['contact_category'] = null;
                    }
                }

                if(array_key_exists('contact_tags',$clientData)){
                    if($clientData['contact_tags']){
                        $ProdnameArray = array_map('trim', explode(',', $clientData['contact_tags']));
                        $ingArray = Client_contact_tag::where('client_id', $this->clientID)->whereIn('name',$ProdnameArray)->pluck('id');
                        if(count($ingArray) > 0){
                            $clientData['contact_tags'] = $ingArray;
                        }else{
                            $clientData['contact_tags'] = null;
                        }
                    }else{
                        $clientData['contact_tags'] = null;
                    }
                }

                if(array_key_exists('company',$clientData)){
                    $pr_company = Client_company::where('client_id', $this->clientID)->where('company_name',$clientData['company'] )->first();
                    if($pr_company){
                        $clientData['company'] = $pr_company->id;
                    }else{
                        $clientData['company'] = null;
                    }
                }

                $clientData['client_id'] = $this->clientID;
                $clientData['created_by'] = $this->user_id;
                $clientData['updated_by'] = $this->user_id;
                $clientData['primary_contact'] = 0;
                if($clientData['company']){
                    $contact = Client_contact::where('client_id', $this->clientID)->where('company',$clientData['company'])->where('email', $clientData['email'])->first();
                    if ($contact) {
                        if(array_key_exists('primary_contact',$clientData) && $clientData['primary_contact'] == "Yes"){
                            Client_contact::where('client_id', $this->clientID)->where('company',$clientData['company'])->update(['primary_contact' => 0]);
                            $clientData['primary_contact'] = 1;
                            $contact->update($clientData);
                            Client_company::where('id',$clientData['company'])->update(['contact_id' => $contact->id]);
                        }else{
                            Client_contact::create($clientData);
                        }
                    } else {
                        Client_contact::create($clientData);
                    }
                }else{
                    Client_contact::create($clientData);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Contact imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing contact: ' . $e->getMessage());
        }
    }
}
