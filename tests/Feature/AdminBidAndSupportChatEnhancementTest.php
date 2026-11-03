<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\Game\BiddingDeskController;
use App\Http\Controllers\Backend\SupportChatController;
use App\Http\Controllers\Api\PresenceController;
use App\Services\Presence\UserPresenceChannelAuthorizer;
use App\Services\Admin\BiddingDeskProfitLossReportService;
use App\Services\Admin\AdminDashboardService;
use App\Http\Controllers\Backend\User\UserController;
use App\Events\AdminPaymentFailedPaymentAlert;
use App\Events\SupportMessageCreated;
use App\Events\UserPresenceChanged;
use App\Models\AdminPaymentAlert;
use App\Models\Admin;
use App\Models\Bid;
use App\Models\Game;
use App\Models\SupportConversation;
use App\Models\SupportConversationAdminRead;
use App\Models\User;
use App\Models\UserPresenceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminBidAndSupportChatEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(): Game
    {
        return Game::query()->create([
            'name' => 'Realtime Test Game',
            'slug' => 'realtime-test-game',
            'result_time' => '20:00:00',
            'play_start' => '09:00:00',
            'play_end' => '21:00:00',
            'status' => 'active',
            'serial' => 1,
            'reward' => 1,
        ]);
    }

    private function makeUser(string $phone = '9876543210'): User
    {
        return User::query()->create([
            'name' => 'Bid Test User',
            'phone' => $phone,
            'password' => 'test-password',
            'status' => 'active',
            'balance' => 0,
        ]);
    }

    public function test_bidding_desk_keeps_jodi_haruf_and_crossing_aggregates_separate(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser();

        Bid::query()->create([
            'order_no' => 'TEST-ORDER-1', 'user_id' => $user->id, 'game_id' => $game->id,
            'game_date' => '2026-11-03', 'type' => 'jodi', 'number' => '25', 'amount' => 100,
            'status' => 'pending', 'winning_amount' => 0,
        ]);
        Bid::query()->create([
            'order_no' => 'TEST-ORDER-1', 'user_id' => $user->id, 'game_id' => $game->id,
            'game_date' => '2026-11-03', 'type' => 'jodi', 'number' => '01', 'amount' => 50,
            'status' => 'pending', 'winning_amount' => 0,
        ]);
        Bid::query()->create([
            'order_no' => 'TEST-ORDER-2', 'user_id' => $user->id, 'game_id' => $game->id,
            'game_date' => '2026-11-03', 'type' => 'cross', 'number' => '25', 'amount' => 200,
            'status' => 'pending', 'winning_amount' => 0,
        ]);
        Bid::query()->create([
            'order_no' => 'TEST-ORDER-3', 'user_id' => $user->id, 'game_id' => $game->id,
            'game_date' => '2026-11-03', 'type' => 'haruf', 'number' => 'ander-2', 'amount' => 25,
            'status' => 'pending', 'winning_amount' => 0,
        ]);

        $request = Request::create('/admin/bidding-desk/data', 'GET', [
            'game_id' => $game->id,
            'date' => '2026-11-03',
        ]);
        $payload = app(BiddingDeskController::class)->data($request)->getData(true)['data'];

        $this->assertSame(2, $payload['summary']['jodi']['total_bets']);
        $this->assertEquals(150, $payload['summary']['jodi']['total_amount']);
        $this->assertSame(1, $payload['summary']['crossing']['total_bets']);
        $this->assertEquals(200, $payload['summary']['crossing']['total_amount']);
        $this->assertSame(1, $payload['summary']['haruf']['total_bets']);
        $this->assertEquals(25, $payload['summary']['haruf']['total_amount']);
        $this->assertSame(98, $payload['summary']['jodi']['unplayed_numbers']);
    }

    public function test_selected_number_details_include_only_same_user_same_type_other_numbers(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876543211');

        foreach ([
            ['jodi', '25', 100],
            ['jodi', '01', 50],
            ['cross', '25', 200],
            ['haruf', 'ander-2', 25],
        ] as [$type, $number, $amount]) {
            Bid::query()->create([
                'order_no' => 'TEST-' . $type . '-' . $number,
                'user_id' => $user->id,
                'game_id' => $game->id,
                'game_date' => '2026-11-03',
                'type' => $type,
                'number' => $number,
                'amount' => $amount,
                'status' => 'pending',
                'winning_amount' => 0,
            ]);
        }

        $otherUser = $this->makeUser('9876543212');
        Bid::query()->create([
            'order_no' => 'TEST-OTHER-USER',
            'user_id' => $otherUser->id,
            'game_id' => $game->id,
            'game_date' => '2026-11-03',
            'type' => 'jodi',
            'number' => '25',
            'amount' => 300,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        $request = Request::create('/admin/bidding-desk/details', 'GET', [
            'game_id' => $game->id,
            'date' => '2026-11-03',
            'number' => '25',
            'type' => 'jodi',
        ]);
        $payload = app(BiddingDeskController::class)->details($request)->getData(true)['data'];

        $this->assertSame('jodi', $payload['type']);
        $this->assertCount(2, $payload['users']);
        $selectedUser = collect($payload['users'])->firstWhere('user_id', $user->id);
        $otherUserDetails = collect($payload['users'])->firstWhere('user_id', $otherUser->id);
        $numbers = collect($selectedUser['numbers'])->pluck('number')->all();
        $this->assertContains('25', $numbers);
        $this->assertContains('01', $numbers);
        $this->assertNotContains('ander-2', $numbers);
        $this->assertNotContains('01', collect($otherUserDetails['numbers'])->pluck('number')->all());
        $this->assertCount(2, $payload['bids']);
        $this->assertSame('jodi', $payload['bids'][0]['type']);
    }

    public function test_user_bid_modal_endpoint_returns_paginated_individual_bid_details(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876543214');
        Bid::query()->create([
            'order_no' => 'USER-SHOW-ORDER',
            'user_id' => $user->id,
            'game_id' => $game->id,
            'game_date' => '2026-11-03',
            'type' => 'jodi',
            'number' => '42',
            'amount' => 125,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        $response = app(UserController::class)->bids(
            Request::create('/admin/users/' . $user->id . '/bids', 'GET', ['order_no' => 'USER-SHOW-ORDER']),
            $user
        );
        $payload = $response->getData(true);

        $this->assertCount(1, $payload['data']);
        $this->assertSame('42', $payload['data'][0]['number']);
        $this->assertSame('03 Nov 2026', $payload['data'][0]['game_date']);
        $this->assertEquals(125, $payload['data'][0]['amount']);
    }

    public function test_user_game_activity_keeps_each_submission_separate_even_for_same_game_and_type(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876543215');

        foreach ([
            ['SUBMISSION-A', 'jodi', '25', 100],
            ['SUBMISSION-A', 'jodi', '26', 50],
            ['SUBMISSION-B', 'jodi', '25', 200],
            ['SUBMISSION-C', 'haruf', 'ander-2', 75],
        ] as [$orderNo, $type, $number, $amount]) {
            Bid::query()->create([
                'order_no' => $orderNo,
                'user_id' => $user->id,
                'game_id' => $game->id,
                'game_date' => '2026-11-03',
                'type' => $type,
                'number' => $number,
                'amount' => $amount,
                'status' => 'pending',
                'winning_amount' => 0,
            ]);
        }

        $view = app(UserController::class)->show($user);
        $groups = $view->getData()['gameBidStats'];

        $this->assertCount(3, $groups);
        $this->assertSame(
            ['SUBMISSION-C', 'SUBMISSION-B', 'SUBMISSION-A'],
            $groups->pluck('order_no')->all()
        );
        $this->assertSame([1, 1, 2], $groups->pluck('total_bids')->all());
        $this->assertEquals([75, 200, 150], $groups->pluck('total_amount')->all());
    }

    public function test_historical_bids_without_order_numbers_remain_separate_activities(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876543218');

        foreach ([['25', 40], ['26', 60]] as [$number, $amount]) {
            Bid::query()->create([
                'order_no' => '',
                'user_id' => $user->id,
                'game_id' => $game->id,
                'game_date' => '2026-11-03',
                'type' => 'jodi',
                'number' => $number,
                'amount' => $amount,
                'status' => 'pending',
                'winning_amount' => 0,
            ]);
        }

        $view = app(UserController::class)->show($user);
        $groups = $view->getData()['gameBidStats'];

        $this->assertCount(2, $groups);
        $this->assertSame([1, 1], $groups->pluck('total_bids')->all());
        $this->assertEquals([60, 40], $groups->pluck('total_amount')->all());
        $this->assertTrue($groups->every(fn ($group) => $group->order_no === ''));
    }

    public function test_user_bid_details_are_scoped_to_the_requested_submission(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876543216');
        $otherUser = $this->makeUser('9876543217');

        foreach ([[$user, 'OWN-SUBMISSION'], [$otherUser, 'OTHER-SUBMISSION']] as [$owner, $orderNo]) {
            Bid::query()->create([
                'order_no' => $orderNo,
                'user_id' => $owner->id,
                'game_id' => $game->id,
                'game_date' => '2026-11-03',
                'type' => 'jodi',
                'number' => '25',
                'amount' => 100,
                'status' => 'pending',
                'winning_amount' => 0,
            ]);
        }

        $response = app(UserController::class)->bids(
            Request::create('/admin/users/' . $user->id . '/bids', 'GET', ['order_no' => 'OTHER-SUBMISSION']),
            $user
        );

        $this->assertCount(0, $response->getData(true)['data']);
    }

    public function test_admin_can_search_registered_users_without_existing_conversations(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'support-search@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        Auth::guard('admin')->setUser($admin);
        $user = $this->makeUser('9876500012');

        $request = Request::create('/admin/support-chat/users', 'GET', ['q' => '98765']);
        $payload = app(SupportChatController::class)->searchUsers($request)->getData(true);

        $this->assertCount(1, $payload['data']);
        $this->assertSame($user->id, $payload['data'][0]['id']);
        $this->assertFalse($payload['data'][0]['has_conversation']);
        $this->assertSame(0, $payload['data'][0]['unread_count']);
    }

    public function test_admin_all_user_search_route_is_protected_by_admin_authentication(): void
    {
        $route = Route::getRoutes()->getByAction(SupportChatController::class . '@searchUsers');

        $this->assertNotNull($route);
        $this->assertContains('auth:admin', $route->gatherMiddleware());
    }

    public function test_admin_route_reorganization_preserves_names_and_security_middleware(): void
    {
        foreach ([
            'admin.support-chat.index',
            'admin.users.bids',
            'admin.wallet.request-add',
            'admin.results.index',
            'admin.bidding-desk.data',
        ] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Missing Admin route name: {$routeName}");
            $this->assertContains('auth:admin', $route->gatherMiddleware());
            $this->assertContains('admin.site', $route->gatherMiddleware());
        }
    }

    public function test_player_private_broadcast_auth_route_requires_sanctum(): void
    {
        $route = Route::getRoutes()->match(Request::create('/api/broadcasting/auth', 'POST'));

        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
    }

    public function test_starting_the_same_user_conversation_twice_reuses_the_existing_row(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Conversation Admin',
            'email' => 'conversation-admin@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        Auth::guard('admin')->setUser($admin);
        $user = $this->makeUser('9876500013');
        $controller = app(SupportChatController::class);

        $first = $controller->startConversation(Request::create(
            '/admin/support-chat/conversations',
            'POST',
            ['user_id' => $user->id]
        ));
        $secondAdmin = Admin::query()->create([
            'name' => 'Second Conversation Admin',
            'email' => 'conversation-admin-two@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        Auth::guard('admin')->setUser($secondAdmin);
        $second = $controller->startConversation(Request::create(
            '/admin/support-chat/conversations',
            'POST',
            ['user_id' => $user->id]
        ));

        $this->assertSame(201, $first->getStatusCode());
        $this->assertSame(200, $second->getStatusCode());
        $this->assertSame($first->getData(true)['data']['id'], $second->getData(true)['data']['id']);
        $this->assertSame(1, SupportConversation::query()->where('user_id', $user->id)->count());
    }
    public function test_global_unread_total_is_per_admin_and_includes_all_conversations(): void
    {
        $firstAdmin = Admin::query()->create([
            'name' => 'Unread Admin One',
            'email' => 'unread-one@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        $secondAdmin = Admin::query()->create([
            'name' => 'Unread Admin Two',
            'email' => 'unread-two@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);

        $firstUser = $this->makeUser('9876500021');
        $secondUser = $this->makeUser('9876500022');
        $firstConversation = SupportConversation::query()->create(['user_id' => $firstUser->id]);
        $secondConversation = SupportConversation::query()->create(['user_id' => $secondUser->id]);

        $firstMessage = $firstConversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $firstUser->id,
            'body' => 'First unread message',
        ]);
        $secondConversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $secondUser->id,
            'body' => 'Second unread message',
        ]);

        SupportConversationAdminRead::query()->create([
            'conversation_id' => $firstConversation->id,
            'admin_id' => $firstAdmin->id,
            'last_read_message_id' => $firstMessage->id,
            'last_read_at' => now(),
        ]);

        Auth::guard('admin')->setUser($firstAdmin);
        $firstPayload = app(SupportChatController::class)
            ->conversations(Request::create('/admin/support-chat/conversations', 'GET'))
            ->getData(true);
        $this->assertSame(1, $firstPayload['meta']['total_unread_count']);

        Auth::guard('admin')->setUser($secondAdmin);
        $secondPayload = app(SupportChatController::class)
            ->conversations(Request::create('/admin/support-chat/conversations', 'GET'))
            ->getData(true);
        $this->assertSame(2, $secondPayload['meta']['total_unread_count']);
    }

    public function test_admin_realtime_events_are_scoped_to_their_site(): void
    {
        $user = $this->makeUser('9876500031');
        $conversation = SupportConversation::query()->create(['user_id' => $user->id]);
        $message = $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'body' => 'Site-scoped support message',
        ]);

        $supportChannels = (new SupportMessageCreated($message->id))->broadcastOn();
        $this->assertSame('private-admin.support-chat.' . $message->site_key, $supportChannels[0]->name);

        $presenceChannels = (new UserPresenceChanged($user->id, 'active'))->broadcastOn();
        $this->assertSame('private-admin.user-presence.' . $user->site_key, $presenceChannels[0]->name);

        $alert = AdminPaymentAlert::query()->create([
            'wallet_request_id' => null,
            'user_id' => $user->id,
            'amount' => 100,
            'gateway' => 'test',
            'failure_reason' => 'Test failure',
            'failed_at' => now(),
        ]);
        $paymentChannels = (new AdminPaymentFailedPaymentAlert($alert->id))->broadcastOn();
        $this->assertSame('private-admin.payment-alerts.' . $alert->site_key, $paymentChannels[0]->name);
    }

    public function test_admin_dashboard_service_returns_site_scoped_summary_and_user_report(): void
    {
        $this->makeUser('9876500099');
        $this->makeGame();

        $report = app(AdminDashboardService::class)->build('not-a-valid-period');

        $this->assertSame('7days', $report['period']);
        $this->assertSame(1, $report['totalUsers']);
        $this->assertSame(1, $report['totalGames']);
        $this->assertSame(0, (int) $report['todayTotalBids']);
        $this->assertCount(1, $report['totalUserTable']);
        $this->assertSame('Not Played', $report['totalUserTable'][0]['playing_period']);
    }

    public function test_bidding_desk_profit_loss_report_service_preserves_game_totals(): void
    {
        $game = $this->makeGame();
        $user = $this->makeUser('9876500098');
        $bid = Bid::query()->create([
            'order_no' => (string) Str::uuid(),
            'user_id' => $user->id,
            'game_id' => $game->id,
            'game_date' => '2026-11-04',
            'type' => 'jodi',
            'number' => '25',
            'amount' => 100,
            'status' => 'won',
            'winning_amount' => 40,
        ]);
        Winner::query()->create([
            'bid_id' => $bid->id,
            'user_id' => $user->id,
            'game_id' => $game->id,
            'number' => '25',
            'type' => 'jodi',
            'game_date' => '2026-11-04',
            'amount' => 100,
            'winning_amount' => 40,
        ]);

        $report = app(BiddingDeskProfitLossReportService::class)->build('2026-11-04', '2026-11-04');
        $gameStats = $report['gameStats']->firstWhere('id', $game->id);

        $this->assertNotNull($gameStats);
        $this->assertEquals(100, $gameStats['total_bid']);
        $this->assertEquals(40, $gameStats['total_win']);
        $this->assertEquals(60, $gameStats['profit_loss']);
        $this->assertEquals(60, $report['grandProfitLoss']);
    }

    public function test_disconnecting_one_device_keeps_user_online_while_another_session_is_active(): void
    {
        $user = $this->makeUser('9876500094');
        $user->forceFill([
            'live_status' => 'active',
            'live_last_seen_at' => now()->subSeconds(5),
        ])->save();

        $firstSession = UserPresenceSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'device-session-one',
            'platform' => 'web',
            'app_state' => 'foreground',
            'last_seen_at' => now(),
        ]);
        $secondSession = UserPresenceSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'device-session-two',
            'platform' => 'android',
            'app_state' => 'foreground',
            'last_seen_at' => now(),
        ]);

        $request = Request::create('/api/v1/presence/session', 'DELETE', [
            'session_id' => $firstSession->session_id,
        ]);
        $request->setUserResolver(fn () => $user);
        app(PresenceController::class)->disconnect($request);

        $this->assertNotNull($firstSession->fresh()->ended_at);
        $this->assertNull($secondSession->fresh()->ended_at);
        $this->assertSame('active', $user->fresh()->live_status);

        $secondRequest = Request::create('/api/v1/presence/session', 'DELETE', [
            'session_id' => $secondSession->session_id,
        ]);
        $secondRequest->setUserResolver(fn () => $user);
        app(PresenceController::class)->disconnect($secondRequest);

        $this->assertSame('inactive', $user->fresh()->live_status);
    }

    public function test_stale_presence_command_marks_users_offline_after_the_session_lease_expires(): void
    {
        $user = $this->makeUser('9876500095');
        $user->forceFill([
            'live_status' => 'active',
            'live_last_seen_at' => now()->subMinute(),
        ])->save();

        UserPresenceSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'stale-device-session',
            'platform' => 'web',
            'app_state' => 'foreground',
            'last_seen_at' => now()->subSeconds(90),
        ]);

        Artisan::call('presence:expire');

        $this->assertSame('inactive', $user->fresh()->live_status);
    }

    public function test_presence_session_identifier_cannot_be_reused_by_another_user(): void
    {
        $owner = $this->makeUser('9876500096');
        $other = $this->makeUser('9876500097');

        UserPresenceSession::query()->create([
            'user_id' => $owner->id,
            'session_id' => 'owned-device-session',
            'platform' => 'web',
            'app_state' => 'foreground',
            'last_seen_at' => now(),
        ]);

        $request = Request::create('/api/v1/presence/heartbeat', 'POST', [
            'session_id' => 'owned-device-session',
            'platform' => 'web',
            'app_state' => 'foreground',
        ]);
        $request->setUserResolver(fn () => $other);

        $response = app(PresenceController::class)->heartbeat($request);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame($owner->id, UserPresenceSession::query()->where('session_id', 'owned-device-session')->value('user_id'));
    }

    public function test_player_presence_channel_authorizer_allows_only_the_matching_account(): void
    {
        $user = $this->makeUser('9876500091');
        $otherUser = $this->makeUser('9876500092');
        $authorizer = app(UserPresenceChannelAuthorizer::class);

        $this->assertSame([
            'id' => 'player-' . $user->id,
            'type' => 'player',
        ], $authorizer->authorize($user, $user->id));
        $this->assertFalse($authorizer->authorize($user, $otherUser->id));
    }

    public function test_admin_presence_channel_authorizer_enforces_site_scope_and_active_status(): void
    {
        $user = $this->makeUser('9876500093');
        $admin = Admin::query()->create([
            'name' => 'Presence Admin',
            'email' => 'presence-admin@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        $authorizer = app(UserPresenceChannelAuthorizer::class);

        config(['sites.current_key' => $user->site_key]);
        $this->assertSame([
            'id' => 'admin-' . $admin->id,
            'type' => 'admin',
        ], $authorizer->authorize($admin, $user->id));

        config(['sites.current_key' => 'different-site']);
        $this->assertFalse($authorizer->authorize($admin, $user->id));

        $admin->forceFill(['status' => 'inactive'])->save();
        config(['sites.current_key' => $user->site_key]);
        $this->assertFalse($authorizer->authorize($admin->fresh(), $user->id));
    }

    public function test_admin_private_channel_authorization_rejects_a_different_site(): void
    {
        config([
            'sites.current_key' => 'galidisawar',
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.app_id' => 'test-app',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.options.cluster' => 'ap2',
        ]);

        $admin = Admin::query()->create([
            'name' => 'Site Scoped Admin',
            'email' => 'site-scoped-admin@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        Auth::guard('admin')->setUser($admin);

        $request = Request::create('/broadcasting/auth', 'POST', [
            'socket_id' => '123.456',
            'channel_name' => 'private-admin.support-chat.play-online-khaiwal',
        ]);
        $request->setUserResolver(fn ($guard = null) => Auth::guard($guard)->user());

        $this->expectException(AccessDeniedHttpException::class);
        Broadcast::auth($request);
    }

}
