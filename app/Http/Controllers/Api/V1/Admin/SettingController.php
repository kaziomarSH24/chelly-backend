<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    /**
     * Get all global settings
     */
    public function index()
    {
        $settings = $this->settingService->getAllSettings();
        return response_success('Settings retrieved successfully.', $settings);
    }

    /**
     * Get a specific setting by key
     */
    public function show(string $key)
    {
        $value = $this->settingService->getSettingByKey($key);

        if (is_null($value)) {
            return response_error('Setting not found.', [], 404);
        }

        return response_success('Setting retrieved successfully.', [
            $key => $value
        ]);
    }

    /**
     * Update settings
     */
    public function update(UpdateSettingsRequest $request)
    {
        $this->settingService->updateSettings($request->validated());
        return response_success('Settings updated successfully.');
    }
}
