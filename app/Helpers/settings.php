<?php

use App\Services\AppSettingsService;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        static $settings = null;

        if ($settings === null) {
            $settings = app(AppSettingsService::class)->all();
        }

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('settings')) {
    function settings(?array $keys = null): array
    {
        static $allSettings = null;

        if ($allSettings === null) {
            $allSettings = app(AppSettingsService::class)->all();
        }

        if ($keys === null) {
            return $allSettings;
        }

        return collect($keys)->mapWithKeys(function ($key) use ($allSettings) {
            return [$key => $allSettings[$key] ?? null];
        })->toArray();
    }
}
