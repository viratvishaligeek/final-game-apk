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

    public function test_homepage_renders_current_month_results_as_date_by_game_tables(): void
    {
        Setting::updateOrCreate(['option' => 'chart_chunk_size'], ['value' => '1']);
        app(AppSettingsService::class)->forgetCache();

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
            ->assertSee('Download App')
            ->assertSee('<th scope="col">Date</th>', false)
            ->assertSee('First Market')
            ->assertSee('Second Market')
            ->assertSee('07')
            ->assertDontSee('entries per page')
            ->assertDontSee('Current-month chart pagination')
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
            ->assertSee('Games Per Homepage Results Table')
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

    public function test_slug_only_result_page_and_monthly_ajax_endpoint_return_only_selected_game_data(): void
    {
        $game = $this->createGame('First Market', 'first-market', 1);
        $other = $this->createGame('Second Market', 'second-market', 2);
        Result::create(['game_id' => $game->id, 'game_date' => '2026-10-05', 'type' => 'jodi', 'number' => '07']);
        Result::create(['game_id' => $game->id, 'game_date' => '2026-09-30', 'type' => 'jodi', 'number' => '99']);
        Result::create(['game_id' => $other->id, 'game_date' => '2026-10-05', 'type' => 'jodi', 'number' => '55']);

        $this->get('/result/first-market')->assertOk()->assertSee('October 2026')->assertSee('First Market')->assertSee('07')->assertDontSee('55');

        $this->postJson('/get_result', ['slug' => 'first-market', 'year' => '2026', 'month' => '10'])
            ->assertOk()->assertJsonPath('game.slug', 'first-market')
            ->assertJsonPath('month_label', 'October 2026')
            ->assertJsonPath('days.4.result', '07')
            ->assertJsonMissing(['result' => '55']);

        $this->postJson('/get_result', ['slug' => 'first-market', 'year' => '2026', 'month' => '9'])
            ->assertOk()->assertJsonPath('days.29.result', '99');
        $this->postJson('/get_result', ['slug' => 'not-a-game', 'year' => '2026', 'month' => '10'])->assertNotFound();
        $this->postJson('/get_result', ['slug' => 'first-market', 'year' => '2026', 'month' => '13'])->assertUnprocessable();
        $this->postJson('/get_result', ['slug' => 'first-market', 'year' => '2027', 'month' => '1'])->assertNotFound();
        $this->get('/about')->assertOk();
        $this->get('/not-a-real-page')->assertNotFound();
    }

    public function test_homepage_chunk_setting_distributes_games_evenly_without_pagination(): void
    {
        Setting::updateOrCreate(['option' => 'chart_chunk_size'], ['value' => '10']);
        app(AppSettingsService::class)->forgetCache();
        for ($i = 1; $i <= 22; $i++) {
            $this->createGame('Market ' . $i, 'market-' . $i, $i);
        }

        $response = $this->get(route('index'))->assertOk();
        preg_match_all('/<table class="quick-record-table homepage-results-table">(.*?)<\/table>/s', $response->getContent(), $matches);
        $this->assertCount(3, $matches[1]);
        $sizes = [];
        foreach ($matches[1] as $table) {
            preg_match_all('/<th scope="col"><a href="/', $table, $headers);
            $sizes[] = count($headers[0]);
        }
        $this->assertSame([8, 7, 7], $sizes);
        $this->assertStringNotContainsString('pagination', strtolower($response->getContent()));
    }

    public function test_homepage_matrix_uses_the_actual_number_of_days_in_leap_and_short_months(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-02-15 10:00:00', 'Asia/Kolkata'));
        $game = $this->createGame('February Market', 'february-market', 1);
        Result::create(['game_id' => $game->id, 'game_date' => '2024-02-29', 'type' => 'jodi', 'number' => '04']);

        $response = $this->get(route('index'))->assertOk()->assertSee('February 2024');
        preg_match('/<table class="quick-record-table homepage-results-table">(.*?)<\/table>/s', $response->getContent(), $table);
        preg_match('/<tbody>(.*?)<\/tbody>/s', $table[1] ?? '', $tbody);
        preg_match_all('/<tr>/', $tbody[1] ?? '', $rows);
        $this->assertCount(29, $rows[0]);
        $this->assertStringContainsString('>29</th>', $tbody[1]);
        $this->assertStringNotContainsString('>30</th>', $tbody[1]);
        $this->assertStringContainsString('04', $tbody[1]);

        $charts = app(\App\Services\MonthlyChartService::class);
        $this->assertCount(28, $charts->monthDates(2025, 2)[2]);
        $this->assertCount(30, $charts->monthDates(2026, 4)[2]);
    }

    public function test_legacy_market_and_month_urls_redirect_to_the_slug_only_result_page(): void
    {
        $game = $this->createGame('First Market', 'first-market', 1);
        Result::create(['game_id' => $game->id, 'game_date' => '2025-05-12', 'type' => 'jodi', 'number' => '18']);

        $this->get('/markets/first-market')->assertRedirect(route('frontend.market', ['slug' => $game->slug]));
        $this->get('/charts/first-market/2025/5')
            ->assertRedirect(route('frontend.market', ['slug' => $game->slug, 'year' => 2025, 'month' => 5]));
        $this->get('/charts/first-market/2025/13')->assertNotFound();
        $this->get('/charts/not-a-real-game/2025/5')->assertNotFound();
        $this->get('/chart/2025/5/first-market')
            ->assertRedirect(route('frontend.market', ['slug' => $game->slug, 'year' => 2025, 'month' => 5]));
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

    public function test_global_disclaimer_rich_text_is_sanitized_before_rendering(): void
    {
        Setting::updateOrCreate(
            ['option' => 'disclaimer_content'],
            ['value' => '<p>Safe disclaimer copy</p><script>alert(1)</script><a href="javascript:alert(2)">unsafe link</a>']
        );
        app(AppSettingsService::class)->forgetCache();

        $this->get(route('information', ['page' => 'disclaimer']))
            ->assertOk()
            ->assertSee('Safe disclaimer copy')
            ->assertDontSee('alert(1)')
            ->assertDontSee('javascript:alert(2)');
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
        $this->createGame('Gali', 'gali-custom-slug', 1);

        $seeders = [
            \Database\Seeders\PlayOnlineKhaiwalSettingsSeeder::class,
            \Database\Seeders\GameSeeder::class,
            \Database\Seeders\PageSeeder::class,
            \Database\Seeders\PlayOnlineKhaiwalFaqSeeder::class,
        ];

        $this->seed($seeders);
        $gameCount = Game::query()->count();
        $pageCount = Page::query()->count();
        $settingCount = Setting::query()->count();
        $faqCount = \App\Models\Faq::query()->count();

        $this->seed($seeders);

        $this->assertSame($gameCount, Game::query()->count());
        $this->assertSame($pageCount, Page::query()->count());
        $this->assertSame($settingCount, Setting::query()->count());
        $this->assertSame($faqCount, \App\Models\Faq::query()->count());
        $this->assertSame(0, Result::query()->count());
        $this->assertSame(1, Game::query()->where('name', 'Gali')->count());
        $this->assertDatabaseHas('games', ['name' => 'Gali', 'slug' => 'gali-custom-slug']);
        $this->assertDatabaseHas('settings', [
            'option' => 'canonical_url',
            'value' => 'https://playonlinekhaiwal.com',
        ]);
        $whatsappContent = (string) Page::query()->where('slug', 'whatsapp')->value('content');
        $this->assertStringNotContainsString('support@example.com', $whatsappContent);
        $this->assertStringNotContainsString('91XXXXXXXXXX', $whatsappContent);
    }

    public function test_canonical_brand_metadata_and_sitemap_use_slug_only_result_urls(): void
    {
        Setting::updateOrCreate(['option' => 'canonical_url'], ['value' => 'https://playonlinekhaiwal.com']);
        Setting::updateOrCreate(['option' => 'title'], ['value' => 'Play Online Khaiwal']);
        app(AppSettingsService::class)->forgetCache();

        $game = $this->createGame('First Market', 'first-market', 1);
        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-05',
            'type' => 'jodi',
            'number' => '07',
        ]);
        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-09-05',
            'type' => 'jodi',
            'number' => '08',
        ]);

        $this->get(route('index'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://playonlinekhaiwal.com/">', false)
            ->assertSee('Play Online Khaiwal')
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('Download App')
            ->assertDontSee('Find market');

        $sitemap = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('https://playonlinekhaiwal.com/result/first-market')
            ->assertSee('<loc>https://playonlinekhaiwal.com/about</loc>', false)
            ->assertDontSee('https://playonlinekhaiwal.com/info/about')
            ->assertDontSee('https://playonlinekhaiwal.com/charts/first-market')
            ->assertDontSee('https://playonlinekhaiwal.com/get_result');

        preg_match_all('/<loc>(.*?)<\/loc>/', $sitemap->getContent(), $locations);
        $this->assertSame(count($locations[1]), count(array_unique($locations[1])));
    }

    public function test_homepage_features_most_recently_published_game_and_keeps_remaining_games_serial_order(): void
    {
        $first = $this->createGame('First Market', 'first-market', 1);
        $second = $this->createGame('Second Market', 'second-market', 2);
        $third = $this->createGame('Third Market', 'third-market', 3);

        Result::create([
            'game_id' => $first->id,
            'game_date' => '2026-10-11',
            'type' => 'jodi',
            'number' => '11',
            'created_at' => '2026-10-11 08:00:00',
            'updated_at' => '2026-10-11 08:00:00',
        ]);
        Result::create([
            'game_id' => $second->id,
            'game_date' => '2026-10-11',
            'type' => 'jodi',
            'number' => '22',
            'created_at' => '2026-10-11 09:00:00',
            'updated_at' => '2026-10-11 10:30:00',
        ]);
        Result::create([
            'game_id' => $third->id,
            'game_date' => '2026-10-11',
            'type' => 'jodi',
            'number' => '33',
            'created_at' => '2026-10-11 07:00:00',
            'updated_at' => '2026-10-11 07:00:00',
        ]);

        $html = $this->get(route('index'))->assertOk()->getContent();

        $this->assertStringContainsString('<div class="spotlight-market">Second Market</div>', $html);
        $this->assertStringContainsString('<div class="spotlight-number">22</div>', $html);

        preg_match('/<div class="result-board-grid">(.*?)<\/div>\s*<\/div>\s*<\/section>/s', $html, $board);
        $boardHtml = $board[1] ?? '';
        $this->assertStringNotContainsString('Second Market', $boardHtml);
        $this->assertStringContainsString('First Market', $boardHtml);
        $this->assertStringContainsString('Third Market', $boardHtml);

        preg_match('/<div class="market-grid" id="market-grid">(.*?)<\/div>\s*<p class="empty-state" id="no-markets"/s', $html, $marketGrid);
        $marketHtml = $marketGrid[1] ?? '';
        $this->assertStringNotContainsString('Second Market', $marketHtml);
        $this->assertStringContainsString('href="/result/first-market"', $marketHtml);
        $this->assertStringContainsString('href="/result/third-market"', $marketHtml);
        $this->assertStringNotContainsString('href="/result/second-market"', $marketHtml);

        preg_match_all('/href="#([^"]+)"/', $html, $fragmentLinks);
        preg_match_all('/\bid="([^"]+)"/', $html, $elementIds);
        $ids = array_unique($elementIds[1]);
        foreach (array_unique($fragmentLinks[1]) as $fragment) {
            $this->assertContains($fragment, $ids, "Homepage link #{$fragment} must point to an existing element.");
        }

        $this->assertStringNotContainsString('#quick-record', $html);
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
