<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetAndGameValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bets_reject_amounts_with_more_than_two_decimal_places(): void
    {
        $user = User::create([
            'name' => 'Bet Test User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 100,
            'status' => 'active',
        ]);

        $now = now()->timezone(config('app.timezone'));
        $game = Game::create([
            'name' => 'Bet Test Game',
            'slug' => 'bet-test-game',
            'result_time' => $now->copy()->addMinutes(30)->format('H:i:s'),
            'play_start' => $now->copy()->subHour()->format('H:i:s'),
            'play_end' => $now->copy()->addHour()->format('H:i:s'),
            'last_result' => 'Wait',
            'status' => 'active',
            'serial' => 1,
            'reward' => 98,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/games/{$game->id}/bids", [
                'mode' => 'single',
                'total_amount' => '10.00',
                'total_bets' => 1,
                'single_bets' => [
                    '12' => '10.001',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('single_bets.12');

        $this->assertEquals(100.0, (float) $user->fresh()->balance);
    }

    public function test_admin_cannot_create_a_game_with_an_invalid_time(): void
    {
        $admin = Admin::create([
            'name' => 'Game Admin',
            'email' => 'game-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.games.store'), [
                'name' => 'Invalid Time Game',
                'result_time' => 'not-a-time',
                'play_start' => '10:00',
                'play_end' => '11:00',
                'status' => 'active',
                'serial' => 1,
                'reward' => '98.00',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('result_time');

        $this->assertDatabaseMissing('games', [
            'name' => 'Invalid Time Game',
        ]);
    }
}
