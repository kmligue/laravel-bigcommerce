<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Limonlabs\Bigcommerce\Models\StoreInfo;
use Limonlabs\Bigcommerce\Models\Webhook;
use Limonlabs\Bigcommerce\Models\AppExtension;
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
            $store_info = StoreInfo::where('user_id', $request->session()->get('user_id'))->first();
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

                $id = str_replace('stores/', '', $data['context']);

                $tenant = \Limonlabs\Bigcommerce\Models\Tenant::where('id', $id)->first();
                $domain = null;

                if ($tenant) {
                    $tenant->update([
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $this->getStoreTimezone($data['context'], $data['access_token'])
                    ]);
                } else {
                    $tenant = \Limonlabs\Bigcommerce\Models\Tenant::create([
                        'id' => $id,
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_id' => $data['user']['id'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $this->getStoreTimezone($data['context'], $data['access_token']),
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

            $this->installScripts($data);
            $this->installWebhooks($tenant);

            if (auth()->check()) {
                Auth::logout();
            }

            $loggedInUser = Auth::guard('tenant')->loginUsingId($tenant->id);

            if (!$loggedInUser) {
                abort(404);
            }

            // return view('limonlabs/bigcommerce::overview.index', compact('storeHash'));
            return redirect('/'. $id .'/overview')->tenant($domain);
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

        $id = str_replace('stores/', '', $verifiedSignedRequestData['context']);
        $tenant = \Limonlabs\Bigcommerce\Models\Tenant::where('id', $id)->first();

        if ($tenant) {
            $user_id = $verifiedSignedRequestData['user']['id'];
            $storeHash = $verifiedSignedRequestData['context'];

            if (auth()->check()) {
                Auth::logout();
            }

            $loggedInUser = Auth::guard('tenant')->loginUsingId($tenant->id);

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

            return redirect('/'. $id .'/overview?' . http_build_query($params));
        } else {
            return redirect('error')->with('error', 'Store not found.');
        }
    }

    private function getStoreTimezone($store_hash, $access_token) {
        $client = new Client();
        $result = $client->request('GET', 'https://api.bigcommerce.com/'. $store_hash .'/v2/store', [
            'headers' => [
                'X-Auth-Token'  => $access_token,
                'Content-Type'  => 'application/json',
            ]
        ]);

        $statusCode = $result->getStatusCode();

        if ($statusCode == 200) {
            $data = json_decode($result->getBody(), true);

            if (isset($data['timezone']) && isset($data['timezone']['name'])) {
                return $data['timezone']['name'];
            }
        }

        return '';
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

                $id = str_replace('stores/', '', $verifiedSignedRequestData['context']);
                $tenant = \Limonlabs\Bigcommerce\Models\Tenant::where('id', $id)->first();

                if ($tenant) {
                    $this->uninstallAppExtension($tenant);
                    $this->uninstallWebhooks($tenant);
                }
            } else {
                return redirect('error')->with('error', 'The signed request from BigCommerce could not be validated.');
            }
        } else {
            return redirect('error')->with('error', 'The signed request from BigCommerce was empty.');
        }
    }

    protected function uninstallAppExtension($tenant) {
        $tenant->run(function() use ($tenant) {
            $extensions = AppExtension::get();

            if ($extensions->count() > 0) {
                foreach ($extensions as $extension) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $tenant->access_token
                    ])->post('https://api.bigcommerce.com/'. $tenant->store_hash .'/graphql', [
                        'query' => 'mutation AppExtension($input: DeleteAppExtensionInput!) {  appExtension {    deleteAppExtension(input: $input) {      deletedAppExtensionId    }  }}',
                        'variables' => [
                            'input' => [
                                'id' => $extension->app_extension_id
                            ]
                        ]
                    ]);

                    if ($response->successful()) {
                        $extension->delete();
                    }
                }
            }
        });
    }

    protected function installAppExtension($tenant) {
        $tenant->run(function() use ($tenant) {
            $extension = AppExtension::get();

            if ($extension->count() == 0) {
                $appExtensions = config('app-extensions');

                foreach ($appExtensions as $appExtension) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $tenant->access_token
                    ])->post('https://api.bigcommerce.com/'. $tenant->store_hash .'/graphql', $appExtension);

                    if ($response->successful()) {
                        $json = $response->json();

                        AppExtension::create([
                            'app_extension_id' => $json['data']['appExtension']['createAppExtension']['appExtension']['id']
                        ]);
                    }
                }
            }
        });
    }

    protected function uninstallWebhooks($tenant) {
        $tenant->run(function() use ($tenant) {
            $webhooks = Webhook::get();

            if ($webhooks->count() > 0) {
                foreach ($webhooks as $webhook) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $tenant->access_token
                    ])->delete('https://api.bigcommerce.com/'. $tenant->store_hash .'/v3/hooks/'. $webhook->webhook_id);

                    if ($response->successful()) {
                        $webhook->delete();
                    }
                }
            }
        });
    }

    protected function installWebhooks($tenant) {
        $tenant->run(function() use ($tenant) {
            $webhook = Webhook::get();

            if ($webhook->count() == 0) {
                $hooks = config('webhooks');

                foreach ($hooks as $hook) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Auth-Token' => $tenant->access_token
                    ])->post('https://api.bigcommerce.com/'. $tenant->store_hash .'/v3/hooks', $hook);

                    if ($response->successful()) {
                        $json = $response->json();

                        Webhook::create([
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
        });
    }

    protected function installScripts($data) {
        $scripts = config('scripts.scripts');

        $styles = config('scripts.scripts');

        $client = new Client();

        foreach ($styles as $style) {
            $client->request('POST', 'https://api.bigcommerce.com/'. $data['context'] .'/v3/content/scripts', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => $data['access_token']
                ],
                'json' => [
                    'name' => $style['name'],
                    'description' => $style['description'],
                    'html' => $style['html'],
                    'auto_uninstall' => true,
                    'load_method' => 'default',
                    'location' => 'head',
                    'visibility' => 'all_pages',
                    'kind' => 'script_tag',
                    'consent_category' => 'essential'
                ]
            ]);
        }

        foreach ($scripts as $script) {
            $client->request('POST', 'https://api.bigcommerce.com/'. $data['context'] .'/v3/content/scripts', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => $data['access_token']
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
                    'consent_category' => 'essential'
                ]
            ]);
        }
    }
}
