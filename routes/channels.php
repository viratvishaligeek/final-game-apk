<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

$authorizeAdminSiteChannel = static function (\\App\\Models\\Admin $admin, string $siteKey): bool {
    $currentSiteKey = (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'));

    return $admin->status === 'active' && hash_equals($currentSiteKey, $siteKey);
};

Broadcast::channel('admin.payment-alerts.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);
Broadcast::channel('admin.user-presence.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);
Broadcast::channel('admin.support-chat.{siteKey}', $authorizeAdminSiteChannel, ['guards' => ['admin']]);

Broadcast::channel('users.{id}', function (\App\Models\User $user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);
