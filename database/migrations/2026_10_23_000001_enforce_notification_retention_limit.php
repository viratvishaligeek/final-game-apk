<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the newest ten rows for each user before adding the retrieval index.
        DB::table('notifications')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id')
            ->each(function ($userId): void {
                $oldIds = DB::table('notifications')
                    ->where('user_id', $userId)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->offset(10)
                    ->limit(1000000)
                    ->pluck('id');

                if ($oldIds->isNotEmpty()) {
                    DB::table('notifications')
                        ->where('user_id', $userId)
                        ->whereIn('id', $oldIds)
                        ->delete();
                }
            });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at', 'id'], 'notifications_user_created_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex('notifications_user_created_id_idx');
        });
    }
};
