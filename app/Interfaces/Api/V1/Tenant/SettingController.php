<?php

namespace App\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $group = $request->input('group');
            $query = Setting::query();

            // Filter by group if provided
            if ($group) {
                $query->where('group', $group);
            }

            // Filter out private settings for non-admin users
            if (!$request->user()->hasRole('admin')) {
                $query->where('is_private', false);
            }

            $settings = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $settings
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update settings.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'settings' => 'required|array',
                'settings.*.key' => 'required|string|exists:settings,key',
                'settings.*.value' => 'nullable',
            ]);

            $settings = $request->input('settings');
            $updatedSettings = [];

            foreach ($settings as $setting) {
                $settingModel = Setting::where('key', $setting['key'])->first();
                
                // Skip private settings for non-admin users
                if ($settingModel->is_private && !$request->user()->hasRole('admin')) {
                    continue;
                }
                
                $settingModel->value = $setting['value'];
                $settingModel->save();
                $updatedSettings[] = $settingModel;
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => $updatedSettings
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available setting groups.
     */
    public function groups(Request $request): JsonResponse
    {
        try {
            $query = Setting::select('group')->distinct();
            
            // Filter out private groups for non-admin users
            if (!$request->user()->hasRole('admin')) {
                $query->where('is_private', false);
            }
            
            $groups = $query->pluck('group')->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Setting groups retrieved successfully',
                'data' => $groups
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve setting groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
