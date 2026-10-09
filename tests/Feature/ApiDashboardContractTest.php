<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDashboardContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_list_includes_global_result_notifications_and_user_notifications(): void
    {
        $user = $this->createUser();

        Notification::create([
            'user_id' => $user->id,
            'subject' => 'Personal notice',
            'message' => 'A message for this user.',
        ]);

        Notification::create([
            'user_id' => null,
            'subject' => 'Game Result Out',
            'message' => 'A public result was published.',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['subject' => 'Personal notice'])
            ->assertJsonFragment(['subject' => 'Game Result Out']);
    }

    public function test_user_endpoint_keeps_legacy_name_and_returns_profile_data(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('user', 'API Dashboard User')
            ->assertJsonPath('data.user.phone', $user->phone)
            ->assertJsonPath('data.user.balance', 125.5);
    }

    public function test_game_list_reads_admin_notices_from_shared_settings_service(): void
    {
        Setting::updateOrCreate(['option' => 'notice_status'], ['value' => 'active']);
        Setting::updateOrCreate(['option' => 'admin_notice'], ['value' => 'Market update']);
        Setting::updateOrCreate(['option' => 'marquee'], ['value' => 'Please check results']);
        app(AppSettingsService::class)->forgetCache();

        $this->actingAs($this->createUser(), 'sanctum')
            ->getJson('/api/v1/games/list')
            ->assertOk()
            ->assertJsonPath('notice.status', true)
            ->assertJsonPath('notice.content', 'Market update')
            ->assertJsonPath('marquee.content', 'Please check results');
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'API Dashboard User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 125.5,
            'status' => 'active',
        ]);
    }
}
