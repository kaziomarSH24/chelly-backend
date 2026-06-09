<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Retrieve all settings as a flat key-value array
     */
    public function getAllSettings(): array
    {
        return Cache::remember('global_settings', 86400, function () {

            $excludedKeys = [
                'privacy_policy',
                'terms_conditions',
                'payment_guidelines'
            ];

            return Setting::whereNotIn('key', $excludedKeys)
                ->pluck('value', 'key')
                ->toArray();
        });
    }


    /**
     * Retrieve a specific setting by its key
     */
    public function getSettingByKey(string $key)
    {
        return Cache::remember("setting_{$key}", 86400, function () use ($key) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : null;
        });
    }

    /**
     * Update or create multiple settings at once
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                Setting::where('key', $key)->delete();
            } else {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            // Clear the specific cache for this individual key
            Cache::forget("setting_{$key}");
        }

        // Keep this if you have another method that caches all settings together
        Cache::forget('global_settings');
    }
}
