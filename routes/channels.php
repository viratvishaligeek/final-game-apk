<?php

use App\Services\Presence\UserPresenceChannelAuthorizer;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

$authorizeAdminSiteChannel = static function (\App\Models\Admin $admin, string $siteKey): bool {
    $currentSiteKey = (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'));

    return $admin->status === 'active' && hash_equals($currentSiteKey, $siteKey);
};

Broadcast::channel('admin.payment-alerts.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);
Broadcast::channel('admin.user-presence.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);
Broadcast::channel('admin.support-chat.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);

Broadcast::channel('users.{id}', function (\App\Models\User $user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

/*
 * A per-account presence channel avoids a site-wide player roster. The player
 * can join only their own channel; an active Admin can inspect only users
 * visible in the currently selected site scope. Presence member metadata
 * contains role-only labels, never names, phone numbers, or wallet data.
 */
Broadcast::channel('user-presence.{id}', function ($principal, $id) {
    return app(UserPresenceChannelAuthorizer::class)->authorize($principal, $id);
}, ['guards' => ['sanctum', 'admin']]);

        return $targetUser
            ? ['id' => 'admin-' . $principal->id, 'type' => 'admin']
            : false;
    }

    return false;
}, ['guards' => ['sanctum', 'admin']]);
