<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolveSite;
use App\Models\AdminPaymentAlert;
use App\Models\AdminPaymentAlertRead;
use App\Models\Banner;
use App\Models\Bid;
use App\Models\CommunityPost;
use App\Models\CommunityReply;
use App\Models\Faq;
use App\Models\Game;
use App\Models\HomePage;
use App\Models\Notification;
use App\Models\Page;
use App\Models\PushSubscription;
use App\Models\ReferralReward;
use App\Models\Result;
use App\Models\Setting;
use App\Models\SupportConversation;
use App\Models\SupportConversationAdminRead;
use App\Models\SupportMessage;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPresenceSession;
use App\Models\WalletRequest;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteIsolationTest extends TestCase
{
    public function test_domain_owned_models_apply_the_active_site_global_scope(): void
    {
        $models = [
            User::class,
            Notification::class,
            Game::class,
            Result::class,
            Page::class,
            Banner::class,
            HomePage::class,
            Faq::class,
            Winner::class,
            Bid::class,
            Transaction::class,
            WalletRequest::class,
            PushSubscription::class,
            Setting::class,
            SupportConversation::class,
            SupportMessage::class,
            ReferralReward::class,
            CommunityPost::class,
            CommunityReply::class,
            AdminPaymentAlert::class,
            AdminPaymentAlertRead::class,
            SupportConversationAdminRead::class,
            UserPresenceSession::class,
        ];

        foreach (['play-online-khaiwal', 'galidisawar'] as $siteKey) {
            config(['sites.current_key' => $siteKey]);

            foreach ($models as $model) {
                $query = $model::query();

                $this->assertStringContainsString(
                    'site_key',
                    $query->toSql(),
                    "{$model} must be scoped to the active site."
                );
                $this->assertContains(
                    $siteKey,
                    $query->getBindings(),
                    "{$model} must bind the active site key."
                );
            }
        }
    }

    public function test_public_domain_resolves_to_its_configured_site(): void
    {
        $middleware = new ResolveSite();

        $middleware->handle(
            Request::create('https://playonlinekhaiwal.com/'),
            fn () => response('ok')
        );
        $this->assertSame('play-online-khaiwal', config('sites.current_key'));

        $middleware->handle(
            Request::create('https://galidisawar.com/'),
            fn () => response('ok')
        );
        $this->assertSame('galidisawar', config('sites.current_key'));
        $this->assertSame('Gali Disawar', config('sites.current.name'));
    }

    public function test_admin_site_cookie_overrides_host_only_inside_admin_routes(): void
    {
        $middleware = new ResolveSite();
        $request = Request::create('https://galidisawar.com/admin/dashboard');
        $request->cookies->set('admin_site', 'play-online-khaiwal');

        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('play-online-khaiwal', config('sites.current_key'));
    }
    public function test_site_scoped_unique_indexes_and_password_reset_storage_are_migrated(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'site_key'));
        $this->assertTrue(Schema::hasColumn('pages', 'site_key'));
        $this->assertTrue(Schema::hasColumn('password_reset_otps', 'site_key'));

        $gameIndexes = collect(Schema::getIndexes('games'))->pluck('name');
        $pageIndexes = collect(Schema::getIndexes('pages'))->pluck('name');

        $this->assertContains('games_site_key_name_unique', $gameIndexes);
        $this->assertContains('games_site_key_slug_unique', $gameIndexes);
        $this->assertContains('pages_site_key_name_unique', $pageIndexes);
    }

    public function test_robots_txt_advertises_the_current_domain_sitemap(): void
    {
        $this->get('https://galidisawar.com/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: https://galidisawar.com/sitemap.xml', false);
    }

}
