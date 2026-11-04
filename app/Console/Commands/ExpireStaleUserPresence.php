<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Presence\UserPresenceManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStaleUserPresence extends Command
{
    protected $signature = 'presence:expire';
    protected $description = 'Mark users inactive when all foreground/background presence leases have expired.';

    public function handle(UserPresenceManager $presence): int
    {
        $originalSiteKey = config('sites.current_key');
        $siteKeys = DB::table('users')->select('site_key')->distinct()->pluck('site_key')->filter();
        $now = now();
        $foregroundLeaseCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.heartbeat_timeout_seconds', 45))
        );
        $idleCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.idle_timeout_seconds', 300))
        );
        $backgroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.background_grace_seconds', 90))
        );
        $count = 0;

        foreach ($siteKeys as $siteKey) {
            config(['sites.current_key' => $siteKey]);

            User::query()
                ->where('live_status', 'active')
                ->whereDoesntHave('presenceSessions', function ($query) use ($foregroundLeaseCutoff, $idleCutoff, $backgroundCutoff) {
                    $query->whereNull('ended_at')
                        ->where(function ($active) use ($foregroundLeaseCutoff, $idleCutoff, $backgroundCutoff) {
                            $active->where(function ($foreground) use ($foregroundLeaseCutoff, $idleCutoff) {
                                $foreground->where('app_state', 'foreground')
                                    ->where('updated_at', '>=', $foregroundLeaseCutoff)
                                    ->where('last_seen_at', '>=', $idleCutoff);
                            })->orWhere(function ($background) use ($backgroundCutoff) {
                                $background->where('app_state', 'background')
                                    ->where('updated_at', '>=', $backgroundCutoff);
                            });
                        });
                })
                ->chunkById(200, function ($users) use (&$count, $presence) {
                    foreach ($users as $user) {
                        // Re-check under the same account lock used by heartbeat
                        // and disconnect to avoid an expiry/heartbeat race.
                        DB::transaction(function () use ($user, $presence, &$count) {
                            $lockedUser = User::query()->lockForUpdate()->find($user->id);
                            if (!$lockedUser || $lockedUser->live_status !== 'active') {
                                return;
                            }

                            if ($presence->sync($lockedUser) === 'inactive') {
                                $count++;
                            }
                        });
                    }
                });
        }

        config(['sites.current_key' => $originalSiteKey ?? config('sites.default', 'play-online-khaiwal')]);

        $this->info("Marked {$count} user(s) inactive.");
        return self::SUCCESS;
    }
}
