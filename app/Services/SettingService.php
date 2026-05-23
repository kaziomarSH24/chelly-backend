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
        // Cache settings for 24 hours to improve performance on frontend
        return Cache::remember('global_settings', 86400, function () {
            return Setting::pluck('value', 'key')->toArray();
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
        }
        Cache::forget('global_settings');
    }
}
