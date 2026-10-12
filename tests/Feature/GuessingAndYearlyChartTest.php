<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ResultChartController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GuessingAndYearlyChartTest extends TestCase
{
    public function test_market_list_and_historical_chart_api_routes_remain_registered(): void
    {
        $games = Route::getRoutes()->getByAction(GameController::class . '@index');
        $chart = Route::getRoutes()->getByAction(ResultChartController::class . '@index');

        $this->assertNotNull($games);
        $this->assertNotNull($chart);
        $this->assertContains('auth:sanctum', $games->gatherMiddleware());
        $this->assertContains('auth:sanctum', $chart->gatherMiddleware());
    }

    public function test_game_controller_uses_the_existing_playability_contract(): void
    {
        $this->assertTrue(method_exists(GameController::class, 'index'));
        $this->assertTrue(method_exists(GameController::class, 'show'));
    }
}
