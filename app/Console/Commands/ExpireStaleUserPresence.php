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
        $foregroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.heartbeat_timeout_seconds', 45))
        );
        $backgroundCutoff = $now->copy()->subSeconds(
            max(1, (int) config('presence.background_grace_seconds', 90))
        );
        $count = 0;

        foreach ($siteKeys as $siteKey) {
            config(['sites.current_key' => $siteKey]);

            User::query()
                ->where('live_status', 'active')
                ->whereDoesntHave('presenceSessions', function ($query) use ($foregroundCutoff, $backgroundCutoff) {
                    $query->whereNull('ended_at')
                        ->where(function ($active) use ($foregroundCutoff, $backgroundCutoff) {
                            $active->where(function ($foreground) use ($foregroundCutoff) {
                                $foreground->where('app_state', 'foreground')
                                    ->where('last_seen_at', '>=', $foregroundCutoff);
                            })->orWhere(function ($background) use ($backgroundCutoff) {
                                $background->where('app_state', 'background')
                                    ->where('last_seen_at', '>=', $backgroundCutoff);
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
