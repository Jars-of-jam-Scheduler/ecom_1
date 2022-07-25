<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Test extends Controller
{
	use \App\Http\Traits\AkeneoConnector;
    
	function test() {

		$response = $this->getFromAkeneo(config('akeneo.connections.rest_api.endpoint') . '/products', [
			'pagination_type' => 'search_after',
			'limit' => 100

		], function($query_result) {

			do {

				$response_as_object = $query_result->object();
				var_dump($response_as_object->_links);
				
			} while(
				property_exists($response_as_object->_links, 'next') && $query_result = $this->getFromAkeneo($response_as_object->_links->next->href)
			);

		});

	}
    
}
