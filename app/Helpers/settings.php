<?php

use App\Services\AppSettingsService;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(AppSettingsService::class)->value($key, $default);
    }
}

if (!function_exists('settings')) {
    function settings(?array $keys = null): array
    {
        $allSettings = app(AppSettingsService::class)->all();

        if ($keys === null) {
            return $allSettings;
        }

        return collect($keys)->mapWithKeys(function ($key) use ($allSettings) {
            return [$key => $allSettings[$key] ?? null];
        })->toArray();
    }
}
