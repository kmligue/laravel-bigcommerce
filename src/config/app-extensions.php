<?php

return [
    // [
    //     'query' => 'mutation AppExtension($input: CreateAppExtensionInput!) {  appExtension {    createAppExtension(input: $input) {      appExtension {        id        context        label {          defaultValue          locales {            value            localeCode          }        }        model        url      }    }  }}',
    //     'variables' => [
    //         'input' => [
    //             'context' => 'PANEL',
    //             'model' => 'ORDERS',
    //             'url' => '/orders/${id}/notes',
    //             'label' => [
    //                 'defaultValue' => 'Order Notes',
    //                 'locales' => [
    //                     [
    //                         'value' => 'Order Notes',
    //                         'localeCode' => 'en-US'
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]
    // ]
];