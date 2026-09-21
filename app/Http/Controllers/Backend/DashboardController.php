<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // $totaluser = User::where('status', 1)->count();
        // $total_match = Game::where('status', 'active')->count();
        // $total_trans = Trans::count();
        // $today = Gamewiner::count();

        // $total_trans4 = Trans::where('status', 1)
        //     ->where('type', 'CREDIT')
        //     ->where('subject', 'LIKE', 'money%')
        //     ->sum('amount');

        // $total_trans5 = Withdrawal::where('status', 2)
        //     ->sum('amount');

        // $topPlayers = User::where('status', 1)
        //     ->orderByDesc('wallet')
        //     ->limit(10)
        //     ->get();

        return view(
            'backend.dashboard',
            // compact(
            //     // 'totaluser',
            //     // 'total_match',
            //     // 'total_trans',
            //     // 'today',
            //     // 'total_trans4',
            //     // 'total_trans5',
            //     // 'topPlayers'
            // )
        );
    }

    // public function totalWinner()
    // {
    //     $winners = GameWiner::orderBy('id', 'desc')->get();

    //     $winners = $winners->map(function ($winner) {
    //         $user = User::where('phone', $winner->phone)->first();
    //         $game = Game::find($winner->gameid);

    //         return [
    //             'username' => $user->name ?? 'N/A',
    //             'phone' => $user->phone ?? 'N/A',
    //             'game' => $game->name ?? 'N/A',
    //             'amount' => $winner->amount,
    //             'winamount' => $winner->winamount,
    //             'type' => $winner->type,
    //             'number' => $winner->number,
    //             'time' => $winner->time,
    //         ];
    //     });
    //     return view('backend.total_winner', compact('winners'));
    // }
}
