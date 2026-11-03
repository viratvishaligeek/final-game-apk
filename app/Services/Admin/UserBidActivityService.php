<?php

namespace App\Services\Admin;

use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Collection;

class UserBidActivityService
{
    /**
     * Return one summary for every individual bid submission.
     *
     * order_no is generated once per API submission and shared only by the
     * individual Bid rows created from that request. Game, type and timestamp
     * are deliberately not grouping dimensions.
     */
    public function submissionsFor(User $user): Collection
    {
        $submissions = $user->bids()
            ->selectRaw('order_no, COUNT(*) as total_bids, COALESCE(SUM(amount), 0) as total_amount, MIN(id) as representative_bid_id, MAX(id) as latest_bid_id, MIN(created_at) as submitted_at')
            ->groupBy('order_no')
            ->orderByDesc('latest_bid_id')
            ->get();

        $representativeBids = Bid::query()
            ->with('game:id,name')
            ->whereIn('id', $submissions->pluck('representative_bid_id'))
            ->get()
            ->keyBy('id');

        return $submissions->map(function ($submission) use ($representativeBids): object {
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
