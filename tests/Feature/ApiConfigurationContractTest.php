<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\AppSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiConfigurationContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_api_accepts_csv_keys_and_returns_payment_limits(): void
    {
        Setting::updateOrCreate(['option' => 'min_deposit'], ['value' => '10']);
        Setting::updateOrCreate(['option' => 'max_deposit'], ['value' => '500']);
        Setting::updateOrCreate(['option' => 'min_bid_amount_jodi'], ['value' => '5']);
        Setting::updateOrCreate(['option' => 'max_bid_amount_haruf'], ['value' => '50']);
        app(AppSettingsService::class)->forgetCache();

        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/settings?keys=min_deposit,max_deposit&key=max_bid_amount_haruf')
            ->assertOk()
            ->assertJsonPath('data.min_deposit', '10')
            ->assertJsonPath('data.max_deposit', '500')
            ->assertJsonPath('data.max_bid_amount_haruf', '50');
    }

    public function test_payment_methods_returns_admin_barcode_and_gateway_configuration_state(): void
    {
        Setting::updateOrCreate(['option' => 'payment_bar_code'], ['value' => 'payment_barcode_test.png']);
        Setting::updateOrCreate(['option' => 'api_key'], ['value' => 'test-gateway-key']);
        app(AppSettingsService::class)->forgetCache();

        config([
            'services.manual_upi.upi_id' => 'merchant@example',
            'services.manual_upi.name' => 'Merchant',
            'services.upi_gateway.create_order_url' => 'https://gateway.example/create',
            'services.upi_gateway.status_url' => 'https://gateway.example/status',
            'services.upi_gateway.return_url' => 'https://app.example/payment/return',
        ]);

        $this->actingAs($this->createUser(), 'sanctum')
            ->getJson('/api/v1/wallet/payment-methods')
            ->assertOk()
            ->assertJsonPath('data.manual_upi.upi_id', 'merchant@example')
            ->assertJsonPath('data.manual_upi.qr_url', asset('uploads/payment/payment_barcode_test.png'))
            ->assertJsonPath('data.gateway.enabled', true);
    }

    public function test_gateway_order_fails_if_provider_does_not_return_a_payment_url(): void
    {
        Setting::updateOrCreate(['option' => 'api_key'], ['value' => 'test-gateway-key']);
        app(AppSettingsService::class)->forgetCache();

        config([
            'services.upi_gateway.create_order_url' => 'https://gateway.example/create',
            'services.upi_gateway.status_url' => 'https://gateway.example/status',
            'services.upi_gateway.return_url' => 'https://app.example/payment/return',
            'services.upi_gateway.webhook_url' => 'https://app.example/api/v1/wallet/gateway/webhook',
        ]);

        Http::fake([
            'https://gateway.example/create' => Http::response([
                'status' => true,
                'data' => [],
            ], 200),
        ]);

        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wallet/gateway/create-order', ['amount' => '100.00'])
            ->assertStatus(502)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('wallet_requests', [
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'gateway',
            'status' => 'failed',
            'gateway_status' => 'invalid_payment_url',
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'API Contract User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 100,
            'status' => 'active',
        ]);
    }
}
