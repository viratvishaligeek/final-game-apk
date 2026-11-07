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
        $now = ($now ?? now())->copy()->utc();

        $foregroundLeaseCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.heartbeat_timeout_seconds', 45))
        );
        $idleCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.idle_timeout_seconds', 300))
        );
        $backgroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.background_grace_seconds', 90))
        );
        \Log::info('TIMEZONE DEBUG', [
            'app_timezone' => config('app.timezone'),
            'php_timezone' => date_default_timezone_get(),
            'carbon_now' => now()->toIso8601String(),
            'carbon_utc' => now('UTC')->toIso8601String(),
        ]);
        $hasActiveSession = $user->presenceSessions()
            ->whereNull('ended_at')
            ->where(function ($query) use ($foregroundLeaseCutoff, $idleCutoff, $backgroundCutoff) {
                $query->where(function ($foreground) use ($foregroundLeaseCutoff, $idleCutoff) {
                    // updated_at is the heartbeat lease; last_seen_at is actual
                    // user activity, so an app that keeps heartbeating while
                    // idle still expires after the configured idle timeout.
                    $foreground->where('app_state', 'foreground')
                        ->where('updated_at', '>=', $foregroundLeaseCutoff)
                        ->where('last_seen_at', '>=', $idleCutoff);
                })->orWhere(function ($background) use ($backgroundCutoff) {
                    $background->where('app_state', 'background')
                        ->where('updated_at', '>=', $backgroundCutoff);
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
