<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessDateDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_public_results_page_uses_disawar_business_date_only_for_disawar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 03:00:00', config('app.timezone')));

        $disawar = $this->createGame('Disawar', 'disawar', '07:00:00');
        $other = $this->createGame('Delhi Bazar', 'delhi-bazar', '15:00:00');

        $this->createResult($disawar, '2026-10-08', '12');
        $this->createResult($disawar, '2026-10-07', '34');
        $this->createResult($disawar, '2026-10-09', '56');

        $this->createResult($other, '2026-10-09', '78');
        $this->createResult($other, '2026-10-08', '90');

        $this->get('/')
            ->assertOk()
            ->assertSee('<td class="qr-val">12</td>', false)
            ->assertSee('<td class="qr-val">34</td>', false)
            ->assertSee('<td class="qr-val">78</td>', false)
            ->assertSee('<td class="qr-val">90</td>', false);
    }

    public function test_admin_dashboard_totals_follow_each_games_business_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 03:00:00', config('app.timezone')));

        $admin = Admin::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Dashboard User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 100,
            'status' => 'active',
        ]);

        $disawar = $this->createGame('Disawar', 'disawar', '07:00:00');
        $other = $this->createGame('Delhi Bazar', 'delhi-bazar', '15:00:00');

        Bid::create([
            'order_no' => 'DISAWAR-1',
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
            'order_no' => 'DELHI-1',
            'user_id' => $user->id,
            'game_id' => $other->id,
            'game_date' => '2026-10-09',
            'type' => 'jodi',
            'number' => '34',
            'amount' => 20,
            'status' => 'pending',
            'winning_amount' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('todayTotalBids', 2)
            ->assertViewHas('todayBidAmount', 30.0);
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

    private function createResult(Game $game, string $date, string $number): void
    {
        Result::create([
            'game_id' => $game->id,
            'game_date' => $date,
            'type' => 'jodi',
            'number' => $number,
        ]);
    }
}
