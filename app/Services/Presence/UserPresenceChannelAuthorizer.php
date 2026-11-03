<?php

namespace App\Services\Presence;

use App\Models\Admin;
use App\Models\User;

class UserPresenceChannelAuthorizer
{
    /**
     * Authorize a player for their own channel or an active same-site Admin.
     *
     * The returned presence member information deliberately contains no
     * personally identifying or wallet data.
     */
    public function authorize(mixed $principal, int|string $userId): array|false
    {
        if ($principal instanceof User) {
            return (int) $principal->id === (int) $userId
                ? ['id' => 'player-' . $principal->id, 'type' => 'player']
                : false;
        }

        if ($principal instanceof Admin && $principal->status === 'active') {
            // User's site global scope is applied using the selected site key.
            $targetUser = User::query()->find($userId);

            return $targetUser
                ? ['id' => 'admin-' . $principal->id, 'type' => 'admin']
                : false;
        }

        return false;
    }
}
