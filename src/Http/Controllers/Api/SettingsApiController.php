<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsApiController
{
    public function index(Request $request, string $storeHash): JsonResponse
    {
        $settings = tenant()->settings ?? [];

        $keys = $request->query('keys');
        if ($keys !== null && $keys !== '') {
            $keyList = array_filter(array_map('trim', explode(',', (string) $keys)));
            $filtered = [];

            foreach ($keyList as $key) {
                if (array_key_exists($key, $settings)) {
                    $filtered[$key] = $settings[$key];
                }
            }

            return response()->json($filtered);
        }

        return response()->json($settings);
    }

    public function store(Request $request, string $storeHash): JsonResponse
    {
        $data = $request->all();

        if ($data === [] || ! $this->isAssociativeArray($data)) {
            return response()->json([
                'message' => 'Request body must be a JSON object with key-value pairs.',
            ], 422);
        }

        $tenant = tenant();
        $current = $tenant->settings ?? [];

        $tenant->update([
            'settings' => array_merge($current, $data),
        ]);

        return response()->json($data);
    }

    private function isAssociativeArray(array $data): bool
    {
        return array_keys($data) !== range(0, count($data) - 1);
    }
}
