<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Traits\ElasticsearchConnector;

class Searcher extends Controller
{
    use ElasticsearchConnector;

	function searchAll() {
		$query_parameters = '
		{
			
		}
		';
		return $this->queryElasticsearch('get', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1/_search', $query_parameters, function($query_result) {
			return $query_result;
		});
	}

	function searchByDescription(Request $request) {
		$query_parameters = '
		{
			"query": {
				"multi_match": {
					"query": "' . $request->query('query_parameter_search') . '",
					"fields": ["description", "description.stemmed"]
				}
			}
		}
		';
		return $this->queryElasticsearch('get', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1/_search', $query_parameters, function($query_result) {
			return $query_result;
		});
	}

}
