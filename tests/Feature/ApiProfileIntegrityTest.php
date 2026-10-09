<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProfileIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_maps_legacy_bank_fields_to_database_columns(): void
    {
        $user = User::create([
            'name' => 'Profile User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/update-profile', [
                'name' => 'Profile User Updated',
                'gender' => 'female',
                'bank' => 'Example Bank',
                'acc' => '1234567890',
                'ifsc' => 'ABCD0123456',
                'holdername' => 'Profile User',
            ])
            ->assertOk()
            ->assertJsonPath('data.user.bank_name', 'Example Bank')
            ->assertJsonPath('data.user.account_number', '1234567890')
            ->assertJsonPath('data.user.ifsc_code', 'ABCD0123456')
            ->assertJsonPath('data.user.gender', 'Female');

        $this->assertSame('Example Bank', $user->fresh()->bank_name);
        $this->assertSame('1234567890', $user->fresh()->account_number);
    }
}
