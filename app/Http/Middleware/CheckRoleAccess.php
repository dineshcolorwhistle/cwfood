<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    public function handle(Request $request, Closure $next, $type): Response
    {
        if($type == "admin"){
            if(in_array(session('role_id'), [4,7] )){
                return redirect()->route('member.no-aceess');
            }
        }elseif ($type == "manage") {
            if (session('role_id') == 7) {
                return redirect()->route('member.no-aceess');
            }elseif (session('role_id') == 4 && str_contains($request->path(), 'edit')) {
               return redirect()->route('member.no-aceess');
            }
        }elseif ($type == "billing") {
            if(!in_array(session('role_id'), [1,2] )){
                return redirect()->route('member.no-aceess');
            }
        }elseif ($type == "upload") {
            if(!in_array(session('role_id'), [1] )){
                return redirect()->route('member.no-aceess');
            }
        }
        return $next($request);
    }
}
