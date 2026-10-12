<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneUserNotifications extends Command
{
    protected $signature = 'notifications:prune {--dry-run : Report old records without deleting them}';

    protected $description = 'Retain only the latest ten private notification records per user';

    public function handle(): int
    {
        $userIds = DB::table('notifications')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 10')
            ->pluck('user_id');

        $deleted = 0;
        foreach ($userIds as $userId) {
            $oldIds = Notification::withoutGlobalScope('site')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->offset(10)
                ->limit(PHP_INT_MAX)
                ->pluck('id');

            if ($oldIds->isEmpty()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $deleted += $oldIds->count();
                continue;
            }

            $deleted += Notification::withoutGlobalScope('site')
                ->where('user_id', $userId)
                ->whereIn('id', $oldIds)
                ->delete();
        }

        $this->info(($this->option('dry-run') ? 'Would delete ' : 'Deleted ') . $deleted . ' older notification record(s).');

        return self::SUCCESS;
    }
}
