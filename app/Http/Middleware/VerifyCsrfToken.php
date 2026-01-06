<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            Session::regenerateToken();
            // Perform logout
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }


            // If it's an AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'CSRF token expired',
                    'csrf_token' => csrf_token()
                ], 419);
            }

            // For regular form submissions
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please try again.');
        }
    }
}
