<?php

return [

	'authentication' => [
		'client_id'		=> 	env('ELASTICSEARCH_CLIENT_ID'),
		'secret'		=> 	env('ELASTICSEARCH_SECRET'),
	],

	'connections' => [

		'rest_api' => [
			'endpoint' 	=>	env('ELASTICSEARCH_ENDPOINT'),
		],

	]

];