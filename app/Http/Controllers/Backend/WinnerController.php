<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WinnerController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input(
            'date',
            Carbon::today()->toDateString()
        );

        $query = Winner::query()
            ->with([
                'user:id,name,phone',
                'game:id,name',
            ])
            ->whereDate('game_date', $selectedDate)
            ->latest('id');

        // Optional game filter
        if ($request->filled('game_id')) {
            $query->where('game_id', $request->integer('game_id'));
        }

        // Optional winner type filter
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Optional user search
        if ($request->filled('user')) {
            $search = trim($request->input('user'));

            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $winners = $query->paginate(10)->withQueryString();

        $games = Game::query()
            ->orderBy('serial')
            ->get(['id', 'name']);

        $totalWinners = Winner::query()
            ->whereDate('game_date', $selectedDate)
            ->when(
                $request->filled('game_id'),
                fn($query) =>
                $query->where('game_id', $request->integer('game_id'))
            )
            ->when(
                $request->filled('type'),
                fn($query) =>
                $query->where('type', $request->input('type'))
            )
            ->count();

        $totalWinningAmount = Winner::query()
            ->whereDate('game_date', $selectedDate)
            ->when(
                $request->filled('game_id'),
                fn($query) =>
                $query->where('game_id', $request->integer('game_id'))
            )
            ->when(
                $request->filled('type'),
                fn($query) =>
                $query->where('type', $request->input('type'))
            )
            ->sum('winning_amount');

        $pageName = 'Winner list';
        return view('backend.winner', compact(
            'pageName',
            'winners',
            'games',
            'selectedDate',
            'totalWinners',
            'totalWinningAmount'
        ));
    }
}
