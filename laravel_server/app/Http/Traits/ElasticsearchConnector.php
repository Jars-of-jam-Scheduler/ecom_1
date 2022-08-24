<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use GuzzleHttp\Client;

use App\Exceptions\ElasticsearchQueryProblemException;
use App\Exceptions\ElasticsearchQueryUnknownProblemException;

trait ElasticsearchConnector {

	function queryElasticsearch($method_type, $request_uri, $data = NULL, $delegated_function = NULL) {
		$query_result = NULL;

		try {
			$client = new Client([
				'base_uri'  => config('elasticsearch.connections.rest_api.endpoint'),
				'verify'    => FALSE,
				'auth' => [config('elasticsearch.authentication.client_id'), config('elasticsearch.authentication.secret')]
			]);
			$request = $client->$method_type($request_uri, [
				'headers' => [
					'accept' =>  'application/json',
					'Content-Type' =>  'application/json',
				],
				'body' => $data
			]);
			$query_result = json_decode((string) $request->getBody());
		
			if(isset($delegated_function)) {
				$delegated_function($query_result);
			}

		} catch(\Illuminate\Http\Client\RequestException | \Illuminate\Http\Client\ConnectionException $e) {
			$error_code = 500;
			$error_message = __('elasticsearch.errors.connection_problem');

			if($e instanceof \Illuminate\Http\Client\RequestException) {
				switch($e->response->status()) {
					case 403:
						$error_code = 403;
						$error_message = __('elasticsearch.errors.forbidden');
						break;
					case 404:
						$error_code = 404;
						$error_message = __('elasticsearch.errors.not_found');
						break;
					case 429:
						$error_code = 429;
						$error_message = __('elasticsearch.errors.too_many_requests');
						break;
				}
			}

			report($e);  // In an outside-jobs execution context, top-catching these (important) exceptions could prevent them from being reported
			throw new ElasticsearchQueryProblemException($error_message, $error_code, $e);

		}
		catch(\League\Flysystem\UnableToWriteFile | \Exception $e) {
			report($e);
			throw new ElasticsearchQueryUnknownProblemException(__('elasticsearch.errors.unable_to_query_unknown_reason'), 500, $e);
		}

		return $query_result;
	}

}