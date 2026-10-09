<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Result;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BettingWorkflowContractTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_api_can_create_a_bid_with_the_phone_field_used_by_the_bidding_desk(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $user = $this->createUser('9876543210', 100);
        $game = $this->createGame('Test Game', 'test-game', 10);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/games/{$game->id}/bids", [
                'mode' => 'single',
                'total_amount' => '10.00',
                'total_bets' => 1,
                'single_bets' => ['12' => '10.00'],
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('bids', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'phone' => '9876543210',
            'type' => 'jodi',
            'number' => '12',
            'amount' => '10.00',
            'status' => 'pending',
        ]);

        $this->assertSame(90.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => '10.00',
            'status' => 'completed',
        ]);
    }

    public function test_result_settlement_pays_crossing_jodi_and_haruf_winners(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $admin = Admin::create([
            'name' => 'Settlement Admin',
            'email' => 'settlement-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);
        $user = $this->createUser('9876543211', 1000);
        $game = $this->createGame('Settlement Game', 'settlement-game', 10);

        $jodi = $this->createBid($user, $game, 'JODI-1', 'jodi', '12', 10);
        $cross = $this->createBid($user, $game, 'CROSS-1', 'cross', '12', 10);
        $haruf = $this->createBid($user, $game, 'HARUF-1', 'haruf', 'ander-1', 10);
        $loser = $this->createBid($user, $game, 'LOSER-1', 'jodi', '34', 10);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.results.storeOrUpdate'), [
                'game_id' => $game->id,
                'game_date' => '2026-10-09',
                'result' => '12',
            ])
            ->assertRedirect();

        foreach ([$jodi, $cross, $haruf] as $bid) {
            $this->assertDatabaseHas('bids', [
                'id' => $bid->id,
                'status' => 'win',
            ]);
        }

        $this->assertSame(3, Winner::query()->where('game_id', $game->id)->count());
        $this->assertSame(3, Transaction::query()
            ->where('game_id', $game->id)
            ->where('type', 'credit')
            ->count());

        // Jodi and crossing pay the full reward; Haruf pays one tenth.
        $this->assertSame(1210.0, (float) $user->fresh()->balance);
        $this->assertSame(100.0, (float) $jodi->fresh()->winning_amount);
        $this->assertSame(100.0, (float) $cross->fresh()->winning_amount);
        $this->assertSame(10.0, (float) $haruf->fresh()->winning_amount);
        $this->assertDatabaseHas('bids', [
            'id' => $loser->id,
            'status' => 'loss',
        ]);
    }

    public function test_correcting_a_result_reopens_previous_losers_before_resettlement(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $admin = Admin::create([
            'name' => 'Correction Admin',
            'email' => 'correction-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);
        $user = $this->createUser('9876543212', 1000);
        $game = $this->createGame('Correction Game', 'correction-game', 10);
        $oldWinner = $this->createBid($user, $game, 'OLD-WINNER', 'jodi', '12', 10);
        $newWinner = $this->createBid($user, $game, 'NEW-WINNER', 'jodi', '34', 10);

        $this->actingAs($admin, 'admin')->post(route('admin.results.storeOrUpdate'), [
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'result' => '12',
        ])->assertRedirect();

        $this->assertSame(1100.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('bids', ['id' => $newWinner->id, 'status' => 'loss']);

        $this->actingAs($admin, 'admin')->post(route('admin.results.storeOrUpdate'), [
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'result' => '34',
        ])->assertRedirect();

        $this->assertSame(1100.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('bids', ['id' => $oldWinner->id, 'status' => 'loss']);
        $this->assertDatabaseHas('bids', ['id' => $newWinner->id, 'status' => 'win']);
        $this->assertSame(1, Winner::query()->where('game_id', $game->id)->count());
        $this->assertSame(1, Transaction::query()
            ->where('game_id', $game->id)
            ->where('type', 'debit')
            ->where('subject', 'like', 'Result reversal%')
            ->count());
    }

    public function test_api_rejects_bets_after_the_result_is_published(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $user = $this->createUser('9876543213', 100);
        $game = $this->createGame('Closed Result Game', 'closed-result-game', 10);

        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '12',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/games/{$game->id}/bids", [
                'mode' => 'single',
                'total_amount' => '10.00',
                'total_bets' => 1,
                'single_bets' => ['34' => '10.00'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('game');

        $this->assertSame(100.0, (float) $user->fresh()->balance);
        $this->assertDatabaseMissing('bids', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    private function createUser(string $phone, float $balance): User
    {
        return User::create([
            'name' => 'Betting Test User',
            'phone' => $phone,
            'password' => 'Password123',
            'balance' => $balance,
            'status' => 'active',
        ]);
    }

    private function createGame(string $name, string $slug, float $reward): Game
    {
        return Game::create([
            'name' => $name,
            'slug' => $slug,
            'result_time' => '07:00:00',
            'play_start' => '00:00:00',
            'play_end' => '23:59:59',
            'last_result' => 'Wait',
            'status' => 'active',
            'serial' => 1,
            'reward' => $reward,
        ]);
    }

    private function createBid(
        User $user,
        Game $game,
        string $orderNo,
        string $type,
        string $number,
        float $amount
    ): Bid {
        return Bid::create([
            'order_no' => $orderNo,
            'user_id' => $user->id,
            'game_id' => $game->id,
            'phone' => $user->phone,
            'game_date' => '2026-10-09',
            'type' => $type,
            'number' => $number,
            'amount' => $amount,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);
    }
}
