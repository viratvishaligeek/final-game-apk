<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
    public function profile()
    {
        $admin = auth('admin')->user();
        $pageName = 'Admin Profile Settings';
        return view('backend.profile', compact('admin', 'pageName'));
    }

    /**
     * Update basic profile information.
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
        ]);
        $admin->update($validated);
        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->with('error', 'The provided current password does not match our records.');
        }
        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
