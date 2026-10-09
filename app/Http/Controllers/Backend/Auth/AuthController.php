<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard')->with('info', 'You are already logged in.');
        }

        return view('backend.auth.login');
    }

    public function tryLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $remember = $request->boolean('remember');
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();

            if (($admin->status ?? 'active') !== 'active') {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->with('error', 'Your admin account is inactive. Contact another administrator.')
                    ->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', 'Login successful!');
        }
        return back()->with('error', 'Invalid email or password.')->withInput($request->only('email'));
    }

    // public function forgetPassword(Request $request)
    // {
    //     return view('backend.auth.forgot');
    // }

    // later will check
    // public function velidateEmail(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:users',
    //     ]);
    //     if ($validator->passes()) {
    //         $AdminEmail = 'vishalnight11@gmail.com';
    //         $token = Str::random(64);
    //         $result = DB::table('admin_token')->insert([
    //             'email' => $request->email,
    //             'token' => $token,
    //             'created_at' => Carbon::now(),
    //         ]);
    //         $data = ['token' => $token];
    //         try {
    //             Mail::to($AdminEmail)->send(new AdminPasswordMail($data));
    //             $mailStatus = true;
    //         } catch (Exception $e) {
    //             $mailStatus = false;
    //         }
    //         if ($mailStatus) {
    //             return response()->json(['email_success_message' => Config('messages.users.email_success_message'), 'status' => 1]);
    //         } else {
    //             return response()->json(['email_error_message' => Config('messages.users.email_error_message'), 'status' => 0]);
    //         }
    //     } else {
    //         return response()->json(['email_error_message' => $validator->errors()->first(), 'status' => 0]);
    //     }
    // }
    // public function forget_pass_form($token)
    // {
    //     $sekh = [];
    //     $Email = DB::table('admin_token')
    //         ->select('admin_token.token', 'admin_token.email')
    //         ->where('admin_token.token', $token)
    //         ->get();
    //     $data = json_decode(json_encode($Email, true));
    //     foreach ($data as $value) {
    //         $sekh[] = array('token' => $value->token, 'email' => $value->email);
    //     }
    //     if (count($sekh) > 0) {
    //         $Email = $sekh[0]['email'];
    //         $token = $sekh[0]['token'];
    //     } else {
    //         return abort(403, 'Just Go Away.');
    //     }
    //     return view('auth.admin.forgot_form', ['token' => $token, 'Email' => $Email]);
    // }
    // public function forget_pass_post(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:users',
    //         'password' => 'required|confirmed|min:6',
    //         'password_confirmation' => 'min:6',
    //     ]);
    //     if ($validator->passes()) {
    //         $updatePassword = DB::table('admin_token')
    //             ->where([
    //                 'email' => $request['email'],
    //                 'token' => $request['token'],
    //             ])
    //             ->first();
    //         if (!$updatePassword) {
    //             return response()->json(['token_mismatch_error_message' => Config('messages.admin.token_mismatch_error_message'), 'status' => 0]);
    //         }
    //         $admin = users::where(
    //             'email',
    //             $request['email']
    //         )
    //             ->update(['password' => Hash::make($request['password']), 'updated_at' => date('y-m-d h:i:s', time())]);
    //         DB::table('admin_token')->where(['email' => $request->email])->delete();
    //         if ($admin) {
    //             return response()->json(['reset_pwd_sucess_message' => Config('messages.admin.reset_pwd_sucess_message'), 'status' => 1]);
    //         } else {
    //             return response()->json(['reset_pwd_error_message' => Config('messages.admin.reset_pwd_error_message'), 'status' => 0]);
    //         }
    //     } else {
    //         return response()->json(['reset_pwd_error_message' => $validator->errors()->first(), 'status' => 0]);
    //     }
    // }
}
