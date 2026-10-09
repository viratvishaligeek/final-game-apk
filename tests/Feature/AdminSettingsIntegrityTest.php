<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_settings_does_not_store_password_fields_as_plaintext_options(): void
    {
        $admin = Admin::create([
            'name' => 'Settings Admin',
            'email' => 'settings-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.setting.store'), [
                'title' => 'Example Site',
                'email' => 'support@example.com',
                'current_password' => 'Password123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'option' => 'title',
            'value' => 'Example Site',
        ]);
        $this->assertDatabaseMissing('settings', [
            'option' => 'password',
        ]);
        $this->assertDatabaseMissing('settings', [
            'option' => 'current_password',
        ]);
    }

    public function test_mobile_app_limits_save_the_same_bid_limit_keys_used_by_betting(): void
    {
        $admin = Admin::create([
            'name' => 'Limits Admin',
            'email' => 'limits-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.mobile-app.update_limits'), [
                'min_deposit' => '10.00',
                'max_deposit' => '500.00',
                'min_withdraw' => '20.00',
                'max_withdraw' => '300.00',
                'min_bid_amount_jodi' => '5.00',
                'max_bid_amount_jodi' => '100.00',
                'min_bid_amount_haruf' => '2.00',
                'max_bid_amount_haruf' => '50.00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'option' => 'min_bid_amount_jodi',
            'value' => '5.00',
        ]);
        $this->assertDatabaseHas('settings', [
            'option' => 'max_bid_amount_haruf',
            'value' => '50.00',
        ]);
    }

    public function test_gateway_deposit_respects_the_configured_maximum(): void
    {
        $user = User::create([
            'name' => 'Deposit User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 0,
            'status' => 'active',
        ]);

        Setting::updateOrCreate(['option' => 'min_deposit'], ['value' => '10.00']);
        Setting::updateOrCreate(['option' => 'max_deposit'], ['value' => '100.00']);
        app(AppSettingsService::class)->forgetCache();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wallet/gateway/create-order', [
                'amount' => '101.00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount');
    }
}
