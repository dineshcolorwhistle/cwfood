<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\{User,Member};

class RoleBasedAccessMiddleware
{
    public function handle($request, Closure $next, $accessLevel)
    {
        $session = $request->session()->all();
        $clientId = $session['client'];
        $user = User::where('id', $session['user_id'])->first();   
        if (!$user instanceof User) {
            // Handle the case where $user is not an instance of User
            abort(500, 'Invalid user instance');
        }
        $roleId = $session['role_id'];
        switch ($accessLevel) {
            case 'platform':
                if(!in_array($roleId,[1])){
                    abort(403, 'Unauthorized client access');
                }

                // if (!$user->canAccessPlatform()) {
                //     abort(403, 'Unauthorized platform access');
                // }
                break;

            case 'client':
                if(!in_array($roleId,[1,2,3])){
                    abort(403, 'Unauthorized client access');
                }
                // $clientId = $session['client'];
                // if (!$user->canAccessClient($clientId)) {
                //     abort(403, 'Unauthorized client access');
                // }

                break;

            case 'workspace':
                $clientId = $session['client'];
                $workspaceId = $session['workspace'];
                if (!$user->canAccessWorkspace($workspaceId, $clientId)) {
                    abort(403, 'Unauthorized workspace access');
                }
                break;
        }
        return $next($request);
    }
}
