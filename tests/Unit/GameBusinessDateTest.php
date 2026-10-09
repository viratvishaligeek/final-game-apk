<?php

namespace Tests\Unit;

use App\Models\Game;
use Carbon\Carbon;
use Tests\TestCase;

class GameBusinessDateTest extends TestCase
{
    public function test_disawar_uses_the_previous_date_before_result_time(): void
    {
        $game = new Game([
            'name' => 'Disawar',
            'slug' => 'disawar',
            'result_time' => '07:00:00',
        ]);

        $now = Carbon::parse('2026-10-09 06:59:59', config('app.timezone'));

        $this->assertSame('2026-10-08', $game->businessDate($now));
    }

    public function test_disawar_uses_the_current_date_at_result_time(): void
    {
        $game = new Game([
            'name' => 'Disawar',
            'slug' => 'disawar',
            'result_time' => '07:00',
        ]);

        $now = Carbon::parse('2026-10-09 07:00:00', config('app.timezone'));

        $this->assertSame('2026-10-09', $game->businessDate($now));
    }

    public function test_other_games_keep_the_calendar_date(): void
    {
        $game = new Game([
            'name' => 'Delhi Bazar',
            'slug' => 'delhi-bazar',
            'result_time' => '07:00:00',
        ]);

        $now = Carbon::parse('2026-10-09 02:00:00', config('app.timezone'));

        $this->assertSame('2026-10-09', $game->businessDate($now));
    }
}
