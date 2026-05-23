<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
    }

    /**
     * Get all global settings
     */
    public function index()
    {
        $settings = $this->settingService->getAllSettings();
        return response_success('Settings retrieved successfully.', $settings);
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
