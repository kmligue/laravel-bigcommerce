<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsApiController
{
    public function index(Request $request, string $storeHash): JsonResponse
    {
        $settings = $this->settingsForResponse(tenant()->settings);

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

    public function show(Request $request, string $storeHash, string $key): JsonResponse
    {
        $settings = $this->settingsForResponse(tenant()->settings);

        if (! array_key_exists($key, $settings)) {
            return response()->json([
                'message' => "Setting '{$key}' not found.",
            ], 404);
        }

        return response()->json([
            $key => $settings[$key],
        ]);
    }

    public function update(Request $request, string $storeHash, string $key): JsonResponse
    {
        $content = $request->getContent();

        if ($content === '' || $content === false) {
            return response()->json([
                'message' => 'Request body must be a JSON value.',
            ], 422);
        }

        $value = json_decode($content, true);

        if ($value === null && strtolower(trim($content)) !== 'null') {
            return response()->json([
                'message' => 'Request body must be valid JSON.',
            ], 422);
        }

        $settings = $this->settingsForResponse(tenant()->settings);
        $settings[$key] = $value;

        tenant()->update([
            'settings' => $settings,
        ]);

        return response()->json([
            $key => $value,
        ]);
    }

    public function store(Request $request, string $storeHash): JsonResponse
    {
        $data = $this->requestPayload($request);

        if ($data === [] || ! $this->isAssociativeArray($data)) {
            return response()->json([
                'message' => 'Request body must be a JSON object with key-value pairs.',
            ], 422);
        }

        tenant()->update([
            'settings' => $data,
        ]);

        return response()->json($data);
    }

    /**
     * Read only the JSON request body. BigcommerceStoreAuth merges `tenant`
     * into the request input, which must not be persisted as a setting.
     */
    private function requestPayload(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '' || $content === false) {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>|null  $settings
     * @return array<string, mixed>
     */
    private function settingsForResponse(?array $settings): array
    {
        $settings = $settings ?? [];
        unset($settings['tenant']);

        return $settings;
    }

    private function isAssociativeArray(array $data): bool
    {
        return array_keys($data) !== range(0, count($data) - 1);
    }
}
