<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiInactiveAccountMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_account_gets_a_json_forbidden_response_without_token_delete_error(): void
    {
        $user = User::create([
            'name' => 'Inactive API User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 0,
            'status' => 'inactive',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.account.0', 'Your account is currently Inactive Or Blocked.');
    }
}
