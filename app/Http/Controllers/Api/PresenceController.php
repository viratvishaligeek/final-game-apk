<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPresenceSession;
use App\Services\Presence\UserPresenceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    public function heartbeat(Request $request, UserPresenceManager $presence): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:64'],
            'platform' => ['nullable', 'string', 'in:web,android,ios'],
            'app_state' => ['nullable', 'string', 'in:foreground,background,idle'],
        ]);

        $userId = (int) $request->user()->id;
        $now = now();

        $result = DB::transaction(function () use ($validated, $userId, $now, $presence) {
            // Serialize status reconciliation per account so simultaneous
            // heartbeats/disconnects from different devices cannot race.
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $session = UserPresenceSession::query()
                ->where('session_id', $validated['session_id'])
                ->lockForUpdate()
                ->first();

            if ($session && (int) $session->user_id !== $userId) {
                return null;
            }

            $session ??= new UserPresenceSession([
                'user_id' => $userId,
                'session_id' => $validated['session_id'],
            ]);

            $appState = $validated['app_state'] ?? 'foreground';
            $session->user_id = $userId;
            $session->platform = $validated['platform'] ?? $session->platform;
            $session->app_state = $appState;
            $session->last_seen_at = $now;
            // An idle client relinquishes its lease. Its next interaction
            // reactivates the same session with a foreground heartbeat.
            $session->ended_at = $appState === 'idle' ? $now : null;
            $session->save();

            $user->forceFill(['live_last_seen_at' => $now])->save();
            $status = $presence->sync($user, $now);

            return $status;
        });

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Presence session does not belong to this account.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'live_status' => $result,
            'idle_timeout_seconds' => max(30, (int) config('presence.idle_timeout_seconds', 300)),
        ]);
    }

    public function disconnect(Request $request, UserPresenceManager $presence): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:64'],
        ]);

        $userId = (int) $request->user()->id;

        DB::transaction(function () use ($validated, $userId, $presence) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $session = UserPresenceSession::query()
                ->where('user_id', $userId)
                ->where('session_id', $validated['session_id'])
                ->lockForUpdate()
                ->first();

            if ($session) {
                $session->forceFill(['ended_at' => now()])->save();
                $presence->sync($user);
            }
        });

        return response()->json(['success' => true]);
    }
}
