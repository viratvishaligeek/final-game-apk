<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            'password' => ['required', 'string'],
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

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
        ]);

        $url = config('services.sms_gateway.url');
        $token = config('services.sms_gateway.token');
        $sender = config('services.sms_gateway.sender');

        if (!is_string($url) || trim($url) === '' || !is_string($token) || trim($token) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Password reset SMS delivery is not configured. Please contact support.',
            ], 503);
        }

        $phone = $validated['phone'];
        $rateLimitKey = 'password-reset-send:' . hash('sha256', $phone);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 600);

        $user = $this->findUserByPhone($phone);

        // Keep the response the same for unknown/inactive accounts to avoid
        // revealing whether a phone number is registered.
        if (!$user || $this->isUserInactive($user)) {
            return response()->json([
                'success' => true,
                'message' => 'If the phone number is registered, a password reset code will be sent shortly.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_otps')
            ->where('phone', $phone)
            ->delete();

        DB::table('password_reset_otps')->insert([
            'phone' => $phone,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withToken($token)
                ->post($url, [
                    'to' => $phone,
                    'message' => "Your password reset code is {$otp}. It expires in 10 minutes.",
                    'sender' => is_string($sender) ? $sender : null,
                ]);

            $body = $response->json();
            $providerRejected = is_array($body)
                && (($body['success'] ?? true) === false || ($body['status'] ?? true) === false);

            if (!$response->successful() || $providerRejected) {
                DB::table('password_reset_otps')
                    ->where('phone', $phone)
                    ->delete();

                Log::warning('Password reset SMS provider rejected delivery.', [
                    'phone_hash' => hash('sha256', $phone),
                    'http_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send a password reset code right now. Please try again later.',
                ], 503);
            }
        } catch (\Throwable $exception) {
            DB::table('password_reset_otps')
                ->where('phone', $phone)
                ->delete();

            Log::warning('Password reset SMS delivery failed.', [
                'phone_hash' => hash('sha256', $phone),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send a password reset code right now. Please try again later.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'If the phone number is registered, a password reset code will be sent shortly.',
        ]);
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
