<?php

namespace App\Console\Commands;

use App\Events\UserPresenceChanged;
use App\Models\User;
use Illuminate\Console\Command;

class ExpireStaleUserPresence extends Command
{
    protected $signature = 'presence:expire';
    protected $description = 'Mark users inactive when all application sessions have timed out.';

    public function handle(): int
    {
        $staleBefore = now()->subSeconds(30);
        $count = 0;

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

        $this->info("Marked {$count} user(s) inactive.");
        return self::SUCCESS;
    }
}
