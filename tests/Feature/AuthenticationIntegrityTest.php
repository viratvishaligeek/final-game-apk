<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_accepts_a_phone_number_with_formatting(): void
    {
        User::create([
            'name' => 'Auth Test User',
            'phone' => '9876543210',
            'password' => Hash::make('Password123'),
            'balance' => 0,
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/login', [
            'phone' => '98765-43210',
            'password' => 'Password123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '9876543210');
    }

    public function test_password_reset_does_not_claim_otp_was_sent_without_an_sms_provider(): void
    {
        $user = User::create([
            'name' => 'Auth Test User',
            'phone' => '9876543210',
            'password' => Hash::make('Password123'),
            'balance' => 0,
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/forgot/send-otp', [
            'phone' => $user->phone,
        ])
            ->assertStatus(503)
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('password_reset_otps', [
            'phone' => $user->phone,
        ]);
    }
}
