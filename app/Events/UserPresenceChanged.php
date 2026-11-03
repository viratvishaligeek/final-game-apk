<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $status,
        public ?string $lastSeenAt = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.user-presence')];
    }

    public function broadcastAs(): string
    {
        return 'presence.changed';
    }

    public function broadcastWith(): array
    {
        $user = User::query()->find($this->userId);

        return [
            'user_id' => $this->userId,
            'user_name' => $user?->name,
            'phone' => $user?->phone,
            'live_status' => $this->status,
            'last_seen_at' => $this->lastSeenAt,
        ];
    }
}
