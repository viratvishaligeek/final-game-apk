<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_browser_can_register_a_push_token_idempotently(): void
    {
        $token = 'test-fcm-token-for-browser-subscription';

        $payload = ['token' => $token, 'platform' => 'web'];

        $this->postJson('/api/v1/push/subscribe', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson('/api/v1/push/subscribe', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, PushSubscription::query()->where('token_hash', hash('sha256', $token))->count());
        $this->assertDatabaseHas('push_subscriptions', [
            'token_hash' => hash('sha256', $token),
            'platform' => 'web',
            'user_id' => null,
        ]);
    }
}
