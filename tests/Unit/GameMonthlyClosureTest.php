<?php

namespace Tests\Unit;

use App\Models\Game;
use Carbon\Carbon;
use Tests\TestCase;

class GameMonthlyClosureTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_month_end_closure_uses_the_actual_calendar_month_length(): void
    {
        $timezone = config('app.timezone');

        $cases = [
            ['2025-02-28 12:00:00', true],  // February, non-leap year
            ['2024-02-29 12:00:00', true],  // February, leap year
            ['2025-04-30 12:00:00', true],  // 30-day month
            ['2025-01-31 12:00:00', true],  // 31-day month
            ['2025-05-01 12:00:00', false], // First day resumes normal schedule
        ];

        $game = new Game();
        $game->forceFill([
            'name' => 'Test Market',
            'slug' => 'test-market',
            'status' => 'active',
            'play_start' => '00:00:00',
            'play_end' => '23:59:59',
        ]);

        foreach ($cases as [$dateTime, $shouldClose]) {
            $now = Carbon::parse($dateTime, $timezone);
            Carbon::setTestNow($now);

            $this->assertSame(
                $shouldClose,
                $game->isMonthlyClosureDate($now),
                'Unexpected closure decision for ' . $dateTime
            );

            $this->assertSame(
                !$shouldClose,
                $game->isPlayableAt($now),
                'Unexpected playability decision for ' . $dateTime
            );

        }
    }

    public function test_month_end_check_uses_application_timezone_not_disawar_business_date(): void
    {
        $timezone = config('app.timezone');
        $localLastDay = Carbon::parse('2025-04-30 23:30:00', $timezone);
        $utcInstant = $localLastDay->copy()->utc();

        $game = new Game();
        $game->forceFill([
            'name' => 'Disawar',
            'slug' => 'disawar',
            'result_time' => '05:00:00',
            'status' => 'active',
            'play_start' => '00:00:00',
            'play_end' => '23:59:59',
        ]);

        $this->assertTrue($game->isMonthlyClosureDate($utcInstant));
        $this->assertFalse($game->isPlayableAt($utcInstant));
    }
}
