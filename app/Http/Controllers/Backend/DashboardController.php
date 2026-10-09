<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\Winner;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function dashboard(Request $request, DashboardService $dashboardService)
    {
        return view(
            'backend.dashboard',
            $dashboardService->getDashboardData($request)
        );
    }

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
