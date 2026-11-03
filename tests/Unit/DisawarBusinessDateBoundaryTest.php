<?php

namespace Tests\Unit;

use App\Models\Game;
use Carbon\Carbon;
use Tests\TestCase;

class DisawarBusinessDateBoundaryTest extends TestCase
{
    public function test_disawar_uses_previous_business_date_before_its_configured_result_time(): void
    {
        config(['app.timezone' => 'Asia/Kolkata']);

        $game = new Game([
            'name' => 'Disawar',
            'slug' => 'disawar',
            'result_time' => '02:00:00',
        ]);

        $beforeBoundary = Carbon::parse('2026-11-03 01:59:59', 'Asia/Kolkata');
        $atBoundary = Carbon::parse('2026-11-03 02:00:00', 'Asia/Kolkata');

        $this->assertSame('2026-11-02', $game->businessDate($beforeBoundary));
        $this->assertSame('2026-11-03', $game->businessDate($atBoundary));
    }

    public function test_non_disawar_games_keep_the_calendar_date(): void
    {
        config(['app.timezone' => 'Asia/Kolkata']);

        $game = new Game([
            'name' => 'Other Game',
            'slug' => 'other-game',
            'result_time' => '02:00:00',
        ]);

        $now = Carbon::parse('2026-11-03 01:00:00', 'Asia/Kolkata');

        $this->assertSame('2026-11-03', $game->businessDate($now));
    }
}
