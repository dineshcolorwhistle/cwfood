<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
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
     * Show the users list view, based on the route name.
     * Either shows all users except the admin role for Members,
     * or shows only the admin role for Team Members     * 
     */
    public function index(Request $request)
    {
        $routeName = $request->route()->getName();

        // Determine which users and roles to show based on the route
        if ($routeName === 'members.index') {
            $users = User::with(['role', 'client'])
                ->whereHas('role', function ($query) { $query->where('id', '!=', 1); })->get();
            $roles = Role::select('id', 'name', 'scope')->where('id', '!=', 1)->get();
            $pageTitle = 'Members';
        } elseif ($routeName === 'team-members.index') {
            $users = User::with(['role', 'client'])
                ->where('id', '>', 1)
                ->whereHas('role', function ($query) {
                    $query->where('id', 1);
                })
                ->get();
            $roles = Role::select('id', 'name', 'scope')
                ->where('id', 1)
                ->get();
            $pageTitle = 'Team Members';
        }

        // Fetch active clients
        $clients = Client::select('id', 'name')
            ->where('status', 1)
            ->get();

        return view('backend.user.users', compact('users', 'roles', 'clients', 'pageTitle'));
    }

    /**
     * Store a newly created user in the database.
     */
    public function store(Request $request)
    {
        try {
            // Get validation rules from the User model
            $validationRules = User::validationRules();

            // If role is platform, make client_id nullable
            $roleScope = Role::find($request->input('role_id'))->scope;
            if ($roleScope === 'platform') {
                $validationRules['client_id'] = 'nullable';
            }

            // Validate the request
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Handle picture upload
            $picturePath = null;
            if ($request->hasFile('picture')) {
                $picturePath = $request->file('picture')->store('users', 'public');
            }

            // Get validated data
            $data = $validator->validated();

            
            // Always store the password as a hash in the database
            $data['password'] = $data['password'];

            // Set additional metadata
            $data['picture'] = $picturePath;
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;

            // Remove client_id for platform roles
            if ($roleScope === 'platform') {
                $data['client_id'] = null;
            }else{
                $data['client_id'] = $this->clientID;
            }
            // Create user
            $user = User::create($data);

            // Mail::send('email.signup', ['name'=>$data['name']], function($message) use($data){
            //     $message->to($data['email']);
            //     $message->subject('Welcome to Nutriflow - Get Started with Your Account');
            // });

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user in the database.     * 
     */
    public function update(Request $request, User $user)
    {
        try {
            // Get validation rules from the User model
            $validationRules = User::validationRules($user->id);

            // If role is platform, make client_id nullable
            $roleScope = Role::find($request->input('role_id'))->scope;
            if ($roleScope === 'platform') {
                $validationRules['client_id'] = 'nullable';
            }

            // Validate the request
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            // Handle picture upload
            if ($request->hasFile('picture')) {
                // Delete old picture if exists
                if ($user->picture) {
                    Storage::disk('public')->delete($user->picture);
                }
                $data['picture'] = $request->file('picture')->store('users', 'public');
            }

            // Only update password if provided
            if (!$request->filled('password')) {
                unset($data['password']);
            }

            // Set update metadata
            $data['updated_by'] = $this->user_id;

            // Remove client_id for platform roles
            if ($roleScope === 'platform') {
                $data['client_id'] = null;
            }

            // Update user
            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            // Delete user picture if exists
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }

            // Delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    public function profile()
    {
        return view('backend.user-profile.edit');
    }

    public function profileUpdate(Request $request)
    {
        $auth0user = $request->user();
        $user = User::where('email', $auth0user->email)->first();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'picture' => 'nullable|image|mimes:jpg,jpeg,png,bmp,tiff|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle profile picture
            if ($request->hasFile('picture')) {
                // Remove existing picture if exists
                if ($user->picture && file_exists(public_path('assets/img/profile/' . $user->picture))) {
                    unlink(public_path('assets/img/profile/' . $user->picture));
                }

                $file = $request->file('picture');
                $filename = $user->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/profile'), $filename);
                $user->picture = $filename;
            }

            // Update name and email
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->save();

            $picture_url = $user->picture
                ? asset('assets/img/profile/' . $user->picture)
                : asset('assets/img/default-avatar.png');

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user,
                'picture_url' => $picture_url,
            ]);
        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function passwordUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                'different:old_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ], [
            'password.confirmed' => 'The new password confirmation does not match.',
            'password.different' => 'The new password must be different from the current password.',
            'password.min' => 'The password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            // Custom error message formatting
            $errors = $validator->errors()->all();
            $formattedErrors = implode(' ', $errors);

            return response()->json([
                'success' => false,
                'errors' => $formattedErrors
            ], 422);
        }
        $auth0user = Auth::user();
        $user = User::where('email', $auth0user->email)->first();

        // $user = Auth::user();

        // Custom error handling for old password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => 'Current password is incorrect'
            ], 422);
        }

        try {
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Password Change Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the password. Please try again.'
            ], 500);
        }
    }

    public function removePicture(Request $request)
    {
        $auth0user = $request->user();
        $user = User::where('email', $auth0user->email)->first();
        // $user = $request->user();

        try {
            if ($user->picture && file_exists(public_path('assets/img/profile/' . $user->picture))) {
                unlink(public_path('assets/img/profile/' . $user->picture));
            }

            User::where('id', $user->id)->update(['picture' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Remove Picture Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove profile picture: ' . $e->getMessage()
            ], 500);
        }
    }
}
