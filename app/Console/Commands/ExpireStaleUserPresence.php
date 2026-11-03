<?php

namespace App\Console\Commands;

use App\Events\UserPresenceChanged;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStaleUserPresence extends Command
{
    protected $signature = 'presence:expire';
    protected $description = 'Mark users inactive when all their application sessions have timed out.';

    public function handle(): int
    {
        $originalSiteKey = config('sites.current_key');
        $siteKeys = DB::table('users')->select('site_key')->distinct()->pluck('site_key')->filter();
        $count = 0;
        $staleBefore = now()->subSeconds(30);

        foreach ($siteKeys as $siteKey) {
            config(['sites.current_key' => $siteKey]);

            User::query()
                ->where('live_status', 'active')
                ->whereDoesntHave('presenceSessions', function ($query) use ($staleBefore) {
                    $query->whereNull('ended_at')
                        ->where('last_seen_at', '>=', $staleBefore);
                })
                ->chunkById(200, function ($users) use (&$count) {
                    foreach ($users as $user) {
                        $user->forceFill(['live_status' => 'inactive'])->save();
                        UserPresenceChanged::dispatch(
                            $user->id,
                            'inactive',
                            $user->live_last_seen_at?->toIso8601String()
                        );
                        $count++;
                    }
                });
        }

        config(['sites.current_key' => $originalSiteKey ?? config('sites.default', 'play-online-khaiwal')]);

        $this->info("Marked {$count} user(s) inactive.");
        return self::SUCCESS;
    }
}
