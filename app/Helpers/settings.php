<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        static $settings = null;
        if ($settings === null) {
            $settings = Setting::query()
                ->pluck('value', 'option')
                ->toArray();
        }
        if (!array_key_exists($key, $settings)) {
            return $default;
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('settings')) {
    function settings(?array $keys = null): array
    {
        static $allSettings = null;
        if ($allSettings === null) {
            $allSettings = Setting::query()
                ->pluck('value', 'option')
                ->toArray();
        }
        if ($keys === null) {
            return $allSettings;
        }
        return collect($keys)
            ->mapWithKeys(function ($key) use ($allSettings) {
                return [
                    $key => $allSettings[$key] ?? null,
                ];
            })
            ->toArray();
    }
}
