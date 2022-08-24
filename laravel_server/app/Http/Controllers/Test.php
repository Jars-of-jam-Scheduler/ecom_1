<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Exceptions\AkeneoQueryProblemException;
use App\Exceptions\AkeneoQueryUnknownProblemException;
use App\Models\AkeneoProduct;

use App\Notifications\AkeneoSynchronized;
use App\Models\User;

class Test extends Controller
{
	use \App\Http\Traits\AkeneoConnector;
	use \App\Http\Traits\ElasticsearchConnector;
    
	function test() {

		// try {
			$response = $this->getFromAkeneo(config('akeneo.connections.rest_api.endpoint') . '/products', [
				'pagination_type' => 'search_after',
				'limit' => 100

			], function($query_result) {

				do {

					$response_as_object = $query_result->object();
					var_dump($response_as_object->_links);
					exit;
					
				} while(
					property_exists($response_as_object->_links, 'next') && $query_result = $this->getFromAkeneo($response_as_object->_links->next->href)
				);

			});
	
		// } catch(AkeneoQueryUnknownProblemException | AkeneoQueryProblemException $e) {
		// 	echo $e->getMessage();
		// }

	}

	function test2() {
		$akeneo_products_to_insert_or_update = [];

        $result = $this->getFromAkeneo(config('akeneo.connections.rest_api.endpoint') . '/products', [
			'pagination_type' => 'search_after',
			'limit' => 100

		], function($query_result) {

			do {
				$response_as_object = $query_result->object();

				$akeneo_products_to_insert_or_update = array_map(fn ($akeneo_product) => [
					'code' => $akeneo_product->identifier,  
					'reference' => $akeneo_product->identifier,
					'name' => property_exists($akeneo_product->values, 'name') ? $akeneo_product->values->name[0]->data : NULL,
					'description' => property_exists($akeneo_product->values, 'description') ? $akeneo_product->values->description[0]->data : NULL, 
					'type' => property_exists($akeneo_product->values, 'name') && str_contains(strtolower($akeneo_product->values->name[0]->data), 'system') ? 'service' : 'simple_product'
				], $response_as_object->_embedded->items);

				AkeneoProduct::upsert($akeneo_products_to_insert_or_update, ['reference'], ['name', 'description', 'type']);
				
			} while(
				property_exists($response_as_object->_links, 'next') && $query_result = $this->getFromAkeneo($response_as_object->_links->next->href)
			);

		});

		$user = User::find(1);
		$user->notify(new AkeneoSynchronized());
	}

	function test_fill_elastic_search() {
		$data_for_elasticsearch = '';
		foreach(AkeneoProduct::all() as $akeneo_product) {
			$data_for_elasticsearch .= "{\"index\": {}}\n";
			$data_for_elasticsearch .= json_encode([
					'code' => $akeneo_product->code, 
					'reference' => $akeneo_product->reference, 
					'name' => $akeneo_product->name, 
					'type' => $akeneo_product->type
				]) . "\n";
		}

		$res = $this->queryElasticsearch('post', config('elasticsearch.connections.rest_api.endpoint') . '/products/_bulk?pretty', $data_for_elasticsearch);
		var_dump($res);
		
	}
    
}
