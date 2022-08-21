<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Traits\ElasticsearchConnector;

class Searcher extends Controller
{
    use ElasticsearchConnector;

	function search() {
		$result = $this->getFromElasticsearch(config('elasticsearch.connections.rest_api.endpoint') . '/customer/_search', NULL, function($query_result) {
			var_dump($query_result);
		});
	}

}
