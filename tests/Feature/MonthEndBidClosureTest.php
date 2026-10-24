<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MonthEndBidClosureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_direct_bid_api_rejects_new_bets_on_month_end_without_changing_wallet_or_history(): void
    {
        $now = Carbon::parse('2025-04-30 12:00:00', config('app.timezone'));
        Carbon::setTestNow($now);

        $user = User::factory()->create([
            'status' => 'active',
            'balance' => 500.00,
        ]);

        $game = Game::query()->create([
            'name' => 'Month End Test Market',
            'slug' => 'month-end-test-market',
            'result_time' => '18:00:00',
            'play_start' => '00:00:00',
            'play_end' => '23:59:59',
            'status' => 'active',
            'serial' => 999,
            'reward' => 10,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/' . $game->id . '/bids', [
            'mode' => 'single',
            'total_amount' => 1,
            'total_bets' => 1,
            'single_bets' => ['00' => 1],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('game');

        $this->assertEquals(500.00, (float) $user->fresh()->balance);
        $this->assertSame(0, Bid::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'type' => 'debit',
            'subject' => 'Bet placed - ' . $game->name,
        ]);
    }
}
