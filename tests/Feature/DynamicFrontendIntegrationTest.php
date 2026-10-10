<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Game;
use App\Models\HomePage;
use App\Models\Page;
use App\Models\Result;
use App\Models\Setting;
use App\Models\User;
use App\Models\Winner;
use App\Services\AppSettingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Kolkata']);
        Carbon::setTestNow(Carbon::parse('2026-10-11 10:00:00', 'Asia/Kolkata'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_homepage_renders_saved_sections_and_paginated_current_month_results(): void
    {
        Setting::updateOrCreate(['option' => 'chart_chunk_size'], ['value' => '1']);

        $first = $this->createGame('First Market', 'first-market', 1);
        $second = $this->createGame('Second Market', 'second-market', 2);

        Result::create([
            'game_id' => $first->id,
            'game_date' => '2026-10-05',
            'type' => 'jodi',
            'number' => '07',
        ]);
        Result::create([
            'game_id' => $first->id,
            'game_date' => '2026-09-30',
            'type' => 'jodi',
            'number' => '99',
        ]);

        HomePage::create([
            'title' => 'Admin Managed Section',
            'short_desc' => 'Managed short description',
            'content' => '<p>Managed section body</p>',
            'status' => 'active',
            'location' => 'first_place',
            'whatsapp' => null,
            'phone' => null,
            'telegram' => null,
            'background' => '#ffffff',
        ]);

        $response = $this->get(route('index'));

        $response->assertOk()
            ->assertSee('Admin Managed Section')
            ->assertSee('Managed short description')
            ->assertSee('Managed section body')
            ->assertSee('October 2026')
            ->assertSee('07')
            ->assertSee('1 entries per page')
            ->assertSee('Next')
            ->assertDontSee('99');

        $this->assertDatabaseMissing('results', ['game_id' => $second->id, 'type' => 'jodi']);

        $this->get(route('index', ['page' => 2]))->assertOk()->assertSee('Second Market');
    }

    public function test_global_settings_persist_homepage_copy_and_chart_chunk_size(): void
    {
        $admin = \App\Models\Admin::create([
            'name' => 'Frontend Settings Admin',
            'email' => 'frontend-settings@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.setting.store'), [
                'title' => 'Updated Brand Name',
                'homepage_intro' => 'Updated homepage introduction',
                'site_description' => 'Updated global description',
                'chart_chunk_size' => '15',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', ['option' => 'homepage_intro', 'value' => 'Updated homepage introduction']);
        $this->get(route('index'))->assertOk()->assertSee('Updated Brand Name')->assertSee('Updated homepage introduction');
        $this->assertDatabaseHas('settings', ['option' => 'site_description', 'value' => 'Updated global description']);
        $this->assertDatabaseHas('settings', ['option' => 'chart_chunk_size', 'value' => '15']);
    }

    public function test_admin_interfaces_expose_global_chart_and_page_menu_settings(): void
    {
        $admin = \App\Models\Admin::create([
            'name' => 'Admin UI Test',
            'email' => 'admin-ui-test@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.setting.create'))
            ->assertOk()
            ->assertSee('Monthly Chart Entries Per Page')
            ->assertSee('Brand Tagline')
            ->assertSee('Default Open Graph Image');

        $this->get(route('admin.pages.create'))
            ->assertOk()
            ->assertSee('Show in public menu')
            ->assertSee('SEO description');
    }

    public function test_published_cms_pages_render_with_page_specific_seo_and_inactive_pages_are_hidden(): void
    {
        Page::create([
            'name' => 'About Project',
            'slug' => 'about-project',
            'content' => '<p>Page-managed content</p><script>alert(1)</script>',
            'status' => 'active',
            'is_editable' => 'yes',
            'menu_visible' => true,
            'menu_order' => 1,
            'meta_title' => 'About Project SEO',
            'meta_description' => 'About Project description',
            'meta_keywords' => 'project, records',
            'noindex' => true,
        ]);

        Page::create([
            'name' => 'Hidden Page',
            'slug' => 'hidden-page',
            'content' => 'Not public',
            'status' => 'inactive',
            'is_editable' => 'yes',
            'menu_visible' => true,
            'menu_order' => 2,
            'noindex' => false,
        ]);

        $this->get(route('frontend.page', ['slug' => 'about-project']))
            ->assertOk()
            ->assertSee('About Project SEO')
            ->assertSee('Page-managed content')
            ->assertDontSee('alert(1)')
            ->assertSee('noindex,follow', false);

        $this->get(route('index'))
            ->assertOk()
            ->assertSee('About Project')
            ->assertDontSee('Hidden Page');

        $this->get(route('frontend.page', ['slug' => 'hidden-page']))->assertNotFound();
    }

    public function test_dedicated_game_chart_is_month_scoped_and_legacy_urls_redirect_to_monthly_urls(): void
    {
        $game = $this->createGame('First Market', 'first-market', 1);

        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-09-30',
            'type' => 'jodi',
            'number' => '99',
        ]);
        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-05',
            'type' => 'jodi',
            'number' => '07',
        ]);

        $this->get(route('frontend.chart', ['slug' => $game->slug, 'year' => 2026, 'month' => 10]))
            ->assertOk()
            ->assertSee('October 2026')
            ->assertSee('First Market')
            ->assertSee('07')
            ->assertDontSee('99')
            ->assertSee('<th>Game</th><th>Date</th><th>Result</th><th>Status</th>', false);

        $this->get(route('frontend.chart', ['slug' => $game->slug, 'year' => 2026, 'month' => 9]))
            ->assertOk()
            ->assertSee('September 2026')
            ->assertSee('99')
            ->assertDontSee('07');

        $this->get(route('frontend.chart', ['slug' => $game->slug]))
            ->assertRedirect(route('frontend.chart', ['slug' => $game->slug, 'year' => 2026, 'month' => 10]));
    }

    public function test_public_winners_require_an_explicit_approved_display_name(): void
    {
        $game = $this->createGame('First Market', 'first-market', 1);
        $user = User::create([
            'name' => 'Private Customer Name',
            'phone' => '9890000011',
            'password' => 'Password123',
            'status' => 'active',
        ]);
        $bid = Bid::create([
            'order_no' => 'TEST-WINNER-001',
            'user_id' => $user->id,
            'game_id' => $game->id,
            'game_date' => '2026-10-05',
            'type' => 'jodi',
            'number' => '07',
            'amount' => 10,
            'status' => 'win',
            'winning_amount' => 90,
        ]);
        $winner = Winner::create([
            'bid_id' => $bid->id,
            'user_id' => $user->id,
            'game_id' => $game->id,
            'number' => '07',
            'type' => 'jodi',
            'game_date' => '2026-10-05',
            'amount' => 10,
            'winning_amount' => 90,
            'is_public' => false,
        ]);

        $this->get(route('index'))->assertOk()->assertDontSee('Private Customer Name');

        $admin = \App\Models\Admin::create([
            'name' => 'Winner Admin',
            'email' => 'winner-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.winner.public-display', $winner), [
                'is_public' => '1',
                'public_display_name' => 'Approved Public Winner',
            ])
            ->assertRedirect();

        $this->get(route('index'))
            ->assertOk()
            ->assertSee('Approved Public Winner')
            ->assertSee('First Market');
    }

    public function test_download_button_uses_configured_destination_and_does_not_invent_one(): void
    {
        Setting::updateOrCreate(
            ['option' => 'app_download_url'],
            ['value' => 'https://playonlinekhaiwal.com/app-download.apk']
        );
        app(AppSettingsService::class)->forgetCache();

        $this->get(route('frontend.app-download'))
            ->assertRedirect('https://playonlinekhaiwal.com/app-download.apk');

        Setting::updateOrCreate(['option' => 'app_download_url'], ['value' => '']);
        app(AppSettingsService::class)->forgetCache();

        $this->get(route('frontend.app-download'))
            ->assertRedirect(route('information', ['page' => 'contact']));
    }

    public function test_brand_seeder_is_repeatable_and_does_not_seed_fake_result_values(): void
    {
        $seeders = [
            \Database\Seeders\PlayOnlineKhaiwalSettingsSeeder::class,
            \Database\Seeders\GameSeeder::class,
            \Database\Seeders\PageSeeder::class,
        ];

        $this->seed($seeders);
        $gameCount = Game::query()->count();
        $pageCount = Page::query()->count();
        $settingCount = Setting::query()->count();

        $this->seed($seeders);

        $this->assertSame($gameCount, Game::query()->count());
        $this->assertSame($pageCount, Page::query()->count());
        $this->assertSame($settingCount, Setting::query()->count());
        $this->assertSame(0, Result::query()->count());
        $this->assertDatabaseHas('settings', [
            'option' => 'canonical_url',
            'value' => 'https://playonlinekhaiwal.com',
        ]);
        $this->assertDatabaseMissing('pages', [
            'slug' => 'whatsapp',
            'content' => 'support@example.com',
        ]);
    }

    private function createGame(string $name, string $slug, int $serial): Game
    {
        return Game::create([
            'name' => $name,
            'slug' => $slug,
            'result_time' => '18:00:00',
            'play_start' => '10:00:00',
            'play_end' => '20:00:00',
            'last_result' => null,
            'status' => 'active',
            'serial' => $serial,
            'reward' => 1,
        ]);
    }
}
