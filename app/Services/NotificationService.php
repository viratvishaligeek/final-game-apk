<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;


class NotificationService
{
    public function create(
        User $user,
        string $subject,
        string $message
    ): Notification {
        $notification = $user->notifications()->create([
            'subject' => $subject,
            'message' => $message,
        ]);

        try {
            app(PushNotificationService::class)->sendToUser($user, $subject, $message);
        } catch (Throwable $exception) {
            // Push delivery must not interrupt wallet/game transactions.
            Log::warning('Unable to dispatch user push notification.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $notification;
    }
}
