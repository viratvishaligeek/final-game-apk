<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ResultChartController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GuessingAndYearlyChartTest extends TestCase
{
    public function test_market_list_and_historical_chart_controllers_remain_routed(): void
    {
        $actions = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getActionName());

        $this->assertTrue($actions->contains(fn ($action) => str_contains($action, GameController::class)));
        $this->assertTrue($actions->contains(fn ($action) => str_contains($action, ResultChartController::class)));
        $this->assertTrue(method_exists(GameController::class, 'index'));
        $this->assertTrue(method_exists(GameController::class, 'show'));
        $this->assertTrue(method_exists(ResultChartController::class, 'index'));
    }
}
