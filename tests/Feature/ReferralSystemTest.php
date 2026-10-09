<?php

namespace Tests\Feature;

use App\Models\ReferralReward;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\AppSettingsService;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $phone): User
    {
        return User::create([
            'name' => 'Referral Test',
            'phone' => $phone,
            'password' => Hash::make('Password123'),
            'balance' => 0,
            'status' => 'active',
        ]);
    }

    public function test_first_successful_deposit_pays_minimum_reward_only_once(): void
    {
        $referrer = $this->makeUser('9876500001');
        $referred = $this->makeUser('9876500002');
        $referred->update(['referrer_user_id' => $referrer->id]);

        Setting::updateOrCreate(['option' => 'referral_percentage'], ['value' => '2']);
        Setting::updateOrCreate(['option' => 'referral_min_amount'], ['value' => '10']);
        app(AppSettingsService::class)->forgetCache();

        $deposit = WalletRequest::create([
            'user_id' => $referred->id,
            'request_type' => 'credit',
            'payment_method' => 'manual_upi',
            'amount' => 100,
            'status' => 'approved',
            'utr' => 'REF-TEST-1001',
            'processed_at' => now(),
        ]);

        $service = app(ReferralService::class);
        $service->recordSuccessfulDeposit($referred, $deposit);
        $service->recordSuccessfulDeposit($referred, $deposit);

        $this->assertDatabaseCount('referral_rewards', 1);
        $this->assertDatabaseHas('referral_rewards', [
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'first_wallet_request_id' => $deposit->id,
            'first_deposit_amount' => 100,
            'reward_amount' => 10,
        ]);
        $this->assertEquals(10.0, (float) $referrer->fresh()->balance);
        $this->assertNotNull($referred->fresh()->first_deposit_at);
    }

    public function test_referral_code_cannot_be_applied_after_an_approved_deposit(): void
    {
        $referrer = $this->makeUser('9876500003');
        $referred = $this->makeUser('9876500004');

        WalletRequest::create([
            'user_id' => $referred->id,
            'request_type' => 'credit',
            'payment_method' => 'manual_upi',
            'amount' => 50,
            'status' => 'approved',
            'utr' => 'REF-TEST-1002',
            'processed_at' => now(),
        ]);

        try {
            app(ReferralService::class)->applyCode($referred, $referrer->referral_code);
            $this->fail('A referral code must be locked after a successful deposit.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('referral_code', $exception->errors());
        }
    }

    public function test_users_cannot_apply_their_own_referral_code(): void
    {
        $user = $this->makeUser('9876500005');

        try {
            app(ReferralService::class)->applyCode($user, $user->referral_code);
            $this->fail('Self-referral must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('referral_code', $exception->errors());
        }
    }
}
