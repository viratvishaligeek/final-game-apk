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
        $this->assertStringContainsString('Authenticate:sanctum', implode('|', $games->gatherMiddleware()));
        $this->assertStringContainsString('Authenticate:sanctum', implode('|', $chart->gatherMiddleware()));
    }

    public function test_game_controller_keeps_its_public_api_methods(): void
    {
        $this->assertTrue(method_exists(GameController::class, 'index'));
        $this->assertTrue(method_exists(GameController::class, 'show'));
    }
}
