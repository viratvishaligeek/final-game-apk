<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Game;
use App\Models\Notification;
use App\Services\AppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class DashboardController extends Controller
{
    public function __construct(
        protected AppSettingsService $appSettings
    ) {}

    public function getDashboard(): JsonResponse
    {
        $banners = Banner::query()
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get([
                'id',
                'name',
                'image',
            ])
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'name' => $banner->name,
                    'image' => $banner->image,
                    'image_url' => $banner->image_url ?? $banner->image,
                ];
            })
            ->values();

        $settings = $this->appSettings->all();
        $noticeStatus = $settings['notice_status'] ?? 'inactive';
        $noticeContent = $settings['admin_notice'] ?? null;
        $marqueeContent = $settings['marquee'] ?? null;

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched successfully',
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'banner' => $banners,
            'notice' => [
                'status' => $noticeStatus === 'active',
                'content' => $noticeContent,
            ],
            'marquee' => [
                'content' => $marqueeContent,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ], 200);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            // Preserve the legacy string field while providing a structured profile.
            'user' => $user->name,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'balance' => round((float) $user->balance, 2),
                    'address' => $user->address,
                    'city' => $user->city,
                    'gender' => $user->gender,
                    'bank_name' => $user->bank_name,
                    'account_number' => $user->account_number,
                    'ifsc_code' => $user->ifsc_code,
                    'account_holder_name' => $user->account_holder_name,
                    'phonepe' => $user->phonepe,
                    'gpay' => $user->gpay,
                    'paytm' => $user->paytm,
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:200',
            'gender'     => ['nullable', 'string', 'in:male,female,other,Male,Female,Other'],
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:20',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
        ]);
        if (isset($validated['gender']) && $validated['gender'] !== null) {
            $validated['gender'] = ucfirst(strtolower($validated['gender']));
        }

        foreach (
            [
                'bank' => 'bank_name',
                'acc' => 'account_number',
                'ifsc' => 'ifsc_code',
                'holdername' => 'account_holder_name',
            ] as $input => $column
        ) {
            if (array_key_exists($input, $validated)) {
                $validated[$column] = $validated[$input];
                unset($validated[$input]);
            }
        }

        $user->update($validated);
        $user = $user->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'gender' => $user->gender,
                    'bank_name' => $user->bank_name,
                    'account_number' => $user->account_number,
                    'ifsc_code' => $user->ifsc_code,
                    'account_holder_name' => $user->account_holder_name,
                    'phonepe' => $user->phonepe,
                    'gpay' => $user->gpay,
                    'paytm' => $user->paytm,
                ],
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password matches not in our records.'
            ], 422);
        }
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $currentToken = $user->currentAccessToken();
        if ($currentToken && isset($currentToken->id)) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. Other sessions have been signed out.',
        ]);
    }

    public function notificationList(Request $request)
    {
        $user = $request->user();

        $perPage = min(
            max((int) $request->input('per_page', 20), 1),
            100
        );

        $notifications = Notification::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->latest('created_at')
            ->paginate($perPage);

        $data = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'subject' => $notification->subject,
                'message' => $notification->message,
                'created_at' => $notification->created_at?->toISOString(),
                'updated_at' => $notification->updated_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ],
        ]);
    }

    /**
     * Compatibility endpoint for clients that display a notification badge.
     * There is no read-state column, so this is the count of visible notifications.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = Notification::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function getSetting(Request $request): JsonResponse
    {
        $allowedKeys = [
            'min_deposit',
            'max_deposit',
            'min_withdraw',
            'max_withdraw',
            'min_bid_amount_jodi',
            'max_bid_amount_jodi',
            'min_bid_amount_haruf',
            'max_bid_amount_haruf',
            'add_money_notice',
            'withdraw_money_notice',
            'admin_notice',
            'marquee',
            'notice_status',
            'contact_phone',
            'contact_whatsapp',
            'contact_telegram',
            'contact_email',
            'contact_address',
        ];

        $keysInput = $request->input('keys', []);
        if (!is_array($keysInput)) {
            $keysInput = explode(',', (string) $keysInput);
        }

        if ($request->filled('key')) {
            $keysInput[] = $request->input('key');
        }

        $keys = collect($keysInput)
            ->map(fn($key) => trim((string) $key))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($keys === []) {
            $keys = $allowedKeys;
        }

        $keys = array_values(array_intersect($keys, $allowedKeys));
        $allSettings = $this->appSettings->all();
        $data = collect($keys)
            ->mapWithKeys(fn($key) => [$key => $allSettings[$key] ?? null])
            ->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
