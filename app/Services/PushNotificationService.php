<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushNotificationService
{
    public function register(string $token, string $platform, ?User $user = null): PushSubscription
    {
        return PushSubscription::query()->updateOrCreate(
            ['token_hash' => hash('sha256', $token)],
            ['token' => $token, 'user_id' => $user?->id, 'platform' => $platform]
        );
    }

    public function sendToUser(User $user, string $title, string $body): void
    {
        $this->sendToSubscriptions(
            PushSubscription::query()->where('user_id', $user->id)->get(),
            $title,
            $body
        );
    }

    public function broadcast(string $title, string $body): int
    {
        Notification::query()->create([
            'user_id' => null,
            'subject' => $title,
            'message' => $body,
        ]);

        return $this->sendToSubscriptions(
            PushSubscription::query()->get(),
            $title,
            $body
        );
    }

    private function sendToSubscriptions($subscriptions, string $title, string $body): int
    {
        $sent = 0;

        foreach ($subscriptions as $subscription) {
            try {
                if ($this->send($subscription->token, $title, $body)) {
                    $sent++;
                }
            } catch (Throwable $exception) {
                // Push delivery must never break the underlying game/wallet action.
                Log::warning('Push notification delivery failed.', [
                    'subscription_id' => $subscription->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function send(string $token, string $title, string $body): bool
    {
        $projectId = config('services.fcm.project_id');
        $serviceAccountJson = config('services.fcm.service_account_json');

        if (!$projectId || !$serviceAccountJson) {
            Log::notice('FCM is not configured; push delivery skipped.');
            return false;
        }

        $accessToken = $this->accessToken($serviceAccountJson);
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data' => ['url' => '/notifications'],
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id' => 'default',
                            'sound' => 'notification_tune',
                        ],
                    ],
                    'apns' => [
                        'payload' => ['aps' => ['sound' => 'default']],
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon' => '/icons/icon-192.webp',
                            'badge' => '/favicon.ico',
                            'requireInteraction' => false,
                        ],
                        'fcm_options' => ['link' => 'https://galidisawar.com/notifications'],
                    ],
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        $errorCode = $response->json('error.details.0.errorCode');
        if ($errorCode === 'UNREGISTERED') {
            PushSubscription::query()->where('token_hash', hash('sha256', $token))->delete();
        }

        Log::warning('FCM rejected a push message.', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return false;
    }

    private function accessToken(string $serviceAccountJson): string
    {
        return Cache::remember('fcm.oauth_access_token', now()->addMinutes(50), function () use ($serviceAccountJson) {
            $account = json_decode($serviceAccountJson, true, flags: JSON_THROW_ON_ERROR);
            $now = time();
            $encode = static fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
            $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $encode(json_encode([
                'iss' => $account['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsigned = $header.'.'.$claims;

            if (!openssl_sign($unsigned, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Unable to sign FCM service-account assertion.');
            }

            $assertion = $unsigned.'.'.$encode($signature);
            $response = Http::asForm()->post(
                $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $assertion,
                ]
            )->throw();

            return (string) $response->json('access_token');
        });
    }
}
