<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function isUserInactive($user): bool
    {
        if (!$user) return false;
        if (isset($user->status) && $user->status !== 'active') {
            return true;
        }
        return false;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'phone'    => ['required', 'string', 'regex:/^[0-9+\-\s]{7,15}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'password' => $validated['password'],
        ]);
        $token = $user->createToken('session_auth_token', ['*'], now()->addDay())->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'phone' => $user->phone,
                ],
            ]
        ], 201);
    }

    public function tryLogin(Request $request)
    {
        $credentials = $request->validate([
            'phone'    => ['required', 'string', 'regex:/^[0-9+\-\s]{7,15}$/'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $user = User::where('phone', $credentials['phone'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password credentials.',
                'errors'  => [
                    'phone' => ['The credentials provided do not match our records.']
                ]
            ], 422);
        }
        if ($this->isUserInactive($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is Inactive or Blocked. Please contact support.',
                'errors'  => [
                    'phone' => ['Your account is currently Inactive or Blocked.']
                ]
            ], 403);
        }
        $user->tokens()->delete();
        $isRemembered = $request->boolean('remember');
        $tokenName    = $isRemembered ? 'persistent_auth_token' : 'session_auth_token';
        $expiresAt = $isRemembered ? now()->addDays(6) : now()->addDay();
        $token     = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'balance' => $user->balance ?? 0,
                ],
            ]
        ], 200);
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{7,15}$/', 'exists:users,phone'],
        ], [
            'phone.exists' => 'No account found with this phone number.'
        ]);
        $phone = preg_replace('/[\s-]/', '', $request->phone);
        $user = User::where('phone', $phone)->first();
        if ($this->isUserInactive($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This account is Inactive or Blocked. Cannot recover password or send OTP.',
                'errors'  => [
                    'phone' => ['Account is currently Inactive or Blocked.']
                ]
            ], 403);
        }
        $otp = rand(100000, 999999);

        DB::table('password_reset_otps')->where('phone', $phone)->delete();
        DB::table('password_reset_otps')->insert([
            'phone'      => $phone,
            'otp'        => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to your phone number.',
            // 'debug_otp' => $otp // Testing only, remove in production

        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'phone'                 => ['required', 'string', 'regex:/^[0-9+\-\s]{7,15}$/', 'exists:users,phone'],
            'otp'                   => ['required', 'string', 'digits:6'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $phone = preg_replace('/[\s-]/', '', $request->phone);
        $user = User::where('phone', $phone)->first();
        if ($this->isUserInactive($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This account is deactivated. Cannot reset password.',
            ], 403);
        }
        $otpRecord = DB::table('password_reset_otps')
            ->where('phone', $phone)
            ->first();
        if (! $otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP request found. Please request a new OTP.',
                'errors'  => ['otp' => ['Invalid or expired request.']]
            ], 422);
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            DB::table('password_reset_otps')->where('phone', $phone)->delete();
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'errors'  => ['otp' => ['OTP expired.']]
            ], 422);
        }

        if (! Hash::check($request->otp, $otpRecord->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP entered.',
                'errors'  => ['otp' => ['The OTP entered is incorrect.']]
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();
        DB::table('password_reset_otps')->where('phone', $phone)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login with your new password.',
        ]);
    }
}
