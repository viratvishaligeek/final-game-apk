<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class WinnerController extends Controller
{
    public function updatePublicDisplay(Request $request, Winner $winner): RedirectResponse
    {
        $validated = $request->validate([
            'is_public' => ['required', 'boolean'],
            'public_display_name' => ['nullable', 'string', 'max:60', 'required_if:is_public,1'],
        ]);

        $displayName = trim(strip_tags((string) ($validated['public_display_name'] ?? '')));
        if ((bool) $validated['is_public'] && $displayName === '') {
            return back()->withErrors(['public_display_name' => 'Enter an approved public display name before publishing this winner.']);
        }

        $winner->forceFill([
            'is_public' => (bool) $validated['is_public'],
            'public_display_name' => (bool) $validated['is_public'] ? $displayName : null,
        ])->save();

        return back()->with('success', 'Public winner display settings updated.');
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
            'type' => ['nullable', 'string', 'in:jodi,haruf'],
            'user' => ['nullable', 'string', 'max:100'],
        ]);

        $selectedDate = $validated['date']
            ?? now()->timezone(config('app.timezone'))->toDateString();
        $gameId = isset($validated['game_id']) ? (int) $validated['game_id'] : null;
        $type = $validated['type'] ?? null;
        $userSearch = trim($validated['user'] ?? '');

        $query = Winner::query()
            ->with([
                'user:id,name,phone',
                'game:id,name',
            ])
            ->whereDate('game_date', $selectedDate)
            ->latest('id');

        // Optional game filter
        if ($gameId !== null) {
            $query->where('game_id', $gameId);
        }

        // Optional winner type filter
        if ($type !== null) {
            $query->where('type', $type);
        }

        // Optional user search
        if ($userSearch !== '') {
            $query->whereHas('user', function ($userQuery) use ($userSearch) {
                $userQuery
                    ->where('name', 'like', "%{$userSearch}%")
                    ->orWhere('phone', 'like', "%{$userSearch}%");
            });
        }

        $winners = $query->paginate(10)->withQueryString();

        $games = Game::query()
            ->orderBy('serial')
            ->get(['id', 'name']);

        $totalWinners = Winner::query()
            ->whereDate('game_date', $selectedDate)
            ->when(
                $gameId !== null,
                fn ($query) => $query->where('game_id', $gameId)
            )
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type)
            )
            ->count();

        $totalWinningAmount = Winner::query()
            ->whereDate('game_date', $selectedDate)
            ->when(
                $gameId !== null,
                fn ($query) => $query->where('game_id', $gameId)
            )
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type)
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
