<?php

namespace App\Http\Controllers\Api;

use App\Events\UserPresenceChanged;
use App\Http\Controllers\Controller;
use App\Models\UserPresenceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:64'],
            'platform' => ['nullable', 'string', 'in:web,android,ios'],
            'app_state' => ['nullable', 'string', 'in:foreground,background'],
        ]);

        $user = $request->user();
        $session = UserPresenceSession::query()->firstOrNew([
            'session_id' => $validated['session_id'],
        ]);

        if ($session->exists && (int) $session->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Presence session does not belong to this account.',
            ], 409);
        }

        $now = now();
        $session->user_id = $user->id;
        $session->platform = $validated['platform'] ?? $session->platform;
        $session->app_state = $validated['app_state'] ?? 'foreground';

        // The client sends a heartbeat every 15 seconds; persist at most once per interval.
        $persistHeartbeat = !$session->exists
            || !$session->last_seen_at
            || $session->last_seen_at->lt($now->copy()->subSeconds(15));

        if (!$session->exists || $persistHeartbeat || $session->ended_at !== null) {
            $session->last_seen_at = $now;
            $session->ended_at = null;
            $session->save();
        }

        if ($user->live_status !== 'active') {
            $user->forceFill([
                'live_status' => 'active',
                'live_last_seen_at' => $now,
            ])->save();

            UserPresenceChanged::dispatch($user->id, 'active', $now->toIso8601String());
        } elseif ($persistHeartbeat) {
            $user->forceFill(['live_last_seen_at' => $now])->save();
        }

        return response()->json(['success' => true, 'live_status' => 'active']);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:64'],
        ]);
        $user = $request->user();

        $session = UserPresenceSession::query()
            ->where('user_id', $user->id)
            ->where('session_id', $validated['session_id'])
            ->first();

        if ($session) {
            $session->forceFill(['ended_at' => now()])->save();
        }

        $anotherSessionIsActive = UserPresenceSession::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('last_seen_at', '>=', now()->subSeconds(30))
            ->exists();

        if (!$anotherSessionIsActive && $user->live_status !== 'inactive') {
            $user->forceFill(['live_status' => 'inactive'])->save();
            UserPresenceChanged::dispatch(
                $user->id,
                'inactive',
                $user->live_last_seen_at?->toIso8601String()
            );
        }

        return response()->json(['success' => true]);
    }
}
