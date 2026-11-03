<?php

namespace App\Services\Admin;

use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Collection;

class UserBidActivityService
{
    /**
     * Return one activity per betting submission. Legacy empty or NULL order
     * identifiers are keyed by bid id so unrelated historical rows stay apart.
     */
    public function forUser(User $user): Collection
    {
        $submissionGroup = "CASE WHEN order_no IS NULL OR order_no = '' THEN CONCAT('legacy-', id) ELSE order_no END";

        $submissionStats = $user->bids()
            ->selectRaw("order_no, {$submissionGroup} as submission_group_key, COUNT(*) as total_bids, COALESCE(SUM(amount), 0) as total_amount, MIN(id) as representative_bid_id, MAX(id) as latest_bid_id, MIN(created_at) as submitted_at")
            ->groupByRaw($submissionGroup)
            ->groupBy('order_no')
            ->orderByDesc('latest_bid_id')
            ->get();

        if ($submissionStats->isEmpty()) {
            return collect();
        }

        $representativeBids = Bid::query()
            ->with('game:id,name')
            ->whereIn('id', $submissionStats->pluck('representative_bid_id'))
            ->get()
            ->keyBy('id');

        return $submissionStats->map(function ($submission) use ($representativeBids) {
            $representative = $representativeBids->get($submission->representative_bid_id);

            return (object) [
                'order_no' => $submission->order_no,
                'total_bids' => (int) $submission->total_bids,
                'total_amount' => (float) $submission->total_amount,
                'latest_bid_id' => (int) $submission->latest_bid_id,
                'submitted_at' => $submission->submitted_at,
                'game_id' => $representative?->game_id,
                'game_name' => $representative?->game?->name ?? 'Game Deleted',
                'type' => $representative?->type ?? 'unknown',
                'game_date' => $representative?->game_date,
            ];
        });
    }
}
