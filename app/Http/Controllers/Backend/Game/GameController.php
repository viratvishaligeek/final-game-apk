<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function index()
    {
        $pageName = 'Games List';
        $games = Game::orderBy('serial', 'asc')->get();
        return view('backend.games.index', compact('pageName', 'games'));
    }

    public function create()
    {
        $pageName = 'Add Game';
        return view('backend.games.create', compact('pageName'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:games,name',
            'result_time' => ['required', 'date_format:H:i,H:i:s'],
            'play_start'  => ['required', 'date_format:H:i,H:i:s'],
            'play_end'    => ['required', 'date_format:H:i,H:i:s'],
            'status'      => 'required|in:active,inactive',
            'serial'      => 'required|integer|min:1',
            'reward' => 'required|numeric|min:1|decimal:0,2',
        ]);

        $slug = Str::slug($request->name);

        if ($slug === '' || Game::withTrashed()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['This game name produces an empty or already-used slug. Choose a different name.'],
            ]);
        }

        Game::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'result_time' => Carbon::parse($request->result_time)->format('H:i:s'),
            'play_start'  => Carbon::parse($request->play_start)->format('H:i:s'),
            'play_end'    => Carbon::parse($request->play_end)->format('H:i:s'),
            'status'      => $request->status,
            'serial'      => $request->serial,
            'reward'      => $request->reward,

        ]);

        return redirect()->route('admin.games.index')->with('success', 'New game added successfully.');
    }

    public function edit(string $id)
    {
        $pageName = 'Edit Game';
        $game = Game::findOrFail($id);
        return view('backend.games.edit', compact('pageName', 'game'));
    }

    public function update(Request $request, string $id)
    {
        $game = Game::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255|unique:games,name,' . $game->id,
            'result_time' => ['required', 'date_format:H:i,H:i:s'],
            'play_start'  => ['required', 'date_format:H:i,H:i:s'],
            'play_end'    => ['required', 'date_format:H:i,H:i:s'],
            'status'      => 'required|in:active,inactive',
            'serial'      => 'required|integer|min:1',
            'reward' => 'required|numeric|min:1|decimal:0,2',
        ]);

        $slug = Str::slug($request->name);

        if (
            $slug === ''
            || Game::withTrashed()
                ->where('slug', $slug)
                ->where('id', '!=', $game->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => ['This game name produces an empty or already-used slug. Choose a different name.'],
            ]);
        }

        $game->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'result_time' => Carbon::parse($request->result_time)->format('H:i:s'),
            'play_start'  => Carbon::parse($request->play_start)->format('H:i:s'),
            'play_end'    => Carbon::parse($request->play_end)->format('H:i:s'),
            'status'      => $request->status,
            'serial'      => $request->serial,
            'reward'      => $request->reward,
        ]);

        return redirect()->route('admin.games.index')->with('success', 'Game updated successfully.');
    }

    public function destroy(string $id)
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return redirect()->back()->with('success', 'Game archived. Historical results and bids have been retained.');
    }
}
