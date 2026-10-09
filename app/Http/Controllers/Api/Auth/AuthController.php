<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s-]/', '', trim($phone)) ?? '';
    }

    private function findUserByPhone(string $phone): ?User
    {
        return User::query()
            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$phone])
            ->first();
    }

    private function isUserInactive(?User $user): bool
    {
        return $user !== null && $user->status !== 'active';
    }

    public function register(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:200'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $duplicate = User::query()
            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$validated['phone']])
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken(
            'session_auth_token',
            ['*'],
            now()->addDay()
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ],
            ],
        ], 201);
    }

    public function tryLogin(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $credentials = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = $this->findUserByPhone($credentials['phone']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password credentials.',
                'errors' => [
                    'phone' => ['The credentials provided do not match our records.'],
                ],
            ], 422);
        }

        if ($this->isUserInactive($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive or blocked. Please contact support.',
                'errors' => [
                    'phone' => ['Your account is currently inactive or blocked.'],
                ],
            ], 403);
        }

        $user->tokens()->delete();
        $isRemembered = $request->boolean('remember');
        $tokenName = $isRemembered ? 'persistent_auth_token' : 'session_auth_token';
        $expiresAt = $isRemembered ? now()->addDays(6) : now()->addDay();
        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'balance' => round((float) $user->balance, 2),
                ],
            ],
        ]);
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
        ]);

        // No SMS provider is configured in config/services.php. Do not store an
        // OTP and claim it was sent when there is no delivery mechanism.
        return response()->json([
            'success' => false,
            'message' => 'Password reset OTP delivery is not configured. Please contact support.',
        ], 503);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'otp' => ['required', 'string', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $phone = $validated['phone'];
        $attemptKey = 'password-reset-verify:' . hash('sha256', $phone);

        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP attempts. Please try again later.',
            ], 429);
        }

        $result = DB::transaction(function () use ($phone, $validated, $attemptKey) {
            $user = $this->findUserByPhone($phone);

            if (!$user || $this->isUserInactive($user)) {
                return 'invalid';
            }

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $otpRecord = DB::table('password_reset_otps')
                ->where('phone', $phone)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$otpRecord) {
                return 'invalid';
            }

            if (now()->greaterThan($otpRecord->expires_at)) {
                DB::table('password_reset_otps')
                    ->where('phone', $phone)
                    ->delete();

                return 'expired';
            }

            if (!Hash::check($validated['otp'], $otpRecord->otp)) {
                RateLimiter::hit($attemptKey, 600);

                return 'invalid';
            }

            $user->password = Hash::make($validated['password']);
            $user->save();
            $user->tokens()->delete();

            DB::table('password_reset_otps')
                ->where('phone', $phone)
                ->delete();

            return 'success';
        });

        if ($result === 'success') {
            RateLimiter::clear($attemptKey);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result === 'expired'
                ? 'OTP has expired. Please request a new one.'
                : 'Invalid or expired password reset request.',
            'errors' => [
                'otp' => ['Invalid or expired password reset request.'],
            ],
        ], 422);
    }
}
