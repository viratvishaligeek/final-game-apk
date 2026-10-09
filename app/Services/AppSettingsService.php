<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AppSettingsService
{
    private const CACHE_KEY = 'app_settings';

    public function all(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(5),
            fn() => Setting::query()
                ->pluck('value', 'option')
                ->toArray()
        );
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function get(string $key, float $default = 0): float
    {
        return (float) ($this->all()[$key] ?? $default);
    }

    public function validateRange(
        float $amount,
        string $minimumKey,
        string $maximumKey,
        string $field = 'amount'
    ): void {
        $minimum = $this->get($minimumKey);
        $maximum = $this->get($maximumKey);

        if ($amount < $minimum) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => ["Minimum allowed amount is {$minimum}."],
            ]);
        }

        if ($maximum > 0 && $amount > $maximum) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => ["Maximum allowed amount is {$maximum}."],
            ]);
        }
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function save(array $settings): void
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $option => $value) {
                Setting::updateOrCreate(
                    ['option' => $option],
                    ['value' => $value]
                );
            }
        });

        $this->forgetCache();
    }
}
