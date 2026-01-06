<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\{CognitoUserToken};

class AutoLogout
{
    public function handle($request, Closure $next)
    {
        $timeout = config('session.lifetime') * 60; // minutes

        if (Auth::check()) {
            $lastActivity = session('lastActivityTime');
            $now = Carbon::now();
            if ($lastActivity && $now->diffInMinutes($lastActivity) > $timeout) {
                CognitoUserToken::where('user_id',Auth::user()->id)->delete(); // Remove Congnito user
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'Session expired due to inactivity.'], 401);
                }
                Log::info('Application triggered logout from middleware');
                return redirect()->route('login');
            }
            session(['lastActivityTime' => $now]);
        }
        return $next($request);
    }
}
