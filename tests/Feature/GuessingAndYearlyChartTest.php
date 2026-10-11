<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\CommunityReply;
use App\Models\Game;
use App\Models\Result;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuessingAndYearlyChartTest extends TestCase
{
    use RefreshDatabase;

    private function game(string $slug = 'gali'): Game
    {
        return Game::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'result_time' => '18:00:00',
            'play_start' => '09:00:00',
            'play_end' => '20:00:00',
            'status' => 'active',
            'serial' => 1,
            'reward' => 1,
        ]);
    }

    private function member(string $phone = '9876543210'): User
    {
        return User::create([
            'name' => 'Community Member',
            'phone' => $phone,
            'password' => 'Password123',
            'status' => 'active',
        ]);
    }

    public function test_homepage_chart_is_current_month_only_and_has_no_historical_selector(): void
    {
        $game = $this->game();
        Result::create(['game_id' => $game->id, 'game_date' => now()->timezone(config('app.timezone'))->startOfMonth()->toDateString(), 'type' => 'jodi', 'number' => '07']);

        $this->get(route('index', ['year' => 2020, 'month' => 1]))
            ->assertOk()
            ->assertSee(now()->timezone(config('app.timezone'))->format('F Y'))
            ->assertDontSee('homepage-chart-year')
            ->assertDontSee('homepage-chart-month')
            ->assertSee(route('frontend.year-chart', ['slug' => $game->slug, 'year' => now()->timezone(config('app.timezone'))->year]));
    }

    public function test_year_chart_has_twelve_month_columns_and_only_saved_values(): void
    {
        $game = $this->game();
        Result::create(['game_id' => $game->id, 'game_date' => '2024-02-29', 'type' => 'jodi', 'number' => '09']);
        Result::create(['game_id' => $game->id, 'game_date' => '2024-10-05', 'type' => 'jodi', 'number' => '41']);

        $response = $this->get(route('frontend.year-chart', ['slug' => $game->slug, 'year' => 2024]));
        $response->assertOk()
            ->assertSee('Jan')->assertSee('Feb')->assertSee('Mar')->assertSee('Apr')
            ->assertSee('May')->assertSee('Jun')->assertSee('Jul')->assertSee('Aug')
            ->assertSee('Sep')->assertSee('Oct')->assertSee('Nov')->assertSee('Dec')
            ->assertSee('09')->assertSee('41')
            ->assertSee('yearly-result-table', false);
        $this->assertDatabaseCount('results', 2);
    }

    public function test_legacy_chart_url_redirects_only_when_replacement_has_records(): void
    {
        $game = $this->game();
        Result::create(['game_id' => $game->id, 'game_date' => '2025-05-12', 'type' => 'jodi', 'number' => '18']);

        $this->get('/chart/2025/5/gali')
            ->assertRedirect(route('frontend.chart', ['slug' => $game->slug, 'year' => 2025, 'month' => 5]));
        $this->get('/chart/2025/13/gali')->assertNotFound();
        $this->get('/charts/not-a-real-game/2025/5')->assertNotFound();
        $this->get('/charts/gali/2027/1')->assertNotFound();
    }

    public function test_manual_approval_keeps_posts_private_until_admin_approves_them(): void
    {
        $game = $this->game();
        $member = $this->member();
        Setting::updateOrCreate(['option' => 'community_post_approval'], ['value' => 'manual']);
        app(AppSettingsService::class)->forgetCache();

        $this->post(route('community.store'), ['game_id' => $game->id, 'title' => 'A community topic', 'body' => 'This is a useful written discussion.'])
            ->assertRedirect(route('community.login'));

        $this->actingAs($member, 'web')
            ->post(route('community.store'), ['game_id' => $game->id, 'title' => 'A community topic', 'body' => 'This is a useful written discussion.'])
            ->assertRedirect(route('community.index'));

        $post = CommunityPost::query()->firstOrFail();
        $this->assertSame('pending', $post->status);
        $this->get(route('community.show', $post->id))->assertNotFound();

        $admin = Admin::create(['name' => 'Moderator', 'email' => 'moderator@example.com', 'password' => 'Password123', 'status' => 'active']);
        $this->actingAs($admin, 'admin')
            ->patch(route('admin.guessing.moderate', $post), ['status' => 'rejected'])
            ->assertRedirect();
        $this->get(route('community.show', $post->id))->assertNotFound();

        $this->patch(route('admin.guessing.moderate', $post), ['status' => 'approved'])->assertRedirect();
        $this->get(route('community.show', $post->id))->assertOk()->assertSee('A community topic');

        $this->post(route('admin.guessing.policy'), ['community_post_approval' => 'automatic'])->assertRedirect();
        $this->assertDatabaseHas('settings', ['option' => 'community_post_approval', 'value' => 'automatic']);
    }

    public function test_automatic_approval_publishes_posts_and_authenticated_replies_are_saved(): void
    {
        $game = $this->game();
        $member = $this->member();
        Setting::updateOrCreate(['option' => 'community_post_approval'], ['value' => 'automatic']);
        app(AppSettingsService::class)->forgetCache();

        $this->actingAs($member, 'web')
            ->from(route('community.create'))
            ->post(route('community.store'), ['game_id' => 999999, 'title' => 'Published discussion', 'body' => 'This content should be published automatically.'])
            ->assertSessionHasErrors('game_id');

        $this->post(route('community.store'), ['game_id' => $game->id, 'title' => 'Published discussion', 'body' => 'This content should be published automatically.'])
            ->assertRedirect();

        $post = CommunityPost::query()->firstOrFail();
        $this->assertSame('approved', $post->status);
        $this->post(route('community.reply', $post->id), ['body' => 'A valid reply.'])->assertRedirect();
        $this->assertSame(1, CommunityReply::query()->where('community_post_id', $post->id)->count());

        $this->actingAs($this->member('9876543211'), 'web')
            ->get(route('community.show', $post->id))
            ->assertOk()
            ->assertSee('Published discussion')
            ->assertSee('A valid reply.');
    }
}
