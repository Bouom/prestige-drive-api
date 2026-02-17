<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AppSettingResource;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class SystemSettingsController extends BaseController
{
    /**
     * Display a listing of all settings.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AppSetting::query();

        if ($request->filled('group')) {
            $query->where('group_name', $request->group);
        }

        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $settings = $query->orderBy('group_name')->orderBy('key')->paginate(50);

        return AppSettingResource::collection($settings);
    }

    /**
     * Display the specified setting.
     */
    public function show(string $key): JsonResponse
    {
        $setting = AppSetting::where('key', $key)->firstOrFail();

        return $this->sendResponse(
            new AppSettingResource($setting),
            'Paramètre récupéré avec succès.'
        );
    }

    /**
     * Store a new setting.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:app_settings',
            'value' => 'required',
            'type' => ['required', Rule::in(['string', 'integer', 'float', 'boolean', 'json', 'array'])],
            'description' => 'nullable|string',
            'group_name' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        $setting = new AppSetting($validated);
        $setting->setTypedValue($request->value);
        $setting->save();

        return $this->sendResponse(
            new AppSettingResource($setting),
            'Paramètre créé avec succès.'
        );
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $setting = AppSetting::where('key', $key)->firstOrFail();

        $validated = $request->validate([
            'value' => 'required',
            'type' => ['sometimes', Rule::in(['string', 'integer', 'float', 'boolean', 'json', 'array'])],
            'description' => 'nullable|string',
            'group_name' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        if (isset($validated['type'])) {
            $setting->type = $validated['type'];
        }

        $setting->setTypedValue($request->value);

        if (isset($validated['description'])) {
            $setting->description = $validated['description'];
        }

        if (isset($validated['group_name'])) {
            $setting->group_name = $validated['group_name'];
        }

        if (isset($validated['is_public'])) {
            $setting->is_public = $validated['is_public'];
        }

        $setting->save();

        return $this->sendResponse(
            new AppSettingResource($setting),
            'Paramètre mis à jour avec succès.'
        );
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = AppSetting::where('key', $key)->firstOrFail();

        $setting->delete();

        return $this->sendResponse([], 'Paramètre supprimé avec succès.');
    }

    /**
     * Get settings by group.
     */
    public function byGroup(string $group): JsonResponse
    {
        $settings = AppSetting::getByGroup($group);

        return $this->sendResponse([
            'group' => $group,
            'settings' => $settings,
        ], 'Paramètres du groupe récupérés avec succès.');
    }

    /**
     * Get all public settings.
     */
    public function public(): JsonResponse
    {
        $settings = AppSetting::getAll(true);

        return $this->sendResponse(
            ['settings' => $settings],
            'Paramètres publics récupérés avec succès.'
        );
    }

    /**
     * Get all groups.
     */
    public function groups(): JsonResponse
    {
        $groups = AppSetting::selectRaw('group_name, COUNT(*) as count')
            ->whereNotNull('group_name')
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->get()
            ->pluck('count', 'group_name');

        return $this->sendResponse(
            ['groups' => $groups],
            'Groupes de paramètres récupérés avec succès.'
        );
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request): JsonResponse
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

        return $this->sendResponse([
            'updated_count' => count($updated),
            'updated_keys' => $updated,
        ], 'Paramètres mis à jour avec succès.');
    }

    /**
     * Initialize default settings.
     */
    public function initializeDefaults(): JsonResponse
    {
        $defaults = [
            ['key' => 'app.name', 'value' => 'LCP VTC', 'type' => 'string', 'group_name' => 'general', 'description' => 'Nom de l\'application', 'is_public' => true],
            ['key' => 'app.tagline', 'value' => 'Louer un Chauffeur Prestige', 'type' => 'string', 'group_name' => 'general', 'description' => 'Slogan de l\'application', 'is_public' => true],
            ['key' => 'app.email', 'value' => 'contact@lcp-vtc.fr', 'type' => 'string', 'group_name' => 'general', 'description' => 'E-mail de contact', 'is_public' => true],
            ['key' => 'app.phone', 'value' => '+33 1 23 45 67 89', 'type' => 'string', 'group_name' => 'general', 'description' => 'Téléphone de contact', 'is_public' => true],

            ['key' => 'ride.commission_rate', 'value' => '15', 'type' => 'float', 'group_name' => 'rides', 'description' => 'Taux de commission plateforme (%)', 'is_public' => false],
            ['key' => 'ride.cancellation_fee', 'value' => '5', 'type' => 'float', 'group_name' => 'rides', 'description' => 'Frais d\'annulation (EUR)', 'is_public' => false],
            ['key' => 'ride.max_distance_km', 'value' => '500', 'type' => 'integer', 'group_name' => 'rides', 'description' => 'Distance maximale de course (km)', 'is_public' => true],
            ['key' => 'ride.driver_search_radius_km', 'value' => '10', 'type' => 'integer', 'group_name' => 'rides', 'description' => 'Rayon de recherche chauffeur (km)', 'is_public' => false],

            ['key' => 'payment.currency', 'value' => 'EUR', 'type' => 'string', 'group_name' => 'payment', 'description' => 'Devise par défaut', 'is_public' => true],
            ['key' => 'payment.tax_rate', 'value' => '20', 'type' => 'float', 'group_name' => 'payment', 'description' => 'Taux de TVA (%)', 'is_public' => true],
            ['key' => 'payment.stripe_enabled', 'value' => 'true', 'type' => 'boolean', 'group_name' => 'payment', 'description' => 'Activer les paiements Stripe', 'is_public' => false],

            ['key' => 'driver.min_rating', 'value' => '4.0', 'type' => 'float', 'group_name' => 'driver', 'description' => 'Note minimale du chauffeur', 'is_public' => false],
            ['key' => 'driver.auto_assign', 'value' => 'true', 'type' => 'boolean', 'group_name' => 'driver', 'description' => 'Attribution automatique des chauffeurs', 'is_public' => false],

            ['key' => 'notification.email_enabled', 'value' => 'true', 'type' => 'boolean', 'group_name' => 'notification', 'description' => 'Activer les notifications par e-mail', 'is_public' => false],
            ['key' => 'notification.sms_enabled', 'value' => 'false', 'type' => 'boolean', 'group_name' => 'notification', 'description' => 'Activer les notifications SMS', 'is_public' => false],
        ];

        $created = 0;

        foreach ($defaults as $default) {
            if (! AppSetting::where('key', $default['key'])->exists()) {
                AppSetting::set($default['key'], $default['value'], $default['type']);

                $setting = AppSetting::where('key', $default['key'])->first();
                $setting->update([
                    'group_name' => $default['group_name'],
                    'description' => $default['description'],
                    'is_public' => $default['is_public'],
                ]);

                $created++;
            }
        }

        return $this->sendResponse([
            'created_count' => $created,
            'total_defaults' => count($defaults),
        ], 'Paramètres par défaut initialisés avec succès.');
    }
}
