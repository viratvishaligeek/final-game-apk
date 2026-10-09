<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\AppSettingsService;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService,
        protected AppSettingsService $settings
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $referrals = User::query()
            ->where('referrer_user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'phone', 'referrer_user_id', 'first_deposit_at', 'created_at'])
            ->map(function (User $referred) {
                $reward = ReferralReward::query()
                    ->where('referred_user_id', $referred->id)
                    ->first();

                return [
                    'id' => $referred->id,
                    'name' => $referred->name,
                    'phone' => is_string($referred->phone) && strlen($referred->phone) > 4
                        ? str_repeat('*', max(0, strlen($referred->phone) - 4)) . substr($referred->phone, -4)
                        : $referred->phone,
                    'joined_at' => $referred->created_at,
                    'first_deposit_at' => $referred->first_deposit_at,
                    'first_deposit_amount' => $reward?->first_deposit_amount,
                    'reward_amount' => $reward?->reward_amount,
                    'reward_status' => $reward?->status ?? 'pending',
                ];
            });

        $rewardTotal = ReferralReward::query()
            ->where('referrer_user_id', $user->id)
            ->where('status', 'paid')
            ->sum('reward_amount');

        $canApplyCode = !$user->first_deposit_at && !WalletRequest::query()
            ->where('user_id', $user->id)
            ->where('request_type', 'credit')
            ->where('status', 'approved')
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'share_url' => rtrim(config('app.url'), '/') . '/sign-up?ref=' . rawurlencode((string) $user->referral_code),
                'referral_percentage' => $this->settings->get('referral_percentage', 2),
                'referral_min_amount' => $this->settings->get('referral_min_amount', 10),
                'total_referrals' => $referrals->count(),
                'total_reward' => round((float) $rewardTotal, 2),
                'referrals' => $referrals,
                'applied_referral_code' => $user->referrer?->referral_code,
                'can_apply_code' => $canApplyCode,
            ],
        ]);
    }

    public function applyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'referral_code' => ['required', 'string', 'min:4', 'max:32'],
        ]);

        $user = $this->referralService->applyCode($request->user(), $validated['referral_code']);

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied successfully.',
            'data' => [
                'applied_referral_code' => $user->referrer?->referral_code,
                'can_apply_code' => !$user->first_deposit_at,
            ],
        ]);
    }
}
