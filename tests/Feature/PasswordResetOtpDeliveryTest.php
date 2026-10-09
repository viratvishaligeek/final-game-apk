<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordResetOtpDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_otp_is_sent_and_can_reset_the_password(): void
    {
        config([
            'services.sms_gateway.url' => 'https://sms.example/send',
            'services.sms_gateway.token' => 'test-token',
            'services.sms_gateway.sender' => 'GAMEAPP',
        ]);

        $user = User::create([
            'name' => 'OTP User',
            'phone' => '9876543210',
            'password' => 'OldPassword123',
            'balance' => 0,
            'status' => 'active',
        ]);

        $sentMessage = null;
        Http::fake([
            'https://sms.example/send' => function ($request) use (&$sentMessage) {
                $sentMessage = (string) $request['message'];

                return Http::response(['success' => true], 200);
            },
        ]);

        $this->postJson('/api/v1/forgot/send-otp', ['phone' => $user->phone])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertIsString($sentMessage);
        $this->assertMatchesRegularExpression('/\b\d{6}\b/', $sentMessage);
        preg_match('/\b(\d{6})\b/', $sentMessage, $matches);
        $otp = $matches[1];

        $otpRecord = DB::table('password_reset_otps')
            ->where('phone', $user->phone)
            ->latest('id')
            ->first();

        $this->assertNotNull($otpRecord);
        $this->assertTrue(Hash::check($otp, $otpRecord->otp));

        $this->postJson('/api/v1/forgot/reset', [
            'phone' => $user->phone,
            'otp' => $otp,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_otps', [
            'phone' => $user->phone,
        ]);
    }

    public function test_password_reset_otp_fails_closed_without_sms_configuration(): void
    {
        config([
            'services.sms_gateway.url' => null,
            'services.sms_gateway.token' => null,
        ]);

        $this->postJson('/api/v1/forgot/send-otp', ['phone' => '9876543210'])
            ->assertStatus(503)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('password_reset_otps', 0);
    }
}
