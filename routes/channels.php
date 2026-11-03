<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.payment-alerts', function (\App\Models\Admin $admin) {
    return $admin->status === 'active';
}, ['guards' => ['admin']]);
