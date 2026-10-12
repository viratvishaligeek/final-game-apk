<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ResultChartController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GuessingAndYearlyChartTest extends TestCase
{
    public function test_market_list_and_historical_chart_routes_remain_registered(): void
    {
        $uris = collect(Route::getRoutes()->getRoutes())->map(fn ($route) => $route->uri());

        $this->assertTrue($uris->contains('api/v1/games/list'));
        $this->assertTrue($uris->contains('api/v1/result-chart/index'));
        $this->assertTrue(method_exists(GameController::class, 'index'));
        $this->assertTrue(method_exists(GameController::class, 'show'));
        $this->assertTrue(method_exists(ResultChartController::class, 'index'));
    }
}
