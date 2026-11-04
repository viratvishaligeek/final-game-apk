<?php

namespace App\Services\Presence;

use App\Events\UserPresenceChanged;
use App\Models\User;

class UserPresenceManager
{
    /**
     * Reconcile the account's status against every device/browser session.
     * Background sessions receive a short grace period; idle and ended
     * sessions never keep the account online.
     */
    public function sync(User $user, ?\Illuminate\Support\Carbon $now = null): string
    {
        $now ??= now();
        $foregroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.heartbeat_timeout_seconds', 45))
        );
        $backgroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.background_grace_seconds', 90))
        );

        $hasActiveSession = $user->presenceSessions()
            ->whereNull('ended_at')
            ->where(function ($query) use ($foregroundCutoff, $backgroundCutoff) {
                $query->where(function ($foreground) use ($foregroundCutoff) {
                    $foreground->where('app_state', 'foreground')
                        ->where('last_seen_at', '>=', $foregroundCutoff);
                })->orWhere(function ($background) use ($backgroundCutoff) {
                    $background->where('app_state', 'background')
                        ->where('last_seen_at', '>=', $backgroundCutoff);
                });
            })
            ->exists();

        $nextStatus = $hasActiveSession ? 'active' : 'inactive';

        if ($user->live_status !== $nextStatus) {
            $user->forceFill(['live_status' => $nextStatus])->save();

            UserPresenceChanged::dispatch(
                $user->id,
                $nextStatus,
                $user->live_last_seen_at?->toIso8601String()
            );
        }

        return $nextStatus;
    }
}
