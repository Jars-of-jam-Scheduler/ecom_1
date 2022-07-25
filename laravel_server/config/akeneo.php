<?php

return [

	'authentication' => [
		'endpoint' 				=>	env('AKENEO_AUTHENTICATION_ENDPOINT'),
		'client_id' 			=> 	env('AKENEO_AUTHENTICATION_CLIENT_ID'),
		'secret' 				=> 	env('AKENEO_AUTHENTICATION_SECRET'),
		'grant_type' 			=> 	'password',
		'grant_type_refresh' 	=> 	'refresh_token',
		'username' 				=> 	env('AKENEO_AUTHENTICATION_USERNAME'),
		'password' 				=> 	env('AKENEO_AUTHENTICATION_PASSWORD')
	],

	'connections' => [

		'rest_api' => [
			'endpoint'	=>	env('AKENEO_REST_API_ENDPOINT')
		],

	]

];