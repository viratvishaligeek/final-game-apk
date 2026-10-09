<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiNotificationCountCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_notification_count_route_returns_personal_and_global_count(): void
    {
        $user = User::create([
            'name' => 'Notification Count User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 0,
            'status' => 'active',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'subject' => 'Personal',
            'message' => 'Personal notification',
        ]);

        Notification::create([
            'user_id' => null,
            'subject' => 'Global',
            'message' => 'Global notification',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications-count')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2);
    }
}
