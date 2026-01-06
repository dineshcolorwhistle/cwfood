<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\{Client_contact,Client_contact_tag,Client_company,Client_contact_category};

class Client_contactController extends Controller
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
        $contacts = Client_contact::where('client_id', $this->clientID)->with(['company','Category'])->latest('created_at')->get()->toArray();
        $contact_tags = Client_contact_tag::where('client_id', $this->clientID)->with(['creator'])->latest('created_at')->get()->toArray();
        $companies = Client_company::where('client_id', $this->clientID)->get()->toArray();
        foreach ($contacts as $key => $contact) {
            if($contact['contact_tags']){
                $cmpArray = json_decode($contact['contact_tags']);
                $tags = DB::table('client_contact_tags')->whereIn('id', $cmpArray)->pluck('name')->toArray();
                $contacts[$key]['Cont_Tags'] = implode(', ', $tags);
            }else{
                $contacts[$key]['Cont_Tags'] = "";
            }
            
        }
        $contact_categories = Client_contact_category::where('client_id', $this->clientID)->with(['creator'])->latest('created_at')->get()->toArray();
        return view('backend.client-contact.manage', compact('contacts','contact_tags','companies','contact_categories'));
    }

    public function save_tag(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_contact_tags')
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
            Client_contact_tag::create($data);
            $response['success'] = true;
            $response['message'] = 'Contact Tag created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }


    public function update_tag(Request $request, Client_contact_tag $client_contact_tag){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_contact_tags')
                        ->ignore($client_contact_tag->id)
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $client_contact_tag->update($data);
            $response['success'] = true;
            $response['message'] = 'Tag Updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }

    public function delete_tag(Client_contact_tag $client_contact_tag){
        try {
            $companiesUsingTag = DB::table('client_contacts')
                                ->whereRaw('JSON_CONTAINS(Tags, ?)', ['"' . $client_contact_tag->id . '"'])
                                ->get();
            if ($companiesUsingTag->isEmpty()) { //Safe to delete tag 
                $client_contact_tag->delete(); 
                return response()->json(['success' => true, 'message' => 'Tag Deleted']);
            } else {
                return response()->json(['success' => false, 'message' => 'Cannot delete this tag as it is associated with existing contacts.'], 500);
            }
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function save_contact(Request $request){
        try {
            $contact_validator = Validator::make($request->all(), [
                                    'first_name' => ['required', 'string'],
                                    'last_name'  => ['required', 'string'],
                                    'email'      => ['required', 'email',
                                                    Rule::unique('client_contacts')
                                                        ->where(function ($query) use ($request) {
                                                            return $query
                                                                ->where('client_id', $this->clientID)
                                                                ->where('company', $request->input('company'));
                                                        }),
                                                    ],
                                    'phone'      => ['nullable', 'string'],
                                    'company'    => ['nullable', 'integer'],
                                    'notes'      => ['nullable', 'string'],
                                    'contact_category' => ['nullable', 'integer']
                                ], [
                                    'first_name.required' => 'First name is required',
                                    'last_name.required' => 'Last name is required',
                                ]);
            if ($contact_validator->fails()) {
                return response()->json(['success' => false, 'errors' => $contact_validator->errors()], 422);
            }
            $data = $contact_validator->validated();
            $data['contact_tags'] = ($request->input('contact_tags'))? json_encode($request->input('contact_tags')): null;
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $this->clientID;
            Client_contact::create($data);
            $response['success'] = true;
            $response['message'] = 'Contact created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }
    

    public function update_contact(Request $request, Client_contact $client_contact){
        try {
            $contact_validator = Validator::make($request->all(), [
                                    'first_name' => ['required', 'string'],
                                    'last_name'  => ['required', 'string'],
                                    'email'      => ['required', 'email',
                                                    Rule::unique('client_contacts')
                                                            ->ignore($client_contact->id)
                                                            ->where(function ($query) use ($request) {
                                                                return $query
                                                                    ->where('client_id', $this->clientID)
                                                                    ->where('company', $request->input('company'));
                                                            }),
                                                    ],
                                    'phone'      => ['nullable', 'string'],
                                    'company'    => ['nullable', 'integer'],
                                    'notes'      => ['nullable', 'string'],
                                    'contact_category' => ['nullable', 'integer']
                                ], [
                                    'first_name.required' => 'First name is required',
                                    'last_name.required' => 'Last name is required',
                                ]);
            if ($contact_validator->fails()) {
                return response()->json(['success' => false, 'errors' => $contact_validator->errors()], 422);
            }
            $data = $contact_validator->validated();
            $data['contact_tags'] = ($request->input('contact_tags'))? json_encode($request->input('contact_tags')): null;
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $client_contact->update($data);
            $response['success'] = true;
            $response['message'] = 'Contact updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }
    
    public function delete_contact(Client_contact $client_contact){
        try {
            $customTxt = ($client_contact->archive == 1)? "delete" :"archive"; 
            if($client_contact->company){
                return response()->json(['success' => false, 'message' => "Cannot {$customTxt} this contact as it is associated with existing company."], 500);
            }
            
            if($client_contact->archive == 0){
                $client_contact->update(['archive' => 1]);
                $response_message = "Contact moved to archive status";
            }else{
                $client_contact->delete(); 
                $response_message = "Contact Deleted";
            }
            return response()->json(['success' => true, 'message' => $response_message]);
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update_primary_contact(Request $request,Client_contact $client_contact){
        try {
            
            if($request->check == 0){
                $client_contact->update(['primary_contact' => 0]);
                Client_company::where('id', $client_contact->compnay)->update(['contact_id' => null]);
                $message = "Primary Contact Removed";
            }else{
                Client_contact::where('client_id', $this->clientID)->where('company',$client_contact->company)->update(['primary_contact' => 0]);
                $client_contact->update(['primary_contact' => 1]);
                Client_company::where('id', $client_contact->company)->update(['contact_id' => $client_contact->id]);
                $message = "Primary Contact Updated";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function save_category(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_contact_categories')
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
            Client_contact_category::create($data);
            $response['success'] = true;
            $response['message'] = 'Contact Category created';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }

    public function update_category(Request $request, Client_contact_category $client_contact_category){
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('client_company_categories')
                        ->ignore($client_contact_category->id)
                        ->where(function ($query) {
                            return $query->where('client_id', $this->clientID);
                        }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $client_contact_category->update($data);
            $response['success'] = true;
            $response['message'] = 'Category Updated';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        return response()->json($response);
    }

    public function delete_category(Client_contact_category $client_contact_category){
        try {
            $companiesUsingCategory = DB::table('client_contacts')->where('contact_category', $client_contact_category->id)->exists();
            if (!$companiesUsingCategory) { // Safe to delete tag
                $client_contact_category->delete(); 
                return response()->json(['success' => true, 'message' => 'Category Deleted']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this category as it is associated with existing contacts.'
                ], 500);
            }
        } catch (\Exception $e) { 
           return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function contact_search(Request $request){
        $query = $request->get('email');
        $contacts = Client_contact::where('client_id', $this->clientID)->where('email', 'like', "%{$query}%")->limit(5)->get(['email', 'phone', 'first_name', 'last_name']);
        return response()->json($contacts);
    }

    public function bulk_delete_contact(Request $request){
        try {
            $archiveVal = $request->input('archive');
            $contactArray = json_decode($request->input('contactobj'));
            $contact_name = [];
            foreach ($contactArray as $key => $value) {
                $contact = Client_contact::where('id', $value)->first();
                if($contact->company){
                    $contact_name[] = "{$contact->first_name} {$contact->last_name}";
                    continue;
                }

                if(in_array($archiveVal,['all','0'])){
                    Client_contact::where('id',$value)->update(['archive'=> 1]);
                }else{
                    Client_contact::where('id',$value)->delete();
                }
            }

            $customTxt = ($archiveVal == 1)? "delete" :"archive"; 
            if(sizeof($contact_name) > 0 ){
                $result['status'] = false;
                $undeleteContact = implode(',',$contact_name);
                $message = "Follwing Contact not {$customTxt} because these assigned some companies.: {$undeleteContact}";
            }else{
                $result['status'] = true;
                $message = "Company {$customTxt} successfully";
            }
            $result['status'] = true;
            $result['message'] = $message;
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function unarchive(Client_contact $client_contact)
    {
        try {
            $client_contact->update(['archive' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Contact unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

}
