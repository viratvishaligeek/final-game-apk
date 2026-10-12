<?php

namespace App\Services;

/**
 * Stable notification categories for clients that choose event-specific audio.
 * Only known bundled assets are advertised; missing MP3s are not guessed.
 */
class NotificationSoundMap
{
    public function forEvent(string $event): array
    {
        $asset = match (strtolower($event)) {
            'add_money' => 'notification_tune.wav',
            'withdraw' => 'notification_tune.wav',
            'debit' => 'notification_tune.wav',
            'credit' => 'notification_tune.wav',
            'winning' => 'notification_tune.wav',
            'result' => 'notification_tune.wav',
            default => 'notification_tune.wav',
        };

        return ['event' => strtolower($event), 'asset' => $asset];
    }
}
