<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function create(User $user, string $subject, string $message): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'message' => $message,
        ]);
    }
}
