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

	function createEcom1ElasticsearchIndex() {
		return $this->queryElasticsearch('put', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1', 
		'
		{
			"settings": {
			  "analysis": {
				"filter": {
				  "french_elision": {
					"type": "elision",
					"articles_case": true,
					"articles": ["l", "m", "t", "qu", "n", "s", "j", "d", "c", "jusqu", "quoiqu", "lorsqu", "puisqu"]
				  },
				  "french_synonym": {
					"type": "synonym",
					"ignore_case": true,
					"expand": true,
					"synonyms": [
					  "salade, laitue",
					  "mayo, mayonnaise",
					  "grille, toaste"
					]
				  },
				  "french_stemmer": {
					"type": "stemmer",
					"language": "light_french"
				  }
				},
				"analyzer": {
				  "french_heavy": {
					"tokenizer": "icu_tokenizer",
					"filter": [
					  "french_elision",
					  "icu_folding",
					  "french_synonym",
					  "french_stemmer"
					]
				  },
				  "french_light": {
					"tokenizer": "icu_tokenizer",
					"filter": [
					  "french_elision",
					  "icu_folding"
					]
				  }
				}
			  }
			}
		  }		
		');
	}

	function createElasticsearchIndexMapping() {
		return $this->queryElasticsearch('put', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1/_mapping', 
		'
		{
			  "properties": {
				"description": {
				  "type": "text",
				  "analyzer": "french_light",
				  "fields": {
					"stemmed": {
					  "type": "text",
					  "analyzer": "french_heavy"
					}
				  }
				}
			  }
		}
		');
	}

	function testFillElasticSearch() {
		$data_for_elasticsearch = '';
		foreach(AkeneoProduct::all() as $akeneo_product) {
			$data_for_elasticsearch .= "{\"index\": {}}\n";
			$data_for_elasticsearch .= json_encode([
					'code' => $akeneo_product->code, 
					'reference' => $akeneo_product->reference, 
					'name' => $akeneo_product->name, 
					'type' => $akeneo_product->type,
					'description' => $akeneo_product->description,
				]) . "\n";
		}

		$res = $this->queryElasticsearch('post', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1/_bulk?pretty', $data_for_elasticsearch);
		var_dump($res);
		
	}
    
}
