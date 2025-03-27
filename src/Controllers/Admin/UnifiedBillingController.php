<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UnifiedBillingController
{
    public function index(Request $request) {
        return view('limonlabs/bigcommerce::admin.unified-billing.index');
    }

    public function store(Request $request) {
        $partnerUuid = 'bce7bf5e-7978-41ce-aa3f-3720d07d568b';
        $storeHash = 'oxcsttsg8k';
        $appId = 57592;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => 'c28s58hui6cu5gt8xsvu0qkvn82zb8g'
        ])->post('https://api.bigcommerce.com/accounts/' . $partnerUuid . '/graphql', [
            'query' => 'mutation($checkout:CreateCheckoutInput!){checkout{createCheckout(input:$checkout){checkout{id accountId status checkoutUrl items(first:1){edges{node{subscriptionId status product{id type productLevel}scope{id type}pricingPlan{interval price{value currencyCode}trialDays}redirectUrl description}}}}}}}',
            'variables' => [
                "checkout" => [
                      "accountId" => "bc/account/account/" . $partnerUuid, 
                      "items" => [
                         [
                            "product" => [
                               "type" => "APPLICATION", 
                               "id" => "bc/account/product/" . $appId, 
                               "productLevel" => "Standard Plan" 
                            ], 
                            "scope" => [
                                  "type" => "STORE", 
                                  "id" => "bc/account/scope/" . $storeHash 
                               ], 
                            "description" => "HotJar", 
                            "pricingPlan" => [
                                     "interval" => "MONTH", 
                                     "price" => [
                                        "value" => 10, 
                                        "currencyCode" => "USD" 
                                     ], 
                                     "trialDays" => 14 
                                  ], 
                            "redirectUrl" => "https://store-". $storeHash .".mybigcommerce.com/manage/app/". $appId ."/upgrade_success" 
                         ] 
                      ] 
                   ] 
             ]
        ]);

        return $response->json();
    }
}
