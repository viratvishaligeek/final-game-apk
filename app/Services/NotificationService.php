<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function create(
        User $user,
        string $subject,
        string $message
    ): void {
        Notification::create([
            'user_id'    => $user->id ?? null,
            'phone'      => $user->phone ?? null,
            'subject'    => $subject,
            'message'    => $message,
        ]);
    }
}
