<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\Game\BiddingDeskController;
use App\Http\Controllers\Backend\SupportChatController;
use App\Models\Admin;
use App\Models\Bid;
use App\Models\Game;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $this->assertSame(150.0, $payload['summary']['jodi']['total_amount']);
        $this->assertSame(1, $payload['summary']['crossing']['total_bets']);
        $this->assertSame(200.0, $payload['summary']['crossing']['total_amount']);
        $this->assertSame(1, $payload['summary']['haruf']['total_bets']);
        $this->assertSame(25.0, $payload['summary']['haruf']['total_amount']);
        $this->assertSame(99, $payload['summary']['jodi']['unplayed_numbers']);
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

        $request = Request::create('/admin/bidding-desk/details', 'GET', [
            'game_id' => $game->id,
            'date' => '2026-11-03',
            'number' => '25',
            'type' => 'jodi',
        ]);
        $payload = app(BiddingDeskController::class)->details($request)->getData(true)['data'];

        $this->assertSame('jodi', $payload['type']);
        $this->assertCount(1, $payload['users']);
        $numbers = collect($payload['users'][0]['numbers'])->pluck('number')->all();
        $this->assertContains('25', $numbers);
        $this->assertContains('01', $numbers);
        $this->assertNotContains('ander-2', $numbers);
        $this->assertCount(1, $payload['bids']);
        $this->assertSame('jodi', $payload['bids'][0]['type']);
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
}
