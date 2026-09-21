<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'result_time' => 'required',
            'play_start'  => 'required',
            'play_end'    => 'required',
            'status'      => 'required|in:active,inactive',
            'serial'      => 'required|integer|min:1',
        ]);

        Game::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'result_time' => Carbon::parse($request->result_time)->format('h:i A'),
            'play_start'  => Carbon::parse($request->play_start)->format('h:i A'),
            'play_end'    => Carbon::parse($request->play_end)->format('h:i A'),
            'status'      => $request->status,
            'serial'      => $request->serial,
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
            'result_time' => 'required',
            'play_start'  => 'required',
            'play_end'    => 'required',
            'status'      => 'required|in:active,inactive',
            'serial'      => 'required|integer|min:1',
        ]);

        $game->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'result_time' => Carbon::parse($request->result_time)->format('h:i A'),
            'play_start'  => Carbon::parse($request->play_start)->format('h:i A'),
            'play_end'    => Carbon::parse($request->play_end)->format('h:i A'),
            'status'      => $request->status,
            'serial'      => $request->serial,
        ]);

        return redirect()->route('admin.games.index')->with('success', 'Game updated successfully.');
    }

    public function destroy(string $id)
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return redirect()->back()->with('success', 'Game deleted along with its results.');
    }

    // public function viewBid(Request $request, $id)
    // {
    //     $gameId = $id;
    //     $type = $request->query('type');
    //     $date = $request->query('time') ? date('Y-m-d', strtotime($request->query('time'))) : date('Y-m-d');
    //     $bids = Bid::where('gameid', $gameId)
    //         ->where('time', $date)
    //         ->where('type', $type)
    //         ->get();

    //     $game = Game::find($gameId);

    //     $bidsData = $bids->map(function ($bid) use ($game) {
    //         $user = User::where('phone', $bid->phone)->where('status', 1)->first();
    //         return [
    //             'username' => $user->name ?? 'Unknown',
    //             'mobile' => $user->phone ?? '-',
    //             'game' => $game->name ?? '-',
    //             'amount' => $bid->amount,
    //             'number' => $bid->number,
    //             'value' => $bid->value,
    //             'time' => $bid->time,
    //             'date' => $bid->date
    //         ];
    //     });
    //     return view('backend.view_bid', compact('bidsData', 'gameId', 'type', 'date'));
    // }

    // public function viewWinner(Request $request, $id)
    // {
    //     $gameId = $id;
    //     $date = $request->query('time') ? date('Y-m-d', strtotime($request->query('time'))) : date('Y-m-d');

    //     $winners = Gamewiner::where('gameid', $gameId)
    //         ->where('time', $date)
    //         ->get();

    //     $game = Game::find($gameId);

    //     $winnerData = $winners->map(function ($winner) use ($game) {
    //         $user = User::where('phone', $winner->phone)->first();
    //         return [
    //             'username' => $user->name ?? 'Unknown',
    //             'mobile' => $user->phone ?? '-',
    //             'game' => $game->name ?? '-',
    //             'number' => $winner->number,
    //             'amount' => $winner->amount,
    //             'winamount' => $winner->winamount,
    //             'type' => $winner->type,
    //             'time' => $winner->time
    //         ];
    //     });

    //     return view('backend.view_winner', compact('winnerData', 'gameId', 'date'));
    // }
}
