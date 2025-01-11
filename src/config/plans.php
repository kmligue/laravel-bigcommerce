<?php

return [
	'bronze' =>  [
		'show' => true,
		'plan_id' => env('STRIPE_BRONZE_PLAN_ID', ''),
		'price' => 10,
		'features' => [
			'Customize text',
			'Customize colors'
		]
	],
	'silver' => [
		'show' => true,
		'plan_id' => env('STRIPE_SILVER_PLAN_ID', ''),
		'price' => 20,
		'features' => [
			'Custom css',
			'Free shipping indicator',
            'Suggested items'
		]
	],
	'gold' => [
		'show' => true,
		'plan_id' => env('STRIPE_GOLD_PLAN_ID', ''),
		'price' => 50,
		'features' => [
			'Custom templates',
			'Custom javascript',
            'Priority support'
		]
	]
];