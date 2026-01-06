<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EmailMaster;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showForgetPasswordForm()
    {
        return view('auth.forgetPassword');
    }

    public function submitForgetPasswordForm(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            $token = Str::random(64);
            
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email, 
                'token' => $token, 
                'created_at' => Carbon::now()
            ]);

            $user = User::where('email',$request->email)->pluck('name');
            
            Mail::send('email.forgetPassword', ['token' => $token,'name'=>$user[0]], function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Your Nutriflow Password');
            });

            $result['status']  = true;
            $result['message']  = 'We have e-mailed your password reset link!';
            return response()->json($result);

        } catch (\Exception $e) {
            $result['status']  = false;
            $result['message']  = $e->getMessage();
            return response()->json($result);
        }
    }

    public function showResetPasswordForm($token)
    {

        return view('auth.forgetPasswordLink', ['token' => $token]);
    }

    public function submitResetPasswordForm(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required'
            ]);
            $updatePassword = DB::table('password_reset_tokens')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token
                ])
                ->first();
            if (!$updatePassword) {
                $result['status']  = false;
                $result['message']  = 'Invalid token!';
                return response()->json($result);
                // return back()->withInput()->with('error', 'Invalid token!');
            }
            $user = User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);
            DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
            $result['status']  = true;
            $result['message']  = 'Your password has been changed!';
            return response()->json($result);
            // return redirect('/login')->with('message', 'Your password has been changed!');
        } catch (\Exception $e) {
            $result['status']  = false;
            $result['message']  = $e->getMessage();
            return response()->json($result);
        }
        
    }
}
