<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\{Client_company,Client_company_tag,Client_contact,Client_company_category,Ingredient,Machinery,Packaging,Freight,};


class Client_companyController extends Controller
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

    public function index(){
        $companies = Client_company::where('client_id', $this->clientID)->with(['primaryContact','Category'])->latest('created_at')->get()->toArray();
        foreach ($companies as $key => $company) {
            if($company['company_tags']){
                $cmpArray = json_decode($company['company_tags']);
                $tags = DB::table('client_company_tags')->whereIn('id', $cmpArray)->pluck('name')->toArray();
                $companies[$key]['Comp_Tags'] = implode(', ', $tags);
            }else{
                $companies[$key]['Comp_Tags'] = "";
            }
            
        }
        $company_tags = Client_company_tag::where('client_id', $this->clientID)->with(['creator'])->latest('created_at')->get()->toArray();
        $company_categories = Client_company_category::where('client_id', $this->clientID)->with(['creator'])->latest('created_at')->get()->toArray();
        return view('backend.client-company.manage', compact('companies','company_tags','company_categories'));
    }



    public function save_tag(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_company_tags')
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $this->clientID;
            Client_company_tag::create($data);
            $response['success'] = true;
            $response['message'] = 'Company Tag created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }


    public function update_tag(Request $request, Client_company_tag $client_company_tag){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_company_tags')
                        ->ignore($client_company_tag->id)
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $client_company_tag->update($data);
            $response['success'] = true;
            $response['message'] = 'Tag Updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }

    public function delete_tag(Client_company_tag $client_company_tag){
        try {
            $companiesUsingTag = DB::table('client_companies')
                                ->whereRaw('JSON_CONTAINS(company_tags, ?)', ['"' . $client_company_tag->id . '"'])
                                ->get();
            if ($companiesUsingTag->isEmpty()) { //Safe to delete tag 
                $client_company_tag->delete(); 
                return response()->json(['success' => true, 'message' => 'Tag Deleted']);
            } else {
                return response()->json(['success' => false, 'message' => 'Cannot delete this tag as it is associated with existing companies.'], 500);
            }
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function save_company(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'company_name' => [
                    'required',
                    'string',
                    Rule::unique('client_companies')
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
                'notes' => ['nullable', 'string'],
                'delivery_address' => ['nullable', 'string'],
                'billing_address' => ['nullable', 'string'],
                'ACN' => ['nullable', 'string'],
                'ABN' => ['nullable', 'string'],
                'website' => ['nullable', 'string'],
                'company_category' => ['nullable', 'integer'],
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $data['company_tags'] = ($request->input('company_tags'))? json_encode($request->input('company_tags')): null;
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $this->clientID;
            $company_details = Client_company::create($data); // Company create
            if($request->email){
                $contact_validator = Validator::make($request->all(), [
                                    'first_name' => ['required', 'string'],
                                    'last_name'  => ['required', 'string'],
                                    'email'      => ['required', 'email'],
                                    'phone'      => ['nullable', 'string'],
                                ], [
                                    'first_name.required' => 'First name is required',
                                    'last_name.required' => 'Last name is required',
                                ]);
                if ($contact_validator->fails()) {
                    return response()->json(['success' => false, 'errors' => $contact_validator->errors()], 422);
                }
                $contact_details = $contact_validator->validated();
                $contact_details['created_by'] = $this->user_id;
                $contact_details['updated_by'] = $this->user_id;
                $contact_details['client_id'] = $this->clientID;
                $contact_details['primary_contact'] = 1;
                $contact = Client_contact::create($contact_details); //Contact Create
                Client_company::where('id', $company_details->id)->update(['contact_id' => $contact->id]);
                Client_contact::where('id', $contact->id)->update(['company' => $company_details->id]); //compnay update
            }
            $response['success'] = true;
            $response['message'] = 'Company created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }
    

    public function update_company(Request $request, Client_company $client_company){
        try {
            $validator = Validator::make($request->all(), [
                'company_name' => [
                    'required',
                    'string',
                    Rule::unique('client_companies')
                        ->ignore($client_company->id)
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
                'notes' => ['nullable', 'string'],
                'delivery_address' => ['nullable', 'string'],
                'billing_address' => ['nullable', 'string'],
                'ACN' => ['nullable', 'string'],
                'ABN' => ['nullable', 'string'],
                'website' => ['nullable', 'string'],
                'company_category' => ['nullable', 'integer'],
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $client_company->load('primaryContact');
            $data = $validator->validated();
            $data['company_tags'] = ($request->input('company_tags'))? json_encode($request->input('company_tags')): null;
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $this->clientID;
            $client_company->update($data);
            if($request->email){
                $existes = Client_contact::where('client_id', $this->clientID)->where('company',$client_company->id)->where('email',$request->email)->first();
                if($existes){
                    Client_company::where('id', $client_company->id)->update(['contact_id' => $exists->id]);
                    Client_contact::where('client_id', $this->clientID)->where('company', $client_company->id)->update(['primary_contact' => 0]);
                    $exists->update(['primary_contact' => 1]);
                }else{

                    $contact_validator = Validator::make($request->all(), [
                                    'first_name' => ['required', 'string'],
                                    'last_name'  => ['required', 'string'],
                                    'email'      => ['required', 'email'],
                                    'phone'      => ['nullable', 'string'],
                                ], [
                                    'first_name.required' => 'First name is required',
                                    'last_name.required' => 'Last name is required',
                                ]);
                    if ($contact_validator->fails()) {
                        return response()->json(['success' => false, 'errors' => $contact_validator->errors()], 422);
                    }
                    Client_contact::where('client_id', $this->clientID)->where('company', $client_company->id)->update(['primary_contact' => 0]);
                    $contact_details = $contact_validator->validated();
                    $contact_details['created_by'] = $this->user_id;
                    $contact_details['updated_by'] = $this->user_id;
                    $contact_details['client_id'] = $this->clientID;
                    $contact_details['primary_contact'] = 1;
                    $contact_details['company'] = $client_company->id;
                    $contact = Client_contact::create($contact_details); //Contact Create
                    Client_company::where('id', $client_company->id)->update(['contact_id' => $contact->id]);
                }
            }
            $response['success'] = true;
            $response['message'] = 'Company updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }
    
    public function delete_company(Client_company $client_company){
        try {
            $checks = [
                        [
                            'model' => Ingredient::class,
                            'field' => 'supplier_name',
                            'message' => 'Raw materials are assigned to this company.'
                        ],
                        [
                            'model' => Machinery::class,
                            'field' => 'manufacturer',
                            'message' => 'Machineries are assigned to this company.'
                        ],
                        [
                            'model' => Packaging::class,
                            'field' => 'supplier_id',
                            'message' => 'Packagings are assigned to this company.'
                        ],
                        [
                            'model' => Freight::class,
                            'field' => 'freight_supplier',
                            'message' => 'Freights are assigned to this company.'
                        ],
                    ];
                    
            $customTxt = ($client_company->archive == 1)? "deleted" :"archive";
            foreach ($checks as $check) {
                $count = $check['model']::where('client_id', $client_company->client_id)
                            ->where($check['field'], $client_company->id)
                            ->count();

                if ($count > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "This company cannot be {$customTxt} because " . $check['message']
                    ], 500);
                }
            }
            if($client_company->archive == 0){
                $client_company->update(['archive' => 1]);
                $response_message = "Company moved to archive status";
            }else{
                $client_company->delete(); 
                $response_message = "Company Deleted";
            }
            return response()->json(['success' => true, 'message' => $response_message]);
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function bulk_delete_company(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $companyArray = json_decode($request->input('companyobj'));
            $checks=[
                        [
                            'model' => Ingredient::class,
                            'field' => 'supplier_name',
                            'message' => 'Raw materials are assigned to this company.'
                        ],
                        [
                            'model' => Machinery::class,
                            'field' => 'manufacturer',
                            'message' => 'Machineries are assigned to this company.'
                        ],
                        [
                            'model' => Packaging::class,
                            'field' => 'supplier_id',
                            'message' => 'Packagings are assigned to this company.'
                        ],
                        [
                            'model' => Freight::class,
                            'field' => 'freight_supplier',
                            'message' => 'Freights are assigned to this company.'
                        ],
                    ];
            $company_name = [];
            foreach ($companyArray as $key => $value) {
                $company = Client_company::where('id', $value)->select('company_name')->first();
                $temp_name = [];
                foreach ($checks as $check) {
                    $count = $check['model']::where('client_id', $this->clientID)->where($check['field'], $value)->count();
                    if ($count > 0) {
                        $temp_name[] = $company->company_name;
                        break;
                    }
                }

                if(count($temp_name) > 0){
                    $company_name[] = $company->company_name;
                    continue;
                }

                if(in_array($archiveVal,['all','0'])){
                    Client_company::where('id',$value)->update(['archive'=> 1]);
                }else{
                    Client_company::where('id',$value)->delete();
                }
            }

            $customTxt = ($archiveVal == 1)? "delete" :"archive"; 
            if(sizeof($company_name) > 0 ){
                $result['status'] = false;
                $undeleteCompany = implode(',',$company_name);
                $message = "Follwing Company not {$customTxt} because these assigned some products.: {$undeleteCompany}";
            }else{
                $result['status'] = true;
                $message = "Company {$customTxt} successfully";
            }
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function unarchive(Client_company $client_company)
    {
        try {
            $client_company->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Company unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }


    public function save_category(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_company_categories')
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $this->clientID;
            Client_company_category::create($data);
            $response['success'] = true;
            $response['message'] = 'Company Category created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }


    public function update_category(Request $request, Client_company_category $client_company_category){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_company_categories')
                        ->ignore($client_company_category->id)
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $client_company_category->update($data);
            $response['success'] = true;
            $response['message'] = 'Category Updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }

    public function delete_category(Client_company_category $client_company_category){
        try {
            $companiesUsingCategory = DB::table('client_companies')->where('company_category', $client_company_category->id)->exists();
            if (!$companiesUsingCategory) { // Safe to delete tag
                $client_company_category->delete(); 
                return response()->json(['success' => true, 'message' => 'Category Deleted']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this category as it is associated with existing companies.'
                ], 500);
            }
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


}
