<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::query()
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'image']);

        $games = Game::query()
            ->where('status', 'active')
            ->with('latestResult')
            ->orderBy('serial', 'asc')
            ->get();

        $games = $games->map(function (Game $game) {
            $latestResult = $game->latestResult;
            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'play_start' => $game->play_start,
                'play_end' => $game->play_end,
                'result_time' => $game->result_time,
                'last_result' => $latestResult?->number
                    ?? $game->last_result
                    ?? null,
                'last_result_date' => $latestResult?->number_date?->format('Y-m-d'),
                'last_result_type' => $latestResult?->type,
                'is_playable' => $game->is_playable,
                'status' => $game->status,
                'serial' => $game->serial,
            ];
        });
        $featuredGame = $games
            ->sortByDesc(function ($game) {
                if (!$game['last_result_date']) {
                    return '0000-00-00 00:00:00';
                }
                return $game['last_result_date']
                    . ' '
                    . ($game['result_time'] ?? '00:00:00');
            })
            ->first();
        if (!$featuredGame) {
            $featuredGame = $games->first();
        }
        $marketGames = $games
            ->filter(function ($game) use ($featuredGame) {
                return !$featuredGame
                    || $game['id'] !== $featuredGame['id'];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Data fetched successfully',
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'banners' => $banners,
            'featured_game' => $featuredGame,
            'games' => $marketGames,
        ], 200);
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
