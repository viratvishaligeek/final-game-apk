<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultAdminValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_publication_rejects_future_business_dates(): void
    {
        $admin = Admin::create([
            'name' => 'Result Admin',
            'email' => 'result-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $game = Game::create([
            'name' => 'Result Test Game',
            'slug' => 'result-test-game',
            'result_time' => '18:00:00',
            'play_start' => '10:00:00',
            'play_end' => '17:00:00',
            'last_result' => 'Wait',
            'status' => 'active',
            'serial' => 1,
            'reward' => 2,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.results.storeOrUpdate'), [
                'game_id' => $game->id,
                'game_date' => now()->addDay()->toDateString(),
                'result' => '12',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('game_date');

        $this->assertDatabaseMissing('results', [
            'game_id' => $game->id,
            'game_date' => now()->addDay()->toDateString(),
            'type' => 'jodi',
        ]);
    }

    public function test_winner_report_rejects_invalid_type_filters(): void
    {
        $admin = Admin::create([
            'name' => 'Winner Admin',
            'email' => 'winner-admin@example.com',
            'password' => 'Password123',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.winner.index', ['type' => 'unexpected']))
            ->assertRedirect()
            ->assertSessionHasErrors('type');
    }
}
