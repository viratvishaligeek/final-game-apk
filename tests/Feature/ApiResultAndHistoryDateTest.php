<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResultAndHistoryDateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_game_list_selects_jodi_result_not_the_latest_digit_result(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $user = $this->createUser();
        $game = $this->createGame('Disawar', 'disawar', '07:00:00');

        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '12',
        ]);
        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'ah',
            'number' => '1',
        ]);
        Result::create([
            'game_id' => $game->id,
            'game_date' => '2026-10-09',
            'type' => 'bh',
            'number' => '2',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/games/list')
            ->assertOk()
            ->assertJsonPath('games.0.latest_result.number', '12')
            ->assertJsonPath('games.0.latest_result.type', 'jodi');
    }

    public function test_default_play_history_includes_each_games_current_business_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 03:00:00', config('app.timezone')));

        $user = $this->createUser();
        $disawar = $this->createGame('Disawar', 'disawar', '07:00:00');
        $other = $this->createGame('Delhi Bazar', 'delhi-bazar', '15:00:00');

        Bid::create([
            'order_no' => 'DISAWAR-HISTORY-1',
            'user_id' => $user->id,
            'game_id' => $disawar->id,
            'game_date' => '2026-10-08',
            'type' => 'jodi',
            'number' => '12',
            'amount' => 10,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        Bid::create([
            'order_no' => 'DELHI-HISTORY-1',
            'user_id' => $user->id,
            'game_id' => $other->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '34',
            'amount' => 20,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/play-history')
            ->assertOk()
            ->assertJsonPath('data.date_mode', 'current_business_dates')
            ->assertJsonPath('data.stats.total_slips', 2);

        $dates = collect($response->json('data.slips'))
            ->pluck('date')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['2026-10-08', '2026-10-09'], $dates);
    }

    public function test_play_history_stats_include_slips_beyond_the_current_page(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $user = $this->createUser();
        $game = $this->createGame('Test Market', 'test-market', '07:00:00');

        for ($index = 1; $index <= 51; $index++) {
            Bid::create([
                'order_no' => 'PAGED-HISTORY-' . $index,
                'user_id' => $user->id,
                'game_id' => $game->id,
                'game_date' => '2026-10-09',
                'type' => 'jodi',
                'number' => str_pad((string) ($index % 100), 2, '0', STR_PAD_LEFT),
                'amount' => $index === 51 ? 100 : 10,
                'status' => 'pending',
                'winning_amount' => $index === 51 ? 7 : 0,
            ]);
        }

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/play-history?per_page=50')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 51)
            ->assertJsonPath('data.stats.total_slips', 51)
            ->assertJsonPath('data.stats.total_spent', 600)
            ->assertJsonPath('data.stats.total_won', 7);
    }

    public function test_pending_slip_status_matches_its_business_date_and_play_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', config('app.timezone')));

        $user = $this->createUser();
        $game = $this->createGame('Disawar', 'disawar', '07:00:00');

        foreach ([
            ['order_no' => 'OLD-BUSINESS-DATE', 'game_date' => '2026-10-08', 'number' => '12'],
            ['order_no' => 'CURRENT-BUSINESS-DATE', 'game_date' => '2026-10-09', 'number' => '34'],
        ] as $entry) {
            Bid::create([
                'order_no' => $entry['order_no'],
                'user_id' => $user->id,
                'game_id' => $game->id,
                'game_date' => $entry['game_date'],
                'type' => 'jodi',
                'number' => $entry['number'],
                'amount' => 10,
                'status' => 'pending',
                'winning_amount' => 0,
            ]);
        }

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/play-history?date=2026-10-08')
            ->assertOk()
            ->assertJsonPath('data.slips.0.status', 'closed');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/play-history')
            ->assertOk()
            ->assertJsonPath('data.slips.0.status', 'running');
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'API Test User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 100,
            'status' => 'active',
        ]);
    }

    private function createGame(string $name, string $slug, string $resultTime): Game
    {
        return Game::create([
            'name' => $name,
            'slug' => $slug,
            'result_time' => $resultTime,
            'play_start' => '10:00:00',
            'play_end' => '17:00:00',
            'last_result' => 'Wait',
            'status' => 'active',
            'serial' => 1,
            'reward' => 2,
        ]);
    }
}
