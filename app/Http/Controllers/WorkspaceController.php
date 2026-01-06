<?php

namespace App\Http\Controllers;

use App\Models\{Workspace,ClientSubscription,Member,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
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

    public function index(Request $request, $clientId)
    {
        $workspaces = Workspace::where('client_id', $clientId)->get();
        return view('backend.workspace.workspaces', compact('workspaces', 'clientId'));
    }

    public function show_with_ws(Request $request, $clientId,$ws_id)
    {
        $workspaces = Workspace::where('client_id', $clientId)->get();
        return view('backend.workspace.workspaces', compact('workspaces', 'clientId'));
    }

    public function store(Request $request, $clientId)
    {
        try {
            $validationRules = Workspace::validationRules(null, $clientId);

            $validator = Validator::make(
                array_merge($request->all(), ['client_id' => $clientId]),
                array_merge($validationRules, [
                    'client_id' => 'required|exists:clients,id'
                ])
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $ws_count = Workspace::where('client_id',$clientId)->count();
            $client_plan = ClientSubscription::where('client_id',$clientId)->with(['plan'])->first();
            if($client_plan && $client_plan->plan->max_work_spaces > $ws_count){
                $data = $validator->validated();
                $data['client_id'] = $clientId;
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $workspace = Workspace::create($data);
    
                return response()->json([
                    'success' => true,
                    'message' => 'Workspace created successfully',
                    'workspace' => $workspace
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Already workspace limit reached. Contact Batchbase admin',
                ]);
            }            
        } catch (\Exception $e) {
            Log::error('Workspace creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $clientId, Workspace $workspace)
    {
        try {
            // Ensure the workspace belongs to the client
            if ($workspace->client_id != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized workspace access'
                ], 403);
            }

            $validationRules = Workspace::validationRules($workspace->id, $clientId);

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;

            $workspace->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Workspace updated successfully',
                'workspace' => $workspace
            ]);
        } catch (\Exception $e) {
            Log::error('Workspace update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($clientId, Workspace $workspace)
    {
        try {
            // Ensure the workspace belongs to the client
            if ($workspace->client_id != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized workspace access'
                ], 403);
            }
            // $ws_count = Workspace::where('client_id',$clientId)->count();
            $response = delete_workspace_details($workspace->id,$clientId,'workspace');
            if($response['status'] == true){
                $workspace->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Workspace deleted successfully'
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => $response['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Workspace deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function get_ws_list(Request $request){
        try {
            $clientId = $request->input('client');
            $user = User::where('id',$this->user_id)->first();
            if($user->role_id == 1){
                $ws_list = get_ws_list_based_clientID($clientId);
                $default_ws = get_default_ws($clientId);
            }else{
                $details = Member::where('user_id',$this->user_id)->where('client_id',$clientId)->first();
                session()->put('role_id', $details->role_id);
                if(in_array($details->role_id,[2,3])){
                    $ws_list = get_ws_list_based_clientID($clientId);
                    $default_ws = get_default_ws($clientId);
                }else{
                    $ws_list = get_workspace_scope_wslist($clientId,$this->user_id);
                    $default_ws = $ws_list[0]['id'];
                }
            }
            session()->put('client', $clientId);
            session()->put('workspace', $default_ws);
            return response()->json(['status' => true,'ws_list' => $ws_list, 'ws_id'=>$default_ws]);
        } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function make_primary(Request $request){
        try {
            $cid = $request->input('cid');
            $ws_id = (int)$request->input('ws_id');
            $pr_value = $request->input('pr_value');
            $check = Workspace::where('client_id',$cid)->where('ws_primary',1)->first();
            if($check){
                if($check->id == $ws_id){
                    return response()->json(['status' => false,'message' => "Can't do any action."]);
                }else{
                    Workspace::where('id',$check->id)->update(['ws_primary'=>0]);
                    Workspace::where('client_id',$cid)->where('id',$ws_id)->update(['ws_primary'=>1]);
                    return response()->json(['status' => true,'message' => "Made Primary"]);
                }
            }else{
                Workspace::where('client_id',$cid)->where('id',$ws_id)->update(['ws_primary'=>1]);
                return response()->json(['status' => true,'message' => "Made Primary"]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
