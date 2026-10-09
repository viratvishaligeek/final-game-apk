<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\Game\ResultController;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Notification;
use App\Models\Result;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResultSettlementIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    public function test_correcting_a_result_reverses_old_winnings_before_settling_again(): void
    {
        [$game, $user, $bid] = $this->createWinningScenario();

        $this->publishResult($game, '2026-10-09', '12');

        $user->refresh();
        $bid->refresh();

        $this->assertEquals(120.0, (float) $user->balance);
        $this->assertSame('win', $bid->status);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'bid_id' => $bid->id,
            'type' => 'credit',
            'amount' => '20.00',
        ]);

        $this->publishResult($game, '2026-10-09', '34');

        $user->refresh();
        $bid->refresh();

        $this->assertEquals(100.0, (float) $user->balance);
        $this->assertSame('pending', $bid->status);
        $this->assertDatabaseHas('results', [
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '34',
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'bid_id' => $bid->id,
            'type' => 'debit',
            'amount' => '20.00',
        ]);
        $this->assertSame(0, Winner::query()->where('bid_id', $bid->id)->count());
        $this->assertSame(2, Transaction::query()->where('user_id', $user->id)->count());
    }

    public function test_publishing_the_same_result_twice_does_not_duplicate_credits_or_notifications(): void
    {
        [$game, $user, $bid] = $this->createWinningScenario();

        $this->publishResult($game, '2026-10-09', '12');

        $transactionCount = Transaction::query()->where('user_id', $user->id)->count();
        $notificationCount = Notification::query()->count();
        $balanceAfterFirstPublish = (float) $user->fresh()->balance;

        $this->publishResult($game, '2026-10-09', '12');

        $this->assertEquals($balanceAfterFirstPublish, (float) $user->fresh()->balance);
        $this->assertSame($transactionCount, Transaction::query()->where('user_id', $user->id)->count());
        $this->assertSame($notificationCount, Notification::query()->count());
        $this->assertSame(1, Winner::query()->where('bid_id', $bid->id)->count());
    }

    public function test_reverting_a_result_records_a_debit_reversal_and_restores_bid_state(): void
    {
        [$game, $user, $bid] = $this->createWinningScenario();

        $this->publishResult($game, '2026-10-09', '12');

        $request = Request::create('/admin/results/revert', 'POST', [
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
        ]);

        app()->instance('request', $request);

        (new ResultController())->revert($request);

        $user->refresh();
        $bid->refresh();
        $game->refresh();

        $this->assertSame('100.00', $user->balance);
        $this->assertSame('pending', $bid->status);
        $this->assertEquals(0.0, (float) $bid->winning_amount);
        $this->assertSame('Wait', $game->last_result);
        $this->assertSame(0, Result::query()->where('game_id', $game->id)->where('game_date', '2026-10-09')->count());
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'bid_id' => $bid->id,
            'type' => 'debit',
            'amount' => '20.00',
        ]);
    }

    public function test_result_correction_is_aborted_if_wallet_cannot_cover_reversal(): void
    {
        [$game, $user, $bid] = $this->createWinningScenario();

        $this->publishResult($game, '2026-10-09', '12');

        $user->update(['balance' => 5]);

        $this->publishResult($game, '2026-10-09', '34');

        $user->refresh();
        $bid->refresh();

        $this->assertEquals(5.0, (float) $user->balance);
        $this->assertSame('win', $bid->status);
        $this->assertDatabaseHas('results', [
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '12',
        ]);
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->count());
        $this->assertSame(1, Winner::query()->where('bid_id', $bid->id)->count());
    }

    private function createWinningScenario(): array
    {
        $game = Game::create([
            'name' => 'Test Game',
            'slug' => 'test-game',
            'result_time' => '18:00:00',
            'play_start' => '10:00:00',
            'play_end' => '17:00:00',
            'last_result' => 'Wait',
            'status' => 'active',
            'serial' => 1,
            'reward' => 2,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'phone' => '9999999999',
            'password' => 'test-password',
            'balance' => 100,
            'status' => 'active',
        ]);

        $bid = Bid::create([
            'order_no' => 'TEST-ORDER-1',
            'user_id' => $user->id,
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '12',
            'amount' => 10,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        return [$game, $user, $bid];
    }

    private function publishResult(Game $game, string $date, string $number): void
    {
        $request = Request::create('/admin/results', 'POST', [
            'game_id' => $game->id,
            'game_date' => $date,
            'result' => $number,
        ]);

        app()->instance('request', $request);

        (new ResultController())->storeOrUpdate($request);
    }
}
