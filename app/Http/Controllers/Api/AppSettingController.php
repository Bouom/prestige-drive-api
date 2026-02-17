<?php

namespace App\Http\Controllers\Api;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSettingController extends BaseController
{
    /**
     * Display a listing of settings.
     */
    public function index(Request $request): JsonResponse
    {
        $settings = AppSetting::getAll(publicOnly: ! $request->user()->isAdmin());

        return $this->sendResponse($settings, 'Paramètres récupérés avec succès.');
    }

    /**
     * Update settings (admin only).
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:app_settings,key',
            'settings.*.value' => 'required',
        ]);

        $updated = [];

        foreach ($request->settings as $settingData) {
            $setting = AppSetting::where('key', $settingData['key'])->first();

            if ($setting) {
                $setting->setTypedValue($settingData['value']);
                $setting->save();
                $updated[] = $setting->key;
            }
        }

        return $this->sendResponse(
            ['updated_keys' => $updated],
            'Paramètres mis à jour avec succès.'
        );
    }
}
