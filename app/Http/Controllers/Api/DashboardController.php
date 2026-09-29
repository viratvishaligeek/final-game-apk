<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Game;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class DashboardController extends Controller
{
    public function getDashboard(): JsonResponse
    {
        $banners = Banner::where('status', 'active')
            ->get(['id', 'name', 'image']);
        $games = Game::where('status', 'active')
            ->orderBy('serial', 'asc')
            ->get();
        $featuredGame = Game::where('status', 'active')
            ->latest()
            ->first() ?? $games->first();
        return response()->json([
            'status' => true,
            'message' => 'Data fetched successfully',
            'banners' => $banners,
            'featured_game' => $featuredGame,
            'games' => $games,
        ], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user'    => $user->name,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:200',
            'gender'     => 'nullable|in:male,female,other',
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:100',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
        ]);
        $user->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data'    => ['user' => $user->fresh()]
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => ['required', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password matches not in our records.'
            ], 422);
        }
        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }
     public function notificationList(Request $request)
    {
        $user = $request->user();

        $perPage = min(
            max((int) $request->input('per_page', 20), 1),
            100
        );

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->paginate($perPage);

        $data = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'subject' => $notification->subject,
                'message' => $notification->message,
                'created_at' => $notification->created_at?->toISOString(),
                'updated_at' => $notification->updated_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ],
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::query()
            ->where('user_id', $request->user()->id)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
