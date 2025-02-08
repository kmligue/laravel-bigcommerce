<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Limonlabs\Bigcommerce\Models\StoreInfo;
use Limonlabs\Bigcommerce\Models\Webhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BigcommerceController
{
    protected $baseURL;

    public function __construct()
    {
        $this->baseURL = config('app.url');
    }

    private function getAppClientId() {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_client_id');
        } else {
            return config('bigcommerce.bc_app_client_id');
        }
    }

    private function getAppSecret(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_secret');
        } else {
            return config('bigcommerce.bc_app_secret');
        }
    }

    private function getAccessToken(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_access_token');
        } else {
            $store_info = tenant_class()::where('user_id', $request->session()->get('user_id'))->first();
            if ($store_info) {
                return $store_info->access_token;
            }

            return $request->session()->get('access_token');
        }
    }

    private function getStoreHash(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_store_hash');
        } else {
            return $request->session()->get('store_hash');
        }
    }

    public function install(Request $request)
    {
        // Make sure all required query params have been passed
        if (!$request->has('code') || !$request->has('scope') || !$request->has('context')) {
            return redirect('error')->with('error_message', 'Not enough information was passed to install this app.');
        }

        try {
            $client = new Client();
            $result = $client->request('POST', 'https://login.bigcommerce.com/oauth2/token', [
                'json' => [
                    'client_id' => $this->getAppClientId(),
                    'client_secret' => $this->getAppSecret($request),
                    'redirect_uri' => $this->baseURL . '/auth/install',
                    'grant_type' => 'authorization_code',
                    'code' => $request->input('code'),
                    'scope' => $request->input('scope'),
                    'context' => $request->input('context'),
                ]
            ]);

            $statusCode = $result->getStatusCode();
            $data = json_decode($result->getBody(), true);

            if ($statusCode == 200) {
                $request->session()->put('store_hash', $data['context']);
                $request->session()->put('access_token', $data['access_token']);
                $request->session()->put('user_id', $data['user']['id']);
                $request->session()->put('user_email', $data['user']['email']);

                $store_info = tenant_class()::where('store_hash', $data['context'])->first();
                $store_data = $this->getStoreInfo($data['context'], $data['access_token']);
                $timezone = '';

                if (isset($store_data['timezone']) && isset($store_data['timezone']['name'])) {
                    $timezone = $store_data['timezone']['name'];
                }

                if ($store_info) {
                    $store_info->update([
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $timezone
                    ]);
                } else {
                    $store_info = tenant_class()::create([
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_id' => $data['user']['id'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $timezone
                    ]);
                }

                // If the merchant installed the app via an external link, redirect back to the
                // BC installation success page for this app
                if ($request->has('external_install')) {
                    return redirect('https://login.bigcommerce.com/app/' . $this->getAppClientId() . '/install/succeeded');
                }
            }

            $user_id = $data['user']['id'];
            $storeHash = $data['context'];

            $this->installScripts($store_info);
            $this->installWebhooks($store_info);

            if (auth()->check()) {
                Auth::logout();
            }

            $loggedInUser = Auth::guard('store_info')->loginUsingId($store_info->id);

            if (!$loggedInUser) {
                abort(404);
            }

            return redirect(get_install_redirect($storeHash));
        } catch (\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = "An error occurred.";

            if ($e->hasResponse()) {
                if ($statusCode != 500) {
                    dd($e->getMessage());
                    // $errorMessage = Psr7\str($e->getResponse());
                    $errorMessage = $e->getMessage();
                }
            }

            // If the merchant installed the app via an external link, redirect back to the
            // BC installation failure page for this app
            if ($request->has('external_install')) {
                return redirect('https://login.bigcommerce.com/app/' . $this->getAppClientId() . '/install/failed');
            } else {
                return redirect('error')->with('error', $errorMessage);
            }
        }
    }

    public function load(Request $request)
    {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                $request->session()->put('user_id', $verifiedSignedRequestData['user']['id']);
                $request->session()->put('user_email', $verifiedSignedRequestData['user']['email']);
                $request->session()->put('owner_id', $verifiedSignedRequestData['owner']['id']);
                $request->session()->put('owner_email', $verifiedSignedRequestData['owner']['email']);
                $request->session()->put('store_hash', $verifiedSignedRequestData['context']);
            } else {
                return redirect('error')->with('error', 'The signed request from BigCommerce could not be validated.');
            }
        } else {
            return redirect('error')->with('error', 'The signed request from BigCommerce was empty.');
        }

        $store_info = tenant_class()::where('store_hash', $verifiedSignedRequestData['context'])->first();

        if ($store_info) {
            $user_id = $verifiedSignedRequestData['user']['id'];
            $storeHash = $verifiedSignedRequestData['context'];

            if (auth()->check()) {
                Auth::logout();
            }

            $loggedInUser = Auth::guard('store_info')->loginUsingId($store_info->id);

            if (!$loggedInUser) {
                abort(404);
            }

            $params = [];

            if ($request->has('action') && $request->get('action') == 'upgrade') {
                $params['action'] = 'upgrade';
            }

            if ($request->has('success')) {
                $params['success'] = $request->get('success');
            }

            return redirect(get_load_redirect($storeHash) . '?' . http_build_query($params));
        } else {
            return redirect('error')->with('error', 'Store not found.');
        }
    }

    private function getStoreInfo($store_hash, $access_token) {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $access_token
        ])->get('https://api.bigcommerce.com/'. $store_hash .'/v2/store');

        if ($response->ok()) {
            $json = $response->json();

            return $json;
        }

        return [];
    }

    private function verifySignedRequest($signedRequest, $appRequest)
    {
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);

        // confirm the signature
        $expectedSignature = hash_hmac('sha256', $jsonStr, $this->getAppSecret($appRequest), $raw = false);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');
            return null;
        }

        return $data;
    }

    public function makeBigCommerceAPIRequest(Request $request, $endpoint)
    {
        $requestConfig = [
            'headers' => [
                'X-Auth-Client' => $this->getAppClientId(),
                'X-Auth-Token'  => $this->getAccessToken($request),
                'Content-Type'  => 'application/json',
            ]
        ];

        if ($request->method() === 'PUT') {
            $requestConfig['body'] = $request->getContent();
        }

        $client = new Client();
        $result = $client->request($request->method(), 'https://api.bigcommerce.com/' . $this->getStoreHash($request) . '/' . $endpoint, $requestConfig);
        return $result;
    }

    public function proxyBigCommerceAPIRequest(Request $request, $endpoint)
    {
        if (strrpos($endpoint, 'v2') !== false) {
            // For v2 endpoints, add a .json to the end of each endpoint, to normalize against the v3 API standards
            $endpoint .= '.json';
        }

        $result = $this->makeBigCommerceAPIRequest($request, $endpoint);

        return response($result->getBody(), $result->getStatusCode())->header('Content-Type', 'application/json');
    }

    public function uninstall(Request $request) {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                $request->session()->put('user_id', $verifiedSignedRequestData['user']['id']);
                $request->session()->put('user_email', $verifiedSignedRequestData['user']['email']);
                $request->session()->put('owner_id', $verifiedSignedRequestData['owner']['id']);
                $request->session()->put('owner_email', $verifiedSignedRequestData['owner']['email']);
                $request->session()->put('store_hash', $verifiedSignedRequestData['context']);
            } else {
                return redirect('error')->with('error', 'The signed request from BigCommerce could not be validated.');
            }
        } else {
            return redirect('error')->with('error', 'The signed request from BigCommerce was empty.');
        }
    }

    protected function installWebhooks($store) {
        if ($store->webhooks->count() == 0) {
            $hooks = config('webhooks');
            $storeHash = str_replace('stores/', '', $store->store_hash);

            foreach ($hooks as $hook) {
                foreach ($hook as $key => $value) {
                    $hook[$key] = str_replace('{storeHash}', $storeHash, $value);
                }

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => $store->access_token
                ])->post('https://api.bigcommerce.com/'. $store->store_hash .'/v3/hooks', $hook);
    
                if ($response->successful()) {
                    $json = $response->json();
    
                    Webhook::create([
                        'store_id' => $store->id,
                        'webhook_id' => $json['data']['id'],
                        'client_id' => $json['data']['client_id'],
                        'store_hash' => $json['data']['store_hash'],
                        'webhook_created_at' => $json['data']['created_at'],
                        'webhook_updated_at' => $json['data']['updated_at'],
                        'scope' => $json['data']['scope'],
                        'destination' => $json['data']['destination'],
                        'is_active' => $json['data']['is_active'],
                        'headers' => $json['data']['headers']
                    ]);
                }
            }
        }
    }

    protected function installScripts($store_info) {
        $scripts = config('scripts.scripts');
        $styles = config('scripts.styles');
        $storeHash = str_replace('stores/', '', $store_info->store_hash);

        $client = new Client();

        foreach ($styles as $style) {
            foreach ($style as $key => $value) {
                $style[$key] = str_replace('{storeHash}', $storeHash, $value);
            }

            foreach ($store_info->channels as $channel) {
                $client->request('POST', 'https://api.bigcommerce.com/'. $store_info->store_hash .'/v3/content/scripts', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $store_info->access_token
                    ],
                    'json' => [
                        'name' => $style['name'],
                        'description' => $style['description'],
                        'html' => $style['html'],
                        'auto_uninstall' => true,
                        'load_method' => 'default',
                        'location' => isset($style['location']) ? $style['location'] : 'head',
                        'visibility' => 'all_pages',
                        'kind' => 'script_tag',
                        'consent_category' => 'essential',
                        'channel_id' => $channel['id']
                    ]
                ]);
            }
        }

        foreach ($scripts as $script) {
            foreach ($script as $key => $value) {
                $script[$key] = str_replace('{storeHash}', $storeHash, $value);
            }

            foreach ($store_info->channels as $channel) {
                $client->request('POST', 'https://api.bigcommerce.com/'. $store_info->store_hash .'/v3/content/scripts', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $store_info->access_token
                    ],
                    'json' => [
                        'name' => $script['name'],
                        'description' => $script['description'],
                        'src' => $script['src'],
                        'auto_uninstall' => true,
                        'load_method' => 'default',
                        'location' => isset($script['location']) ? $script['location'] : 'footer',
                        'visibility' => 'all_pages',
                        'kind' => 'src',
                        'consent_category' => 'essential',
                        'channel_id' => $channel['id']
                    ]
                ]);
            }
        }
    }
}
