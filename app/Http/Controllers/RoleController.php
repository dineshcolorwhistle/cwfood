<?php

namespace App\Http\Controllers;

use App\Models\{Role,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
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
     * Show the roles list view, based on the route name.
     * Either shows all roles except 'platform' scope for User Roles,
     * or shows only 'platform' scope roles for Team Members Roles
     */
    public function index(Request $request)
    {
        $routeName = $request->route()->getName();
        $scopes = Role::SCOPES;

        // Determine which roles to show based on the route
        if ($routeName === 'roles.index') {
            // Show all roles except 'platform' scope for User Roles
            $roles = Role::where('scope', '!=', 'platform')->get();
            $pageTitle = 'User Roles';
        } elseif ($routeName === 'team-member-roles.index') {
            // Show only 'platform' scope roles for Team Members Roles
            $roles = Role::where('scope', 'platform')->get();
            $pageTitle = 'Team Members Roles';
        } else {
            $roles = collect();
            $pageTitle = 'Roles';
        }

        return view('backend.role.roles', compact('roles', 'scopes', 'pageTitle'));
    }

    /**
     * Create a new role and store it in the database
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), Role::validationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Add created_by and updated_by if needed (assuming authenticated user)
            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;

            // Create role
            $role = Role::create($data);

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
    public function update(Request $request, Role $role)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), Role::validationRules($role->id));

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
    public function destroy(Role $role)
    {
        try {

           if(User::where('role_id',$role->id)->count() == 0){
                $role->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully'
                ]);
           }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete this Role. Its already assigned.'
                ]);
           }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
