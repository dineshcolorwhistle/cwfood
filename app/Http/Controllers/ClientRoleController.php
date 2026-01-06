<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Client_role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientRoleController extends Controller
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
        $session = $request->session()->all();
        $cid = (int)$session['client'];
        $ws_id = (int)$session['workspace'];
        // $client_roles = Client_role::where('client_id',$cid)->where('workspace_id',$ws_id)->get()->toArray();
        // $na_roles = Role::where('scope', 'workspace')->get()->toArray();
        // $roles = array_merge($na_roles,$client_roles);
        $roles = Client_role::where('client_id',$cid)->where('workspace_id',$ws_id)->get()->toArray();
        return view('backend.client-role.manage', compact('roles'));
    }

     /**
     * Create a new role and store it in the database
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), Client_role::validationRules());
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            // Add created_by and updated_by if needed (assuming authenticated user)
            $session = $request->session()->all();
            $data = $validator->validated();
            $data['client_id'] = (int)$session['client'];
            $data['workspace_id'] = (int)$session['workspace'];
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            // Create role
            $role = Client_role::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing role and store it in the database     
     */
    public function update(Request $request, Client_role $role)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), Client_role::validationRules($role->id));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Add updated_by if needed
            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;
            // Update role
            $role->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role from the database
     */
    public function destroy(Client_role $role)
    {
        try {
            $role->delete();
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

}
