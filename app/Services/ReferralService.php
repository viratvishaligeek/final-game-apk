<?php

namespace App\Services;

use App\Models\ReferralReward;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public function __construct(
        protected AppSettingsService $settings,
        protected WalletService $walletService
    ) {}

    public function applyCode(User $user, string $code): User
    {
        return DB::transaction(function () use ($user, $code) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->first_deposit_at || WalletRequest::query()
                ->where('user_id', $lockedUser->id)
                ->where('request_type', 'credit')
                ->where('status', 'approved')
                ->exists()) {
                throw ValidationException::withMessages([
                    'referral_code' => ['A referral code cannot be applied or changed after your first successful deposit.'],
                ]);
            }

            $referrer = User::query()
                ->where('referral_code', strtoupper(trim($code)))
                ->first();

            if (!$referrer) {
                throw ValidationException::withMessages([
                    'referral_code' => ['This referral code is invalid.'],
                ]);
            }

            if ($referrer->id === $lockedUser->id) {
                throw ValidationException::withMessages([
                    'referral_code' => ['You cannot use your own referral code.'],
                ]);
            }

            $lockedUser->referrer_user_id = $referrer->id;
            $lockedUser->save();

            return $lockedUser->fresh();
        });
    }

    public function recordSuccessfulDeposit(User $user, WalletRequest $walletRequest): void
    {
        DB::transaction(function () use ($user, $walletRequest) {
            $referred = User::query()->lockForUpdate()->findOrFail($user->id);

            // A unique reward row and this timestamp make retries/callbacks idempotent.
            if ($referred->first_deposit_at !== null) {
                return;
            }

            $referred->first_deposit_at = now();
            $referred->save();

            if (!$referred->referrer_user_id) {
                return;
            }

            if (ReferralReward::query()->where('referred_user_id', $referred->id)->exists()) {
                return;
            }

            $referrer = User::query()->lockForUpdate()->findOrFail($referred->referrer_user_id);
            $percentage = max(0, min(100, $this->settings->get('referral_percentage', 2)));
            $minimum = max(0, $this->settings->get('referral_min_amount', 10));
            $depositAmount = round((float) $walletRequest->amount, 2);
            $rewardAmount = max(round($depositAmount * $percentage / 100, 2), $minimum);

            ReferralReward::query()->create([
                'referrer_user_id' => $referrer->id,
                'referred_user_id' => $referred->id,
                'first_wallet_request_id' => $walletRequest->id,
                'first_deposit_amount' => $depositAmount,
                'reward_amount' => $rewardAmount,
                'percentage' => $percentage,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($rewardAmount > 0) {
                $this->walletService->credit(
                    $referrer,
                    $rewardAmount,
                    'Referral reward for user #' . $referred->id . ' first deposit'
                );
            }
        });
    }
}
