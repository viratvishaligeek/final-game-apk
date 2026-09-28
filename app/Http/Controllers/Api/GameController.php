<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $games = Game::query()
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->orderBy('serial')
            ->get()
            ->map(function ($game, $index) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'result_time' => $game->result_time,
                    'play_start' => $game->play_start,
                    'play_end' => $game->play_end,
                    'last_result' => $game->last_result,
                    'serial' => $game->serial,
                    'is_featured' => $index === 0,
                    'is_playable' => $game->is_playable,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $games,
        ]);
    }


    public function show(Game $game)
    {
        if ($game->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This game is currently inactive.',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'result_time' => $game->result_time,
                'play_start' => $game->play_start,
                'play_end' => $game->play_end,
                'last_result' => $game->last_result,
                'serial' => $game->serial,
                'is_playable' => $game->is_playable,
            ],
        ]);
    }
}
